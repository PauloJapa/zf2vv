<?php

namespace LivrariaAdmin\Form;

/**
 * EscolhaAdm
 * Fomulario para manipular os dados da entity
 */
class Orcamento extends AbstractEndereco { 
    
    /**
     * Array para montar um Select com as seguradoras
     * @var array
     */
    protected $seguradoras;
    
    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('orcamento');
        
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new OrcamentoFilter);

        $this->setInputHidden('id');
        
        $this->setInputHidden('codano');
        $this->setInputHidden('taxa');
        $this->setInputHidden('taxaIof');
        $this->setInputHidden('canceladoEm');
        $this->setInputHidden('fechadoId');
        
        $this->setInputHidden('imovel');
        $this->setInputHidden('imovelTel');
        $this->setInputHidden('imovelStatus');
        $this->setInputHidden('status');
        $this->setInputHidden('user');
        $this->setInputHidden('multiplosMinimos');
        
        //Dados do Locador
        $this->setInputHidden('locador');
        $attributes = ['placeholder' => 'Pesquise aqui pelo nome, cpf ou cnpj!',
                       'onKeyUp' => 'autoCompLocador();',
                       'autoComplete'=>'off'];        
        $this->setInputText('locadorNome', 'Locador', $attributes);

        $options = [''=>'','fisica'=>'Pessoa Fisica','juridica'=>'Pessoa Juridica'];
        $this->setInputSelect('tipoLoc', 'Fisica/Juridica', $options, ['onChange' => 'showTipo()']);
        
        $attributes=[];
        $attributes['placeholder'] = 'xxx.xxx.xxx-xx';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $this->setInputText('cpfLoc', 'CPF', $attributes);
        
        $attributes['placeholder'] = 'xx.xxx.xxx/xxxx-xx';
        $this->setInputText('cnpjLoc', 'CNPJ', $attributes); 
        
        //Dados do Locatario
        $this->setInputHidden('locatario');
        $attributes = ['placeholder' => 'Pesquise aqui pelo nome, cpf ou cnpj!',
                       'onKeyUp' => 'autoCompLocatario();',
                       'autoComplete'=>'off'];        
        $this->setInputText('locatarioNome', 'Locatario', $attributes);

        $options = [''=>'','fisica'=>'Pessoa Fisica','juridica'=>'Pessoa Juridica'];
        $this->setInputSelect('tipo', 'Fisica/Juridica', $options, ['onChange' => 'showTipo()']);
        
        $attributes=[];
        $attributes['placeholder'] = 'xxx.xxx.xxx-xx';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $this->setInputText('cpf', 'CPF', $attributes);
        
        $attributes['placeholder'] = 'xx.xxx.xxx/xxxx-xx';
        $this->setInputText('cnpj', 'CNPJ', $attributes);   
        
        $this->setInputHidden('atividade');
        $attributes = ['placeholder' => 'Pesquise aqui!!',
                       'onKeyUp' => 'autoCompAtividade();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('atividadeDesc', 'Atividade', $attributes);
     
        $this->setInputText('proposta', 'Proposta',['readOnly'=>'true']);
        $this->setInputText('valorAluguel', 'Valor Aluguel',['onKeyPress'=>'cleanCoberturas()']);
        
        $tipoCobertura = $this->getParametroSelect('tipoCobertura');
        $tipoCob = ['onChange'=>'travaResidencial();'];
        $this->setInputSelect('tipoCobertura', 'Tipo de Cobertura', $tipoCobertura, $tipoCob);
        
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', 'Inicio da Vigência', $attributes);

        $this->setInputText('fim', 'Fim da Vigência', $attributes);
        $this->get('fim')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));
        
        $this->setInputText('criadoEm', 'Data', $attributes);
        
        $this->setInputRadio('seguroEmNome', 'Seguro em nome', ['01' => 'Locador','02' => 'Locatário']);
        
        $ocupacao = $this->getParametroSelect('ocupacao', TRUE);
        $attributes = ['onClick' => "cleanAtividade();travaResidencial();"];
        $this->setInputRadio('ocupacao', 'Ocupação', $ocupacao,$attributes);
        
        $validade = $this->getParametroSelect('validade',true);
        $this->setInputRadio('validade', 'Tipo do Seguro', $validade);
        
        $this->setInputText('codigoGerente', 'Cod. Gerente');
        
        $this->setInputText('refImovel', 'Ref. do Imóvel');
        
        $formaPagto = $this->getParametroSelect('formaPagto');
        $this->setInputSelect('formaPagto', 'Forma de pagto', $formaPagto);
        
        $label = 'Incêndio, raio, explosão e queda de aeronaves(obrigatório)';
        $this->setInputText('incendio', $label);
        
        $this->setInputText('conteudo', 'Conteúdo - Móveis, Máquinas e utensilios');
        
        $this->setInputText('aluguel', 'Perda de aluguel');
        
        $this->setInputText('eletrico', 'Danos elétricos');
        
        $this->setInputText('vendaval', 'Vendaval, granizo, impacto de veiculos terrestres');
        
        $this->setInputHidden('numeroParcela');
        $this->setInputHidden('premioLiquido');
        $this->setInputHidden('premio');
        $this->setInputText('premioTotal','Pagamento total de :');
        $this->setInputHidden('codFechado');
        
        $attributes = ['rows' => "8",'class'=>'span8'];
        $this->setInputTextArea('observacao', 'Obs', $attributes);
        
        $options =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesNiver', 'Mês de aniversário',$options);
        
        $this->setInputHidden('administradora');
        $this->setInputHidden('comissaoEnt');
        
        if ($this->isAdmin) {
            $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
            $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras);
            $comissao = $this->getParametroSelect('comissaoParam', TRUE);
            $this->setInputSelect('comissao', 'Comissão da Administradora',$comissao);
        } else {
            $this->setInputHidden('seguradora');
            $this->setInputHidden('comissao');
        }       
        
        $this->getEnderecoElements($em);
        
        $this->addAttributeInputs('onchange', 'limpaImovel()');
        
        $this->setInputText('bloco', 'Predio Bloco', ['placeholder'=>'Predio Bloco', 'class'=>'input-small']);

        $this->setInputText('apto', 'Apartamento', ['placeholder'=>'Apto Numero', 'class'=>'input-small']);

        $this->setInputSubmit('enviar', 'Salvar');
        
        $this->setInputSubmit('calcula', 'Calcular',['onClick'=>'return calcular()']);
        
        $this->setInputSubmit('fecha', 'Fechar Seguro',['onClick'=>'return fechar()']);
        
        $this->setInputSubmit('getpdf', 'Imprimir Proposta',['onClick'=>'return printProposta()']);
        
        $this->setInputButton('logOrca', 'Exibir Logs',['onClick'=>'return viewLogsOrcamento()']);
        
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
        $this->get('ocupacao')->setAttribute('disabled', 'true');   
        $this->get('atividadeDesc')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('fim')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }
    
    public function bloqueiaCampos(){
        $this->isAdmin = FALSE;
        
        $this->get('formaPagto')->setAttribute('disabled', 'true');   
    }
    
    public function setForRenovacao(){
        $pdf = $this->get('getpdf');
        if($pdf){
            $pdf->setValue('Imprimir Renovação');
        }
    }
    
}
