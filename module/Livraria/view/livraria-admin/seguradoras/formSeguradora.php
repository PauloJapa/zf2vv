<?php
$form->prepare();
echo 
$this->FormDefault($this, $form,['legend' => 'Dados Basicos da Seguradora:', 'hidden' => 'id'],'inicio'),
    "<td>\r",
        $this->FormDefault($this, $form,['nome', 'tel', 'site'],'text'),
    "</td><td>\r",
        $this->FormDefault($this, $form,['apelido', 'email'],'text'),
    "</td><td>\r",
        $this->FormDefault($this, $form,['cnpj' => 'text', 'status' => 'select']),
    "</td>\r",
$this->FormDefault($this, $form,['submit' => 'submit'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

$this->FormDefault($this, $form,['submit' => 'submit','noField' => true],'fim');
?>