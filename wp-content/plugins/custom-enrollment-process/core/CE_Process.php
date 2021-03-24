<?php


class CE_Process
{
    private const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    public const COUNT_STEPS = 4;

    /**
     * Current enrollment session data
     *
     * @var CE_Data
     */
    public $enrollmentData;

    /**
     * @var CE_ProcessPlugin
     */
    public $plugin;

    /**
     * @var CE_ProcessCart
     */
    public $cart;

    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }
        if (isset($_SESSION['enrollment'])) {
            $this->enrollmentData = new CE_Data($_SESSION['enrollment']);
        } else {
            $this->enrollmentData = new CE_Data([
                'steps' => []
            ]);
        }
        $this->plugin = CE_ProcessPlugin::getInstance();
        $this->cart = new CE_ProcessCart();
    }

    /**
     * @return CE_Data
     */
    public function getEnrollmentData()
    {
        return $this->enrollmentData;
    }

    /**
     * Returns the step number from the passed code
     *
     * @param $stepCode
     * @return false|int
     */
    public function getStepNum($stepCode)
    {
        if (empty($stepCode)) {
            return false;
        }
        foreach ($this->enrollmentData->steps as $key => $step) {
            if ($step['id'] == $stepCode) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Generates a new enrollment session
     */
    public function createNewEnrollSession($countSteps = 4)
    {
        $steps = [];
        for ($i = 1; $i <= $countSteps; $i++) {
            $steps[$i] = [
                'id' => $this->generateSessionCode(),
                'payload' => []
            ];
        }
        $this->enrollmentData = new CE_Data([
            'steps' => $steps
        ]);
        $this->saveSession();
    }

    /**
     * Clearing the data of the current enrollment session
     */
    public function clearEnrollmentSession()
    {
        unset($_SESSION['enrollment']);
    }

    /**
     * Sets the payload of the current step
     *
     * @param int $number Step number
     * @param $payload mixed Step payload
     */
    public function setStepPayload($number, $payload)
    {
        $this->enrollmentData->steps[$number]['payload'] = $payload;
        $this->saveSession();
    }

    /**
     * Returns the payload of the step
     *
     * @param int $number Step number
     * @return mixed|null Step payload
     */
    public function getStepPayload($number)
    {
        if (isset($this->enrollmentData->steps[$number])) {
            return $this->enrollmentData->steps[$number]['payload'];
        } else {
            return null;
        }
    }

    /**
     * Sets the payload of enrollment session
     *
     * @param $payload
     */
    public function setSessionPayload($payload)
    {
        $this->enrollmentData->sessionPayload = $payload;
        $this->saveSession();
    }

    /**
     * Returns payload of enrollment session
     *
     * @return array
     */
    public function getSessionPayload()
    {
        return $this->enrollmentData->sessionPayload;
    }

    /**
     * Adds a redirect action after adding to cart
     *
     * @param int $productId
     * @param string $url
     */
    public function addAfterAddToCartRedirectAction($productId, $url)
    {
        $this->setSessionPayload([
            'redirectAfterAddToCart' => [
                'productId' => $productId,
                'redirectUrl' => $url
            ]
        ]);
    }

    /**
     * Returns the step code for the passed number
     *
     * @param int $number Step number
     * @return mixed|null
     */
    public function getStepId($number)
    {
        if (isset($this->enrollmentData->steps[$number])) {
            return $this->enrollmentData->steps[$number]['id'];
        } else {
            return null;
        }
    }

    /**
     * Saves the modified step data in the $_SESSION array
     */
    public function saveSession()
    {
        $_SESSION['enrollment'] = $this->enrollmentData->getData();
    }

    /**
     * Redirects to the URL of the passed step
     *
     * @param int $num Step number
     */
    public function redirectToStep($num)
    {
        wp_redirect($this->getStepUrl($num));
        exit();
    }

    /**
     * Returns step url
     *
     * @param int $num
     * @return string
     */
    public function getStepUrl($num)
    {
        if (isset($this->enrollmentData->steps[$num])) {
            return '/enrollment/' . $this->getStepId($num);
        } else {
            return '/enrollment/' . $this->getStepId(1);
        }
    }

    /**
     * Generates a random string
     *
     * @param int $length String length
     * @return string
     */
    private function generateSessionCode($length = 10)
    {
        $alphabet = str_repeat(self::ALPHABET, (int)($length / mb_strlen(self::ALPHABET)) + 1);
        return mb_substr(str_shuffle($alphabet), 0, $length);
    }
}