
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
$this->FormDefault(['legend' => 'Dados da Atividade', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['descricao' => 'text']),
        $this->FormDefault(['codSeguradora','subOpcao','danosEletricos','equipEletro','vendavalFumaca','basica','roubo'], 'hidden'),
    "</td><td>\r",
        $this->FormDefault(['ocupacao' => 'select']),
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
    function importarFile(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
        envia(tar,'',formName);
        return false;
    }
</script>