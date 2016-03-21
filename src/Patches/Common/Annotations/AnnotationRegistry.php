<?php // @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches\Common\Anotations;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(AnnotationRegistry::class);
$patchedFile = sprintf('%s/AnnotationRegistry_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+registerFile\s*\(.*\)\s*{~',
        sprintf(
            '
                function registerFile($file) {
                    $file = str_replace("\\\\\", "/", realpath($file));

                    if (\Vanio\Stdlib\Strings::endsWith($file, "/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php")) {
                        require_once "%1$s/../../ORM/Mapping/Driver/DoctrineAnnotations.php";
                    } elseif (\Vanio\Stdlib\Strings::endsWith($file, "/doctrine/orm/lib/Doctrine/ORM/Mapping/Column.php")) {
                        require_once "%1$s/../../ORM/Mapping/Column.php";
                    } else {
                        require_once $file;
                    }

                    return;
            ',
            addslashes(__DIR__)
        ),
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
