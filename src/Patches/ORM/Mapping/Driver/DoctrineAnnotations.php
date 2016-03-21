<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Mapping\Driver;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(__NAMESPACE__ . '\DoctrineAnnotations');
$patchedFile = sprintf('%s/DoctrineAnnotations_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = str_replace(
        "__DIR__.'/../Column.php'",
        sprintf("'%s'", addslashes(__DIR__ . '/../Column.php')),
        file_get_contents($originalFile)
    );
    $code = str_replace('__DIR__', sprintf("'%s'", addslashes(dirname($originalFile))), $code);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
