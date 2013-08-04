<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;
/**
 * AbstractForm
 * Abstração dos inputs + usados para montagem do from
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractForm extends Form {
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em; 
    
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
    
    /**
     * Objeto que pega os dados do usuario armazenado
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authService;

    public function __construct($name = null) {
        parent::__construct($name);
        
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');
        $this->setInputHidden('scrolX');
        $this->setInputHidden('scrolY');
        
        $this->setIsAdmin();
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
            $input['attributes'] = array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)', 'onBlur' => 'toUp(this)'); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)', 'onBlur' => 'toUp(this)'),$attributes); 
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
            $input['attributes'] = array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)', 'onBlur' => 'toUp(this)'); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)', 'onBlur' => 'toUp(this)'),$attributes); 
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
            $input['attributes'] = array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)'); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name,'onKeyDown' => 'return changeEnterToTab(this,event)'),$attributes); 
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
    
    /**
     * Monta os paramentro basicos para se fazer um input button
     * @param string $name
     * @param string $label
     * @param array  $attributes
     */
    public function setInputButton($name,$label,array $attributes = []){
        $bt = new Element\Button($name);
        $bt->setLabel($label);
        $attrib = array('id' => $name,
                        'value' => $label,
                        'class' => 'btn-success');
        if(empty($attributes)){
            $bt->setAttributes($attrib);
        }else{
            $bt->setAttributes(array_merge($attrib,$attributes));
        }
        $this->add($bt);        
    }
    
    public function setInputRadio($name, $label, $options, $attributes=[]){
        
        $input['type'] = 'Zend\Form\Element\Radio';
        $input['name'] = $name;
        
        if(empty($attributes)){
            $input['attributes'] = array('id' => $name,'onKeyPress' => 'return changeEnterToTab(this,event)'); 
        }else{
            $input['attributes'] = array_merge(array('id' => $name,'onKeyPress' => 'return changeEnterToTab(this,event)'),$attributes); 
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
    
    /**
     * Baseado na $key busca os registro cadastrados e monta um array para select
     * @param string $key
     * @param boolean $shift
     * @return array
     * 
     */
    public function getParametroSelect($key,$shift=false){
        $array = $this->em->getRepository('Livraria\Entity\ParametroSis')->fetchPairs($key);
        if($shift)
            $retira = array_shift($array);
        
        return $array;
    }
 
    /** 
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return Livraria\Entity\User | boolean
     */     
    public function getIdentidade() { 
        if (is_object($this->authService)) {
            return $this->authService->getIdentity();
        }else{
            $sessionStorage = new SessionStorage("LivrariaAdmin");
            $this->authService = new AuthenticationService;
            $this->authService->setStorage($sessionStorage);
            if ($this->authService->hasIdentity()) 
                return $this->authService->getIdentity();
        }
        return FALSE;
    }
    
    public function setIsAdmin(){
        if($this->getIdentidade()->getTipo() == 'admin')
            $this->isAdmin = TRUE;
        else
            $this->isAdmin = FALSE;
    }
}
