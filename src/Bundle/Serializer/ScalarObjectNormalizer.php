<?php
namespace Vanio\DoctrineGenericTypes\Bundle\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;

class ScalarObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param ScalarObject $object
     * @param string|null $format
     * @param mixed[] $context
     * @return string|int|float|bool
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->scalarValue();
    }

    /**
     * @param string|int|float|bool $data
     * @param string $class
     * @param string|null $format
     * @param mixed[] $context
     * @return ScalarObject
     */
    public function denormalize($data, $class, $format = null, array $context = []): ScalarObject
    {
        return is_callable([$class, 'create']) ? $class::create($value) : new $class($value);
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ScalarObject;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_scalar($data) && is_a($type, ScalarObject::class, true);
    }
}
