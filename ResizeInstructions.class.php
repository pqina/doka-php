<?php

namespace Doka;

class ResizeInstructions {
    public $width;
    public $height;
    public $mode;
    public $upscale;
    public function __construct($width = null, $height = null, $mode = 'cover', $upscale = false) {
        $this->width = $width;
        $this->height = $height;
        $this->mode = $mode;
        $this->upscale = $upscale;
    }
}