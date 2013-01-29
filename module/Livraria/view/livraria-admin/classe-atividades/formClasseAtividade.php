<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Classe Atividade', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['inicio' => 'calend', 'classeTaxas' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['fim' => 'calend', 'atividade' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>