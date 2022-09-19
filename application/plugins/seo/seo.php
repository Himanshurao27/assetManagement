<?php

use Framework\StringMethods as StringMethods;

class SEO {
    
    protected $_title;
    protected $_photo;
    protected $_description;
    protected $_keyword;

    public function __construct($options) {
        if (!isset($options["title"])) {
            throw new Exception("Title not set");
        }

        $this->_title       = $options["title"];
        $this->_photo       = $options["photo"];
        $this->_description = $options["description"];
        $this->_keyword     = $options["keyword"];
    }

    public function __call($name, $arguments) {

        $getMatches = StringMethods::match($name, "^get([a-zA-Z0-9]+)$");
        if ($getMatches && count($getMatches) > 0) {
            $normalized = lcfirst($getMatches[0]);
            $property = "_{$normalized}";

            if (property_exists($this, $property)) {
                return $this->$property;
            }
        }

        $setMatches = StringMethods::match($name, "^set([a-zA-Z0-9]+)$");
        if ($setMatches && count($setMatches) > 0) {
            $normalized = lcfirst($setMatches[0]);
            $property = "_{$normalized}";

            if (property_exists($this, $property)) {
                $this->$property = $arguments[0];
                return;
            }
        }
    }
    
}