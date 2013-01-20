<?php

$form->prepare();
echo $this->form()->openTag($form);

echo "<fieldset>";
echo "  <legend>Dados Basicos:</legend>";
echo $this->formHidden($form->get('id')),"\r";
echo "<table style='width : 100% ;'>";
echo "<tr valign='top'>";
echo "<td>";
echo $this->formRow($form->get('nome')),"\r";
echo $this->formRow($form->get('tel')),"\r";
echo $this->formRow($form->get('site')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('apelido')),"\r";
echo $this->formRow($form->get('email')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('cnpj')),"\r";
echo $this->formRow($form->get('status')),"\r";
echo "</td></tr>";
echo "</table>";
echo " </fieldset>";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";

require "/var/www/zf2vv/module/Livraria/view/livraria-admin/enderecos/formEnderecoInc.php";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";

echo $this->form()->closeTag();
?>