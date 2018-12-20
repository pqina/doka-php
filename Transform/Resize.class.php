<?php

namespace Doka;

class ResizeTransform {
    public $width;
    public $height;
    public $mode;
    public $upscale;
    public function __construct($options) {
        $this->width = $options['size']['width'];
        $this->height = $options['size']['height'];
        $this->mode = isset($options['mode']) ? $options['mode'] : 'cover';
        $this->upscale = isset($options['upscale']) ? $options['upscale'] : false;
    }
}