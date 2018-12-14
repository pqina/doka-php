<?php

namespace Doka;

require_once('Helper/Image.class.php');
require_once('Helper/Vector.class.php');
require_once('Helper/Rect.class.php');
require_once('Helper/Utils.php');

require_once('Transform/Crop.class.php');
require_once('Transform/Resize.class.php');

function arg_object_to_array($arg) {
    if (is_object($arg)) {
        return json_decode(json_encode($arg), true);
    }
    return $arg;
}

function is_output_argument($arg) {
    if (isset($arg['quality']) || isset($args['type'])) {
        return [
            'output' => $arg
        ];
    }
    return [];
}

function is_transform_argument($arg) {
    if (is_array($arg) && (isset($arg['crop']) || isset($arg['resize']))) {
        return [
            'transforms' => $arg
        ];
    }
    return [];
}

function is_target($arg) {
    if (is_string($arg)) {
        return [
            'target' => $arg
        ];
    }
    return [];
};

function is_transform($t) {
    return $t === 'crop' || $t === 'resize';
}


function transform($source, ...$args) {

    $arg_mappers = [
        'is_target',
        'is_transform_argument',
        'is_output_argument'
    ];

    $params = [];

    foreach ($args as $arg) {
        $value = arg_object_to_array($arg);
        foreach ($arg_mappers as $mapper) {
            $param = call_user_func(__NAMESPACE__ . '\\' . $mapper, $value);
            $params = array_merge($params, $param);
        }
    }

    // create the image object
    $image = new Image($source);

    // exit, failed to create image
    if ($image === null) {
        return null;
    }

    // apply transforms
    foreach ($params['transforms'] as $key => $value) {
        if (!is_transform($key)) { continue; }
        call_user_func(__NAMESPACE__ . '\\' . $key, $image, $value);
    }

    // set quality and type if defined
    $quality = isset($params['output']) && isset($params['output']['quality']) ? $params['output']['quality'] : -1;
    $type = isset($params['output']) && isset($params['output']['type']) ? $params['output']['type'] : null;
    
    // if should save, do it now
    if (isset($params['target'])) {
        $image->save($params['target'], $quality, $type);

        // clean up
        $image->destroy();
        return null;
    }

    // return resource so can render to page immidiately
    $resource = $image->output($quality, $type);

    // clean up
    $image->destroy();

    // return data
    return $resource;
}

function rotate($image, $rotation) { return imagerotate($image, -$rotation * (180 / pi()), 0); }

function scale($image, $scale) {
    $width = imagesx($image);
    $height = imagesy($image);
    $canvas = imagecreatetruecolor($width * $scale, $height * $scale);
    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
    imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width * $scale, $height * $scale, $width, $height);
    return $canvas;
}

function crop($image, $data) {

    $crop = new CropTransform($data);

    // get image size
    $image_size = $image->getSize();

    $aspect_ratio = $crop->aspectRatio ? $crop->aspectRatio : $image_size->height / $image_size->width;

    $crop_center = new Vector(
        $crop->center ? $crop->center->x : .5,
        $crop->center ? $crop->center->y : .5
    );
    
    $output_rect = calculateOutputSize($image_size, $aspect_ratio, $crop->zoom);

    $stage_zoom_factor = getImageRectZoomFactor(
        $image_size,
        getCenteredCropRect(
            $output_rect,
            $aspect_ratio
        ),
        $crop->rotation,
        $crop_center
    );

    $scale = ($crop->zoom ? $crop->zoom : 1) * $stage_zoom_factor;

    // flip
    $transformed = $image->flip($crop->flip->horizontal, $crop->flip->vertical);

    // apply scale
    $transformed = scale($transformed, $scale);
    
    // apply rotation
    $transformed = rotate($transformed, $crop->rotation);

    // get output size
    $scaled_rotated_size = new Rect(0, 0, imagesx($transformed),  imagesy($transformed));
    
    // set center to move rotated and scaled image to center of output image
    $translate_x = (-$scaled_rotated_size->center->x) + $output_rect->center->x;
    $translate_y = (-$scaled_rotated_size->center->y) + $output_rect->center->y;

    $width_scaled = $image_size->width * $scale;
    $height_scaled = $image_size->height * $scale;

    // correct offset with only center values
    $offset_x = ($width_scaled * .5) - ($width_scaled * $crop_center->x);
    $offset_y = ($height_scaled * .5) - ($height_scaled * $crop_center->y);
    
    // correct for image rotation
    $sin = sin($crop->rotation);
    $cos = cos($crop->rotation);
    $translate_x += ($cos * $offset_x) - ($sin * $offset_y);
    $translate_y += ($sin * $offset_x) + ($cos * $offset_y);

    // draw transformed image back to image object
    $image->draw($transformed, $translate_x, $translate_y, $output_rect->width, $output_rect->height);

    // remove transformed version from memory
    imagedestroy($transformed);
}

function resize($image, $data) {

    $size = new ResizeTransform($data);

    $image_size = $image->getSize();

    $output_size = new Rect(0, 0,
        isset($size->width) ? $size->width : $size->height,
        isset($size->height) ? $size->height : $size->width
    );

    if ($output_size->width == null && $output_size->height == null) {
        return $input;
    }

    $target_rect = new Rect(0, 0, $output_size->width, $output_size->height);

    if ($size->mode !== 'force') {

        $x = 0;
        $y = 0;
        
        $scalar_width = $target_rect->width / $image_size->width;
        $scalar_height = $target_rect->height / $image_size->height;
        $scalar = 1;

        if ($size->mode === 'cover') {
            $scalar = max($scalar_width, $scalar_height);
        }
        else if ($size->mode === 'contain') {
            $scalar = min($scalar_width, $scalar_height);
        }

        if ($scalar > 1 && $size->upscale === false) {
            return $input;
        }

        $width = $image_size->width * $scalar;
        $height = $image_size->height * $scalar;

        if ($size->mode === 'cover') {
            $x = $target_rect->width * .5 - $width * .5;
            $y = $target_rect->height * .5 - $height * .5;
        }
        else if ($size->mode === 'contain') {
            $output_size = new Rect(0, 0,
                $width,
                $height
            );
        }
        
        $target_rect = new Rect($x, $y, $width, $height);
    }

    $image->redrawTo(
        $output_size->width, $output_size->height,
        $target_rect->x, $target_rect->y,
        $target_rect->width, $target_rect->height
    );

}