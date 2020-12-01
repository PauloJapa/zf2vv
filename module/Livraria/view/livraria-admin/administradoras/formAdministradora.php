
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
$this->FormDefault(['legend' => 'Dados Basicos:'],'inicio',$this, $form),
    "<td>\n",
        $this->FormDefault(['ajaxStatus','subOpcao'],'hidden'),
        $this->FormDefault(['id'], 'text'),
    "</td><td>",
        $this->FormDefault(['seguradora'], 'select'),
    "</td><td>",
        $this->FormDefault(['codigoCol'], 'text'),
    "</td>",
"</tr><tr>",
    "<td>\n",
        $this->FormDefault(['nome','tel'], 'text'),
    "</td><td>",
        $this->FormDefault(['apelido','email'], 'text'),
    "</td><td>",
        $this->FormDefault(['cnpj'], 'text'),
        $this->FormDefault(['status'], 'select'),
    "</td>\n";
        
    if($this->UserIdentity('LivrariaAdmin')->getNome() == 'Paulo Cordeiro Watakabe'){
        echo
    "</tr><tr>",
        "<td>\n",
            $this->formRow($form->get('content')),
        "</td><td>",
            $this->FormDefault(['importar'], 'submitOnly'),
        "</td><td>",
        "</td>\n";
    }        
echo        
"</tr>\r",
"</table>\r",
 $this->FormDefault(['legend'=>'Parametros para Seguros'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['formaPagto' => 'select']),
        $this->FormDefault(['propPag' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['tipoCobertura' => 'select']),
        $this->FormDefault(['tipoCoberturaRes' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['validade' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['assist24' => 'radio']),
    "</td>\r",
    "</tr><tr>",
    "<td>\r",
        $this->FormDefault(['geraExpSep' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['showCusInd' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['blockFechamento' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['exptRefOrder' => 'radio']),
    "</tr><tr>",
    "<td>\r",
        $this->FormDefault(['parcela4x' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['parcela5x' => 'radio']),
    "</td><td>\r",
    "</td><td>\r",
    "</td>\r",
$this->FormDefault([],'fieldFim'),  
        
$this->FormDefault(['submit' => 'enviar'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

$this->FormDefault(['submit' => 'enviar','noField' => true],'fim');

/*
$noFilter=true;

require 'index.phtml';
 */

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        envia(tar,'salvar',formName);
        return false;
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
    function importarFile(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
        envia(tar,'',formName);
        return false;
    }
</script>
