# Doka PHP

A image manipulation library to apply [Doka](https://pqina.nl/doka) and [FilePond](https://pqina.nl/filepond) image transform information on the server instead of on the client.

Map FilePond and Doka on the `CropInstructions` and `ResizeInstructions` classes to supply instructions to `Doka\transform`.

## Usage

```php
<?php

// Load the Doka class
require_once('Doka.class.php');
require_once('Vector.class.php');
require_once('CropInstructions.class.php');
require_once('ResizeInstructions.class.php');

// These can also be derived from both the Doka and FilePond metadata objects
$crop = new CropInstructions(
    1,                  // crop square
    new Vector(.5, .5), // from center
    0,                  // zero rotation
    1                   // normal zoom level
);

$resize = new ResizeInstructions(
    128,                // target width
    128,                // target height
    'cover',            // resize mode ('cover', 'contain', or 'force')
    false               // don't upscale
);

// Apply transforms
Doka\transform(
    // source file
    './my-input-file.jpeg', 

    // target file
    './my-output-file.jpeg',

    // transforms to apply
    [
        array('crop' => $crop),
        array('resize' => $resize)
    ]
);
```