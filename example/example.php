<?php
error_reporting(E_ALL);
require '../palette.php';

$testimages_full_process = array(
    'example.gif',
    'example.jpg',
    'example.png'
);

$testimages_controlled_process = array(
    'example.gif',
    'example.jpg',
    'example.png'
);

echo "<h1>Full process (creates palette, saves to same dir as palette.php)</h1>\n";
foreach ($testimages_full_process as $image) {

    // Initiation with image triggers Palette::run()
    $test = new \ivuorinen\Palette\Palette($image);

    echo "<pre>"
       . "Processing {$test->filename}\n"
       . print_r($test->colorsArray, true)
       . "</pre>\n";
}

echo "<h1>Controlled process</h1>\n";
foreach ($testimages_controlled_process as $image) {
    $test = new \ivuorinen\Palette\Palette();

    // We set the image, precision and amount of colors to return
    $test->filename     = $image;   // Full, or relative path to our image
    $test->precision    = 10;       // Bigger is faster, smaller returns more colors
    $test->returnColors = 5;        // How many colors we want in our array at most

    // Get the colors as an array
    $colors = $test->getPalette();

    echo "<pre>"
       . "Processing {$test->filename}\n"
       . print_r($colors, true)
       . "</pre>\n";

}

echo "<h1>This one fails</h1>\n";
$test = new \ivuorinen\Palette\Palette('/bin/sh');
