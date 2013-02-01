<?php

namespace LivrariaAdmin\Form;

use Zend\InputFilter\InputFilter;

class ClasseFilter extends InputFilter {

    public function __construct() {
        $this->add(array(
           'name' => 'cod',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Campo não pode estar em branco'),
                    )
                )
            )
        ));
        
        $this->add(array(
           'name' => 'descricao',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Campo não pode estar em branco'),
                    )
                )
            )
        ));
        
        $this->add(array(
           'name' => 'seguradora',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Campo não pode estar em branco'),
                    )
                )
            )
        ));
    }    
    
}
