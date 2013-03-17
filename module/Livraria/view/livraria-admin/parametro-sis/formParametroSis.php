
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
$this->FormDefault(['legend' => 'Dados do Parametro do Sistema', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao'],'hidden'),
        $this->FormDefault(['name' => 'key','js' => 'buscaKey()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td><td>\r",
        $this->FormDefault(['conteudo' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['descricao' => 'text']),
    "</td>\r",
$this->FormDefault(['submit' => 'enviar'],'fim');

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

    function buscaKey(){
        envia(tar,'buscar',formName);
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }

</script>