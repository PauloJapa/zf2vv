<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Log extends Form {
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isEdit = false;

    public function __construct($name = null) {
        parent::__construct('log');

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new LogFilter);    

        $this->add(array(
                        'name' => 'subOpcao',
                        'attributes' => array(
                                             'id' => 'subOpcao',
                                             'type'  => 'hidden'
                                             )
                        )
                 );

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'id' => 'id',
                'type'  => 'hidden'
            )
        ));    
        
        $this->add(array(
            'name' => 'userDesc',
            'options' => array(
                'type' => 'text',
                'label' => '*Usuario'
            ),
            'attributes' => array(
                'id' => 'userDesc',
                'placeholder' => 'Pesquise digitando o aqui!',
                'onKeyUp' => 'autoCompUser();'
            )
        ));
        
        $this->add(array(
            'name'      => 'user',
            'attributes' => array(
                'id'    => 'bairro',
                'type'  => 'user'
            )
        ));
        
        $this->add(array(
            'name' => 'data',
            'options' => array(
                'type' => 'text',
                'label' => 'Data'
            ),
            'attributes' => array(
                'id' => 'data',
                'placeholder' => 'dd/mm/yyyy',
                'onClick' => "displayCalendar(this,dateFormat,this)"
            )
        ));
        
        $this->add(array(
            'name'      => 'tabela',
            'options' => array(
                'type' => 'text',
                'label' => 'Tabela'
            ),
            'attributes' => array(
                'id'    => 'tabela',
            )
        ));
        
        $this->add(array(
            'name'      => 'idDoReg',
            'options' => array(
                'type' => 'text',
                'label' => 'Id do Registro'
            ),
            'attributes' => array(
                'id'    => 'idDoReg',
            )
        ));
        
        $this->add(array(
            'name'      => 'controller',
            'options' => array(
                'type' => 'text',
                'label' => 'Programa'
            ),
            'attributes' => array(
                'id'    => 'controller',
            )
        ));
        
        $this->add(array(
            'name'      => 'action',
            'options' => array(
                'type' => 'text',
                'label' => 'Ação'
            ),
            'attributes' => array(
                'id'    => 'action',
            )
        ));
        
        $this->add(array(
            'name'      => 'dePara',
            'options' => array(
                'type' => 'text',
                'label' => 'Campos afetados'
            ),
            'attributes' => array(
                'id'    => 'dePara',
            )
        ));
        
        $this->add(array(
            'name'      => 'ip',
            'options' => array(
                'type' => 'text',
                'label' => 'IP do computador'
            ),
            'attributes' => array(
                'id'    => 'ip',
            )
        ));
        
        $this->add(array(
            'name' => 'enviar',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Salvar',
                'class' => 'btn-success',
                'onClick' => 'salvar()'
            )
        ));
    }
    
    public function setEdit(){
        $this->isEdit = TRUE;
        $this->get('tabela')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('controller')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('action')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('idDoReg')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('ip')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }

}
