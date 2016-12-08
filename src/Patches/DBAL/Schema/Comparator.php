<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Schema;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(Comparator::class);
$patchedFile = sprintf('%s/Comparator_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

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

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
