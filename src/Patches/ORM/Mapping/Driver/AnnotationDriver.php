<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Mapping\Driver;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(AnnotationDriver::class);
$patchedFile = sprintf('%s/AnnotationDriver_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~throw.+MappingException::propertyTypeIsRequired\s*\(.*\)~',
        '',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
