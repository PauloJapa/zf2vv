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
        $this->setInputHidden('fechadoOrigemId');
        $this->setInputHidden('mensalSeq');
        $this->setInputHidden('orcaReno');
        $this->setInputHidden('gerado');
        
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
        
        //Dados do Locatarioc
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
     
        $this->setInputText('proposta', 'Proposta',['readOnly'=>'true', 'placeholder' => 'Preenchimento Automatico']);
        $this->setInputText('valorAluguel', 'Valor Aluguel',['onKeyUp'=>'cleanCoberturas()']);
        
        $tipoCobertura = $this->getParametroSelect('tipoCobertura');
        $tipoCob = ['onChange'=>'setCobertura(this);showIncOrIncCon()'];
        $this->setInputSelect('tipoCobertura', 'Tipo de Cobertura', $tipoCobertura, $tipoCob);
        
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', 'Inicio da Vigência', array_merge($attributes, ['onChange'=>'setMesNiverOfMensal();']));

        $this->setInputText('fim', 'Fim da Vigência', ['readOnly' => 'true', 'placeholder' => 'Preenchimento Automatico']);
        
        $this->setInputText('criadoEm', 'Data', $attributes);
        
        $this->setInputRadio('seguroEmNome', 'Seguro em nome', ['01' => 'Locador','02' => 'Locatário']);
        
        $ocupacao = $this->getParametroSelect('ocupacao', TRUE);
        $attributes = ['onClick' => "cleanAtividade();setCobertura(this);setComissao(this);"];
        $this->setInputRadio('ocupacao', 'Ocupação', $ocupacao,$attributes);
        
        $validade = $this->getParametroSelect('validade',true);
        $this->setInputRadio('validade', 'Tipo do Seguro', $validade, ['onClick' => "checkValidade();travaFormaPagto();setMesNiverOfMensal();"]);
        
        $this->setInputText('codigoGerente', 'Cod. Gerente');
        
        $this->setInputText('refImovel', 'Ref. do Imóvel');
        
        $formaPagto = $this->getParametroSelect('formaPagto');
        $this->setInputSelect('formaPagto', 'Forma de pagto', $formaPagto, ['onChange'=>'travaFormaPagto();']);
        
        $label = 'Incêndio, raio, explosão e queda de aeronaves';
        $style = ['style' => 'text-align:right;'];
        $this->setInputText('incendio', $label, $style);
        
        $this->setInputText('conteudo', 'Incêndio + Conteúdo - Móveis, Máquinas e utensilios', $style);
        
        $this->setInputText('aluguel', 'Perda de aluguel', $style);
        
        $this->setInputText('eletrico', 'Danos elétricos', $style);
        
        $this->setInputText('vendaval', 'Vendaval, granizo, impacto de veiculos terrestres', $style);
        
        $this->setInputHidden('numeroParcela');
        $this->setInputHidden('premioLiquido');
        $this->setInputHidden('premio');
        $this->setInputText('premioTotal','Pagamento total de :');
        $this->setInputText('parcelaVlr','Valor da Parcela de :');
        
        $attributes = ['rows' => "8",'class'=>'span8'];
        $this->setInputTextArea('observacao', 'Obs', $attributes);
        
        $options =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesNiver', 'Mês de aniversário',$options, ['onChange'=>'setMesNiverOfMensal(true);']);
        
        $this->setInputHidden('administradora');
        $this->setInputHidden('administradoraDesc');
        $this->setInputHidden('comissaoEnt');
        
        if ($this->isAdmin) {
            $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
            $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras, ['onChange'=>'getComissao();']);
            if(isset($filtro['seguradora'])){
                $this->setComissao($filtro['seguradora']);                
            }        
            $assist24 = ['N' => 'Não', 'S' => 'Sim'];
            $this->setInputRadio('assist24', 'Assistencia 24', $assist24);
        } else {
            $this->setInputHidden('seguradora');
            $this->setInputHidden('comissao');
            $this->setInputHidden('assist24');
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
        
        $this->setInputButton('novoOrca', 'Novo Orçamento',['onClick'=>'return newOrcamento()']);

        $this->setInputButton('edImovel', 'Editar Imovel', ['onClick'=>'editImovel();return false;']);
        
        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
        
        
    }
    
    /**
     * Seta as comissões se baseando na seguradora selecionada
     * @param array $data
     * @return no return
     */
    public function setComissao($data){
        if(!$this->isAdmin){
            return;
        }
        $this->remove('comissao');
        if(isset($data['seguradora'])){
            $comissaoKey = 'comissaoParam' . str_pad($data['seguradora'], 3, '0', STR_PAD_LEFT);
        }else{
            $comissaoKey = 'comissaoParam%';
        }            
        $comissao = $this->getParametroSelect($comissaoKey, TRUE);
        $this->setInputSelect('comissao', 'Comissão da Administradora',$comissao, ['onChange'=>'setComissao(this);']);
        if(isset($data['comissao'])){
            $this->get('comissao')->setValue($data['comissao']);
        }
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
//        $this->get('atividadeDesc')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('fim')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }
    
    public function bloqueiaCampos(){
        $this->isAdmin = FALSE;
        
        $this->get('formaPagto')->setAttribute('disabled', 'true');   
        $this->get('validade')->setAttribute('disabled', 'true');   
    }
    
    public function setForRenovacao(){
        $pdf = $this->get('getpdf');
        if($pdf){
            $pdf->setValue('Imprimir Renovação');
        }
    }
    
}
