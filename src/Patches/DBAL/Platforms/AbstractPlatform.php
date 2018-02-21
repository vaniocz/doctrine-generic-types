<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Platforms;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr0(AbstractPlatform::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

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
                    self::quoteStringLiteral($comment)
                );
        ',
        file_get_contents($originalFile)
    );

    $code = preg_replace(
        '~function\s+isCommentedDoctrineType\s*\(.*\)\s*{~',
        '
            function isCommentedDoctrineType(\Doctrine\DBAL\Types\Type $doctrineType)
            {
                if ($this->doctrineTypeComments === null) {
                    $this->initializeCommentedDoctrineTypes();
                }

                $typeName = $doctrineType instanceof \Vanio\DoctrineGenericTypes\DBAL\GenericType
                    ? $doctrineType->name()
                    : $doctrineType->getName();

                return in_array($typeName, $this->doctrineTypeComments);
        ',
        $code
    );
    @mkdir($cacheDirectory, 0777, true);

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
