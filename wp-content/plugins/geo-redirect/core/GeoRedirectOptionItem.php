<?php


class GeoRedirectOptionItem
{
    public $id;
    public $label;
    public $type;
    public $afterLabel;
    public $value;
    public $section;
    public $selectOptions;

    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists(GeoRedirectOptionItem::class, $key)) {
                $this->$key = $value;
            }
        }
    }
}