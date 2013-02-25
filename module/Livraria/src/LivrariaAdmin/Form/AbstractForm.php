<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

/**
 * AbstractForm
 * Abstração dos inputs + usados para montagem do from
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractForm extends Form {
    
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isEdit = false;
    
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isAdmin = false;

    public function __construct($name = null) {
        parent::__construct($name);
    }
    
    /**
     * Nome do campo oculto do form
     * @param string $name
     */
    public function setInputHidden($name){
        
        $this->add(array(
            'name'      => $name,
            'attributes' => array(
                'id'    => $name,
                'type'  => 'hidden'
            )
        ));
    }
    
    /**
     * Monta os paramentro basicos para se fazer um input text
     * @param string $name
     * @param string $label
     * @param array $attributes
     */
    public function setInputText($name,$label,array $attributes = array()){
        
        $input['name'] = $name;
        $input['options'] = array('type' => 'text','label' => $label);
        
        if(empty($attributes)){
            $input['attributes'] = array('id' => $name); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name),$attributes); 
        }
        
        $this->add($input);
    }
    
    /**
     * Monta os paramentro basicos para se fazer um input text Area
     * @param string $name
     * @param string $label
     * @param array $attributes
     */
    public function setInputTextArea($name,$label,array $attributes = array()){
        //$tst = new Element\Textarea($name);
        //$tst->setLabelAttributes($attributes);
        
        $input['type'] = 'Zend\Form\Element\Textarea';
        $input['name'] = $name;
        $input['options'] = array('label' => $label,'rows'=>'6','cols'=>'200');
        
        if(empty($attributes)){
            $input['attributes'] = array('id' => $name); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name),$attributes); 
        }
        
        $this->add($input);
    }
    
    /**
     * Monta os paramentro basicos para se fazer um input select
     * @param string $name
     * @param string $label
     * @param array  $options
     * @param array  $attributes
     */
    public function setInputSelect($name,$label,array & $options = [],array $attributes = []){  
        
        $input['type'] = 'Zend\Form\Element\Select';
        $input['name'] = $name;
        
        if(empty($attributes)){
            $input['attributes'] = array('id' => $name); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name),$attributes); 
        }
        
        $input['options'] = array(
            'label' => $label,
            'empty_option' => 'Escolha da lista',
            'value_options' => $options
        ); 
        
        $this->add($input);
    }
    
    /**
     * Monta os paramentro basicos para se fazer um input submit
     * @param string $name
     * @param string $label
     * @param array  $attributes
     */
    public function setInputSubmit($name,$label,array $attributes = []){
           
        $input['type'] = 'Zend\Form\Element\Submit';
        $input['name'] = $name;
        
        $attrib = array('id' => $name,
                        'value' => $label,
                        'class' => 'btn-success',
                        'onClick' => 'return salvar()');
        
        if(empty($attributes)){
            $input['attributes'] = $attrib;
        }else{
            if(isset($attributes['onClick']))
                unset ($attrib['onClick']);
            $input['attributes'] = array_merge($attrib,$attributes); 
        }
        
        $this->add($input);        
    }
    
    public function setInputRadio($name, $label, $options, $attributes=[]){
        
        $input['type'] = 'Zend\Form\Element\Radio';
        $input['name'] = $name;
        
        if(empty($attributes)){
            $input['attributes'] = array('id' => $name); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name),$attributes); 
        }
        
        $input['options'] = array(
            'label' => $label,
            'value_options' => $options
        ); 
        
        $this->add($input);        
       
    }
    /**
     * Função para setar varios inputs com com algo padrão
     * Por padrão o array são os inputs visiveis na tela
     * 
     * @param string $key
     * @param string $attribute
     * @param array  $inputs
     * @return void no return
     */
    public function addAttributeInputs($key,$attribute,array $inputs=[]){
        if(empty($inputs)){
            return;
        }
        foreach ($inputs as $input) {
            $this->get($input)->setAttribute($key, $attribute);
        }
    }
}
