<?php
declare(strict_types=1);

namespace Unn\GettextBlade\Scanner;

use Gettext\Scanner\FunctionsScannerInterface;
use Illuminate\View\Compilers\BladeCompiler;

class BladeRaw extends Blade
{
    /**
     * Retrieve a scanner for Blade functions.
     *
     * @return FunctionsScannerInterface
     */
    public function getFunctionsScanner(): FunctionsScannerInterface
    {
        if (!$this->functionsScanner) {
            $this->functionsScanner = new BladeRawFunctions(array_keys($this->functions));
        }

        return parent::getFunctionsScanner();
    }

    /**
     * Sets a compiler. This method is not necessary for raw Blade files. Calling this method will result in
     * an exception.
     *
     * @param BladeCompiler|null $compiler
     *
     * @throws \BadMethodCallException
     *
     * @return static
     */
    public function setCompiler(?BladeCompiler $compiler)
    {
        throw new \BadMethodCallException("There is no need for a compiler when handling raw Blade files.");
    }
}
