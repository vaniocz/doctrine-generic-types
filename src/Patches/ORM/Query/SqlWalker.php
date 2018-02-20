<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Query;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SqlWalker::class);
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
    $code = preg_replace(
        '~throw\s*QueryException::associationPathCompositeKeyNotSupported\(\s*\);~',
        '
            $targetEntityMetadata = $this->em->getClassMetadata($class->associationMappings[$fieldName]["targetEntity"]);
    
            if ($targetEntityMetadata->identifierDiscriminatorField) {
                unset($assoc["targetToSourceKeyColumns"][$targetEntityMetadata->identifierDiscriminatorField]);
    
                if (count($assoc["targetToSourceKeyColumns"]) > 1) {
                    throw QueryException::associationPathCompositeKeyNotSupported();
                }
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
