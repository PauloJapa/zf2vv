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