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
$this->FormDefault(['legend' => 'Dados da Log', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['user','subOpcao','autoComp','ajaxStatus'],'hidden'),
        $this->FormDefault(['name' => 'userDesc','icone' => 'icon-search','js' => 'autoCompUser()','span' => "popUser' style='position:absolute"],'icone'),
        $this->FormDefault(['tabela','idDoReg'],'text'),
    "</td><td>\r",
        $this->FormDefault(['data' => 'calend','controller' => 'text','dePara' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['ip' => 'text','action' => 'text']),
    "</td>\r",
$this->FormDefault(['submit' => 'enviar'],'fim');

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function buscaClasse(){
        envia(tar,'buscar',formName);
    }
    function salvar(){
        envia(tar,'salvar',formName);
    }
    function autoCompUser(){
        document.getElementById('autoComp').value = 'userDesc';
        var filtros = 'userDesc,autoComp';
        var servico = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'users','action'=>'autoComp')); ?>";
        var returns = Array('user','userDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popUser',returns,'2',functionCall);
        
    }
</script>
