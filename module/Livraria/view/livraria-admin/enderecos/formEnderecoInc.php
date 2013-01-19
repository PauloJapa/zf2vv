<?php

/*
 * campos do formulario endereço para ser reaproveitado
 */
echo "<fieldset>";
echo "  <legend>Endereço:</legend>";
echo $this->formHidden($form->get('idEnde'));
echo "<table style='width : 100% ;'>";
echo "<tr>";
echo "<td colspan='4'>";
echo $this->formRow($form->get('cep'));
echo "<a href='javascript:buscarEndCep();'>Buscar</a>";
echo "<span id='checar'></span></font>";
echo "</td>";
echo "</tr>";
echo "<td colspan='2'>";
echo $this->formRow($form->get('rua'));
echo "</td><td>";
echo $this->formRow($form->get('numero'));
echo "</td><td>";
echo $this->formRow($form->get('compl'));
echo "</td></tr>";
echo "<tr>";
echo "<td>";
echo $this->formRow($form->get('bairroDesc'));
echo $this->formHidden($form->get('bairro'));
echo "<br /><span id='popBairros' style='position:absolute'></span>";
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('cidadeDesc'));
echo $this->formHidden($form->get('cidade'));
echo "<br /><span id='popCidades' style='position:absolute'></span>";
echo "</td>";
echo "<td>";
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('estado'));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo $this->formRow($form->get('pais'));
echo "</td>";
echo "<td>";

echo "</td>";
echo "<td>";

echo "</td>";
echo "<td>";

echo "</td>";
echo "</tr>";
echo "</table>";
echo "</fieldset>";
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

    function autoCompCidade(){
        var filtros = 'cidadeDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'cidades','action'=>'autoComp')); ?>";
        var returns = Array('cidade','cidadeDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popCidades',returns,'2',functionCall);
        
    }

    function submitenter(obj,e){
        var keycode;
        if (window.event) 
            keycode = window.event.keyCode;
        else if (e) 
            keycode = e.which;
        else 
            return true;
        if (keycode == 13){
            buscarEndCep();
            return false;
        }
        return true;
    }
    function buscarEndCep(){
        cleanInputAll('bairro');
        cleanInputAll('cidade');
        buscar_cep();
    }
</script>
