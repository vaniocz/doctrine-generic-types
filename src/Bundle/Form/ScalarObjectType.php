<?php
namespace Vanio\DoctrineGenericTypes\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;

class ScalarObjectType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setDataMapper($this)
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'])
            ->add('value', $options['type'], $options['options'] + [
                'required' => $options['required'],
                'error_bubbling' => true,
                'label' => false,
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars += [
            'dataClass' => $options['data_class'],
            'nonCompoundWrapper' => true,
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'empty_data' => null,
                'error_bubbling' => false,
                'type' => null,
                'options' => [],
                'documentation' => ['type' => 'string']
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
        $forms['value']->setData($data instanceof ScalarObject ? $data->scalarValue() : null);
    }

    /**
     * @param \Iterator|FormInterface[] $forms
     * @param mixed $data
     */
    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        $form = $forms['value'];
        $class = $form->getParent()->getConfig()->getOption('data_class');
        $data = $form->getData() !== null || $form->getParent()->isRequired()
            ? $this->createScalarObject($class, $form->getData())
            : null;
    }

    /**
     * @internal
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        $class = $event->getForm()->getConfig()->getOption('data_class');

        if ($data !== null && !$data instanceof ScalarObject) {
            $event->setData($this->createScalarObject($class, $data));
        }
    }

    /**
     * @internal
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (is_scalar($data) || $data === null) {
            $event->setData(['value' => $data]);
        }
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

        switch ($dataClass::{'scalarType'}()) {
            case ScalarObject::INTEGER:
                return IntegerType::class;
            case ScalarObject::FLOAT:
                return NumberType::class;
            case ScalarObject::BOOLEAN:
                return CheckboxType::class;
        }

        return TextType::class;
    }

    /**
     * @param string $class
     * @param mixed $value
     * @return ScalarObject
     */
    private function createScalarObject(string $class, $value): ScalarObject
    {
        return is_callable([$class, 'create']) ? $class::{'create'}($value) : new $class($value);
    }
}
