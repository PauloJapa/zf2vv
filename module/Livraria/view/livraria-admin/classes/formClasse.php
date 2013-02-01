<?php

$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Classe', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['subOpcao' => 'hidden', 'seguradora' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['cod' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['descricao' => 'text']),
    "</td>\r",
$this->FormDefault(['submit' => 'enviar'],'fim');


require 'index.phtml';

?>
<script language="javascript">
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function buscaSeguradora(){
        envia(tar,'busca',formName);
    }
    function salvar(){
        envia(tar,'',formName);
    }
</script>
