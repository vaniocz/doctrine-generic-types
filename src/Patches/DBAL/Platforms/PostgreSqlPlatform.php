<?php // @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(PostgreSqlPlatform::class);
$patchedFile = sprintf('%s/PostgreSqlPlatform_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

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

    $code = preg_replace(
        '~if\s*\(\$columnDiff->hasChanged\(\'type\'\)\s*\|\|\s*\$columnDiff->hasChanged\(\'precision\'\)\s*\|\|\s*\$columnDiff->hasChanged\(\'scale\'\)\s*\|\|\s*\$columnDiff->hasChanged\(\'fixed\'\)\)\s*{~',
        '
            $fromTypeName = $columnDiff->fromColumn->getType() instanceof \Vanio\DoctrineGenericTypes\DBAL\GenericType
                ? $columnDiff->fromColumn->getType()->name()
                : $columnDiff->fromColumn->getType()->getName();
            $toTypeName = $columnDiff->column->getType() instanceof \Vanio\DoctrineGenericTypes\DBAL\GenericType
                ? $columnDiff->column->getType()->name()
                : $columnDiff->column->getType()->getName();
        
            if (
                $fromTypeName !== $toTypeName
                || $columnDiff->hasChanged("precision")
                || $columnDiff->hasChanged("scale")
                || $columnDiff->hasChanged("fixed")
            ) {
        ',
        $code
    );

    $code = preg_replace(
        '~if\s*\(\$columnDiff->hasChanged\(\'default\'\)\s*\|\|\s*\$columnDiff->hasChanged\(\'type\'\)\)\s*{~',
        'if ($fromTypeName !== $toTypeName || $columnDiff->hasChanged("default")) {',
        $code
    );

    $code = preg_replace(
        '~if\s*\(\$columnDiff->hasChanged\(\'comment\'\)\)\s*{~',
        '
            $doctrineTypeCommentChanged = $columnDiff->hasChanged(\'type\') && (
                $columnDiff->fromColumn->getType()->requiresSQLCommentHint($this)
                || $columnDiff->column->getType()->requiresSQLCommentHint($this)
            );

            if ($columnDiff->hasChanged(\'comment\') || $doctrineTypeCommentChanged) {',
        $code
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
