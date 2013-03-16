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
$this->FormDefault(['legend' => 'Dados Basicos:', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\n",
        $this->FormDefault(['ajaxStatus','subOpcao'],'hidden'),
        $this->FormDefault(['nome','tel'], 'text'),
        $this->FormDefault(['seguradora'], 'select'),
    "</td><td>",
        $this->FormDefault(['apelido','email'], 'text'),
    "</td><td>",
        $this->FormDefault(['cnpj'], 'text'),
        $this->FormDefault(['status'], 'select'),
    "</td>\n",
        
"</tr>\r",
"</table>\r",
 $this->FormDefault(['legend'=>'Parametros para Seguros'],'fieldIni'),
    "<td width='33%'>\r",
        $this->FormDefault(['formaPagto' => 'select']),
    "</td><td width='33%'>\r",
        $this->FormDefault(['validade' => 'radio']),
    "</td><td>\r",
        $this->FormDefault(['tipoCobertura' => 'select']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),  
        
$this->FormDefault(['submit' => 'enviar'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

$this->FormDefault(['submit' => 'enviar','noField' => true],'fim');

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        envia(tar,'salvar',formName);
        return false;
    }
</script>
