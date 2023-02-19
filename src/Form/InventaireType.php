<?php

namespace App\Form;

use App\Entity\Inventaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createdAt')
            ->add('updatedAt')
            ->add('type')
            ->add('arbre')
            ->add('epaysage')
            ->add('varietyGrade')
            ->add('healthIndex')
            ->add('aestheticIndex')
            ->add('locationIndex')
            ->add('aestheticColumn')
            ->add('healthColumn')
            ->add('isFinished')
            ->add('user');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inventaire::class,
        ]);
    }
}
