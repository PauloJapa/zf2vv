<?php

$form->prepare();
echo $this->form()->openTag($form);

echo "<fieldset>";
echo "  <legend>Dados da Classe:</legend>";
echo $this->formHidden($form->get('id')),"\r";
echo "<table style='width : 100% ;'>";
echo "<tr valign='top'>";
echo "<td>";
echo $this->formRow($form->get('cod')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('descricao')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('seguradora')),"\r";
echo "</td></tr>";
echo "</table>";
echo " </fieldset>";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";

echo $this->form()->closeTag();
?>
