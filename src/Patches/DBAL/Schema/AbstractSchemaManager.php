<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Schema;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(AbstractSchemaManager::class);
$patchedFile = sprintf('%s/AbstractSchemaManager_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+extractDoctrineTypeFromComment\s*\(.*\)\s*{~',
        '
            function extractDoctrineTypeFromComment($comment, $currentType)
            {
                return preg_match("~\(DC2Type:([a-zA-Z0-9_\x7f-\xff\\\\\\\\\[\]<>, ]+)\)~", $comment, $matches)
                    ? $matches[1]
                    : $currentType;
        ',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
