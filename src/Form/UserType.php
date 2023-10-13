<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('prenom')
            ->add('username')
            ->add('email')
            ->add('password')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('isActive')
            ->add('emailActive')
            ->add('isInit')
            ->add('img', FileType::class, array('label' => 'Img'))
            ->add('profil')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
