
<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
<?php if(count($flashMessages)) : ?>
<div class="control-group error">
<ul class="help-inline">
    <?php foreach ($flashMessages as $msg) : ?>
    <li><?php echo $msg; ?></li>
    <?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados do Imóvel', 'hidden' => 'id'],'inicio',$this, $form),
    "<td colspan='2'>",
        $this->FormDefault(['subOpcao','ajaxStatus','autoComp','locador','atividade','locatario'], 'hidden'),
        $this->FormDefault(['name' => 'locadorDesc','icone' => 'icon-search','js' => 'autoCompLocador()','span' => "popLocador' style='position:absolute"],'icone'),
    "</td>\n",
"</tr><tr>\n",        
    "<td colspan='2'>",
        $this->FormDefault(['name' => 'locatarioNome','icone' => 'icon-search','js' => 'autoCompLocatario()','span' => "popLocatario' style='position:absolute"],'icone'),
    "</td>",
"</tr><tr>\n",        
    "<td>",
        $this->FormDefault(['name' => 'atividadeDesc','icone' => 'icon-search','js' => 'autoCompAtividade()','span' => "popAtividade' style='position:absolute"],'icone'),
    "</td><td>\n",
        $this->FormDefault(['status'], 'select'),
    "</td>\n",
"</tr><tr>\n",        
    "<td>",
        $this->FormDefault(['refImovel'], 'text'),
    "</td><td>\n",
        $this->FormDefault(['tel'], 'text'),
    "</td>\n",
"</tr>\r",
"</table>\r",
        
$this->FormDefault(['legend'=>'Dados do ultimo Seguro.'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['fechadoId' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['fechadoAno' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['fechadoFim' => 'text']),
    "</td><td>\r",
        $this->FormDefault(['vlrAluguel' => 'text']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),
        
        
$this->FormDefault(['submit' => 'enviar'],'fieldFim'),

$this->FormDefault(['legend' => 'Endereço:', 'hidden' => 'idEnde'],'fieldIni',$this, $form),

    "<td colspan='3'>\n",
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td>",        
"</tr><tr>\n",        
    "<td>\n",
        $this->FormDefault(['ajaxStatus' => 'hidden']),
        $this->FormDefault(['rua' => 'text']),
    "</td><td>\n",
        $this->FormDefault(['numero' => 'text']),
    "</td><td>\n",
        $this->FormDefault(['bloco'], 'text'),
    "</td><td>\n",
        $this->FormDefault(['apto'], 'text'),
    "</td>\n",
"</tr><tr>\n",        
    "<td colspan='3'>\n",
        $this->FormDefault(['compl' => 'text']),
    "</td>\n",
"</tr>\n",   
"</table>\n",   
"<table style='width : 100% ;'>\n",        
"<tr valign='top'>\n",   
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
        
$this->FormDefault(['submit' => 'enviar','noField' => true],'fim');

$noFilter=true;

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        var locador = document.getElementById('locador');
      //  if(locador.value == ''){
      //      alert('O locador deve ser escolhido da lista!');
      //      return false;
      //  }
        //var ides = new Array('nome','tipo','status');
        //if(!valida(ides))
        //    return false;
        
        envia(tar,'salvar',formName);
        return false;
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
</script>

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

    function autoCompLocador(){
        document.getElementById('autoComp').value = 'locadorDesc';
        var filtros = 'locadorDesc,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'locadors','action'=>'autoComp')); ?>";
        var returns = Array('locador','locadorDesc');
        var functionCall = 'buscaLocador()';
        autoComp2(filtros,servico,'popLocador',returns,'4',functionCall);
    }
    function buscaLocador(){
        envia(tar,'buscar',formName);
    }

    function autoCompLocatario(){
        var locatario = document.getElementById('locatario');
        document.getElementById('autoComp').value = 'locatarioNome';
        var filtros = 'locatarioNome,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'locatarios','action'=>'autoComp')); ?>";
        var returns = Array('locatario','locatarioNome');
        var functionCall = 'setCpfOrCnpj()';
        autoComp2(filtros,servico,'popLocatario',returns,'2',functionCall,'');
    }

    function autoCompAtividade(){
        document.getElementById('autoComp').value = 'atividadeDesc';
        var filtros = 'atividadeDesc,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'atividades','action'=>'autoComp')); ?>";
        var returns = Array('atividade','atividadeDesc');
        var functionCall = 'buscaAtividade()';
        autoComp2(filtros,servico,'popAtividade',returns,'2',functionCall);
    }
    function buscaAtividade(){
        envia(tar,'buscar',formName);
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
