<?php

return [
    // When enabled, will try to parse numbers that use non-standard decimal and thousand separators into a float.
    'use_forgiving_number_parser' => env('USE_FORGIVING_NUMBER_PARSER', true),

    // The disk to use when uploading files to be used with textract
    'textract_disk' => env('TEXTRACT_DISK'),

    'textract_region' => env('TEXTRACT_REGION'),
    'textract_version' => env('TEXTRACT_VERSION'),
    'textract_key' => env('TEXTRACT_KEY'),
    'textract_secret' => env('TEXTRACT_SECRET', '2018-06-27'),
];
