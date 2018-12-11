# Doka PHP

A image manipulation library to apply [Doka](https://pqina.nl/doka) and [FilePond](https://pqina.nl/filepond) image transform information on the server instead of on the client.

## Usage

```php
require_once('Doka/Doka.class.php');

header('Content-Type: image/jpeg');

/*
// Load image transform instructions from JSON file
$file = 'hello.json';
$handle = fopen($file, 'r');
$data = json_decode(fread($handle, filesize($file)));
fclose($handle);
*/

Doka\transform(

    // The source image.
    './hello.jpeg', 

    // The target image. This parameter is optional. 
    // When not supplied (like now), Doka will return 
    // the file resource instead.
    // './hello_out.jpeg',

    // The transform instructions, can optionally be 
    // set to a decoded JSON file `$data` instead
    // of the associative array below
    [
        'crop' => [
            'aspectRatio' => 1,
            'center' => [
                'x' => .5, 
                'y' => .5
            ],
            'rotation' => 1.57079,
            'zoom' => 1.5,
            'flip' => [
                'horizontal' => false,
                'vertical' => true
            ]
        ],
        'resize' => [
            'width' => 256,
            'height' => 256,
            'mode' => 'cover',
            'upscale' => false
        ]
    ],

    // The output instructions. This parameter is optional.
    [
        'quality' => 75,
        'type' => 'image/jpeg'
    ]
);
```