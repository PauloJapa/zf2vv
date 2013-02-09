<?php

namespace LivrariaAdmin\Form;

class Log extends AbstractForm {
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isEdit = false;

    public function __construct($name = null) {
        parent::__construct('log');

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new LogFilter);    

        $this->setInputHidden('subOpcao');
        $this->setInputHidden('autoComp');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('id');

        $this->setInputHidden('user');
        $this->setInputText('userDesc', '*Usuario', ['placeholder' => 'Pesquise digitando o aqui!','onKeyUp' => 'autoCompUser();']);

        $this->setInputText('data', 'Data', ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"]);        
        
        $this->setInputText('tabela', 'Tabela');
        $this->setInputText('idDoReg', 'Id do Registro');
        $this->setInputText('controller', 'Programa');
        $this->setInputText('action', 'Ação');
        $this->setInputText('dePara', 'Campos afetados');
        $this->setInputText('ip', 'IP do computador');
        
        $this->setInputSubmit('enviar', 'Salvar');
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
