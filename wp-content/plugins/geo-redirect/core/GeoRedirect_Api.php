<?php


class GeoRedirect_Api
{
    private $action;
    private $token;
    private $apiUrl;

    public function __construct($action, $token, $apiUrl = '')
    {
        $this->action = $action;
        $this->token = $token;
        $this->apiUrl = $apiUrl;

        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action("wp_ajax_$this->action", [$this, 'api_endpoint'], 10, 1);
        add_action("wp_ajax_nopriv_$this->action", [$this, 'api_endpoint'], 10, 1);
    }

    public function api_endpoint()
    {
        $headers = apache_request_headers();
        $action = $_REQUEST['geo-redirect-action'];
        if (!isset($headers['X-Token']) || !$this->checkToken($headers['X-Token']) ||
            !$action || !method_exists($this, $action . '_handler')) {
            http_response_code(400);
            return;
        }
        $func = $action . '_handler';
        $response = $this->$func();
        header('Content-Type: application/json');
        echo json_encode($response);
        wp_die();
    }

    public function get_rules_handler()
    {
        $plugin = GeoRedirect_Plugin::getInstance();
        $rules = $plugin->getRules();
        return [
            'list' => $rules
        ];
    }

    public function request($action)
    {
        $url = $this->apiUrl . '?' . http_build_query([
                'action' => $this->action,
                'geo-redirect-action' => $action
            ]);
        $params = array(
            'headers' => [
                'X-token' => $this->token
            ],
            'method' => 'GET',
        );

        $reqRes = wp_remote_request($url, $params);
        if (!($reqRes instanceof WP_Error) && isset($reqRes['body'])) {
            $body = json_decode($reqRes['body'], true);
            return isset($body['list']) ? $body['list'] : [];
        }
        return [];
    }

    private function checkToken($token)
    {
        return $token == $this->token;
    }
}