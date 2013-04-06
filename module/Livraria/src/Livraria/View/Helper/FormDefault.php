<?php

namespace Livraria\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
/**
 * FormDefault
 * View Helper para trabalhar com formularios
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class FormDefault extends AbstractHelper {
    
    /**
     *
     * @var Zend\Form\View\Helper
     */
    protected $formView;
    
    /**
     *
     * @var Zend\Form
     */
    protected $form;
    
    /**
     *
     * @var Zend\Form\Element\ElementErros 
     */
    protected $inputError;
    
    /**
     * Metodo magico que é acionado quando acessado esta classe pela view
     * Configura as variaveis e direciona para o metodo requerido
     * @param Zend\Form\View\Helper $formView
     * @param Zend\Form $form
     * @param array $options
     * @param array $acao
     */
    public function __invoke(array $options, $acao = null, $formView = null, $form = null) {
        if(!is_null($formView)) $this->formView = $formView;
        if(!is_null($form))     $this->form     = $form;
        
        switch ($acao) {
            case 'inicio':
                $this->renderFormInicio($options);
                break;
            
            case 'fim':
                $this->renderFormFim($options);
                break;
            
            case 'fieldIni':
                $this->renderFieldsetIni($options);
                break;
            
            case 'fieldFim':
                $this->renderFieldsetFim($options);
                break;
            
            case 'icone':
                $this->renderInpuIcone($options);
                break;
            
            case 'iconeLine':
                $this->renderInpuIconeLine($options);
                break;
            
            case "submits":
                $this->renderInputSubmits($options);
                break;
            
            case null;
                foreach ($options as $option => $acao) {
                    $this->direciona($acao,$option);               
                }
                break;

            default:
                foreach ($options as $option) {
                    $this->direciona($acao,$option);               
                }
                break;
        }
    }

    /**
     * Metodo que direciona para renderizar o tipo de input requisitado
     * @param String $acao com a tipo do input
     * @param String $name com o name do input
     */
    public function direciona($acao, $name) {
        switch ($acao) {
            
            case "hidden":
                $this->renderInputHidden($name);
                break;
            
            case "text":
                $this->renderInputText($name);
                break;
            
            case "textArea":
                $this->renderInputTextArea($name);
                break;
            
            case "select":
                $this->renderInputSelect($name);
                break;
            
            case "radio":
                $this->renderInputRadio($name);
                break;
            
            case "submit":
                $this->renderInputSubmit($name);
                break;
            
            case "submitOnly":
                $this->renderInputSubmitOnly($name);
                break;
            
            case "buttonOnly":
                $this->renderInputButtonOnly($name);
                break;
            
            case "calend":
                $this->renderInputCalend($name);
                break;
            
            case "moeda":
                $this->renderInputMoeda($name);
                break;
            
            case "float":
                $this->renderInputFloat($name);
                break;
            
            case "floatLine":
                $this->renderInputFloatLine($name);
                break;
            
            case "float4":
                $this->renderInputFloat4($name);
                break;
            
            case "textLine":
                $this->renderInputTextLine($name);
                break;
            
            case "selectLine":
                $this->renderInputSelectLine($name);
                break;
            
            case "moedaLine":
                $this->renderInputMoedaLine($name);
                break;

            default:
                echo
                    "<h1>deb $acao $name</h1>";
                break;
        } 
    }

    /**
     * Renderiza o inicio do form e a parte do fieldset  e inicio do table 
     * para organizar o formulario em colunas recebe no options legend e um input hidden se houver
     * @param Array $options 
     */
    public function renderFormInicio($options) {
        echo $this->formView->form()->openTag($this->form),
             $this->renderFieldsetIni($options);
                   
    }

    /**
     * Renderiza o inicio do fieldset  e inicio do table 
     * para organizar o formulario em colunas 
     * Recebe no options legend e um input hidden se houver
     * @param Array $options 
     */
    public function renderFieldsetIni($options) {
        echo "<fieldset>", PHP_EOL,
                "<legend>";
        if(isset($options['legend'])) 
            echo $options['legend'];
        echo    "</legend>", PHP_EOL;
        if(isset($options['hidden'])) 
            $this->renderInputHidden($options['hidden']);
        echo            
                "<table style='width : 100% ;'>", PHP_EOL,
                    "<tr valign='top'>", PHP_EOL; 
    }

    /**
     * Renderiza o fim do fieldset, table
     * Coloca o botao submit se requerido
     * @param Array $options
     */
    public function renderFieldsetFim($options) {
        echo
                "</tr>", PHP_EOL,
            "</table>", PHP_EOL,
        "</fieldset>", PHP_EOL;
        if(isset($options['submit'])) 
            $this->renderInputSubmit($options['submit']);
    }

    /**
     * Renderiza o fim do fieldset, table e form
     * Não renderiza o fim do fieldset e table se for definido noField nas opções
     * Coloca o botao submit se requerido
     * @param Array $options
     */
    public function renderFormFim($options) {
        if(!isset($options['noField'])){
            echo $this->renderFieldsetFim($options);
        }else{
            if(isset($options['submit']))
                $this->renderInputSubmit($options['submit']);
        }
        echo $this->formView->form()->closeTag(), PHP_EOL;        
    }

    /**
     * Renderiza o input Submit 
     * @param String $name
     */
    public function renderInputSubmitOnly($name) {
        echo
            $this->formView->formSubmit($this->form->get($name));
    }

    /**
     * Renderiza o input Submit 
     * @param String $name
     */
    public function renderInputButtonOnly($name) {
        echo
            $this->formView->formButton($this->form->get($name));
    }

    /**
     * Renderiza o input Submit no centro da tela
     * @param String $name
     */
    public function renderInputSubmit($name) {
        echo
        "<div align='center'>",
            $this->formView->formSubmit($this->form->get($name)),
        "</div>", PHP_EOL;
    }
    
    public function renderInputSubmits($names=[]){
        echo '<table width="100%"><tr>';
        foreach ($names as $name) {
            echo '<td align=center>',
            $this->formView->formSubmit($this->form->get($name)),
                 '</td>';   
        }
        echo '</tr></table>';
    }

    /**
     * Renderiza o input hidden 
     * @param String $name
     */
    public function renderInputHidden($name) {
        echo $this->formView->formHidden($this->form->get($name)), PHP_EOL;          
    }

    /**
     * Renderiza o input text com um botao para limpar o conteudo
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputTextArea($name) {
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo 
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formTextarea($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>", PHP_EOL,
        $this->checkError();
    }

    /**
     * Renderiza o input text com um botao para limpar o conteudo
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputText($name) {
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo 
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>", PHP_EOL,
        $this->checkError();
    }

    /**
     * Renderiza o input text na posição inline com um botao para limpar o conteudo
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputTextLine($name) {
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo 
        '<div class="form-horizontal" id="pop' . $name . '">',
        '<div class="input-append control-group" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>", PHP_EOL,
        "</div>", PHP_EOL,
        $this->checkError();
    }

    /**
     * Renderiza o input text com um botao para limpar o conteudo e outro para 
     * escolher um data para o preencimento
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputCalend($name) {
        $element = $this->form->get($name);
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
            '<span class="add-on hand" onClick="displayCalendar(document.forms[0].', $name ,',dateFormat,this)"><i class="icon-calendar"></i></span>',
        "</div>", PHP_EOL,
        $this->checkError();
    }

    /**
     * Renderiza o input text no estilo moeda com um botao para limpar o conteudo  
     * Adiciona js para mascara de moeda
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     * @param String $symbol para exibir ou não o simbolo da moeda
     */
    public function renderInputMoeda($name,$symbol='true',$dec='2') {
        $element = $this->form->get($name);
        $element->setAttribute('style','text-align:right;');
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>", PHP_EOL,
        '<script language="javascript">',
        '$(function(){$("#',
                $name,
        '").maskMoney({symbol:"R$ ", showSymbol:', $symbol, ', thousands:".", decimal:",", symbolStay:true, precision:', $dec, '});});',
        '</script>',
         
        $this->checkError();
    }

    /**
     * Renderiza o input text no estilo float com um botao para limpar o conteudo  
     * Adiciona js para mascara de decimal
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputFloat($name){
        $this->renderInputMoeda($name,'false');
    }

    /**
     * Renderiza o input text no estilo float com um botao para limpar o conteudo  
     * Adiciona js para mascara de 4 decimal
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputFloat4($name){
        $this->renderInputMoeda($name,'false','4');
    }

    /**
     * Renderiza o input text no estilo moeda com label na horizontal com um botao para limpar o conteudo  
     * Adiciona js para mascara de moeda 
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputMoedaLine($name,$symbol='true',$dec='2') {
        $element = $this->form->get($name);
        $element->setAttribute('style','text-align:right;');
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $name = '';
        echo
        '<div class="form-horizontal">',
        '<div class="input-append control-group" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>", PHP_EOL,
        "</div>", PHP_EOL,
        '<script language="javascript">',
        '$(function(){$("#',
                $name,
        '").maskMoney({symbol:"R$ ", showSymbol:', $symbol, ', thousands:".", decimal:",", symbolStay:true, precision:', $dec, '});});',
        '</script>',
         
        $this->checkError();
    }

    /**
     * Renderiza o input text no estilo float in Line com um botao para limpar o conteudo  
     * Adiciona js para mascara de decimal
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputFloatLine($name){
        $this->renderInputMoedaLine($name,'false');
    }

    /**
     * Renderiza o input text no estilo float in Line com um botao para limpar o conteudo  
     * Adiciona js para mascara de 4 decimal
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputFloat4Line($name){
        $this->renderInputMoedaLine($name,'false','4');
    }

    /**
     * Renderiza um input com botão de limpar e outro botão passado por parametro 
     * Caso exista o parametro span renderiza sua tag
     * @param array $options
     */
    public function renderInpuIcone($options) {
        $element = $this->form->get($options['name']);
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $options['name'] = '';
        echo
        '<div class="input-append" id="pop' . $options['name'] . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $options['name'] ,'\')"><i class="icon-remove"></i></span>',
            '<span class="add-on hand" onClick="', $options['js'] ,'"><i class="', $options['icone'] ,'"></i></span>',
        "</div>", PHP_EOL;
        if(isset($options['span'])) 
            echo "<span id='", $options['span'] ,"'></span></font>";    
        $this->checkError();        
    }

    /**
     * Renderiza um input com botão de limpar e outro botão passado por parametro 
     * Caso exista o parametro span renderiza sua tag
     * @param array $options
     */
    public function renderInpuIconeLine($options) {
        $element = $this->form->get($options['name']);
        $this->checkError($element);
        if($element->getAttribute('readOnly'))
            $options['name'] = '';
        echo
        '<div class="form-horizontal">',
        '<div class="input-append control-group" id="pop' . $options['name'] . '">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $options['name'] ,'\')"><i class="icon-remove"></i></span>',
            '<span class="add-on hand" onClick="', $options['js'] ,'"><i class="', $options['icone'] ,'"></i></span>',
        "</div>", PHP_EOL,
        "</div>", PHP_EOL;
        if(isset($options['span'])) 
            echo "<span id='", $options['span'] ,"'></span></font>";    
        $this->checkError();        
    }

    /**
     * Renderiza o input Selec
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputSelect($name) {
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $this->checkError($element);
        echo 
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formSelect($element),
        "</div>", PHP_EOL;
        $this->checkError();
        if($element->getAttribute('disabled'))
            echo '<input type="hidden" name="', $name, '" id="', $name, '" value="', $element->getValue() ,'">', PHP_EOL;
    }

    /**
     * Renderiza o input Selec
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputSelectLine($name) {
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $this->checkError($element);
        echo 
        '<div class="form-horizontal">',
        '<div class="input-append control-group" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formSelect($element),
        "</div>", PHP_EOL,
        "</div>", PHP_EOL;
        $this->checkError();
        if($element->getAttribute('disabled'))
            echo '<input type="hidden" name="', $name, '" id="', $name, '" value="', $element->getValue() ,'">', PHP_EOL;
    }
    
    
    public function renderInputRadio($name){
        $element = $this->form->get($name);
        if(!$element){
            echo '<h1>Erro ao tentar carregar input= ' . $name ;
            return;
        }
        $disabled = $element->getAttribute('disabled');
     //   if($disabled){ //OnClick nao é necessario quando desativa o campo =)
     //       if($element->getAttribute('onClick')) $element->getAttribute('onClick') = '';
     //   }
        $this->checkError($element);
        echo 
        '<div class="input-append" id="pop' . $name . '">',
            $this->formView->formLabel($element),
            $this->formView->formRadio($element),
        "</div>", PHP_EOL;
        if($disabled)
            echo '<script language="javascript">',
                    'setInputDisabledMulti("', $name, '");',
                 '</script>',
                 '<input type="hidden" name="', $name, '" value="', $element->getValue() ,'">';
        $this->checkError();
    }

    /**
     * Abre e fecha as tags para exibição de erro 
     * Com parametro e verifica se tem erro se sim abre a tag e guarda o erro
     * Sem parametro verifica se tem erro se sim fecha a tag e limpa $this->inputError
     * @param Objet $element
     */
    public function checkError($element = null) {
        if(is_null($element)){
            if($this->inputError){
                echo $this->inputError, "</div>", PHP_EOL;
                $this->inputError = false;
            }
        }else {
            $this->inputError = $this->formView->formElementErrors($element, array('class' => 'help-inline'));
            if($this->inputError){
                echo '<div class="control-group error">';
            }
        }
    }

}
