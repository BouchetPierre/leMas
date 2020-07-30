<?php

namespace App\Form;

use App\Entity\AdminPublication;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AdminPublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject',  TextType::class,['label' =>'Sujet', 'required'=>true, 'translation_domain' => false])
            ->add('text', CKEditorType::class,  ['label' =>'Texte', 'required'=>true, 'translation_domain' => false])
            ->add('imageFile', FileType::class, ['label' => 'Photo (jpg, jpeg ou png file)','required'=> false, 'translation_domain' => false])
            ->add('brochure', FileType::class, [
                'label' => 'Brochure (PDF file)', 'mapped' => false,'required' => false, 'translation_domain' => false,'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Merci de charger un fichier PDF',
                    ])
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdminPublication::class,
        ]);
    }
}
