<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Atividade', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['descricao' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['codSeguradora' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['ocupacao' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fim');
?>