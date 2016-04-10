
<p class=""><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span>
<span class="add-on hand" onClick="novo();"><i class="icon-plus"></i>Nova Taxa de Ajuste</span></p>
<?php if(count($flashMessages)) : ?>
<div class="control-group error">
<ul class="help-inline">
    <?php foreach ($flashMessages as $msg) : ?>
    <li><?php echo $msg; ?></li>
    <?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
<?php
/* @var $form \LivrariaAdmin\Form\TaxaAjuste */
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados das Taxas de Ajuste', 'hidden' => 'id'],'inicio',$this, $form),
    "\r<td>",
        $this->FormDefault(['subOpcao' => 'hidden']),
        $this->FormDefault(['administradora' => 'select']),
        $this->FormDefault(['seguradora' => 'select']),
        $this->FormDefault(['comissao' => 'select']),
    "</td>\r<td>",
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['validade' => 'select']),
    "</td>\r<td>",
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
"</tr><tr>\r",
    "</td>\r<td>",
    "\r<td colspan=2>",
        $this->FormDefault(['ocupacao' => 'radioLine']),
    "</td>\r",
"</tr>\r",
"</table>\r",
        
$this->FormDefault(['legend'=>'Parametrizar Taxas'],'fieldIni');
?>
<td>
<div id='fieldApto'>
    <table width="100%">
        <tr>
            <td width="50%" id="celApt1"></td>
            <td width="50%" id="celApt2"></td>
        </tr>
    </table>
</div>        
<div id='fieldCasa'>
    <table width="100%">
        <tr>
            <td width="50%" id="celCasa1"></td>
            <td width="50%" id="celCasa2"></td>
        </tr>
    </table>
</div>
<div id='fieldCome'>    
    <table width="100%" id="tableCome">
    </table>
</div>
<div id='fieldIndu'></div>
<div id='fieldTaxa'>        
<?
$inputs = $form->getInputs();
//echo '<pre>', var_dump($inputs);
//die;
foreach ($inputs as $input) {
    echo $this->FormDefault([$input => 'float']);
}
$inputs = $form->getInputs(TRUE);
$classes = $form->getClasses();
foreach ($classes as $key => $classe) {    
    foreach ($inputs as $input) {
//        echo $this->FormDefault(['name' => $input . '[' . $key . ']', 'icone' => 'icon-plus', 'js' => 'setEqual(this)'], 'float');
        echo $this->FormDefault([$input . 'Array[' . $key . ']' => 'float']);
    }
    echo $this->FormDefault(['idArray[' . $key . ']' => 'hidden']);
}
echo        
"</div>",       
"</td>",       
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['submit' => 'enviar'],'fim');

$noFilter=true;

//        $this->FormDefault(['classe' => 'select']),
//require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    var inputs = <?php echo json_encode($inputs) ?>;
    var Labels = <?php echo json_encode($form->getLabelOfInputs()) ?>;
    var classes = <?php echo json_encode($classes) ?>;
      
    var novo = function(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'new')); ?>";
        envia(tar,'',formName);        
    };  
    
    var escondeFields = function(id){
        $('#' + id).hide();
    };
    
    var checkedValOfRadioOld = '';
    var setFieldsTaxa = function(){
        $radio = false; 
        $(this).find('input').each(function() {
            if($(this).prop('checked')){
                $radio = $(this);
            }
        });
        if ($radio.val() === checkedValOfRadioOld || $radio === false){
            return;
        }
        //resetar campos da disposição atual
        resetFieldOfTaxa(checkedValOfRadioOld);
        checkedValOfRadioOld = $radio.val();
        // Montar nova visualização baseado na opcao
        setFieldsOf($radio.val())  ;    
    };
    
    var resetFieldOfTaxa = function(opt){  
        switch(opt){
            case '04':
                resetFieldsOfApartamento();
                break;
            case '02':
                resetFieldsOfCasa();
                break;
            case '01':
                resetFieldsOfComercia();
                break;
            case '03':
                resetFieldsOfIndustria();
                break;
            default:    
                console.log(opt);        
        }            
    };
    
    var resetFieldsOfApartamento = function(){  
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldApto = $("#fieldApto");
        $fieldTaxa.append($fieldApto.find('#popcomEletrico').remove());
        $fieldTaxa.append($fieldApto.find('#popsemEletrico').remove());
        $fieldApto.hide();
        console.log('resetado apartamento');        
    };
    
    var resetFieldsOfCasa = function(){   
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCasa = $("#fieldCasa");     
        $fieldTaxa.append($fieldCasa.find('#popcomEletrico').remove());
        $fieldTaxa.append($fieldCasa.find('#popsemEletrico').remove());
        $fieldCasa.hide();
        console.log('resetado resetFieldsOfCasa');        
    };
    
    var resetFieldsOfComercia = function(){  
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCome = $("#fieldCome"); 
        $.each(inputs, function(i,e){
            console.log(e);
            ipt = e;
            $.each(classes, function(ind, ele){
                $fieldTaxa.append($fieldCome.find('#pop' + ipt + '_' + ind).remove());                 
            });            
        });
        $fieldCome.hide();      
        console.log('resetado resetFieldsOfComercia');        
    };
    
    var resetFieldsOfIndustria = function(){ 
        var $fieldTaxa = $("#fieldTaxa");
        var $field = $("#fieldIndu"); 
        $.each(inputs, function(i,e){
            console.log(e);
            ipt = e;
            $.each(classes, function(ind, ele){
                $fieldTaxa.append($field.find('#pop' + ipt + '_' + ind).remove());                 
            });            
        });
        $field.hide();         
        console.log('resetado resetFieldsOfIndustria');        
    };
    
    var setFieldsOfApartamento = function(){  
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldApto = $("#fieldApto");
        $fieldApto.find('#celApt1').append($fieldTaxa.find('#popcomEletrico').remove());
        $fieldApto.find('#celApt1').append($fieldTaxa.find('#popsemEletrico').remove());
        $fieldApto.show();
        console.log('setFieldsOfApartamento ok');        
    };
    
    var setFieldsOfCasa = function(){ 
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCasa = $("#fieldCasa");   
        $fieldCasa.find('#celCasa1').append($fieldTaxa.find('#popcomEletrico').remove());
        $fieldCasa.find('#celCasa1').append($fieldTaxa.find('#popsemEletrico').remove());    
        $fieldCasa.show();
        console.log('setFieldsOfCasa ok');        
    };
    
    var setFieldsOfComercia = function(){ 
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCome = $("#fieldCome"); 
        var $table = $('#tableCome');
        var $tr    = $('<tr/>');
        var $td    = $('<td/>');
        $tr.append($td.clone().html('Classe'));
        $.each(Labels, function(i,e){
            $tr.append($td.clone().html(e));        
        });
        $table.html('');
        $table.attr("width","100%");
        $table.append($tr);
        $tr2    = $('<tr/>');
        $.each(classes, function(ind, ele){
            idclasse = ind;
            $tr2.append($td.clone().html(ele));        
            $.each(inputs, function(i,e){
                $tr2.append($td.clone().html($fieldTaxa.find('#pop' + e + '_' + idclasse).remove()));        
            });
            $table.append($tr2);
            $tr2 = $tr2.clone().html('');
        });            
        $fieldCome.append($table);                 
        $fieldCome.show();
        console.log('setFieldsOfComercia ok');        
    };
    
    var setFieldsOfIndustria = function(){ 
        var $fieldTaxa = $("#fieldTaxa");
        var $field = $("#fieldIndu"); 
        var $table = $('#tableCome');
        var $tr    = $('<tr/>');
        var $td    = $('<td/>');
        $tr.append($td.clone().html('Classe'));
        $.each(Labels, function(i,e){
            $tr.append($td.clone().html(e));        
        });
        $table.html('');
        $table.attr("width","100%");
        $table.append($tr);
        $tr2    = $('<tr/>');
        $.each(classes, function(ind, ele){
            idclasse = ind;
            $tr2.append($td.clone().html(ele));        
            $.each(inputs, function(i,e){
                $tr2.append($td.clone().html($fieldTaxa.find('#pop' + e + '_' + idclasse).remove()));        
            });
            $table.append($tr2);
            $tr2 = $tr2.clone().html('');
        });            
        $field.append($table);                 
        $field.show();       
        console.log('setFieldsOfIndustria');        
    };
    
    var setFieldsOf = function(opt){  
        switch(opt){
            case '04':
                setFieldsOfApartamento();
                break;
            case '02':
                setFieldsOfCasa();
                break;
            case '01':
                setFieldsOfComercia();
                break;
            case '03':
                setFieldsOfIndustria();
                break;
            default:    
                console.log(opt);        
        }        
    };
    
    var setEqual = function(){
        alert('ok');
    };
    
    var cleanAnother = function(ele){
        if(ele.id.search("unica") !== -1){
            $input = $(ele).closest('tr').find('input');
            $input.each(function(){
                if(this.id.search("unica") === -1){
                    $(this).val('');
                }
            });
        }else{
            $input = $(ele).closest('tr').find('input');
            $input.each(function(){
                if(this.id.search("unica") !== -1){
                    $(this).val('');
                }
            });
        }
    };
    
    var showInput = function(){
        var $radios = $('#popocupacao').find('input');
        $radios.each(function(){
            if(this.checked){
                $(this).click();
            }
        });  
    };
    
    function buscaSeguradora(){
        envia(tar,'buscar',formName);
    }
    function buscaClasse(){
        envia(tar,'buscar',formName);
    }
    function salvar(){
        envia(tar,'salvar',formName);
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
    
//    function setComissao(obj){
//        if(typeof obj === 'undefined'){
//            obj = {'name': ''};
//        }
//        seg = $('#seguradora').val();
//        switch($("input[name=ocupacao]:checked").val()){
//        case '01':                
//            $.each(param, function(index, value) {
//                if((index === 'comissaoComercial') && (value !== '')){
//                    $('#comissao').val(value);
//                }
//            });
//            break;
//        case '02':              
//            $.each(param, function(index, value) {
//                if((index === 'comissaoResidencial') && (value !== '')){
//                    $('#comissao').val(value);
//                }
//            });
//            break;
//        }
//    }

    $(function(){
        /**
         * esconder os campos de taxas
         */
        escondeFields('fieldTaxa');  
        escondeFields('fieldApto');  
        escondeFields('fieldCasa');  
        escondeFields('fieldCome');  
        escondeFields('fieldIndu');  
        
        $("#popocupacao").on('click',setFieldsTaxa);      
        
        showInput();
    });
</script>
