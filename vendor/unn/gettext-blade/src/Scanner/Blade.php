<?php
declare(strict_types=1);

namespace Unn\GettextBlade\Scanner;

use Gettext\Scanner\FunctionsHandlersTrait;
use Gettext\Scanner\FunctionsScannerInterface;
use Gettext\Scanner\PhpScanner;
use Illuminate\View\Compilers\BladeCompiler;

class Blade extends PhpScanner
{
    use FunctionsHandlersTrait;

    /**
     * All supported functions mapped onto gettext functions.
     *
     * @var array
     */
    protected $functions = [
        '__' => 'gettext',
        '_i' => 'gettext',
        '_n' => 'ngettext',
    ];

    /**
     * Undocumented variable
     *
     * @var BladeCompiler|null
     */
    protected $compiler = null;

    /**
     * Blade functions scanner.
     *
     * @var BladeFunctions
     */
    protected $functionsScanner;

    /**
     * Retrieve a scanner for Blade functions.
     *
     * @return FunctionsScannerInterface
     */
    public function getFunctionsScanner(): FunctionsScannerInterface
    {
        if (!$this->functionsScanner) {
            $this->functionsScanner = new BladeFunctions(array_keys($this->functions));
            $this->functionsScanner->setCompiler($this->compiler);
        }

        return $this->functionsScanner;
    }

    /**
     * Sets a compiler for Blade code. If no compiler is given, the default
     * Illuminate Blade compiler will be used.
     *
     * @param BladeCompiler|null $compiler
     * @return $this
     */
    public function setCompiler(?BladeCompiler $compiler)
    {
        $this->compiler = $compiler;

        // pass compiler to the Blade functions scanner (if present)
        if ($this->functionsScanner) {
            $this->functionsScanner->setCompiler($compiler);
        }

        return $this;
    }

    /**
     * Scans Blade files for translateable strings.
     *
     * @param string $string
     * @param string $filename
     * @return void
     */
    public function scanString(string $string, string $filename): void
    {
        // let the PHP scanner do its magic
        parent::scanString($string, $filename);
    }
}
