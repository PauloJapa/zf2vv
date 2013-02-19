<?php

namespace LivrariaAdmin\Form;

/**
 * EscolhaAdm
 * Fomulario para manipular os dados da entity
 */
class Orcamento extends AbstractEndereco { 
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('orcamento');
        
        $this->em = $em;

        $this->setAttribute('method', 'post');
        //$this->setInputFilter(new EscolhaAdmFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');
        
        $this->setInputHidden('codano');
        $this->setInputHidden('taxa');
        $this->setInputHidden('comissao');
        $this->setInputHidden('canceladoEm');
        
        $this->setInputHidden('imovel');
        $this->setInputHidden('imovelTel');
        $this->setInputHidden('imovelStatus');
        
        $this->setInputHidden('locador');
        $attributes = ['placeholder' => 'Pesquise aqui pelo nome, cpf ou cnpj!',
                       'onKeyUp' => 'autoCompLocador();',
                       'autoComplete'=>'off'];        
        $this->setInputText('locadorNome', 'Locador', $attributes);
        
        $this->setInputHidden('locatario');
        $attributes = ['placeholder' => 'Pesquise aqui pelo nome, cpf ou cnpj!',
                       'onKeyUp' => 'autoCompLocatario();',
                       'autoComplete'=>'off'];        
        $this->setInputText('locatarioNome', 'Locatario', $attributes);
        
        $this->setInputHidden('atividade');
        $attributes = ['placeholder' => 'Pesquise aqui!!',
                       'onKeyUp' => 'autoCompAtividade();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('atividadeDesc', 'Atividade', $attributes);

        $options = [''=>'','fisica'=>'Pessoa Fisica','juridica'=>'Pessoa Juridica'];
        $this->setInputSelect('tipo', 'Fisica/Juridica', $options, ['onChange' => 'showTipo()']);
        
        $attributes=[];
        $attributes['placeholder'] = 'xxx.xxx.xxx-xx';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $this->setInputText('cpf', 'CPF', $attributes);
        
        $attributes['placeholder'] = 'xx.xxx.xxx/xxxx-xx';
        $this->setInputText('cnpj', 'CNPJ', $attributes);
     
        $this->setInputText('proposta', 'Proposta',['readOnly'=>'true']);
        $this->setInputText('valorAluguel', 'Valor Aluguel');
        
        $this->setInputText('bloco', 'Predio Bloco', ['placeholder'=>'Predio Bloco', 'class'=>'input-small']);

        $this->setInputText('apto', 'Apartamento', ['class'=>'input-small']);
        
        $options = ['01'=>'Prédio', '02'=>'Prédio + conteúdo', '03'=>'Conteúdo'];
        $this->setInputSelect('tipoCobertura', 'Tipo de Cobertura', $options);
        
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', 'Inicio da Vigência', $attributes);

        $this->setInputText('fim', 'Fim da Vigência', $attributes);
        
        $this->setInputText('criadoEm', 'Data', $attributes);
        
        $this->setInputRadio('seguroEmNome', 'Seguro em nome', ['01' => 'Locador','02' => 'Locatário']);
        
        $options = ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria'];
        $attributes = ['onClick' => "cleanAtividade()"];
        $this->setInputRadio('ocupacao', 'Ocupação', $options,$attributes);
        
        $options = ['mensal'=>'Mensal', 'anual'=>'Anual'];
        $this->setInputRadio('validade', 'Tipo do Seguro', $options);
        
        $this->setInputText('codigoGerente', 'Cod. Gerente');
        
        $this->setInputText('refImovel', 'Ref. do Imóvel');
        
        $options = ['01'=>'A vista(no ato)', '02'=>'2 vezes(1+1)', '03'=>'3 vezes(1+2)'];
        $this->setInputSelect('formaPagto', 'Forma de pagto', $options);
        
        $label = 'Incêndio, raio, explosão e queda de aeronaves(obrigatório)';
        $this->setInputText('incendio', $label);
        
        $this->setInputText('aluguel', 'Perda de aluguel');
        
        $this->setInputText('eletrico', 'Danos elétricos');
        
        $this->setInputText('vendaval', 'Vendaval, granizo, impacto de veiculos terrestres');
        
        $this->setInputHidden('numeroParcela');
        $this->setInputHidden('premioLiquido');
        $this->setInputHidden('premio');
        $this->setInputHidden('premioTotal');
        
        $attributes = ['rows' => "8",'class'=>'span8'];
        $this->setInputTextArea('observacao', 'Obs', $attributes);
        
        $options =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesNiver', 'Mês de aniversário',$options);
        
        $this->setInputHidden('administradora');
        $this->setInputHidden('seguradora');
        
        $this->getEnderecoElements($em);

        $this->setInputSubmit('enviar', 'Salvar');
        
        $this->setInputSubmit('calcula', 'Calcular',['onClick'=>'return calcular()']);
        
    }
    
}
