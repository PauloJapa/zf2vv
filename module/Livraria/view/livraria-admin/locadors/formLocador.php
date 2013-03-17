
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
$this->FormDefault(['legend' => 'Dados do Locador', 'hidden' => 'id'],'inicio',$this, $form),
    "<td colspan='2'>\n",
        $this->FormDefault(['ajaxStatus' => 'hidden']),
        $this->FormDefault(['administradora' => 'hidden','administradoraDesc' => 'text']),
        "<br /><span id='popAdministradora' style='position:absolute'></span>",
    "</td>\n",
"</tr><tr valign='top'>\n",
    "<td>\n",
        $this->FormDefault(['subOpcao' => 'hidden']),
        $this->FormDefault(['nome','tel'], 'text'),
        $this->FormDefault(['status'], 'select'),
    "</td><td>\n",
        $this->FormDefault(['tipo'], 'select'),
        $this->FormDefault(['cnpj','cpf','email'], 'text'),
    "</td>\n",
$this->FormDefault(['submit' => 'enviar'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

$noFilter=true;

$this->FormDefault(['submit' => 'enviar','noField' => true],'fim');

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        var cnpj = document.getElementById('cnpj');
        var cpf  = document.getElementById('cpf');
        var tipo = document.getElementById('tipo');
        if((tipo.value == 'fisica')&&(cpf.value == "")){
            alert('Deve ser digitado o numero do CPF!');
            return false;
        }
        if((tipo.value == 'juridica')&&(cnpj.value == "")){
            alert('Deve ser digitado o numero do CNPJ!');
            return false;
        }
        envia(tar,'salvar',formName);
        return false;
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
    function showTipo(){
        var cnpj = document.getElementById('popcnpj');
        var cpf  = document.getElementById('popcpf');
        var tipo = document.getElementById('tipo');
        if(tipo.value == 'fisica'){
            cnpj.style.display = 'none';
            cpf.style.display = 'block';
        }
        if(tipo.value == 'juridica'){
            cnpj.style.display = 'block';
            cpf.style.display = 'none';
        }
        if(tipo.value == ''){
            cnpj.style.display = 'none';
            cpf.style.display = 'none';
        }
    }
    showTipo();

    function autoCompAdministradora(){
        var filtros = 'administradoraDesc';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'administradoras','action'=>'autoComp')); ?>";
        var returns = Array('administradora','administradoraDesc');
        var functionCall = 'buscaAdministradora()';
        autoComp2(filtros,servico,'popAdministradora',returns,'2',functionCall);
    }
    function buscaAdministradora(){
        envia(tar,'buscar',formName);
    }
</script>
