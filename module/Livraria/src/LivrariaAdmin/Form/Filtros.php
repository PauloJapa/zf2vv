<?php

namespace LivrariaAdmin\Form;

/**
 * Description of Filtros
 * Filtro comuns para ser usado em filtro de listagem 
 * @author Paulo Watakabe watakabe05@gmail.com
 */
class Filtros  extends AbstractForm {
    
    
    public function __construct(array $inputs=[]) {
        parent::__construct('filtros');
        
        $this->setAttribute('method', 'post');
        
        foreach ($inputs as $input => $value) {
            $this->setInputText($input, $value);
        }
        
        $this->setInputText('busca', 'Pesquisa');
        
        $this->setInputText('nome', 'Nome');
        
        $this->setInputText('rua', 'Rua');
        
        $this->setInputSubmit('enviar', 'Pesquisar', ['onClick' => 'return buscar()']);
        
        $this->setInputRadio('cpfOuCnpj', 'Escolha', ['cpf' => 'CPF','cnpj' => 'CNPJ']);
        $this->get('cpfOuCnpj')->setValue('cpf');
        
        $attributes=[];
        $attributes['placeholder'] = '';
        $attributes['onKeyUp'] = 'this.value=cpfCnpj(this.value)';
        $attributes['onblur'] = 'if(this.value != varVazio)checkCPF_CNPJ(this)';
        $this->setInputText('documento', 'Documento', $attributes);
        
    }
    
    public function setLogs(){
        $this->setInputText('controller', 'Item do menu:',['class'=>'input-small']);
        $this->setInputText('tabela', 'Arquivo:',['class'=>'input-small']);
        $this->setDate();
        $this->setForUsuario();
    }
    
    public function setOrcamento(){
        $this->setInputText('id', 'Nº do Orçamento',['class'=>'input-small']);
        $this->setInputText('orcamento', 'Nº do Orçamento',['class'=>'input-small']);
        $this->setInputRadio('status', 'Status', ['T'=>'Todos','A'=>'Ativo','F'=>'Fechados','C'=>'Cancelados']);
        $this->get('status')->setValue('A'); 
        $this->setDate();
        $this->setForUsuario();
        $this->setForAdministradora();        
        $this->setInputSubmit('fecharSel', 'Fechar Selecionados', ['onClick' => 'return fecharSelecionados()']);
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
    
}