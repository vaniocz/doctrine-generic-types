<?php // @codeCoverageIgnoreStart
namespace Vanio\DoctrineGenericTypes\Patches;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Debug\DebugClassLoader;

class ComposerUtility
{
    /** @var string[] */
    public static $psr4Paths = [];

    /**
     * Finds a file using PSR0 while resetting composer PSR4 prefix this patch was loaded by.
     *
     * @throws \LogicException
     */
    public static function findClassFileUsingPsr0(string $class): string
    {
        self::resetPsr4('Doctrine\\');
        self::resetPsr4('Doctrine\\Common\\Annotations\\');

        if (!$file = self::classLoader()->findFile($class)) {
            throw new \LogicException(sprintf('The class "%s" file was not found.', $class));
        }

        self::restorePsr4();

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

                if ($autoloadFunction instanceof DebugClassLoader) {
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

    private static function resetPsr4(string $prefix)
    {
        $classLoader = self::classLoader();
        self::$psr4Paths[$prefix] = $classLoader->getPrefixesPsr4()[$prefix] ?? [];
        $classLoader->setPsr4($prefix, []);
    }

    private static function restorePsr4()
    {
        $classLoader = self::classLoader();

        foreach (self::$psr4Paths as $prefix => $path) {
            $classLoader->setPsr4($prefix, $path);
        }
    }
}
