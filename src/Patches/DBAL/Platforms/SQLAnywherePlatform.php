<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Platforms;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SQLAnywherePlatform::class);
$patchedFile = sprintf('%s/SQLAnywherePlatform_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+getCommentOnColumnSQL\s*\(.*\)\s*{~',
        '
            function getCommentOnColumnSQL($table, $column, $comment)
            {
                return sprintf(
                    "COMMENT ON COLUMN %s.%s IS %s",
                    (new Identifier($table))->getQuotedName($this),
                    (new Identifier($column))->getQuotedName($this),
                    $comment === null ? "NULL" : parent::quoteStringLiteral($comment)
                );
        ',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
