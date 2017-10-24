<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Tools;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SchemaTool::class);
$patchedFile = sprintf('%s/SchemaTool_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~\$table->addColumn\(\$discrColumn\[\'name\'\],\s*\$discrColumn\[\'type\'\],\s*\$options\);~',
        '
            if (!$table->hasColumn($discrColumn["name"])) {
                $0
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
