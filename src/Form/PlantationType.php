<?php

namespace App\Form;

use App\Entity\Plantation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlantationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hauteur')
            ->add('diametre')
            ->add('countSubject')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('ville')
            ->add('address')
            ->add('dateEcheance')
            ->add('dateReport')
            ->add('espece')
            ->add('userAdded')
            ->add('userValidate')
            ->add('inventory')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Plantation::class,
        ]);
    }
}
