<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SQLParserUtils::class);
$patchedFile = sprintf('%s/SQLParserUtils_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = str_replace(
        'const ESCAPED_BRACKET_QUOTED_TEXT = \'\[(?:[^\]])*\]\'',
        'const ESCAPED_BRACKET_QUOTED_TEXT = \'(?<!\bARRAY)\[(?:[^\]])*\]\'',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
