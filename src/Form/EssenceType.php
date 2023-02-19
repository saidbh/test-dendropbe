<?php

namespace App\Form;

use App\Entity\Essence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EssenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('critere')
            ->add('countSubject')
            ->add('diametre')
            ->add('hauteur')
            ->add('stadeDev')
            ->add('houppier')
            ->add('etatGeneral')
            ->add('risque')
            ->add('nbreSujetConcerne')
            ->add('travaux')
            ->add('dateTravaux')
            ->add('dateProVisite')
            ->add('codeSite')
            ->add('numSujet')
            ->add('caract')
            ->add('caractOther')
            ->add('domaine')
            ->add('etatSanGeneralOther')
            ->add('nuisance')
            ->add('etatSanGeneralChampignons')
            ->add('etatSanGeneralParasite')
            ->add('critereOther')
            ->add('proximite')
            ->add('proximiteOther')
            ->add('proximitewithDict')
            ->add('tauxFreq')
            ->add('typePassage')
            ->add('typePassageOther')
            ->add('accessibilite')
            ->add('accessibiliteOther')
            ->add('travauxOther')
            ->add('travauxTypeIntervention')
            ->add('travauxCom')
            ->add('travauxSoin')
            ->add('travauxProtection')
            ->add('img1')
            ->add('img2')
            ->add('img3')
            ->add('varietyGrade')
            ->add('healthIndex')
            ->add('healthColumn')
            ->add('aestheticIndex')
            ->add('aestheticColumn')
            ->add('locationIndex')
            ->add('etatSanGeneral')
            ->add('userEditedDateTravaux')
            ->add('espece')
            ->add('abattage')
            ->add('etatSanGeneralParasiteAutres')
            ->add('etatSanGeneralChampignonsAutres')
            ->add('epaysage')
            ->add('statusTravaux');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Essence::class,
        ]);
    }
}
