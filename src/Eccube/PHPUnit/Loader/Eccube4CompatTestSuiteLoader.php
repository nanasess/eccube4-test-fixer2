<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\PHPUnit\Loader;

use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Runner\StandardTestSuiteLoader;

class Eccube4CompatTestSuiteLoader extends StandardTestSuiteLoader
{
    public function load($suiteClassName, $suiteClassFile = '')
    {
        $tokens = Tokens::fromCode(file_get_contents($suiteClassFile));

        $currentIndex = 0;
        while ($matchedTokens = $tokens->findSequence([
            [T_STRING, 'self'],
            [T_DOUBLE_COLON, '::'],
            [T_VARIABLE, '$container'],
        ], $currentIndex)) {
            $indexes = array_keys($matchedTokens);
            $matchedTokens[$indexes[0]]->override([T_VARIABLE, '$this']);
            $matchedTokens[$indexes[1]]->override([T_OBJECT_OPERATOR, '->']);
            $matchedTokens[$indexes[2]]->override([T_STRING, 'container']);
            $currentIndex = $indexes[2] + 1;
        }

        $f = tmpfile();
        fwrite($f, $tokens->generateCode());
        rewind($f);

        return parent::load($suiteClassName, stream_get_meta_data($f)['uri']);
    }
}
