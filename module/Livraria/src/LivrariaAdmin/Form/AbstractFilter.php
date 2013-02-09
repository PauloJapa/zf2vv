<?php

namespace LivrariaAdmin\Form;

use Zend\InputFilter\InputFilter;


/**
 * AbstractFilter
 * Metodos abstraidos e encapsulados de filtro para usar no form
 * 
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AbstractFilter extends InputFilter {
    
    
    public function notEmpty($name){        
        $this->add(array(
            'name' => $name,
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
