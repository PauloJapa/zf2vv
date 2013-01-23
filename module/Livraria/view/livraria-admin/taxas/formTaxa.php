<?php

$form->prepare();
echo $this->form()->openTag($form);

echo "<fieldset>";
echo "  <legend>Dados da Taxa:</legend>";
echo $this->formHidden($form->get('id')),"\r";
echo "<table style='width : 100% ;'>";
echo "<tr valign='top'>";
echo "<td>";
echo $this->formRow($form->get('inicio')),"\r";
echo $this->formRow($form->get('status')),"\r";
echo $this->formRow($form->get('aluguel')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('fim')),"\r";
echo $this->formRow($form->get('incendio')),"\r";
echo $this->formRow($form->get('eletrico')),"\r";
echo "</td><td>";
echo $this->formRow($form->get('classe')),"\r";
echo $this->formRow($form->get('incendioConteudo')),"\r";
echo $this->formRow($form->get('desastres')),"\r";
echo "</td></tr>";
echo "</table>";
echo " </fieldset>";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";

echo $this->form()->closeTag();
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
</script>