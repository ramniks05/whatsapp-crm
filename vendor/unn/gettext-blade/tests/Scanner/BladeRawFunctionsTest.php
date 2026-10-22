<?php

namespace Unn\GettextBlade\Tests;

use Gettext\Scanner\ParsedFunction;
use PHPUnit\Framework\TestCase;
use Unn\GettextBlade\Scanner\BladeRawFunctions;

class BladeRawFunctionsTest extends TestCase
{
    public function testScan()
    {
        $scanner = new BladeRawFunctions([
            '_i',
            '_n',
            '__',
        ]);
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
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['This is a variable.'], $function->getArguments());
        $this->assertSame(2, $function->getLine());
        $this->assertSame(2, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['This is the page title'], $function->getArguments());
        $this->assertSame(12, $function->getLine());
        $this->assertSame(12, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(2, $function->countArguments());
        $this->assertSame(['There are %s red cars.', null], $function->getArguments());
        $this->assertSame(15, $function->getLine());
        $this->assertSame(15, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('__', $function->getName());
        $this->assertSame(16, $function->getLine());
        $this->assertSame(16, $function->getLastLine());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_n', $function->getName());
        $this->assertSame(3, $function->countArguments());
        $this->assertSame(
            ['There is also a single green car.', 'There are also %d green cars.', 2],
            $function->getArguments()
        );
        $this->assertSame(17, $function->getLine());
        $this->assertSame(17, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(22, $function->getLine());
        $this->assertSame(22, $function->getLastLine());

        /** @var \Gettext\Scanner\ParsedFunction $function */
        $function = array_shift($functions);
        $this->assertInstanceOf(ParsedFunction::class, $function);
        $this->assertSame('_i', $function->getName());
        $this->assertSame(2, $function->countArguments());
        $this->assertSame(['This is a multi-line function call. %s', 'Yes.'], $function->getArguments());
        $this->assertSame(25, $function->getLine());
        $this->assertSame(28, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
    }
}
