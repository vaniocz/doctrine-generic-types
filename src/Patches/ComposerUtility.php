<?php // @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches;

use Composer\Autoload\ClassLoader;
use Vanio\Stdlib\Objects;

class ComposerUtility
{
    /**
     * Finds a file using PSR0 while resetting composer PSR4 prefix this patch was loaded by.
     *
     * @throws \LogicException
     */
    public static function findClassFileUsingPsr(string $class): string
    {
        $classLoader = self::classLoader();
        $originalClassMap = $classLoader->getClassMap();
        $classMap = &Objects::getPropertyValue($classLoader, 'classMap', ClassLoader::class);
        $classMap = [];

        if (!$file = self::classLoader()->findFile($class)) {
            throw new \LogicException(sprintf('The class "%s" file was not found.', $class));
        }

        $classMap = $originalClassMap;

        return $file;
    }

    /**
     * @throws \LogicException
     */
    public static function classLoader(): ClassLoader
    {
        static $classLoader;

        if ($classLoader) {
            return $classLoader;
        }

        foreach (spl_autoload_functions() as $autoloadFunction) {
            if (is_array($autoloadFunction)) {
                $autoloadFunction = current($autoloadFunction);

                if (
                    $autoloadFunction instanceof \Symfony\Component\Debug\DebugClassLoader
                    || $autoloadFunction instanceof \Symfony\Component\ErrorHandler\DebugClassLoader
                ) {
                    $autoloadFunction = current($autoloadFunction->getClassLoader());
                }

                if ($autoloadFunction instanceof ClassLoader) {
                    $classLoader = $autoloadFunction;

                    return $classLoader;
                }
            }
        }

        throw new \LogicException('Composer autoloader must be registered.');
    }
}
