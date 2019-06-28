<?php


namespace App\Form;


use App\Entity\Benefits;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiveBenefitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", EntityType::class, [
                'class' => Benefits::class,
                'choice_label' => 'name'
            ])
            ->add("users", EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email'
            ])
        ;

    }


}