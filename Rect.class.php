<?php

namespace Doka;

class Rect {

    private $x;
    private $y;
    private $width;
    private $height;
    private $center;

    public function __construct($x, $y, $width, $height) {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            if ($property === 'center') {
                return new Vector($this->width * .5, $this->height * .5);
            }
            return $this->$property;
        }
    }

    public function __toString() {
        return '<br>x: ' . $this->x . '<br>y: ' . $this->y . '<br>width: ' . $this->width . '<br>height: ' . $this->height . '<br>';
    }
}