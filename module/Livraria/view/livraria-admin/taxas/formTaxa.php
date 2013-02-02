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
$this->FormDefault(['legend' => 'Dados da Taxa', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['subOpcao' => 'hidden','seguradora' => 'select','inicio' => 'calend','incendio' => 'text', 'aluguel' => 'text', 'eletrico' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['classe' => 'select','fim' => 'calend','incendioConteudo' => 'text', 'desastres' => 'text', 'status' => 'select',]),
    "</td>\r",
$this->FormDefault(['submit' => 'enviar'],'fim');

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function buscaSeguradora(){
        envia(tar,'buscar',formName);
    }
    function buscaClasse(){
        envia(tar,'buscar',formName);
    }
    function salvar(){
        envia(tar,'salvar',formName);
    }
</script>
