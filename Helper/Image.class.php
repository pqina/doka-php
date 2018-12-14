<?php

namespace Doka;

const IMAGE_TYPES = [
    'image/jpeg' => IMAGETYPE_JPEG,
    'image/png' => IMAGETYPE_PNG
];

const IMAGE_LOADERS = [
    IMAGETYPE_JPEG => 'imagecreatefromjpeg',
    IMAGETYPE_PNG => 'imagecreatefrompng'
];

const IMAGE_SAVERS = [
    IMAGETYPE_JPEG => 'imagejpeg',
    IMAGETYPE_PNG => 'imagepng'
];

const IMAGE_QUALITY = [
    IMAGETYPE_JPEG => 'imagejpegquality',
    IMAGETYPE_PNG => 'imagepngquality'
];

function imagejpegquality($quality) { return $quality; }
function imagepngquality($quality) { return -1; }

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

    public function update($resource) {
        imagedestroy($this->resource);
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

    private function createCanvas($width, $height) {
        $canvas = imagecreatetruecolor($width, $height);
        if ($this->type === IMAGETYPE_PNG) {
            imagesavealpha($canvas , true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        }
        else {
            imagealphablending($canvas, false);
        }
        return $canvas;
    }

    public function draw($source, $x, $y, $width, $height) {
        
        $canvas = $this->createCanvas($width, $height);
    
        imagecopy(
            $canvas, $source,
            $x, $y,
            0, 0, imagesx($source), imagesy($source)
        );

        $this->update($canvas);
    }

    public function redrawTo($width, $height, $dx, $dy, $dw, $dh) {

        $canvas = $this->createCanvas($width, $height);

        imagecopyresampled(
            $canvas, $this->resource,
            $dx, $dy,
            0, 0,
            $dw, $dh,
            imagesx($this->resource), imagesy($this->resource)
        );

        $this->update($canvas);
    }

    public function save($target, $quality, $forceType) {
        $type = $forceType === null ? $this->type : IMAGE_TYPES[$forceType];
        call_user_func(
            IMAGE_SAVERS[$type],
            $this->resource,
            $target,
            call_user_func(__NAMESPACE__ . '\\' . IMAGE_QUALITY[$type], $quality)
        );
    }

    public function output($quality, $forceType) {
        $type = $forceType === null ? $this->type : IMAGE_TYPES[$forceType];
        return call_user_func(
            IMAGE_SAVERS[$type],
            $this->resource,
            NULL,
            call_user_func(__NAMESPACE__ . '\\' . IMAGE_QUALITY[$type], $quality)
        );
    }

    public function destroy() {
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