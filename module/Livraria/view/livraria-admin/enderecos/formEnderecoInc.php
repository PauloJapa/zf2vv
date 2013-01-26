<?php
echo 
$this->FormDefault(['legend' => 'Endereço:', 'hidden' => 'idEnde'],'fieldIni',$this, $form),
    "<td colspan='4'>\r",
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td>",
"</tr>\r",        
"<tr>\r",        
    "<td colspan='2'>\r",
        $this->FormDefault(['rua' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['numero' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['compl' => 'text']),
    "</td>\r",
"</tr>\r",        
"<tr>\r",        
    "<td>\r",
        $this->FormDefault(['bairro' => 'hidden', 'bairroDesc' => 'text']),
        "<br /><span id='popBairro' style='position:absolute'></span>",
    "</td><td>\r",
        $this->FormDefault(['cidade' => 'hidden', 'cidadeDesc' => 'text']),
        "<br /><span id='popCidade' style='position:absolute'></span>",
    "</td><td>\r",
        $this->FormDefault(['estado' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['pais' => 'select']),
    "</td>\r",
$this->FormDefault([],'fieldFim');
?>
<input name="ajaxStatus" id="ajaxStatus" type="hidden" />
<script language="javascript">
    function autoCompBairro(){
        var filtros = 'bairroDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'bairros','action'=>'autoComp')); ?>";
        var returns = Array('bairro','bairroDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popBairro',returns,'2',functionCall);
    }

    function autoCompCidade(){
        var filtros = 'cidadeDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'cidades','action'=>'autoComp')); ?>";
        var returns = Array('cidade','cidadeDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popCidade',returns,'2',functionCall);
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
