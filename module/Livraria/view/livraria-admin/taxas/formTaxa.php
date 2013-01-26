<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Taxa', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['inicio' => 'calend', 'status' => 'select', 'aluguel' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['fim' => 'calend', 'incendio' => 'text', 'eletrico' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['classe' => 'select', 'incendioConteudo' => 'text', 'desastres' => 'text']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>