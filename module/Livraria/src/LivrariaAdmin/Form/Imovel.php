<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

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

        $this->add(array(
            'name' => 'subOpcao',
            'attributes' => array(
                'id' => 'subOpcao'
            )
        ));

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'id' => 'id',
            )
        ));

        $this->add(array(
            'name' => 'tel',
            'options' => array(
                'type' => 'text',
                'label' => 'Telefone'
            ),
            'attributes' => array(
                'id' => 'tel',
                'placeholder' => '(xx) xxxx-xxxx'
            )
        ));

        $this->add(array(
            'name' => 'bloco',
            'options' => array(
                'type' => 'text',
                'label' => 'Predio Bloco'
            ),
            'attributes' => array(
                'id' => 'bloco',
                'placeholder' => 'Predio Bloco',
                'class'       => 'input-small'
            )
        ));

        $this->add(array(
            'name' => 'apto',
            'options' => array(
                'type' => 'text',
                'label' => 'Apartamento'
            ),
            'attributes' => array(
                'id' => 'apto',
                'placeholder' => 'Predio Bloco',
                'class'       => 'input-small'
            )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'status',
                'attributes' => array(
                    'id' => 'status'
                ),
                'options' => array(
                    'label' => 'Situação',
                    'empty_option' => 'Escolha a situação do cadastro!',
                    'value_options' => array(
                        'A'=>'Ativo',
                        'B'=>'Bloqueado',
                        'C'=>'Cancelado',
                ),
            )
        ));

        $this->add(array(
            'name' => 'atividade',
            'options' => array(
                'type' => 'hidden',
            ),
            'attributes' => array(
                'id' => 'atividade',
            )
        ));

        $this->add(array(
            'name' => 'atividadeDesc',
            'options' => array(
                'type' => 'text',
                'label' => 'Atividade'
            ),
            'attributes' => array(
                'id' => 'atividadeDesc',
                'placeholder' => 'Atividade'
            )
        ));

        $this->add(array(
            'name' => 'locador',
            'options' => array(
                'type' => 'hidden',
            ),
            'attributes' => array(
                'id' => 'locador',
            )
        ));

        $this->add(array(
            'name' => 'locadorDesc',
            'options' => array(
                'type' => 'text',
                'label' => 'Locador'
            ),
            'attributes' => array(
                'id' => 'locadorDesc',
                'placeholder' => 'Pesquise digitando o Locador aqui!',
                'onKeyUp' => 'autoCompLocador();'
            )
        ));

        $this->getEnderecoElements($em);
     
        $this->add(array(
            'name' => 'enviar',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Salvar',
                'class' => 'btn-success',
                'onClick' => 'return salvar()'
            )
        ));
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
