<?php

namespace LivrariaAdmin\Form;

/**
 * ParametroSis
 * Fomulario para manipular os dados da entity
 */
class ParametroSis extends AbstractForm { 
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('orcamento');
        
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ParametroSisFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');
        
        $attributes = ['placeholder' => 'Key do parâmetro'];        
        $this->setInputText2('key', 'Chave do Parâmetro', $attributes);

        $attributes = ['placeholder'=>'Valor'];        
        $this->setInputText2('conteudo', 'Parâmetro', $attributes);

        $attributes = ['placeholder'=>'Descrição', 'class'=>'input-xmlarge'];        
        $this->setInputText2('descricao', 'Descrição', $attributes);

        $this->setInputSubmit('enviar', 'Salvar');
        
    }
    
    /**
     * 
     * Atualiza o form para o modo de edição bloqueando campos se necessario
     * @param boolean $isAdmin Super usuario pode alterar
     * @return void
     */ 
    public function setEdit($isAdmin=false){
        $this->isEdit = TRUE;
        if(($isAdmin)or($this->isAdmin)){
            $this->isAdmin = TRUE;
            return ;
        }
    }
}
