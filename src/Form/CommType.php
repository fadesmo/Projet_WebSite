<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choixMax = $options['data']['max'];
        $choixMin = $options['data']['min'];
        // construction de la liste de choix
        $choices = [];
        for($choix = -$choixMin; $choix <= $choixMax; $choix ++)
            $choices['' . $choix] = $choix;

        $builder
            ->add('choix',
                ChoiceType::class,
                [
                    'label' => 'Choix',
                    'choices' => $choices,
                    'data' => 0,
                    'mapped' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
