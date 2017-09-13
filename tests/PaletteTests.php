<?php

class PaletteTests extends \PHPUnit_Framework_TestCase
{
    public $palette;

    public function test_class_is_found_and_has_default_attributes()
    {
        $this->palette = new \ivuorinen\Palette\Palette();
        $this->assertInstanceOf('ivuorinen\Palette\Palette', $this->palette);

        $this->assertInternalType('integer', $this->palette->precision);
        $this->assertInternalType('integer', $this->palette->returnColors);
        $this->assertInternalType('array', $this->palette->colorsArray);
        $this->assertInternalType('null', $this->palette->filename);
        $this->assertInternalType('string', $this->palette->destination);
    }
}