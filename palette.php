<?php
/**
* Palette
* Parses image and returns most used colors
*
* PHP version 5
*
* @category Default
* @package  Palette
* @author   Ismo Vuorinen <ivuorinen@me.com>
* @license  http://opensource.org/licenses/gpl-license.php GNU Public License
* @link     https://github.com/ivuorinen/palette
*/

namespace ivuorinen\Palette;

/**
 * Palette
 *
 * @category Default
 * @package  Palette
 * @author   Ismo Vuorinen <ivuorinen@me.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/ivuorinen/palette
 * @version  1.0.0
 * @example  example/example.php Usage examples
 **/
class Palette
{
    /**
     * Precision of palette, higher is more precise
     * @var int
     */
    public $precision;

    /**
     * Number of colors to return
     * @var int
     */
    public $returnColors;

    /**
     * Array of colors we use internally
     * @var array
     */
    public $colorsArray;

    /**
     * Full path to image file name
     * @var string
     */
    public $filename;

    /**
     * Destination for .json-file, full path and filename
     *
     * @var string
     **/
    public $destination;

    /**
     * Constructor
     *
     * If you have specified $filename, Palette::run() gets triggered.
     * Palette::run() uses default parameters and processes given image
     * and saves the result as an json-file to datafiles -folder.
     *
     * @param string $filename Full path to image
     **/
    public function __construct($filename = null)
    {
        // Define shortcut to directory separator
        if (! defined('DS')) {
            define("DS", DIRECTORY_SEPARATOR);
        }

        $this->precision    = 10;
        $this->returnColors = 10;
        $this->colorsArray  = array();
        $this->filename     = $filename;
        $this->destination  = dirname(__FILE__)
                                . DS . 'datafiles'
                                . DS . basename($filename) . '.json';

        if (! empty($this->filename)) {
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
     */
    public function run()
    {
        if (empty($this->destination)) {
            throw new Exception("No destination provided, can't save.");
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
     **/
    public function getPalette()
    {
        // We check for input
        try {
            if (empty($this->filename)) {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception("Image was not provided");
        }

        // We check for readability
        try {
            if (! is_readable($this->filename)) {
                throw new \Exception("Image {$this->filename} is not readable");
                return false;
            }
        } catch (\Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }


        $this->colorsArray = $this->countColors();

        if (! empty($this->colorsArray) and is_array($this->colorsArray)) {
            return $this->colorsArray;
        } else {
            return false;
        }
    }

    /**
     * countColors returns an array of colors in the image
     *
     * @return array Array of colors sorted by times used
     */
    private function countColors()
    {
        $this->precision = max(1, abs((int) $this->precision));
        $colors          = array();

        // Test for image type
        $img = $this->imageTypeToResource();

        if (! $img && $img !== null) {
            user_error("Unable to open: {$this->filename}", E_USER_ERROR);
            return false;
        }

        // Get image size and check if it's really an image
        $size            = @getimagesize($this->filename);

        if ($size === false) {
            user_error("Unable to get image size data: {$this->filename}", E_USER_ERROR);
            return false;
        }

        for ($x = 0; $x < $size[0]; $x += $this->precision) {
            for ($y = 0; $y < $size[1]; $y += $this->precision) {
                $thisColor  = imagecolorat($img, $x, $y);
                $rgb        = imagecolorsforindex($img, $thisColor);
                $red        = round(round(($rgb['red']      / 0x33)) * 0x33);
                $green      = round(round(($rgb['green']    / 0x33)) * 0x33);
                $blue       = round(round(($rgb['blue']     / 0x33)) * 0x33);
                $thisRGB    = sprintf('%02X%02X%02X', $red, $green, $blue);

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
     * @param string $destination Where to save json-file
     *
     * @return false
     **/
    public function save()
    {
        try {
            if (empty($this->destination)) {
                throw new \Exception("No destination given for save");
            }
        } catch (\Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }

        try {
            $this->checkDestination();
        } catch (\Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }

        try {
            if (empty($this->colorsArray)) {
                throw new \Exception("Couldn't detect colors from image: {$this->filename}");
            }
        } catch (\Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }

        // Encode for saving
        $colorsData = json_encode($this->colorsArray);

        // Save and return the result of save operation
        file_put_contents($this->destination, $colorsData);
        if (is_readable($this->destination)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * imageTypeToResource returns image resource
     *
     * Function takes $this->filename and returns
     * imagecreatefrom{gif|jpeg|png} for further processing
     *
     * @return resource Image resource based on content
     */
    private function imageTypeToResource()
    {
        $type = exif_imagetype($this->filename);
        switch ($type) {
            case '1': // IMAGETYPE_GIF
                $img = @imagecreatefromgif($this->filename);
                break;
            case '2': // IMAGETYPE_JPEG
                $img = @imagecreatefromjpeg($this->filename);
                break;
            case '3': // IMAGETYPE_PNG
                $img = @imagecreatefrompng($this->filename);
                break;
            default:
                $image_type_code = image_type_to_mime_type($type);
                user_error("Unknown image type: {$image_type_code} ({$type}): {$this->filename}");
                return false;
                break;
        }

        return $img;
    }

    /**
     * checkDestination tries to make sure you have directory to save to
     *
     * Tests done:
     * - Does the destination folder exists?
     * - Is it writable?
     * - Can it be made writable?
     *
     * @return bool|exception True or false, with exceptions
     */
    private function checkDestination()
    {
        $destination_dir = dirname($this->destination);

        // Test if we have destination directory
        try {
            if (! file_exists($destination_dir)) {
                mkdir($destination_dir, 0755);
            }

            if (! file_exists($destination_dir)) {
                throw new \Exception("Couldn't create missing destination dir: {$destination_dir}");
            }
        } catch (Exception $e) {
            user_error($e->getMessage());
            return false;
        }

        // Test if we can write to it
        try {
            if (! is_writable($destination_dir)) {
                chmod($destination_dir, 0755);
            } else {
                return true;
            }

            if (! is_writable($destination_dir)) {
                throw new \Exception("Destination directory not writable: {$destination_dir}");
            }
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }

        return true;
    }
}
