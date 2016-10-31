<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(UnitOfWork::class);
$patchedFile = sprintf('%s/UnitOfWork_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~foreach\s*\(\$data\s*as\s*\$field\s*=>\s*\$value\)\s*{\s*if\s*\(isset\(\$class->fieldMappings\[\$field\]\)\)\s*{\s*\$class->reflFields\[\$field\]->setValue\(\$entity,\s*\$value\);\s*}\s*}~',
        '$class->populateEntity($entity, $data);',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;