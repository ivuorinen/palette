Palette
=======

Palette is a PHP class that takes your images and returns used colors, sorts them by usage and saves the results.

## Usage ##

After adding Palette to your ``composer.json`` file and installed to your vendor folder, you can use the class like this:

### With default settings ###

```php
$image = "example/example.jpg";
$palette = new \ivuorinen\Palette\Palette($image);
print_r($palette->colorsArray);

```

### With custom settings ###

```php
$palette = new \ivuorinen\Palette\Palette();

$palette->filename     = "example/example.jpg"; // Our image
$palette->precision    = 10; // Precision of color collection
$palette->returnColors = 10; // How many colors we want
$palette->destination  = './data/' . md5($palette->filename) . '.json';

// Do the work (same as ``Palette::run()``)
$this->getPalette();
$this->save(); // Not needed, but caching results <3

// We now have ``./data/7233c3b944f5299c6983c77c94e75dce.json`` (if everything went smoothly)
// and we can test against it before running palette generation. Which you should do really.

print_r($palette->colorsArray);
```

