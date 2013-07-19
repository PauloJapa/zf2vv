<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Renovacao extends AbstractForm {

    public function __construct($name = null, $em = null) {
        parent::__construct('renovacao');
        $this->em = $em;

        $this->setAttribute('method', 'post');
        //$this->setInputFilter(new RenovacaoFilter);  
        
        $this->setInputHidden('administradora');
        $attributes = ['placeholder' => 'Pesquise digitando a Administradora aqui!',
                       'onKeyUp' => 'autoCompAdministradora();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('administradoraDesc', 'Pertence a administradora', $attributes); 

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio', $attributes);
        
        $this->setInputText('fim', '*Fim', $attributes);
        
        $comando = [
            "==" => "Extamente Igual a",
            ">=" => "Igual ou Maior que",
            "<=" => "Igual ou Menor que",
        ];
        $this->setInputSelect('comando', 'Instrução', $comando);
        
        $this->setInputText('percent', 'Porcentagem', ['placeholder' => '%']);
        
        $optionsMes =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesNiver', 'Mês fim da vigência',$optionsMes);
        
        $anoAtual = date('Y');
        $anoAtual++;
        for ($i = 0; $i < 5; $i++){
            $arrayAnos[$anoAtual] = $anoAtual;
            $anoAtual--;
        }
        $this->setInputSelect('anoFiltro', 'Ano',$arrayAnos);
        
        $this->setInputSubmit('enviar', 'Buscar',['onClick' => 'return buscar()']);
    }
    
}
