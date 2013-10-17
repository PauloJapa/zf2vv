<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Locatario extends AbstractEndereco { 
    
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
        parent::__construct('locatario');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new LocatarioFilter);  

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
           'name' => 'nome',
            'options' => array(
                'type' => 'text',
                'label' => 'Nome'
            ),
            'attributes' => array(
                'id' => 'nome',
                'maxlength' => '50',
                'placeholder' => 'Entre com o nome do locatario'
            )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tipo',
                'attributes' => array(
                    'id' => 'tipo',
                    'onChange' => 'showTipo()'
                ),
                'options' => array(
                    'label' => 'Fisica/Juridica',
                    'empty_option' => 'Escolha da lista',
                    'value_options' => array(
                        ''=>'',
                        'fisica'=>'Pessoa Fisica',
                        'juridica'=>'Pessoa Juridica',
                ),
            )
        ));

        $this->add(array(
            'name' => 'cpf',
            'options' => array(
                'type' => 'text',
                'label' => 'CPF'
            ),
            'attributes' => array(
                'id' => 'cpf',
                'placeholder' => 'xxx.xxx.xxx-xx'
            )
        ));

        $this->add(array(
            'name' => 'cnpj',
            'options' => array(
                'type' => 'text',
                'label' => 'CNPJ'
            ),
            'attributes' => array(
                'id' => 'cnpj',
                'placeholder' => 'xx.xxx.xxx/xxxx-xx'
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
            'name' => 'email',
            'options' => array(
                'type' => 'text',
                'label' => 'Email'
            ),
            'attributes' => array(
                'id' => 'email',
                'placeholder' => ''
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
    public function reloadSelectClasse(array $filtro){
        if($this->isEdit)
            $this->setEdit();
    }
    
    
}
