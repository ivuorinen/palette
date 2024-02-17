<?php

namespace ivuorinen\Palette\Tests;

use PHPUnit\Framework\TestCase;

class PaletteTest extends TestCase
{
    public function testClassIsFoundAndHasDefaultAttributes(): void
    {
        $palette = new \ivuorinen\Palette\Palette('');
        $this->assertInstanceOf('ivuorinen\Palette\Palette', $palette);

        $this->assertIsInt($palette->precision);
        $this->assertIsInt($palette->returnColors);
        $this->assertIsArray($palette->colorsArray);
        $this->assertIsString($palette->filename);
        $this->assertIsString($palette->destination);
    }

    public function testKnownImagesWithOneColor(): void
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

    public function testKnownImagesWithManyColors(): void
    {
        $location = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
        $images = ['example.gif', 'example.jpg', 'example.png'];

        foreach ($images as $imageFile) {
            $image = $location . $imageFile;
            $this->assertFileExists($image);

            $palette = new \ivuorinen\Palette\Palette($image);
            $this->assertCount(10, $palette->colorsArray);
            $this->assertEquals($image, $palette->filename);
        }
    }

    public function testFailureNoImage(): void
    {
        $palette = new \ivuorinen\Palette\Palette('');
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Image was not provided');
        $palette->getPalette();
    }

    public function testFailureNotAnImage(): void
    {
        $this->expectException(\ErrorException::class);

        $palette = new \ivuorinen\Palette\Palette('NOT_HERE');
        $this->expectExceptionMessage('Image ' . $palette->filename . ' is not readable');

        $palette->getPalette();
    }
}
