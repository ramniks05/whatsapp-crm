# Gettext-Blade
Blade code scanner for [gettext/gettext](https://github.com/php-gettext/Gettext)

## Installation

Add repository to `composer.json`:
```
{
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/livelyworks/Gettext-Blade"
        }
    ]
}
```

Add package to `composer.json`:
```
{
    "require": {
        "unn/gettext-blade": "dev-main"
    }
}
```

## Usage

After installation, initialize the `BladeScanner`.

```php
use Gettext\Generator\PoGenerator;
use Gettext\Translations;
use Unn\GettextBlade\Scanner\Blade as BladeScanner;

// create a new instance of BladeScanner with two domains
$scanner = new BladeScanner(
    Translations::create('domain1'),
    Translations::create('domain2')
);

// specify the default domain. All translations without domain will be mapped to 'domain1'
$scanner->setDefaultDomain('domain1');

// Scan for Blade files
foreach (glob('*.blade.php') as $file) {
    $scanner->scanFile($file);

    // or: use BladeScanner to scan Blade code strings
    // $code = file_get_contents($file);
    // $scanner->scanString($code, $file);
}

// save the translations to .po files using the gettext PoGenerator
$generator = new PoGenerator();

foreach ($scanner->getTranslations() as $domain => $translations) {
    $generator->generateFile($translations, "locales/{$domain}.po");
}
```

### Set custom BladeCompiler
Under its hood, `BladeScanner` uses the default `BladeCompiler` that is part of the `Illuminate` package. However, it is also possible to use a configurated `BladeCompiler`using the method `setCompiler()`.

```php
use Illuminate\View\Compilers\BladeCompiler;
use Unn\GettextBlade\Scanner\Blade as BladeScanner;

// create BladeScanner
$scanner = new BladeScanner();

// create BladeCompiler
$compiler = new BladeCompiler(/** ... */);

// Set the custom BladeCompiler
// Important: setCompiler() only accepts Illuminates BladeCompiler
$scanner->setCompiler($compiler);
```

### Set custom functions
It is possible to set custom functions that `BladeScanner` should search for translateable strings. For this purpose, provide a array that maps the names of these custom functions to gettext functions.

```php
use Unn\GettextBlade\Scanner\Blade as BladeScanner;

// create BladeScanner
$scanner = new BladeScanner();

// set functions
$scanner->setFunctions([
    '_i' => 'gettext',
    '_n' => 'ngettext',
]);
```
