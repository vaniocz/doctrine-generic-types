<?php // @codeCoverageIgnoreStart
namespace Doctrine\DBAL\Schema;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr0(PostgreSqlSchemaManager::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+_getPortableSequenceDefinition\s*\(.*\)\s*{~',
        '
            function _getPortableSequenceDefinition($sequence)
            {
                if ($sequence["schemaname"] != "public") {
                    $sequenceName = "$sequence[schemaname].$sequence[relname]";
                } else {
                    $sequenceName = $sequence["relname"];
                }
            
                $version = (float) $this->_conn->getWrappedConnection()->getServerVersion();
                $data = $this->_conn->fetchAll(sprintf(
                    $version >= 10
                        ? "SELECT min_value, increment_by FROM pg_sequences WHERE schemaname = \'public\' AND sequencename = %s"
                        : "SELECT min_value, increment_by FROM %s",
                    $version >= 10
                        ? $this->_conn->quote($sequenceName)
                        : $this->_platform->quoteIdentifier($sequenceName)
                ));
            
                return new Sequence($sequenceName, $data[0]["increment_by"], $data[0]["min_value"]);
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
