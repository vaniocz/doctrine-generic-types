<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Mapping\Driver;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(YamlDriver::class);
$patchedFile = sprintf('%s/YamlDriver_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = str_replace(
        "\$embeddedMapping['class'],",
        '
            $embeddedMapping[\'class\'],
            "nullable" => $embeddedMapping["nullable"] ?? null,
        ',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;