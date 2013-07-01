<?php
error_reporting(E_ALL);
// require_once '../src/ivuorinen/Palette/Palette.php'; /* If we are not using Composer autoloader */
require_once '../vendor/autoload.php'; /* We are using Composer autoloader (run composer install in project root) */


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

    // Configure your destination file name fully we get
    // an array encoded to json, so .json extension is good choice
    $test->destination  = dirname(__FILE__) . '/' . md5($test->filename) . '.json';

    echo "<pre>"
       . "Processing {$test->filename}\n";

    if (is_readable($test->destination)) {

        echo "Returning data from cache: {$test->destination}\n";

        // We have already processed these files, return the array
        // from cached version to make everything work faster
        $colors = json_decode(file_get_contents($test->destination));

    } else {

        echo "Processing data and saving to {$test->destination}\n";

        // We don't have cached versions so process colors as an array
        $colors = $test->getPalette();

        // And then save the data to file specified in $test->destination
        $test->save();

    }

    echo print_r($colors, true)
       . "</pre>\n";

}

echo "<h1>This one fails</h1>\n";
$test = new \ivuorinen\Palette\Palette('/bin/sh');
