<?php

/*
 * campos do formulario endereço para ser reaproveitado
 */
echo "<fieldset>";
echo "  <legend>Endereço:</legend>";
echo $this->formHidden($form->get('idEnde'));
echo "<table style='width : 100% ;'>";
echo "<tr>";
echo "<td colspan='2'>";
echo $this->formRow($form->get('rua'));
echo "</td><td>";
echo $this->formRow($form->get('numero'));
echo "</tr><tr>";
echo "<td>";
echo $this->formRow($form->get('compl'));
echo $this->formRow($form->get('cidade'));
echo "</td><td>";
echo $this->formRow($form->get('cep'));
echo $this->formRow($form->get('estado'));
echo "</td><td>";
echo $this->formRow($form->get('bairroDesc'));
echo $this->formHidden($form->get('bairro'));
echo "<br /><span id='popBairros' style='position:absolute'></span>";
echo $this->formRow($form->get('pais'));
echo "</td></tr>";
echo "</table>";
echo " </fieldset>";
?>
<input name="ajaxStatus" id="ajaxStatus" type="hidden" />
<script language="javascript">
    function autoCompBairro(){
        var filtros = 'bairroDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'bairros','action'=>'autoComp')); ?>";
        var returns = Array('bairro','bairroDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popBairros',returns,'2',functionCall);
        
    }
</script>
