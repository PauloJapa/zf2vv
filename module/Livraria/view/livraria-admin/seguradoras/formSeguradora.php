<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados Basicos da Seguradora:', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['nome', 'tel', 'site'],'text'),
    "</td><td>\r",
        $this->FormDefault(['apelido', 'email'],'text'),
    "</td><td>\r",
        $this->FormDefault(['cnpj' => 'text', 'status' => 'select']),
    "</td>\r",
$this->FormDefault(['submit' => 'submit'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

$this->FormDefault(['submit' => 'submit','noField' => true],'fim');
?>