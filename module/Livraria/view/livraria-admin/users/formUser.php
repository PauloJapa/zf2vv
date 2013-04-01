
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
$this->FormDefault(['legend' => 'Dados do Usuário:', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao','administradora'],'hidden'),
        $this->FormDefault(['email'],'text'),
        $this->formRow($form->get('password')),
        $this->formRow($form->get('password2')),
    "</td><td>\r",
        $this->FormDefault(['nome'],'text'),
        $this->FormDefault(['name' => 'administradoraDesc','icone' => 'icon-search','js' => 'autoCompAdministradora()','span' => "popAdministradora' style='position:absolute"],'icone'),
    "</td><td>\r",
        $this->FormDefault(['tipo','isAdmin','status'],'select'),
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
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        if(document.getElementById('password').value != document.getElementById('password2').value){
            alert("Senha digita não é igual a anterior!");
            return false;
        }
        envia(tar,'salvar',formName);
        return false;
    }
    function autoCompAdministradora(){
        var filtros = 'administradoraDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'administradoras','action'=>'autoComp')); ?>";
        var returns = Array('administradora','administradoraDesc');
        var functionCall = 'buscaAdministradora()';
        autoComp2(filtros,servico,'popAdministradora',returns,'2',functionCall);
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
</script>