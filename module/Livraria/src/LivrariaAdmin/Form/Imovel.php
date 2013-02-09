<?php

namespace LivrariaAdmin\Form;

/**
 * Imovel
 * Form para manipular dados do Imovel
 * 
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Imovel extends AbstractEndereco { 
    
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
    

    public function __construct($name = null, $em = null) {
        parent::__construct('imovel');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ImovelFilter);  

        $this->setInputHidden('autoComp');
        
        $this->setInputHidden('subOpcao');
        
        $this->setInputHidden('id');

        $this->setInputText('tel', 'Telefone', ['placeholder' => '(xx) xxxx-xxxx']);
        
        $this->setInputText('bloco', 'Predio Bloco', ['placeholder' => 'Predio Bloco','class'       => 'input-small']);

        $this->setInputText('apto', 'Apartamento', ['class'=>'input-small']);

        $options = ['A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'];
        $this->setInputSelect('status', 'Situação', $options);

        $this->setInputHidden('atividade');
        $this->setInputText('atividadeDesc', 'Atividade', ['placeholder' => 'Pesquise digitando a atividade aqui!','onKeyUp' => 'autoCompAtividade();']);

        $this->setInputHidden('locador');
        $this->setInputText('locadorDesc', 'Locador', ['placeholder' => 'Pesquise pelo nome, cpf ou cnpj aqui!','onKeyUp' => 'autoCompLocador();']);

        $this->getEnderecoElements($em);

        $this->setInputSubmit('enviar', 'Salvar', ['onClick' => 'return salvar()']);
    }   
    
    /**
     * Atualiza o form para o modo de edição bloqueando campos se necessario
     */
    public function setEdit(){
        $this->isEdit = TRUE;
        //$this->get('seguradora')->setAttribute('disabled', 'disabled');   
        //$this->get('desastres')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }
    
    /**
     * Recarrega o select baseado em filtro
     * @param array $filtro
     */
    public function reloadSelect__(array $filtro){
        if($this->isEdit)
            $this->setEdit();
    }
    
    
}
