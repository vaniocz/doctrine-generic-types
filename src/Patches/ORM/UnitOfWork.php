<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(UnitOfWork::class);
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
        '~function\s+addToIdentityMap\s*\(.*\)\s*{~',
        '
            function addToIdentityMap($entity)
            {
                $classMetadata = $this->em->getClassMetadata(get_class($entity));
                $entityIdentifiers = $this->entityIdentifiers[spl_object_hash($entity)];
                $idHash = implode(" ", $entityIdentifiers);

                if ($idHash === "") {
                    throw ORMInvalidArgumentException::entityWithoutIdentity($classMetadata->name, $entity);
                }

                $className = $classMetadata->rootEntityName;

                if (isset($this->identityMap[$className][$idHash])) {
                    return false;
                }

                $this->identityMap[$className][$idHash] = $entity;

                if ($classMetadata->identifierDiscriminatorField && count($entityIdentifiers) === 2) {
                    unset($entityIdentifiers[$classMetadata->identifierDiscriminatorField]);
                    $this->identityMap[$className][(string) current($entityIdentifiers)] = $entity;
                }

                return true;
        ',
        file_get_contents($originalFile)
    );
    $code = preg_replace(
        '~\$this->identityMap\[\$class->rootEntityName\]\[\$idHash\]\s*=\s*\$entity~',
        '
            $this->identityMap[$class->rootEntityName][$idHash] = $entity;

            if ($class->identifierDiscriminatorField && count($id) === 2) {
                $singleId = $id;
                unset($singleId[$class->identifierDiscriminatorField]);
                $this->identityMap[$class->rootEntityName][(string) current($id)] = $entity;
            }
        ',
        $code
    );
    $code = preg_replace(
        '~foreach\s*\(\$data\s*as\s*\$field\s*=>\s*\$value\)\s*{\s*if\s*\(isset\(\$class->fieldMappings\[\$field\]\)\)\s*{\s*\$class->reflFields\[\$field\]->setValue\(\$entity,\s*\$value\);\s*}\s*}~',
        '$class->populateEntity($entity, $data);',
        $code
    );
    $code = preg_replace(
        '~case\s*\(\$targetClass->subClasses\):.+?default:~s',
        '
            case ($targetClass->subClasses):
                if ($targetClass->identifierDiscriminatorField !== null && isset($associatedId[$targetClass->identifierDiscriminatorField])) {
                    $assoc["targetEntity"] = $targetClass->discriminatorMap[$associatedId[$targetClass->identifierDiscriminatorField]];
                } else {
                    $newValue = $this->getEntityPersister($assoc["targetEntity"])->loadOneToOneEntity($assoc, $entity, $associatedId);
                    break;
                }
            default:
        ',
        $code
    );
    $code = preg_replace(
        '~function\s+getSingleIdentifierValue\s*\(.*\)\s*{~',
        '
            function getSingleIdentifierValue($entity)
            {
                $classMetadata = $this->em->getClassMetadata(get_class($entity));
                $values = $this->isInIdentityMap($entity)
                    ? $this->getEntityIdentifier($entity)
                    : $classMetadata->getIdentifierValues($entity);
                $idField = $classMetadata->getSingleIdentifierFieldName();
            
                return isset($values[$idField]) ? $values[$idField] : null;
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
