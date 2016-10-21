<?php
namespace Vanio\DoctrineGenericTypes\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterTypeGuessersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $typeGuessers = [];

        foreach ($container->findTaggedServiceIds('vanio_doctrine_generic_types.type_guesser') as $id => $tags) {
            $typeGuessers[$tags[0]['priority'] ?? 0][] = new Reference($id);
        }

        if (!$typeGuessers) {
            return;
        }

        krsort($typeGuessers);
        $typeGuessers = array_merge(...$typeGuessers);
        $container->getDefinition('vanio_doctrine_generic_types.orm.type_guesser_chain')->setArguments($typeGuessers);
    }
}
