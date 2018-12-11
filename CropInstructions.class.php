<?php

namespace Doka;

require_once('Vector.class.php');

class Flip {
    public $horizontal;
    public $vertical;
    public function __construct($horizontal = false, $vertical = false) {
        $this->horizontal = $horizontal;
        $this->vertical = $vertical;
    }
}

class CropInstructions {
    public $center;
    public $zoom;
    public $rotation;
    public $flip;
    public function __construct($aspectRatio = null, $center = null, $rotation = 0, $zoom = 1, $flip = null) {
        $this->aspectRatio = $aspectRatio;
        $this->center = $center === null ? new Vector(.5, .5) : $center;
        $this->rotation = $rotation;
        $this->zoom = $zoom;
        $this->flip = $flip === null ? new Flip() : $flip;
    }
}