<?php

class MlmSoftApi {

    const DOCUMENT_POLLING_MAX_ITERATIONS = 20;
    const DOCUMENT_POLLING_TIMEOUT = 500000;
    const APP_ID = 1;

    protected $mlm_soft_project_url;
    protected $api_token;

    public function __construct($mlm_soft_project_url, $api_token) {
        $this->mlm_soft_project_url = $mlm_soft_project_url;
        $this->api_token = $api_token;
    }

    private function getDocumentResult($documentId)
    {
        for($i=1; $i<=self::DOCUMENT_POLLING_MAX_ITERATIONS; $i++) {
            usleep(self::DOCUMENT_POLLING_TIMEOUT);
            $response = $this->execGet('/api2/online-office/document/get', array('id' => $documentId));
            $response = (new MlmSoftApiResponse($response));
            $payload = $response->getPrimaryPayload();
            $context = $payload->document->context;

            $logVariables = array(
                '%id' => $documentId,
                '%iteration' => $i,
                '%action' => $context->entity . '\\' . $context->action,
                '%result' => !empty($payload->document->result) ? 'received' : 'none',
            );
            if ($response->isPrimarySuccess()) {
                error_log(
                    'Got result for document '
                    . print_r($logVariables, true)
                );
            }
            else {
                error_log(
                    'Error getting result for document '
                    . print_r($logVariables + $response->getStdErrorLogParams(), true)
                );
            }
            if (!empty($payload->document->result)) {
                $result = $payload->document->result;
                return $result;
            }
        }
        return $this->getEmptyResponse(['code' => -1, 'error' => 'Document ' . $documentId . ' not processed']);
    }

    private function sendRequest($url, $params)
    {
        $reqRes = wp_remote_request($url, $params);
        error_log(
            'api call ' . $url . ' ' . print_r($params, true) . print_r($reqRes, true)
        );
        $reqRes = $this->amendInvalidResponse($reqRes);
        $payload = (new MlmSoftApiResponse($reqRes))->getPrimaryPayload();
        if (!empty($payload->queueRequest) && !empty($payload->documentId)) {
            $reqRes = $this->getDocumentResult((int)$payload->documentId);
        }
        return $reqRes;
    }

    public function execGet($endPoint, array $params = array()) {
        $url = $this->mlm_soft_project_url . $endPoint . (count($params) > 0 ? ('?' . http_build_query($params)) : '');
        $params = array(
            'headers' => $this->getHeaders($params),
            'method' => 'GET',
        );
        $reqRes = $this->sendRequest($url, $params);
        return $reqRes;
    }

    public function execPost($endPoint, array $params = array()) {
        $url = $this->mlm_soft_project_url . $endPoint;
        $params = array(
            'headers' => $this->getHeaders($params),
            'method' => 'POST',
            'body' => json_encode($params)
        );
        $reqRes = $this->sendRequest($url, $params);
        return $reqRes;
    }

    public function execDelete($endPoint, array $params = array()) {
        $url = $this->mlm_soft_project_url . $endPoint;
        $params = array(
            'headers' => $this->getHeaders($params),
            'method' => 'DELETE',
            'body' => json_encode($params)
        );
        $reqRes = $this->sendRequest($url, $params);
        return $reqRes;
    }

    private function amendInvalidResponse($reqRes) {

        if ( is_wp_error( $reqRes ) ) {
            $error_message = $reqRes->get_error_message();
            error_log("Something went wrong: $error_message");
            die();
        }
        $resp = json_decode($reqRes['body']);
        return $resp;
    }

    private function getEmptyResponse($reqRes) {
        return (object)array(
            "errorCode" => $reqRes['code'],
            "errorMessage" => $reqRes['error'],
            "primary" => array(),
            "secondary" => array(),
        );
    }

    private function calcSign($params) {
        ksort($params);
        return md5(http_build_query($params) . $this->api_token);
    }

    private function getHeaders($params) {
        return array(
            'Content-Type' => 'application/json',
            'App-Id' => self::APP_ID,
            'Security-Key' => $this->calcSign($params)
        );
    }
}
