<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Classe Atividade', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['atividade' => 'select','inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['classeTaxas' => 'select', 'fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');

require 'index.phtml';
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>
