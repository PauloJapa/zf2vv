<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Comissao', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['administradora' => 'select', 'inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['comissao' => 'text', 'fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>