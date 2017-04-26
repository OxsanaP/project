<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ArticlesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title')

            ->add('slug', TextType::class, array(
                'required'    => false,
                'label' => 'Permanent link',
            ))
            ->add('content', TextareaType::class, array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bbcode' // Skip it if you want to use default theme
                )))
            ->add('status', ChoiceType::class, array(
                'choices' => array(
                    'Published' => 'Published',
                    'Unpublished' => 'Unpublished')
            ));

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Articles'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_articles';
    }


}
