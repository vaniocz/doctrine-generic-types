<?php
namespace Vanio\DoctrineGenericTypes\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;

class ScalarObjectType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', $options['type'], $options['options'] + [
            'required' => true,
            'label' => false,
        ]);
        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'empty_data' => null,
                'error_bubbling' => false,
                'type' => null,
                'options' => [],
            ])
            ->setAllowedTypes('type', ['string', 'null'])
            ->setAllowedTypes('options', 'array')
            ->setNormalizer('type', $this->typeNormalizer());
    }

    /**
     * @param ScalarObject|null $data
     * @param \Iterator|FormInterface[] $forms
     */
    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);
        /** @var FormInterface $form */
        $form = reset($forms);
        $form->setData($data instanceof ScalarObject ? $data->scalarValue() : null);
    }

    /**
     * @param \RecursiveIteratorIterator|FormInterface[] $forms
     * @param mixed $data
     */
    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        /** @var FormInterface $form */
        $form = reset($forms);
        $class = $form->getParent()->getConfig()->getOption('data_class');

        if ($form->getData() !== null || $form->getParent()->isRequired()) {
            $data = new $class($form->getData());
        }
    }

    public function getName(): string
    {
        return 'scalar_object';
    }

    private function typeNormalizer(): \Closure
    {
        return function (Options $options, $innerType) {
            return $innerType ? (string) $innerType : $this->resolveInnerType($options['data_class']);
        };
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function resolveInnerType(string $dataClass): string
    {
        if (!is_a($dataClass, ScalarObject::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid "data_class" option "%s". It must implement "%s" interface.',
                $dataClass,
                ScalarObject::class
            ));
        }

        switch ($dataClass::scalarType()) {
            case ScalarObject::INTEGER:
                return IntegerType::class;
            case ScalarObject::FLOAT:
                return NumberType::class;
            case ScalarObject::BOOLEAN:
                return CheckboxType::class;
        }

        return TextType::class;
    }
}
