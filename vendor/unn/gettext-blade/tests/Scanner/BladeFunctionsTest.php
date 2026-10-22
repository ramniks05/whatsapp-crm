<?php

namespace Unn\GettextBlade\Tests;

use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;
use Unn\GettextBlade\Scanner\BladeFunctions;

class BladeFunctionsTest extends TestCase
{
    public function testScan()
    {
        $scanner = new BladeFunctions();
        $file = dirname(__DIR__) . '/assets/example.blade.php';
        $code = @file_get_contents($file);

        if ($code === false) {
            throw new \Exception('Cannot read example.blade.php file. This should not happen.');
        }

        $functions = $scanner->scan($code, $file);

        $this->assertNotEmpty($functions);

        // first function should be _i
        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['This is a variable.'], $function->getArguments());
        $this->assertSame(2, $function->getLine());
        $this->assertSame($file, $function->getFilename());
    }

    public function testSetCompiler()
    {
        $scanner = new BladeFunctions();
        $compiler = new BladeCompiler(new \Illuminate\Filesystem\Filesystem, sys_get_temp_dir());

        $this->assertSame($scanner, $scanner->setCompiler(null));
        $this->assertSame($scanner, $scanner->setCompiler($compiler));
    }
}
