<?php

namespace LivrariaAdmin\Form;

use Zend\InputFilter\InputFilter;

class EnderecoFilter extends InputFilter {

    public function __construct() {
        $this->add(array(
            'name' => 'rua',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'numero',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'compl',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'compl',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'cep',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'cidade',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'estado',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'pais',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));
    }

}
