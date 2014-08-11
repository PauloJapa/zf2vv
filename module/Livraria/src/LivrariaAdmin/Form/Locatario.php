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

        $this->setInputHidden('subOpcao');
        
        $this->setInputHidden('id');
        
        $this->setInputHidden('ajaxStatus');

        $this->setInputText('nome','Nome',['placeholder'=>'Entre com o nome do locatario','maxlength' => '50']);

        $options = [''=>'','fisica'=>'Pessoa Fisica','juridica'=>'Pessoa Juridica'];
        $this->setInputSelect('tipo', 'Fisica/Juridica', $options, ['onChange' => 'showTipo()']);
        
        $attributes['placeholder'] = 'xxx.xxx.xxx-xx';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != \'\')checkCPF_CNPJ(this)';
        $this->setInputText('cpf', 'CPF', $attributes);
        
        $attributes['placeholder'] = 'xx.xxx.xxx/xxxx-xx';
        $this->setInputText('cnpj', 'CNPJ', $attributes);
        
        $this->setInputText('tel', 'Telefone', ['placeholder'=>'(xx) xxxx-xxxx']);
        
        $this->setInputText('email', 'Email', ['placeholder'=>'fulano@host.com']);

        $options = ['A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'];
        $this->setInputSelect('status', 'Situação', $options);

        $this->getEnderecoElements($em);
        
        $this->setInputSubmit('enviar', 'Salvar');
     
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
