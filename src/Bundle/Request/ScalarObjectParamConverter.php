<?php
namespace Vanio\DoctrineGenericTypes\Bundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;

class ScalarObjectParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();
        $value = $request->attributes->get($configuration->getName());
        $request->attributes->set($configuration->getName(), new $class($value));

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return is_a($configuration->getClass(), ScalarObject::class, true);
    }
}
