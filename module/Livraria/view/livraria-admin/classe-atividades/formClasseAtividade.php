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
$this->FormDefault(['legend' => 'Dados da Classe Atividade', 'hidden' => 'id'],'inicio',$this, $form),
    "<td colpan='3'>\r",
        $this->FormDefault(['seguradora' => 'select']),
    "</td>\r",
"</tr><tr valign='top'>\r",
    "<td>\r",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao','atividade'],'hidden'),
        $this->FormDefault(['name' => 'atividadeDesc','icone' => 'icon-search','js' => 'autoCompAtividade()','span' => "popAtividade' style='position:absolute"],'icone'),
        $this->FormDefault(['inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['classeTaxas' => 'select', 'fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
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

    function autoCompAtividade(){
        //document.getElementById('autoComp').value = 'atividadeDesc';
        var filtros = 'atividadeDesc,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'atividades','action'=>'autoComp')); ?>";
        var returns = Array('atividade','atividadeDesc');
        var functionCall = 'buscaAtividade()';
        autoComp2(filtros,servico,'popAtividade',returns,'2',functionCall);
    }
    function buscaAtividade(){
        envia(tar,'buscar',formName);
    }
    function buscaSeguradora(){
        envia(tar,'buscar',formName);
    }

</script>