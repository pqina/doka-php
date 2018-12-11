<?php

namespace Doka;

class ResizeTransform {
    public $width;
    public $height;
    public $mode;
    public $upscale;
    public function __construct($options) {
        $this->width = $options['width'];
        $this->height = $options['height'];
        $this->mode = $options['mode'];
        $this->upscale = $options['upscale'];
    }
}