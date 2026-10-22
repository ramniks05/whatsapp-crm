<?php

namespace Unn\GettextBlade\Scanner;

use Gettext\Scanner\ParsedFunction;
use Gettext\Scanner\PhpFunctionsScanner;

class BladeRawFunctions extends PhpFunctionsScanner
{
    /**
     * Compiles and scans Blade code for translateable strings.
     *
     * @param string $code
     * @param string $filename
     *
     * @return array
     */
    public function scan(string $code, string $filename): array
    {
        $result = [];

        // parse all functions into a regex pattern
        if (!$this->validFunctions) {
            $fn = '[a-zA-Z0-9_]+';
        } else {
            $fn = implode("|", $this->validFunctions);
        }
        $pattern = sprintf("#(%s)\((.*?)(\))#si", $fn);

        if (preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $i => $match) {
                if ($i !== 1) {
                    continue;
                }

                foreach ($match as $j => $m) {
                    list($matchedFunction, $offset) = $m;
                    $offsetClosingBracket = $matches[3][$j][1];
                    $lineNumber = substr_count(mb_substr($code, 0, $offset), "\n") + 1;
                    $lastLineNumber = substr_count(mb_substr($code, 0, $offsetClosingBracket), "\n") + 1;
                    $argumentStr = $matches[2][$j][0];

                    // we need to simulate some dummy php code so that the php parser can work its magic.
                    $functionStr = "<?php " . $matchedFunction . "(" . $argumentStr . "); ?>";

                    // utilize gettext's PhpFunctionsScanner to retrieve information about the given function
                    /** @var ParsedFunction $parsedFunctionTmp */
                    $parsedFunctionTmp = current(parent::scan($functionStr, $filename));

                    // use the "correct" line number instead of a single line
                    $parsedFunction = new ParsedFunction(
                        $parsedFunctionTmp->getName(),
                        $filename,
                        $lineNumber,
                        $lastLineNumber
                    );

                    foreach ($parsedFunctionTmp->getArguments() as $argument) {
                        $parsedFunction->addArgument($argument);
                    }

                    $result[] = $parsedFunction;
                }
            }
        }

        return $result;
    }
}
