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
$this->FormDefault(['legend' => 'Dados da Taxa', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['subOpcao' => 'hidden']),
        $this->FormDefault(['nome','tel'], 'text'),
        $this->FormDefault(['status'], 'select'),
    "</td><td>\r",
        $this->FormDefault(['tipo'], 'select'),
        $this->FormDefault(['cnpj','cpf','email'], 'text'),
    "</td>\r",
$this->FormDefault(['submit' => 'enviar'],'fieldFim');

$pastas = explode(DIRECTORY_SEPARATOR, __DIR__);
$pastas[count($pastas) - 1] = "enderecos";
$pastas[] = "formEnderecoInc.php";
$enderecoFormPath = implode(DIRECTORY_SEPARATOR, $pastas);
require $enderecoFormPath;

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
        var ides = new Array('nome','tipo','status');
        if(!valida(ides))
            return false;
        
        envia(tar,'salvar',formName);
        return false;
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
</script>
