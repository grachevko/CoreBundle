<?php

namespace Preemiere\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new EnumTransformer($options['data_class']))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->vars['choices'] as $choice) {
            /** @var ChoiceView $choice */
            $choice->value = $choice->label;
            $choice->label = $choice->data;
            $choice->data = $choice->value;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected enum does not exist',
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
