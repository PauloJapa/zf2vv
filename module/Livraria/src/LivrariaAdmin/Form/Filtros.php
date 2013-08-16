<?php

namespace LivrariaAdmin\Form;

/**
 * Description of Filtros
 * Filtro comuns para ser usado em filtro de listagem 
 * @author Paulo Watakabe watakabe05@gmail.com
 */
class Filtros  extends AbstractForm {
    
    
    public function __construct(array $inputs=[], $em=null) {
        parent::__construct('filtros');
        $this->em = $em;
        
        $this->setAttribute('method', 'post');
        
        foreach ($inputs as $input => $value) {
            $this->setInputText($input, $value);
        }
        
        $this->setInputText('busca', 'Pesquisa');
        
        $this->setInputText('nome', 'Nome');
        
        $this->setInputText('rua', 'Rua');
        
        $this->setInputText('refImovel', 'Referência Imóvel');
        
        $this->setInputSubmit('enviar', 'Pesquisar', ['onClick' => 'return buscar()']);
        
        $this->setInputRadio('cpfOuCnpj', 'Escolha', ['cpf' => 'CPF','cnpj' => 'CNPJ']);
        $this->get('cpfOuCnpj')->setValue('cpf');
        
        $attributes=[];
        $attributes['placeholder'] = '';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $this->setInputText('documento', 'Documento', $attributes);
        
    }
    
    public function setTaxas(){
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', 'Situação', $status);

        $this->classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('classe', 'Classe', $this->classes, ["onChange" => "buscaClasse()"] );
        
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('seguradora', 'Seguradora', $this->seguradoras, ["onChange" => "buscaSeguradora()"] );
        
        $validade = $this->getParametroSelect('validade');
        $this->setInputSelect('validade', 'Validade', $validade);
        
        $ocupacao = $this->getParametroSelect('ocupacao');
        $this->setInputSelect('ocupacao', 'Ocupação', $ocupacao);
        
        $comissao = $this->getParametroSelect('comissaoParam');
        $this->setInputSelect('comissao', 'Comissão', $comissao);
        
        $tipoCobertura = $this->getParametroSelect('tipoCobertura');
        $this->setInputSelect('tipoCobertura', 'Tipo de Cobertura', $tipoCobertura);
    }
    
    public function setLogs(){
        $this->setInputText('controller', 'Item do menu:',['class'=>'input-small']);
        $this->setInputText('tabela', 'Arquivo:',['class'=>'input-small']);
        $this->setDate();
        $this->setForUsuario();
    }
    
    public function setOrcamento(){
        $this->setLocadorLocatario();
        $this->setInputText('id', 'Nº do Orçamento',['class'=>'input-small']);
        $this->setInputText('orcamento', 'Nº do Orçamento',['class'=>'input-small']);
        $this->setInputRadio('status', 'Status', ['A'=>'N - Novos', 'R'=>'R - Renovação de Reajuste','C'=>'C - Cancelados']);
        $this->setInputRadio('validade', 'Tipo', [''=>'Ambos','anual'=>'Anual', 'mensal' => 'Mensal']);
        $this->get('status')->setValue('A'); 
        $this->setDate();
        $this->setForUsuario();
        $this->setForAdministradora();        
        $this->setInputSubmit('fecharSel', 'Fechar Selecionados', ['onClick' => 'return fecharSelecionados()']);
    }
    
    public function setRenovado(){
        $this->setLocadorLocatario();
        $this->setInputText('id', 'Nº do Renovação',['class'=>'input-small']);
        $this->setInputText('renovado', 'Nº do Renovação',['class'=>'input-small']);
        $this->setInputRadio('status', 'Status', ['T'=>'Todos','A'=>'Ativo','F'=>'Fechados','C'=>'Cancelados']);
        $this->setInputRadio('validade', 'Tipo', [''=>'Ambos','anual'=>'Anual', 'mensal' => 'Mensal']);
        $this->get('status')->setValue('A'); 
        $this->setDate();
        $this->setForUsuario();
        $this->setForAdministradora();        
        $this->setInputSubmit('fecharSel', 'Fechar Selecionados', ['onClick' => 'return fecharSelecionados()']);
    }
    
    public function setFechadosFull(){
        $this->setLocadorLocatario();
        $this->setInputText('id', 'Nº do Seguro',['class'=>'input-small']);
        $this->setInputText('fechado', 'Nº do Seguro',['class'=>'input-small']);
        $this->setInputRadio('status', 'Status', ['T'=>'T - Todos','A'=>'N - Novos', 'R'=>'R - Renovados','C'=>'C - Cancelados']);
        $this->setInputRadio('validade', 'Tipo', [''=>'Ambos','anual'=>'Anual', 'mensal' => 'Mensal']);
        $this->get('status')->setValue('A'); 
        $this->setDate();
        $this->setForUsuario();
        $this->setForAdministradora();        
    }
    
    public function setFechados(){
        $this->setInputText('fechados', 'Nº do Seguro',['class'=>'input-small']);
        $this->setDate();
        $this->setForUsuario();
    }
    
    public function setRenovacao(){
        $this->setInputText('renovacao', 'Nº da Renovação',['class'=>'input-small']);
        $this->setDate();
        $this->setForUsuario();
    }

    public function setDate($names=['dataI','dataF']){        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)",'class'=>'input-small'];
        $this->setInputText($names[0], 'Data Inicio', $attributes);
        $this->setInputText($names[1], 'Data Fim', $attributes);
    }
    
    public function setLocadorLocatario(){
        $this->setInputHidden('locador');
        $attributes = ['placeholder' => 'Pesquise aqui pelo nome, cpf ou cnpj!',
                       'onKeyUp' => 'autoCompLocador();',
                       'autoComplete'=>'off'];        
        $this->setInputText('locadorNome', 'Locador', $attributes);
        
        $this->setInputHidden('locatario');
        $attributes['onKeyUp'] = 'autoCompLocatario();';        
        $this->setInputText('locatarioNome', 'Locatario', $attributes);
    }
    
    public function setEndereco(){
        $this->setInputHidden('endereco');
        $this->setInputText('rua', 'Endereço', ['placeholder' => 'Endereço','class' => 'input-xmlarge','onKeyUp' => 'autoCompEndRua();', 'autoComplete'=>'off']);
    }

    public function setForUsuario(){
        $this->setInputHidden('user');
        $this->setInputText(
                'usuarioNome'
                , 'Usuario'
                , [
                    'placeholder' => 'Pesquise digitando o nome aqui!'
                    , 'onKeyUp' => 'autoCompUsuario();'
                    , 'autoComplete' => 'off'
                ]
        );
    }
    
    public function setForAdministradora(){
        $this->setInputHidden('administradora');
        $this->setInputText(
                'administradoraDesc'
                , 'Administradora'
                , [
                    'placeholder' => 'Pesquise digitando a Administradora aqui!'
                    , 'onKeyUp' => 'autoCompAdministradora();'
                    , 'autoComplete' => 'off'
                ]
        );
    }
    
    public function setForClasseAtividade(){
        $this->setInputHidden('atividade');
        $this->setInputText(
                'atividadeDesc'
                , '*Atividade'
                , [
                    'placeholder' => 'Pesquise digitando a atividade aqui!'
                    , 'onKeyUp' => 'autoCompAtividade();'
                    , 'autoComplete' => 'off'
                ]
        );
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
        
        $administradora = $this->get('administradora');
        if($administradora){
            $administradora->setAttributes(array('readOnly' => 'true'));
            $this->get('administradoraDesc')->setAttributes(array('readOnly' => 'true', 'onKeyUp' => ''));   
        }
        
    }
}