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
    
    /**
     * Não permitir campo vazio.
     * @param type $name do input a validar
     */
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
    
    /**
     * Forçar a não validar estes campos
     * Especie do bug no zf2 que força a validação dos campos selects
     * @param string $name
     */
    public function emptyTrue($name){
        $this->add(array(
            'name' => $name,
            'required' => false,
        ));
    }
}
