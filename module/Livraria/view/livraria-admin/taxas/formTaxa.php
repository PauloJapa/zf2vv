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
        envia(tar,'busca1',formName);
    }
    function buscaClasse(){
        envia(tar,'busca2',formName);
    }
    function salvar(){
        envia(tar,'',formName);
    }
</script>