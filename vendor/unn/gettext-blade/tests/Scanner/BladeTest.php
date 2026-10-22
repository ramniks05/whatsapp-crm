<?php

namespace Unn\GettextBlade\Tests;

use Gettext\Translations;
use illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;
use Unn\GettextBlade\Scanner\Blade;

class BladeTest extends TestCase
{
    public function testBladeScanner()
    {
        $filePath = dirname(__DIR__) . '/assets/example.blade.php';

        $bladeScanner = new Blade(
            Translations::create('domain1')
        );

        $this->assertCount(1, $bladeScanner->getTranslations());

        $fileContent = @file_get_contents($filePath);

        if ($fileContent === false) {
            throw new \Exception('Cannot read example.blade.php file. This should not happen.');
        }

        $bladeScanner->scanFile($filePath);

        list('domain1' => $domain1) = $bladeScanner->getTranslations();

        $this->assertCount(0, $domain1);

        $bladeScanner->setDefaultDomain('domain1');
        $bladeScanner->extractCommentsStartingWith('');
        $bladeScanner->scanFile($filePath);

        $this->assertCount(7, $domain1);

        /** @var \Gettext\Translation $translation */
        $translation = $domain1->find(null, 'There are %s red cars.');
        $this->assertNotNull($translation);
        $this->assertSame([$filePath => [15]], $translation->getReferences()->toArray());

        /** @var \Gettext\Translation $translation */
        $translation = $domain1->find(null, 'Help, there is a comment above me.');
        $this->assertSame([$filePath => [22]], $translation->getReferences()->toArray());
        $this->assertCount(1, $translation->getExtractedComments());

        /** @var \Gettext\Translation $translation */
        $translation = $domain1->find(null, 'There is also a single green car.');
        $this->assertSame([$filePath => [17]], $translation->getReferences()->toArray());
        $this->assertSame('There are also %d green cars.', $translation->getPlural());
    }

    public function testSetCompiler()
    {
        $scanner = new Blade();
        $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());

        // test fluent interface
        $this->assertSame($scanner, $scanner->setCompiler(null));
        $this->assertSame($scanner, $scanner->setCompiler($compiler));
    }
}
