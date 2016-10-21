<?php
namespace Vanio\DoctrineGenericTypes\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vanio\DoctrineGenericTypes\Bundle\DependencyInjection\RegisterTypeGuessersCompilerPass;
use Vanio\DoctrineGenericTypes\DBAL\GenericTypeRegistry;

class VanioDoctrineGenericTypesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterTypeGuessersCompilerPass);
    }

    public function boot()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        (new GenericTypeRegistry)->register();
    }
}
