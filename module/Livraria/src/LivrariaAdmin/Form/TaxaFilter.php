<?php

namespace LivrariaAdmin\Form;

use Zend\InputFilter\InputFilter;

class TaxaFilter extends InputFilter {

    public function __construct() {
        $this->add(array(
           'name' => 'inicio',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Nome não pode estar em branco'),
                    )
                )
            )
        ));
        
        $this->add(array(
           'name' => 'status',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Uma classe deve ser escolhida'),
                    )
                )
            )
        ));
        
        $this->add(array(
           'name' => 'incendio',
            'required' => true,
            'filters' => array(
                array('name'=>'StripTags'),
                array('name'=>'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options'=>array(
                        'messages' => array('isEmpty'=>'Nome não pode estar em branco'),
                    )
                )
            )
        ));
        
        $this->add(array(
           'name' => 'classe',
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
