<?php

namespace Doka;

class Vector {

    private $x;
    private $y;

    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __toString() {
        return '<br>x: ' . $this->x . '<br>y: ' . $this->y . '<br>';
    }
}

function vectorDistance($a, $b) {
    return sqrt(vectorDistanceSquared($a, $b));
}

function vectorDistanceSquared($a, $b) {
    return vectorDot(vectorSubtract($a, $b), vectorSubtract($a, $b));
}

function vectorSubtract($a, $b) {
    return new Vector($a->x - $b->x, $a->y - $b->y);
}

function vectorDot($a, $b) {
    return $a->x * $b->x + $a->y * $b->y;
}