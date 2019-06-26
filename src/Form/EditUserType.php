<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("email")
            ->add("password")
            ->add("first_name")
            ->add("last_name")
            ->add("phone")
        ;

    }

    // set options for form behaviour
    public function configureOptions(OptionsResolver $resolver)
    {
        // Users este entitatea bazei de date pe care vrea sa o mapeze
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}