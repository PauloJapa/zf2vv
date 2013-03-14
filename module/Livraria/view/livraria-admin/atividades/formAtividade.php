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
        $this->FormDefault(['codSeguradora','subOpcao'], 'hidden'),
    "</td><td>\r",
        $this->FormDefault(['ocupacao' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');
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
</script>