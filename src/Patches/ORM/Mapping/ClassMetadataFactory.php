<?php // @codeCoverageIgnoreStart
namespace Doctrine\ORM\Mapping;

use Vanio\DoctrineGenericTypes\Patches\ComposerUtility;

$originalFile = ComposerUtility::findClassFileUsingPsr0(ClassMetadataFactory::class);
$patchedFile = sprintf('%s/ClassMetadataFactory_%s_%s.php', sys_get_temp_dir(), md5(__DIR__), filemtime($originalFile));

if (!is_readable($patchedFile)) {
    $code = preg_replace(
        '~function\s+addNestedEmbeddedClasses\s*\(.*\)\s*{~',
        '
            function addNestedEmbeddedClasses(ClassMetadata $subClass, ClassMetadata $parentClass, $prefix)
            {
                foreach ($subClass->embeddedClasses as $property => $embeddableClass) {
                    if (isset($embeddableClass["inherited"])) {
                        continue;
                    }
        
                    $embeddableMetadata = $this->getMetadataFor($embeddableClass["class"]);
                    $parentClass->mapEmbedded([
                        "fieldName" => "$prefix.$property",
                        "class" => $embeddableMetadata->name,
                        "nullable" => $embeddableClass["nullable"],
                        "columnPrefix" => $embeddableClass["columnPrefix"],
                        "originalField" => $embeddableClass["originalField"] ?: $property,
                        "declaredField" => $embeddableClass["declaredField"]
                            ? "$prefix.$embeddableClass[declaredField]"
                            : $prefix,
                    ]);
                }

                return;
        ',
        file_get_contents($originalFile)
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;
