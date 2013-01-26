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
    public function __invoke($formView, $form, array $options, $acao = null) {
        $this->formView = $formView;
        $this->form     = $form;
        
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
            
            case "calend":
                $this->renderInputCalend($name);
                break;
            
            case "select":
                $this->renderInputSelect($name);
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
        echo "<fieldset>\r",
                "<legend>";
        if(isset($options['legend'])) 
            echo $options['legend'];
        echo    "</legend>\r";
        if(isset($options['hidden'])) 
            $this->renderInputHidden($options['hidden']);
        echo            
                "<table style='width : 100% ;'>\r",
                    "<tr valign='top'>\r"; 
    }

    /**
     * Renderiza o fim do fieldset, table
     * Coloca o botao submit se requerido
     * @param Array $options
     */
    public function renderFieldsetFim($options) {
        echo
                "</tr>\r",
            "</table>\r",
        "</fieldset>\r";
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
        echo $this->formView->form()->closeTag(),"\r";        
    }

    /**
     * Renderiza o input Submit no centro da tela
     * @param String $name
     */
    public function renderInputSubmit($name) {
        echo
        "<div align='center'>",
            $this->formView->formSubmit($this->form->get($name)),
        "</div>\r";
    }

    /**
     * Renderiza o input hidden 
     * @param String $name
     */
    public function renderInputHidden($name) {
        echo $this->formView->formHidden($this->form->get($name)),"\r";          
    }

    /**
     * Renderiza o input text com um botao para limpar o conteudo
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputText($name) {
        $element = $this->form->get($name);
        $this->checkError($element);
        echo 
        '<div class="input-append">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
        "</div>\r",
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
        echo
        '<div class="input-append">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $name ,'\')"><i class="icon-remove"></i></span>',
            '<span class="add-on hand" onClick="displayCalendar(document.forms[0].', $name ,',dateFormat,this)"><i class="icon-calendar"></i></span>',
        "</div>\r",
        $this->checkError();
    }

    public function renderInpuIcone($options) {
        $element = $this->form->get($options['name']);
        $this->checkError($element);
        echo
        '<div class="input-append">',
            $this->formView->formLabel($element),
            $this->formView->formText($element),
            '<span class="add-on hand" onClick="cleanInput(\'', $options['name'] ,'\')"><i class="icon-remove"></i></span>',
            '<span class="add-on hand" onClick="', $options['js'] ,'"><i class="', $options['icone'] ,'"></i></span>';
        if(isset($options['span'])) 
            echo "<span id='", $options['span'] ,"'></span></font>";    
        echo
        "</div>\r",
        $this->checkError();        
    }

    /**
     * Renderiza o input Selec
     * Caso exista msg de erro sera exibo em vermelho
     * @param String $name
     */
    public function renderInputSelect($name) {
        $element = $this->form->get($name);
        $this->checkError($element);
        echo 
        '<div class="input-append">',
            $this->formView->formLabel($element),
            $this->formView->formSelect($element),
        "</div>\r";
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
                echo $this->inputError, "</div>\r";
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
