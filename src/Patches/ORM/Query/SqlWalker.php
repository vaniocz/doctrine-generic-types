<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Query;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(SqlWalker::class);
$patchedFile = sprintf('%s/SqlWalker_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

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

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
