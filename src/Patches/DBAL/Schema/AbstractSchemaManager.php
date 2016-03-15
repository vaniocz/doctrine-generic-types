<?php
use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

const DOCTRINE_PSR4_PREFIX = 'Doctrine\\';

foreach (spl_autoload_functions() as $autoloader) {
    if (!is_array($autoloader)) {
        continue;
    }

    $autoloader = current($autoloader);

    if (!$autoloader instanceof ClassLoader) {
        continue;
    }

    $paths = $autoloader->getPrefixesPsr4()[DOCTRINE_PSR4_PREFIX] ?? [];
    $autoloader->setPsr4(DOCTRINE_PSR4_PREFIX, []);
    $originalFile = $autoloader->findFile(AbstractSchemaManager::class);
    $autoloader->setPsr4(DOCTRINE_PSR4_PREFIX, $paths);
    $code = file_get_contents($originalFile);
    $code = str_replace('"(\(DC2Type:([a-zA-Z0-9_]+)\))"', '"(\(DC2Type:([a-zA-Z0-9_<>, ]+)\))"', $code);
    $directory = sys_get_temp_dir();
    $patchedFile = $directory . '/DoctrineDBALSchemaAbstractSchemaManager.php';

    if (!is_file($patchedFile)) {
        if (is_writable($directory)) {
            file_put_contents($patchedFile, $code);
        } else {
            eval(sprintf('?>%s', $code));
            return;
        }
    }

    require $patchedFile;
}
