<?php


class CE_Data
{
    /**
     * Registration step data
     *
     * @var array
     */
    public $steps;

    /**
     * Session data
     *
     * @var array
     */
    public $sessionPayload;

    public function __construct($dataArray)
    {
        foreach ($dataArray as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Returns the data of registration steps as an array
     *
     * @return array
     */
    public function getData()
    {
        $vars = get_class_vars('CE_Data');
        $result = [];
        foreach ($vars as $key => $_) {
            $result[$key] = $this->$key;
        }
        return $result;
    }
}