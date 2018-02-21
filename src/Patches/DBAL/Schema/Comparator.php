<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Schema;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr0(Comparator::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~if\s*\(\$key1->onUpdate\(\)\s*!=\s*\$key2->onUpdate\(\)\)\s*{~',
        '
            if ($key1->onUpdate() != $key2->onUpdate()) {
                $class = debug_backtrace()[5]["class"] ?? null;

                if (is_a($class, "Doctrine\Bundle\MigrationsBundle\Command\MigrationsDiffDoctrineCommand", true)) {
                    return $key1->onDelete() != $key2->onDelete();
                }
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
