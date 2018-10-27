<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Persisters\Entity;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$cacheDirectory = defined('VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY')
    ? VANIO_DOCTRINE_GENERIC_TYPES_CACHE_DIRECTORY
    : sys_get_temp_dir();
$originalFile = ComposerUtility::findClassFileUsingPsr(BasicEntityPersister::class);
$patchedFile = sprintf(
    '%s/%s_%s_%s.php',
    $cacheDirectory,
    basename(__FILE__, '.php'),
    md5(__DIR__),
    filemtime($originalFile)
);

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~if\s*\(count\(\$columns\)\s*>\s*1\s*&&\s*\$comparison\s*===\s*Comparison::IN\)\s*{~',
        '
            $count = is_array($value) ? count($value) : 1;
    
            if (count($columns) !== $count && isset($this->class->associationMappings[$field])) {
                $targetEntity = $this->class->associationMappings[$field]["targetEntity"];
                
                if (!$value instanceof $targetEntity) {
                    $targetMetadata = $this->em->getClassMetadata($targetEntity);
        
                    if ($targetMetadata->identifierDiscriminatorField !== null) {
                        foreach ($this->class->associationMappings[$field]["joinColumns"] as $joinColumn) {
                            if ($joinColumn["referencedColumnName"] === $targetMetadata->discriminatorColumn["name"]) {
                                $column = sprintf(
                                    "%s.%s",
                                    $this->getSQLTableAlias($this->class->name),
                                    $this->quoteStrategy->getJoinColumnName($joinColumn, $this->class, $this->platform)
                                );
                                $columns = array_diff($columns, [$column]);
                                break;
                            }
                        }
                    }
                }
            }

            $0
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

__halt_compiler();
class BasicEntityPersister
{}
