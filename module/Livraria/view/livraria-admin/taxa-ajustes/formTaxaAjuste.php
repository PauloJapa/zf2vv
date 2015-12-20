
<p class=""><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
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
    "</td>\r<td>",
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['validade' => 'select']),
    "</td>\r<td>",
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
"</tr><tr>\r",
    "\r<td colspan=3>",
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
            <td width="45%" id="celApt1"></td>
            <td width="10%" align="center"> OU </td>
            <td width="45%" id="celApt2"></td>
        </tr>
    </table>
</div>        
<div id='fieldCasa'></div>
<div id='fieldCome'>    
    <table width="100%" id="tableCome">
    </table>
</div>
<div id='fieldIndu'></div>
<div id='fieldTaxa'>        
<?
$inputs = $form->getInputs();
foreach ($inputs as $input) {
    echo $this->FormDefault([$input => 'float']);
}
$inputs = $form->getInputs(TRUE);
$classes = $form->getClasses();
foreach ($classes as $key => $classe) {    
    foreach ($inputs as $input) {
        echo $this->FormDefault([$input . '[' . $key . ']'  => 'float']);
    }
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
        $fieldTaxa.append($fieldApto.find('#popunica').remove()); 
        $fieldApto.hide();
        console.log('resetado apartamento');        
    };
    
    var resetFieldsOfCasa = function(){   
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCasa = $("#fieldCasa");     
        $fieldTaxa.append($fieldCasa.find('#popunica').remove()); 
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
        console.log('resetado resetFieldsOfIndustria');        
    };
    
    var setFieldsOfApartamento = function(){  
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldApto = $("#fieldApto");
        $fieldApto.find('#celApt1').append($fieldTaxa.find('#popcomEletrico').remove());
        $fieldApto.find('#celApt1').append($fieldTaxa.find('#popsemEletrico').remove());
        $fieldApto.find('#celApt2').append($fieldTaxa.find('#popunica').remove()); 
        $fieldApto.show();
        console.log('setFieldsOfApartamento ok');        
    };
    
    var setFieldsOfCasa = function(){ 
        var $fieldTaxa = $("#fieldTaxa");
        var $fieldCasa = $("#fieldCasa");       
        $fieldCasa.append($fieldTaxa.find('#popunica').remove()); 
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
        $table.append($tr);
//        $tr.html('');
        $.each(inputs, function(i,e){
            console.log(e);
            ipt = e;
            $.each(classes, function(ind, ele){
                $fieldCome.append($fieldTaxa.find('#pop' + ipt + '_' + ind).remove());                 
            });            
        });
        $fieldCome.show();
        console.log('setFieldsOfComercia ok');        
    };
    
    var setFieldsOfIndustria = function(){        
        console.log('ok3');        
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
    });
</script>
