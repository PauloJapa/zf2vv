
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
$this->FormDefault(['legend' => 'Dados Basicos da Seguradora:', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao'],'hidden'),
        $this->FormDefault(['nome', 'tel', 'site'],'text'),
    "</td><td>\r",
        $this->FormDefault(['apelido', 'email'],'text'),
    "</td><td>\r",
        $this->FormDefault(['cnpj' => 'text', 'status' => 'select']),
    "</td>\r",
$this->FormDefault([],'fieldFim');

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
    var varVazio = ''; //Var para testar se campo cnpj ou cpf esta vazio
    
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
</script>