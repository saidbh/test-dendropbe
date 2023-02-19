<?php

namespace App\Form;

use App\Entity\Arbre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArbreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('diametre')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('coord')
            ->add('codeSite')
            ->add('numSujet')
            ->add('critere')
            ->add('implantation')
            ->add('domaine')
            ->add('nuisance')
            ->add('proximite')
            ->add('tauxFreq')
            ->add('typePassage')
            ->add('accessibilite')
            ->add('abattage')
            ->add('travauxCollet')
            ->add('travauxTronc')
            ->add('travauxHouppier')
            ->add('dateTravaux')
            ->add('dateProVisite')
            ->add('comProVisite')
            ->add('caractPied')
            ->add('caractTronc')
            ->add('hauteur')
            ->add('portArbre')
            ->add('stadeDev')
            ->add('etatSanCollet')
            ->add('etatSanTronc')
            ->add('etatSanHouppier')
            ->add('address')
            ->add('comAccess')
            ->add('dict')
            ->add('risque')
            ->add('proximiteOther')
            ->add('proximiteWithDict')
            ->add('accessibiliteOther')
            ->add('caractPiedOther')
            ->add('caractTroncMultiples')
            ->add('etatSanColletCavite')
            ->add('etatSanColletChampignons')
            ->add('etatSanColletChampignonsAutres')
            ->add('etatSanTroncCavite')
            ->add('etatSanTroncCorpsEtranger')
            ->add('etatSanTroncChampignons')
            ->add('etatSanTroncChampignonsAutres')
            ->add('etatSanTroncNuisibles')
            ->add('etatSanHouppierChampignons')
            ->add('etatSanHouppierChampignonsAutres')
            ->add('etatSanHouppierNuisibles')
            ->add('risqueGeneral')
            ->add('travauxCommentaire')
            ->add('critereOther')
            ->add('risqueGeneralOther')
            ->add('typePassageOther')
            ->add('typeIntervention')
            ->add('etatSanGeneral')
            ->add('userEditedDateTravaux')
            ->add('travauxTroncOther')
            ->add('travauxColletOther')
            ->add('travauxHouppierOther')
            ->add('espece')
            ->add('ville')
            ->add('img1')
            ->add('img1file', FileType::class,
                [
                    'label' => 'Images jpeg, jpg et png',
                    // unmapped means that this field is not associated to any entity property
                    'mapped' => false,
                    // make it optional so you don't have to re-upload the PDF file
                    // everytime you edit the Product details
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '4096k',
                            'mimeTypes' => [
                                'image/png',
                                'image/jpeg',
                                'image/jpg',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid image format',
                        ])
                    ]
                ])
            ->add('img2')
            ->add('img3')
            ->add('travauxTroncProtection')
            ->add('nuisanceNuisibles')
            ->add('travauxColletMultiple')
            ->add('travauxTroncMultiple')
            ->add('travauxHouppierMultiple')
            ->add('etatSanTroncNuisiblesAutres')
            ->add('etatSanHouppierNuisiblesAutres')
            ->add('statusTravaux')
            ->add('etatSanColletOther')
            ->add('etatSanTroncOther')
            ->add('etatSanHouppierOther')
            ->add('etatSanGeneralOther');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Arbre::class,
            'csrf_protection' => false
        ]);
    }
}
