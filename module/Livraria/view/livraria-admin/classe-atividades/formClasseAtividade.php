
<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
<?php if (count($flashMessages)) : ?>
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
    "<td>\r",
        $this->FormDefault(['ajaxStatus','autoComp','subOpcao','atividade'],'hidden'),
        $this->FormDefault(['codOld','codciaOld','seq'],'hidden'),
        $this->FormDefault(['name' => 'atividadeDesc','icone' => 'icon-search','js' => 'autoCompAtividade()','span' => "popAtividade' style='position:absolute"],'icone'),
        $this->FormDefault(['inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['classeTaxas' => 'select', 'fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
    "</td>\r";
        
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
$this->FormDefault(['submit' => 'enviar'],'fim');

$noFilter=true;

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
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
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
    function importarFile(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
        envia(tar,'',formName);
        return false;
    }

</script>