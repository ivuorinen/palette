<?php
/**
 * Palette
 * Parses image and returns most used colors
 *
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Ismo Vuorinen <ivuorinen@me.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @category   Default
 * @package    Palette
 * @author     Ismo Vuorinen <ivuorinen@me.com>
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @copyright  2013 Ismo Vuorinen
 * @link       https://github.com/ivuorinen/palette
 */

namespace ivuorinen\Palette;

/**
 * Palette
 *
 * @author   Ismo Vuorinen <ivuorinen@me.com>
 * @license  http://www.opensource.org/licenses/mit-license.php  MIT License
 * @link     https://github.com/ivuorinen/palette
 * @example  example/example.php Usage examples
 **/
class Palette
{
    /** @var int Precision of palette, higher is more precise */
    public $precision;

    /** @var int Number of colors to return */
    public $returnColors;

    /** @var array Array of colors we use internally */
    public $colorsArray;

    /** @var string Full path to image file name */
    public $filename;

    /** @var string Destination for .json-file, full path and filename */
    public $destination;

    /**
     * Constructor
     *
     * If you have specified $filename, Palette::run() gets triggered.
     * Palette::run() uses default parameters and processes given image
     * and saves the result as an json-file to datafiles -folder.
     *
     * @param string $filename Full path to image
     *
     * @throws \Exception
     */
    public function __construct($filename = null)
    {
        // Define shortcut to directory separator
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->precision = 10;
        $this->returnColors = 10;
        $this->colorsArray = array();
        $this->filename = $filename;
        $this->destination = __DIR__
            . DS . 'datafiles'
            . DS . basename($filename) . '.json';

        if (!empty($this->filename)) {
            $this->run();
        }
    }

    /**
     * run the process
     *
     * if you want to change parameters you can init new Palette, then change
     * settings and after that run the palette generation and saving
     *
     * @return bool Returns true always
     * @throws \Exception
     */
    public function run()
    {
        if (empty($this->destination)) {
            throw new \ErrorException("No destination provided, can't save.");
        }

        $this->getPalette();
        $this->save();

        return true;
    }

    /**
     * getPalette
     * Returns colors used in an image specified in $filename
     *
     * @return array|bool If we get array that has colors return the array
     * @throws \Exception
     */
    public function getPalette()
    {
        // We check for input
        if (empty($this->filename)) {
            throw new \ErrorException('Image was not provided');
        }

        // We check for readability
        if (!is_readable($this->filename)) {
            throw new \ErrorException("Image {$this->filename} is not readable");
        }

        $this->colorsArray = $this->countColors();

        if (!empty($this->colorsArray) && is_array($this->colorsArray)) {
            return $this->colorsArray;
        }

        return false;
    }

    /**
     * countColors returns an array of colors in the image
     *
     * @return array|boolean Array of colors sorted by times used
     * @throws \ErrorException
     */
    private function countColors()
    {
        $this->precision = max(1, abs((int)$this->precision));
        $colors = array();

        // Test for image type
        $img = $this->imageTypeToResource();

        if (!$img && $img !== null) {
            throw new \ErrorException("Unable to open: {$this->filename}");
        }

        // Get image size and check if it's really an image
        $size = @getimagesize($this->filename);

        if ($size === false) {
            throw new \ErrorException("Unable to get image size data: {$this->filename}");
        }

        // This is pretty naive approach,
        // but looping through the image is only way I thought of
        for ($x = 0; $x < $size[0]; $x += $this->precision) {
            for ($y = 0; $y < $size[1]; $y += $this->precision) {
                $thisColor = imagecolorat($img, $x, $y);
                $rgb = imagecolorsforindex($img, $thisColor);
                $red = round(round($rgb['red'] / 0x33) * 0x33);
                $green = round(round($rgb['green'] / 0x33) * 0x33);
                $blue = round(round($rgb['blue'] / 0x33) * 0x33);
                $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);

                if (array_key_exists($thisRGB, $colors)) {
                    $colors[$thisRGB]++;
                } else {
                    $colors[$thisRGB] = 1;
                }
            }
        }
        arsort($colors);

        return array_slice($colors, 0, $this->returnColors, true);
    }

    /**
     * save
     * Get array of colors, json_encode it and save to destination
     *
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        // Check for destination
        if (empty($this->destination)) {
            throw new \InvalidArgumentException('No destination given for save');
        }

        // Check destination writability
        $this->checkDestination();

        // Check for data we should write
        if (empty($this->colorsArray)) {
            throw new \ErrorException("Couldn't detect colors from image: {$this->filename}");
        }

        // Encode for saving
        $colorsData = json_encode($this->colorsArray);

        // Save and return the result of save operation
        file_put_contents($this->destination, $colorsData);

        return is_readable($this->destination);
    }

    /**
     * imageTypeToResource returns image resource
     *
     * Function takes $this->filename and returns
     * imagecreatefrom{gif|jpeg|png} for further processing
     *
     * @return resource Image resource based on content
     * @throws \ErrorException
     */
    private function imageTypeToResource()
    {
        try {
            if (filesize($this->filename) < 12) {
                throw new \ErrorException('File size smaller than 12');
            }
            $type = exif_imagetype($this->filename);
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        switch ($type) {
            case '1': // IMAGETYPE_GIF
                return @imagecreatefromgif($this->filename);
            case '2': // IMAGETYPE_JPEG
                return @imagecreatefromjpeg($this->filename);
            case '3': // IMAGETYPE_PNG
                return @imagecreatefrompng($this->filename);
            default:
                $image_type_code = image_type_to_mime_type($type);
                throw new \ErrorException("Unknown image type: {$image_type_code} ({$type}): {$this->filename}");
        }
    }

    /**
     * checkDestination tries to make sure you have directory to save to
     *
     * Tests done:
     * - Does the destination folder exists?
     * - Is it writable?
     * - Can it be made writable?
     *
     * @return boolean True or false, with exceptions
     * @throws \Exception
     */
    private function checkDestination()
    {
        $destination_dir = dirname($this->destination);

        // Test if we have destination directory
        if (!@mkdir($destination_dir, 0755) && !is_dir($destination_dir)) {
            throw new \ErrorException("Couldn't create missing destination dir: {$destination_dir}");
        }

        // Test if we can write to it
        if (is_writable($destination_dir)) {
            return true;
        }
        chmod($destination_dir, 0755);

        if (!is_writable($destination_dir)) {
            throw new \ErrorException("Destination directory not writable: {$destination_dir}");
        }

        return true;
    }
}
