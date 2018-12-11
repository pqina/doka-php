<?php

namespace Doka;

const IMAGE_LOADERS = [
    IMAGETYPE_JPEG => 'imagecreatefromjpeg',
    IMAGETYPE_PNG => 'imagecreatefrompng'
];

const IMAGE_SAVERS = [
    IMAGETYPE_JPEG => 'imagejpeg',
    IMAGETYPE_PNG => 'imagepng'
];

class Image {

    private $type;
    private $resource;
    private $natural_width;
    private $natural_height;

    public function __construct($source) {

        $this->type = exif_imagetype($source);

        if (!$this->type || !IMAGE_LOADERS[$this->type]) {
            return null;
        }

        $this->resource = call_user_func(IMAGE_LOADERS[$this->type], $source);

        $this->natural_width = imagesx($this->resource);
        $this->natural_height = imagesy($this->resource);
    }

    public function createWithSameFormat($width, $height) {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false); 
        imagesavealpha($canvas,true);
        return $canvas;
    }

    public function update($resource) {
        $this->resource = $resource;
    }

    public function flip($horizontal, $vertical) {
        $flipMode = null;
        if ($horizontal && $vertical) {
            $flipMode = IMG_FLIP_BOTH;
        }
        else if ($horizontal) {
            $flipMode = IMG_FLIP_HORIZONTAL;
        }
        else if ($vertical) {
            $flipMode = IMG_FLIP_VERTICAL;
        }
        if ($flipMode !== null) {
            imageflip($this->resource, $flipMode);
        }
        return $this->resource;
    }

    public function save($target, $quality) {

        call_user_func(
            IMAGE_SAVERS[$this->type],
            $this->resource,
            $target,
            $quality
        );

        // remove from memory
        imagedestroy($this->resource);

    }

    public function getSize() {
        return new Rect(0,0,
            imagesx($this->resource),
            imagesy($this->resource)
        );
    }

    public function getCenter() {
        return new Vector(
            imagesx($this->resource) * .5,
            imagesy($this->resource) * .5
        );
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __toString() {
        return '<br>width: ' . $this->width . '<br>height: ' . $this->height . '<br>';
    }
}