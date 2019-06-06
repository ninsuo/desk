<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 1, 'max' => 255]),
                ],
            ])
            ->add('width', NumberType::class, [
                'label' => 'Width',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 2, 'max' => 1000]),
                ],
            ])
            ->add('height', NumberType::class, [
                'label' => 'Height',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 2, 'max' => 1000]),
                ],
            ])
            ->add('size', NumberType::class, [
                'label' => 'Size',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 5, 'max' => 100]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
