<?php
$form->prepare();
echo 
$this->FormDefault($this, $form,['legend' => 'Dados da Taxa', 'hidden' => 'id'],'inicio'),
    "<td>\r",
        $this->FormDefault($this, $form,['inicio' => 'calend', 'status' => 'select', 'aluguel' => 'text']),
    "</td><td>\r",
        $this->FormDefault($this, $form,['fim' => 'calend', 'incendio' => 'text', 'eletrico' => 'text']),
    "</td><td>\r",
        $this->FormDefault($this, $form,['classe' => 'select', 'incendioConteudo' => 'text', 'desastres' => 'text']),
    "</td>\r",
$this->FormDefault($this, $form,['submit' => 'submit'],'fim');
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>