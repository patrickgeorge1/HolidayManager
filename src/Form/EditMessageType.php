<?php


namespace App\Form;


use App\Entity\Messages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditMessageType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title")
            ->add("body", TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Users este entitatea bazei de date pe care vrea sa o mapeze
        $resolver->setDefaults([
            'data_class' => Messages::class
        ]);
    }

}