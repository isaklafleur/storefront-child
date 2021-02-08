<?php

class MlmSoftApiResponse
{

    protected $resp;

    public function __construct(\stdClass $resp)
    {
        $this->resp = $resp;
    }

    public function getErrorCode()
    {
        return isset($this->resp->errorCode) ? $this->resp->errorCode : -1;
    }

    public function getErrorMessage()
    {
        return isset($this->resp->errorMessage) ? $this->resp->errorMessage : '';
    }

    public function getPrimaryMessage()
    {
        return isset($this->resp->primary->message) ? $this->resp->primary->message : '';
    }

    public function getPrimarySuccess()
    {
        return isset($this->resp->primary->success) ? $this->resp->primary->success : false;
    }

    public function isPrimarySuccess()
    {
        return $this->getErrorCode() == 0 && $this->getPrimarySuccess();
    }

    public function getPrimaryPayload()
    {
        return !empty($this->resp->primary->payload) ? $this->resp->primary->payload : null;
    }

    public function getExtraErrorMessages()
    {
        $res = [];
        if (!empty($this->resp->primary->errors)) {
            foreach ($this->resp->primary->errors as $error) {
                if (is_array($error)) {
                    $res[] = array_shift($error);
                } elseif (is_object($error)) {
                    $res[] = $error->message;
                }
            }
        }
        return $res;
    }

    public function getStdErrorLogParams()
    {
        return array(
            '%ercode' => $this->getErrorCode(),
            '%ermsg' => $this->getErrorMessage(),
            '%success' => $this->getPrimarySuccess() ? 'Y' : 'N',
            '%msg' => $this->getPrimaryMessage()
        );
    }

}