<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Internal\Hydration;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(ObjectHydrator::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
        ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
        : sys_get_temp_dir(),
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = str_replace(
        'unset($data[$discrColumn]);',
        '
            $classMetadata = $this->_metadataCache[$this->_rsm->aliasMap[$dqlAlias]];

            if (!isset($classMetadata->fieldMappings[$classMetadata->discriminatorColumn["fieldName"]])) {
                unset($data[$discrColumn]);
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
