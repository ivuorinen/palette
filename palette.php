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
 * @since    0.1.0
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
     * @param string $filename Full path to image
     *
     * @return null
     *
     **/
    public function __construct($filename = null)
    {
        // Define shortcut to directory separator
        define("DS", DIRECTORY_SEPARATOR);

        $this->precision    = 5;
        $this->returnColors = 10;
        $this->colorsArray  = array();
        $this->filename     = $filename;
        $this->destination  = dirname(__FILE__) . DS . basename($filename) . '.json';

        if (! empty($this->filename)) {
            $this->isImage();
            $this->getPalette();
        }
    }

    public function run()
    {
        if (empty($this->destination)) {
            throw new Exception("No destination provided, can't save.")
        }

        $this->isImage();
        $this->getPalette();
        $this->save();
    }

    /**
     * getPalette
     * Returns colors used in an image specified in $filename
     *
     * @return false
     **/
    public function getPalette()
    {
        // We check for input
        if (empty($this->filename)) {
            throw new Exception("Image was not provided");

            return false;
        }

        // We check for readability
        if (! is_readable($this->filename)) {
            throw new Exception("Image {$this->filename} is not readable");

            return false;
        }

        // We check is the file an image
        if (! $this->isImage($this->filename)) {

            throw new Exception("File given was not an image");

            return false;
        }

        $this->countColors($this->filename);

        return false;
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
        if (empty($this->destination)) {
            throw new Exception("No destination given for save");
        }
        if (empty($this->colorsArray)) {
            throw new Exception("No colors to save");
        }

        // Encode for saving
        $colorsData = json_encode($this->colorsArray);

        // Return the result of save operation
        return file_put_contents($this->destination, $colorsData);
    }

    /**
     * Check is given file an image
     *
     * @return boolean True for an image, false if not
     */
    private function isImage()
    {
        if (! empty(getimagesize($this->filename))) {
            return true;
        } else {
            return false;
        }

        return false;
    }
}
