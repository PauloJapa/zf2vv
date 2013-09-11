<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Administradora extends AbstractEndereco {
    
    /**
     * Array para montar um Select com as seguradoras
     * @var array
     */
    protected $seguradoras;    

    public function __construct($name = null, $em = null) {
        $this->em = $em;
        parent::__construct('administradora');
        

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new AdministradoraFilter);
              
        $this->setInputText('id', 'Codigo');
        $this->setInputText('nome', 'Nome',['placeholder' => 'Entre com o nome']);
        $this->setInputText('codigoCol', 'Codigo no Col');
        $this->setInputText('apelido', 'Apelido', ['placeholder' => 'Nome fantasia']);

        $attributes=[];
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $attributes['placeholder'] = 'xx.xxx.xxx/xxxx-xx';
        $this->setInputText('cnpj', 'CNPJ', $attributes);

        $this->setInputText('tel', 'Telefone');
        $this->setInputText('email', 'Email');

        $status = $this->getParametroSelect('status',TRUE);
        $this->setInputSelect('status', 'Situação',$status);
        
        $formaPagto = $this->getParametroSelect('formaPagto');
        $this->setInputSelect('formaPagto', 'Forma de pagto', $formaPagto);
        
        $validade = array_merge(['' => 'Ambos'],$this->getParametroSelect('validade',true));
        $this->setInputRadio('validade', 'Tipo do Seguro', $validade);
        
        $tipoCobertura = $this->getParametroSelect('tipoCobertura');
        $this->setInputSelect('tipoCobertura', 'Tipo de Cobertura (Imoveis Comercial)', $tipoCobertura);
        
        $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
        $this->setInputSelect('seguradora', '*Seguradora',$this->seguradoras);
     
        $this->getEnderecoElements($em);
        
        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
    }

}
