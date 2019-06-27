<?php


namespace App\Form;


use App\Entity\Benefits;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddBenefitType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class)
            ->add("description", TextareaType::class)
        ;

    }

    // set options for form behaviour
    public function configureOptions(OptionsResolver $resolver)
    {
        // Users este entitatea bazei de date pe care vrea sa o mapeze
        $resolver->setDefaults([
                'data_class' => Benefits::class
        ]);
    }

}