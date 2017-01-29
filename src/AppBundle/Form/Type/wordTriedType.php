<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wordTriedType
 *
 * @author SHusson
 */
class wordTriedType extends AbstractType  {
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('word_tried',  Type\TextType::class, array('label' => 'World : ',
                    'constraints'=> array(new NotBlank())))
                ->add('submit', Type\SubmitType::class, array('label' => 'Let me guess...'));    
    }
}
