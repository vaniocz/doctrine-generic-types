<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SQLParserUtils::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
        ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
        : sys_get_temp_dir(),
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = str_replace(
        'const ESCAPED_BRACKET_QUOTED_TEXT = \'\[(?:[^\]])*\]\'',
        'const ESCAPED_BRACKET_QUOTED_TEXT = \'(?<!\bARRAY)\[(?:[^\]])*\]\'',
        file_get_contents($originalFile)
    );
    @mkdir($cacheDirectory, 0777, true);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
