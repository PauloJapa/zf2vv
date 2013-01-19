<?php

$form->prepare();
echo $this->form()->openTag($form);
echo "<fieldset>";
echo "  <legend>Usuario:</legend>";
echo $this->formHidden($form->get('id'));
echo "<table style='width : 100% ;'>";
echo "<tr>";
echo "<td>";
echo $this->formRow($form->get('nome'));
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('email'));
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('tipo'));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo $this->formRow($form->get('password'));
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('administradoraDesc'));
echo $this->formHidden($form->get('administradora'));
echo "<br /><span id='popAdminis' style='position:absolute'></span>";
echo "</td>";
echo "<td>";
echo $this->formRow($form->get('isAdmin'));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo '<label><span>Repetir Senha</span><input name="password2" id="password2" type="password" value=""></label>';
echo "</td>";
echo "<td>";
echo "</td>";
echo "<td>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</fieldset>";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";

require "/var/www/zf2vv/module/Livraria/view/livraria-admin/enderecos/formEnderecoInc.php";

echo "<div align='center'>";
echo $this->formSubmit($form->get('submit'));
echo "</div>";
echo $this->form()->closeTag();
?>
<script language="javascript">
    function autoCompAdminis(){
        var filtros = 'administradoraDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'Administradoras','action'=>'autoComp')); ?>";
        var returns = Array('administradora','administradoraDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popAdminis',returns,'2',functionCall);
        
    }
    function submitvalida(obj){
        if (!valida(Array('nome','email','administradora'))){
            return false;
        }
        if(document.getElementById('password').value != document.getElementById('password2').value){
            alert("Senha digita não é igual a anterior!");
            return false;
        }
        return true;        
    }
    function valida(ids){
        for(i=0; i<ids.length; i++){
            var obj = document.getElementById(ids[i]);
            if(obj.value == ""){
                alert("O campo " + ids[i] + " não pode ficar vazio!!");
                obj.focus();
                return false;
            }
        }
        return true;
    }
</script>