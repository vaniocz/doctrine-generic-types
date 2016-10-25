<?php // @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Debug\DebugClassLoader;

class ComposerUtility
{
    /**
     * Finds a file using PSR0 while resetting composer PSR4 prefix this patch was loaded by.
     *
     * @throws \LogicException
     */
    public static function findClassFileUsingPsr0(string $class): string
    {
        $classLoader = self::getClassLoader();
        $psr4Prefix = 'Doctrine\\';
        $paths = $classLoader->getPrefixesPsr4()[$psr4Prefix] ?? [];
        $classLoader->setPsr4($psr4Prefix, []);

        if (!$file = $classLoader->findFile($class)) {
            throw new \LogicException(sprintf('The class "%s" file was not found.', $class));
        }

        $classLoader->setPsr4($psr4Prefix, $paths);

        return $file;
    }

    /**
     * @throws \LogicException
     */
    public static function getClassLoader(): ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloadFunction) {
            if (is_array($autoloadFunction)) {
                $autoloadFunction = current($autoloadFunction);

                if ($autoloadFunction instanceof DebugClassLoader) {
                    $autoloadFunction = current($autoloadFunction->getClassLoader());
                }

                if ($autoloadFunction instanceof ClassLoader) {
                    return $autoloadFunction;
                }
            }
        }

        throw new \LogicException('Composer autoloader must be registered.');
    }
}
