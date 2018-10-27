<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Mapping\Driver;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr(AnnotationDriver::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~throw.+MappingException::propertyTypeIsRequired\s*\(.*\)~',
        '',
        file_get_contents($originalFile)
    );
    $code = preg_replace(
        '~\$mapping\[\'columnPrefix\'\]\s*=\s*\$embeddedAnnot->columnPrefix~',
        '
           $mapping["columnPrefix"] = $embeddedAnnot->columnPrefix;
           $mapping["nullable"] = $embeddedAnnot->nullable;
        ',
        $code
    );
    @mkdir($cacheDirectory, 0777, true);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;

__halt_compiler();
class AnnotationDriver
{}
