<?php

namespace LivrariaAdmin\Form;

use Zend\InputFilter\InputFilter;

class LogFilter extends InputFilter {

    public function __construct() {
        $this->add(array(
           'name' => 'data',
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
           'name' => 'user',
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
           'name' => 'tabela',
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
           'name' => 'controller',
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
           'name' => 'action',
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
           'name' => 'idDoReg',
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
           'name' => 'dePara',
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
