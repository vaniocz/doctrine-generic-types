<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Internal\Hydration;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(ObjectHydrator::class);
$patchedFile = sprintf('%s/ObjectHydrator_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

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

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
