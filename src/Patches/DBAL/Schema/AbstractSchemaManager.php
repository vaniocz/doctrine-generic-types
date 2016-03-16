<?php
// @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches\DBAL\Schema\AbstractSchemaManager;

use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

$directory = sys_get_temp_dir();
$patchedFile = $directory . '/DoctrineDBALSchemaAbstractSchemaManager.php';

if (!is_readable($patchedFile)) {
    $code = str_replace(
        '"(\(DC2Type:([a-zA-Z0-9_]+)\))"',
        '"(\(DC2Type:([a-zA-Z0-9_\x7f-\xff\\\\\[\]<>, ]+)\))"',
        file_get_contents(find_psr0_class_file(AbstractSchemaManager::class, 'Doctrine\\'))
    );

    if (!@file_put_contents($patchedFile, $code)) {
        eval('?>' . $code);

        return;
    }
}

require $patchedFile;

/**
 * Finds a file using PSR0 while resetting composer PSR4 prefix this patch was loaded by.
 *
 * @param string $class
 * @param string $psr4Prefix
 * @return string
 */
function find_psr0_class_file(string $class, string $psr4Prefix): string
{
    $classLoader = get_class_loader();
    $paths = $classLoader->getPrefixesPsr4()[$psr4Prefix] ?? [];
    $classLoader->setPsr4($psr4Prefix, []);

    if (!$file = $classLoader->findFile($class)) {
        throw new \LogicException(sprintf('The class "%s" file was not found.', $class));
    }

    $classLoader->setPsr4($psr4Prefix, $paths);

    return $file;
}

function get_class_loader(): ClassLoader
{
    foreach (spl_autoload_functions() as $autoloadFunction) {
        if (is_array($autoloadFunction)) {
            $autoloadFunction = current($autoloadFunction);

            if ($autoloadFunction instanceof ClassLoader) {
                return $autoloadFunction;
            }
        }
    }

    throw new \LogicException('Composer autoloader must be registered.');
}
