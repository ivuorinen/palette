<?php

class PaletteTest extends \PHPUnit_Framework_TestCase
{
    public function test_class_is_found_and_has_default_attributes()
    {
        $palette = new \ivuorinen\Palette\Palette();
        $this->assertInstanceOf('ivuorinen\Palette\Palette', $palette);

        $this->assertInternalType('integer', $palette->precision);
        $this->assertInternalType('integer', $palette->returnColors);
        $this->assertInternalType('array', $palette->colorsArray);
        $this->assertInternalType('null', $palette->filename);
        $this->assertInternalType('string', $palette->destination);
    }

    public function test_known_images_with_one_color()
    {
        $location = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
        $images = ['black.png' => '000000', 'red.png' => 'CC3333'];

        foreach ($images as $imageFile => $hex) {
            $image = $location . $imageFile;
            $this->assertFileExists($image);

            $palette = new \ivuorinen\Palette\Palette($image);
            $this->assertCount(1, $palette->colorsArray);
            $this->assertArrayHasKey($hex, $palette->colorsArray);
            $this->assertEquals($image, $palette->filename);
        }
    }

    public function test_failure_no_image()
    {
        $palette = new \ivuorinen\Palette\Palette('');
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Image was not provided');
        $palette->getPalette();
    }

    public function test_failure_not_an_image()
    {
        $palette = new \ivuorinen\Palette\Palette();
        $palette->filename = 'NOT_HERE';
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Image ' . $palette->filename . ' is not readable');
        $palette->getPalette();
    }
}
