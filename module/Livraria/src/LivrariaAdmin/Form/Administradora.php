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
        $this->setInputSelect('tipoCobertura', 'Cobertura Comercial', $tipoCobertura);
        $this->setInputSelect('tipoCoberturaRes', 'Cobertura Residencial', $tipoCobertura);
        
        $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
        $this->setInputSelect('seguradora', '*Seguradora',$this->seguradoras);
        
        $assist24 = ['N' => 'Não', 'S' => 'Sim'];
        $this->setInputRadio('assist24', 'Assistencia 24', $assist24);
        $this->get('assist24')->setValue('N');
        
        $geraExpSep = ['0' => 'Unico arquivo.', '1' => 'Separadamente.'];
        $this->setInputRadio('geraExpSep', 'Gerar arquivos de exportação separados.', $geraExpSep, ['value' => '1']);
        
        $geraExpSep = ['0' => 'NÃO exibir', '1' => 'Exibir'];
        $this->setInputRadio('showCusInd', 'Exibir na proposta Custo Individual.', $geraExpSep, ['value' => '1']);
        
        $geraExpSep = ['0' => 'NÃO ordenar.', '1' => 'Ordenar.'];
        $this->setInputRadio('exptRefOrder', 'Exportar Maritima por Ref Imovel.', $geraExpSep, ['value' => '0']);
        
        $geraExpSep = ['0' => 'NÃO Bloquear.', '1' => 'Bloquear.'];
        $this->setInputRadio('blockFechamento', 'Bloquer qualquer proposta seja fechada.', $geraExpSep, ['value' => '0']);
        
        $parcela4x = ['0' => 'Não', '1' => 'Sim'];
        $this->setInputRadio('parcela4x', 'Permitir parcelar em 4x', $parcela4x, ['value' => '0']);
        
        $parcela5x = ['0' => 'Não', '1' => 'Sim'];
        $this->setInputRadio('parcela5x', 'Permitir parcelar em 5x', $parcela5x, ['value' => '0']);
        
        $propPag = ['N' => 'Com todas as Opções de Pag.', 'S' => 'Somente a escolhida'];
        $this->setInputSelect('propPag', 'Proposta com 1 forma de pag.', $propPag);
     
        $this->getEnderecoElements($em);
        
        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
    }

}
