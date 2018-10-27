<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Schema;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr(AbstractSchemaManager::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+extractDoctrineTypeFromComment\s*\(.*\)\s*{~',
        '
            function extractDoctrineTypeFromComment($comment, $currentType)
            {
                return preg_match("~\(DC2Type:([a-zA-Z0-9_\x7f-\xff\\\\\\\\\[\]<>, ]+)\)~", $comment, $matches)
                    ? $matches[1]
                    : $currentType;
        ',
        file_get_contents($originalFile)
    );
    @mkdir($cacheDirectory, 0777, true);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;

__halt_compiler();
class AbstractSchemaManager
{}
