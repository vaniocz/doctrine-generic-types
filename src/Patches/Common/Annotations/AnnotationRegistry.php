<?php // @codeCoverageIgnoreStart
namespace Doctrine\Common\Annotations;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(AnnotationRegistry::class);
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
    $code = preg_replace(
        '~function\s+registerFile\s*\(.*\)\s*(:(?:.*))?\s*{~',
        sprintf(
            '
                function registerFile($file)$1
                {
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
    @mkdir($cacheDirectory, 0777, true);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
