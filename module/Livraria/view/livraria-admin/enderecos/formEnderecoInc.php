<?php
echo 
$this->FormDefault(['legend' => 'Endereço:', 'hidden' => 'idEnde'],'fieldIni',$this, $form),
    "<td colspan='4'>\n",
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td>",
"</tr>\n",        
"<tr>\n",        
    "<td colspan='2'>\n",
        $this->FormDefault(['rua' => 'text']),
    "</td><td>\n",
        $this->FormDefault(['numero' => 'text']),
    "</td><td>\n",
        $this->FormDefault(['compl' => 'text']),
    "</td>\n",
"</tr>\n",        
"<tr>\n",        
    "<td>\n",
        $this->FormDefault(['bairro' => 'hidden', 'bairroDesc' => 'text']),
        "<br /><span id='popBairro' style='position:absolute'></span>",
    "</td><td>\n",
        $this->FormDefault(['cidade' => 'hidden', 'cidadeDesc' => 'text']),
        "<br /><span id='popCidade' style='position:absolute'></span>",
    "</td><td>\n",
        $this->FormDefault(['estado' => 'select']),
    "</td><td>\n",
        $this->FormDefault(['pais' => 'select']),
    "</td>\n",
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
