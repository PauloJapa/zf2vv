<style>
    .form-horizontal .control-group>label{float:left;width:450px;padding-top:5px;text-align:right;}
</style>

<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
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
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Limites de Contratação', 'hidden' => 'idMultiplos'],'inicio',$this, $form),
    "<td>\n",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao'],'hidden'),
        $this->FormDefault(['seguradora' => 'select']),
    "</td><td>\n",
        $this->FormDefault(['multVigenciaInicio' => 'calend']),
    "</td><td>\n",
        $this->FormDefault(['multVigenciaFim' => 'calend']),
    "</td><td>\n",
        $this->FormDefault(['multStatus' => 'select']),
    "</td><td>\n",
    "</td>\n",
"</tr>\r",
"</table>\r",
        
$this->FormDefault(['legend'=>'Parametros'],'fieldIni'),
    "\n<td>",
        $this->FormDefault(['minPremioAnual' => 'float']),
    "</td>\n<td>",
        $this->FormDefault(['minPremioMensal' => 'float']),
    "</td>\n<td>",
        $this->FormDefault(['minApoliceAnual' => 'float']),
    "</td>\n<td>",
        $this->FormDefault(['minApoliceMensal' => 'float']),
    "</td>\n<td>",
        $this->FormDefault(['minParcelaAnual' => 'float']),
    "</td>\n<td>",
        $this->FormDefault(['minParcelaMensal' => 'float']),
    "</td>\n<td>",
    "</td>\n",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['legend'=>'Cobertura Minimas'],'fieldIni'),
    "<td>\n",
        $this->FormDefault(['minIncendio' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['minConteudo' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['minAluguel' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['minEletrico' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['minVendaval' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['minRespcivil' => 'moeda']),
    "</td>\n",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['legend'=>'Cobertura Maximas'],'fieldIni'),
    "<td>\n",
        $this->FormDefault(['maxIncendio' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['maxConteudo' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['maxAluguel' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['maxEletrico' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['maxVendaval' => 'moeda']),
    "</td><td>\n",
        $this->FormDefault(['maxRespcivil' => 'moeda']),
    "</td>\n",
$this->FormDefault([],'fieldFim'),
        
        
$this->FormDefault(['submit'=>'enviar'],'fim');

require 'index.phtml';
?> 
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    var varVazio = ''; //Var para testar se campo cnpj ou cpf esta vazio
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        envia(tar,'salvar',formName);
        return false;
    }
    function buscar(){
        envia(tar,'buscar',formName);
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }


</script>