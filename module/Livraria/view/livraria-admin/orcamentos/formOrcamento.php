<style type="text/css">
.form-horizontal .control-group>label{float:left;width:450px;padding-top:5px;text-align:right;}
#mensagen {
    left:50px;
    margin:0;
    padding:10px;
    position:absolute;
    top:50%;
    width:450px;
    background-color: #ffffff;
    border: solid #000 1px;
}
</style>
<?php if(count($flashMessages)) : ?>
<div id="mensagen">
    <table width="100%">
        <tr>
            <td>
                <div class="control-group error">
                    <ul class="help-inline">
                        <?php foreach ($flashMessages as $msg) : ?>
                        <li><?php echo $msg; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </td>
            <td valign='top'>
                <a href="javascript:document.getElementById('mensagen').style.display='none';">Fechar <i class="icon-remove-circle"></i></a>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>
<?php
$user = $this->UserIdentity('LivrariaAdmin');

$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados sobre o seguro', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>",
        $this->FormDefault(['comissaoEnt','administradora','ajaxStatus','autoComp','subOpcao','locador','imovel','imovelTel','imovelStatus','locatario','atividade','taxa','canceladoEm','codano','numeroParcela','premio','premioLiquido','codFechado','taxaIof','user','status','multiplosMinimos','scrolX','scrolY'],'hidden'),
        $this->FormDefault(['proposta' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['seguroEmNome' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['criadoEm' => 'calend']),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['name' => 'locadorNome','icone' => 'icon-search','js' => 'autoCompLocador()','span' => "popLocador' style='position:absolute"],'icone'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['tipoLoc' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cpfLoc','cnpjLoc'],'text'),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['name' => 'locatarioNome','icone' => 'icon-search','js' => 'autoCompLocatario()','span' => "popLocatario' style='position:absolute"],'icone'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['tipo' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cpf','cnpj'],'text'),
    "</td>", PHP_EOL,
  "</tr>", PHP_EOL,
"</table>", PHP_EOL;
        
    // Usuario Administrador pode alterar seguradora e valor da comissão    
    if ($user->getTipo() != 'admin') {
        echo $this->FormDefault(['seguradora', 'comissao'], 'hidden');
    } else {
        echo 
        $this->FormDefault(['legend' => 'Parametros do Administrador:'],'fieldIni'),
            "<td>",
                $this->FormDefault(['comissao' => 'float']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['seguradora' => 'select']),
            "</td>", PHP_EOL,
        $this->FormDefault([],'fieldFim');
    }

echo 
$this->FormDefault(['legend' => 'Dados do Imovel:', 'hidden' => 'idEnde'],'fieldIni'),

    "<td colspan='3' nowrap>", PHP_EOL,
        '<a href="javascript:autoCompImoveis();">Exibir Imoveis desse locador <i class="icon-search"></i></a>',
        '<br /><span id="popImoveis" style="position:absolute"></span>',
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td>",        
"</tr><tr>", PHP_EOL,       
    "<td>",
        $this->FormDefault(['ajaxStatus' => 'hidden']),
        $this->FormDefault(['rua' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['numero' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['apto'], 'text'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['bloco'], 'text'),
    "</td>", PHP_EOL,
"</tr><tr>", PHP_EOL,        
    "<td colspan='3'>", PHP_EOL,
        $this->FormDefault(['compl' => 'text']),
    "</td>", PHP_EOL,
"</tr>", PHP_EOL,   
"</table>", PHP_EOL,   
"<table style='width : 100% ;'>", PHP_EOL,        
"<tr valign='top'>", PHP_EOL,   
    "<td>",
        $this->FormDefault(['bairro' => 'hidden', 'bairroDesc' => 'text']),
        "<br /><span id='popBairro' style='position:absolute'></span>",
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cidade' => 'hidden', 'cidadeDesc' => 'text']),
        "<br /><span id='popCidade' style='position:absolute'></span>",
    "</td><td>", PHP_EOL,
        $this->FormDefault(['estado' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['pais' => 'select']),
    "</td>", PHP_EOL,

$this->FormDefault([],'fieldFim'),  


"<table style='width : 100% ;'>", PHP_EOL,        
"<tr valign='top'>", PHP_EOL,  
    "<td>",
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['validade' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['mesNiver' => 'select']),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['ocupacao' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['name' => 'atividadeDesc','icone' => 'icon-search','js' => 'autoCompAtividade()','span' => "popAtividade' style='position:absolute"],'icone'),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['codigoGerente' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['refImovel' => 'text']),
    "</td>", PHP_EOL,
  "</tr>", PHP_EOL,
"</table>", PHP_EOL,
        
$this->FormDefault(['legend' => 'Coberturas'],'fieldIni'),
    "<td>",
        $this->FormDefault(['tipoCobertura' => 'selectLine']),
        $this->FormDefault(['formaPagto' => 'selectLine']),
        $this->FormDefault(['valorAluguel' => 'moedaLine']),
        
        $this->FormDefault(['incendio' => 'moedaLine']),
        $this->FormDefault(['conteudo' => 'moedaLine']),
        $this->FormDefault(['aluguel' => 'moedaLine']),
        $this->FormDefault(['eletrico' => 'moedaLine']),
        $this->FormDefault(['vendaval' => 'moedaLine']),
        
        $this->FormDefault(['premioTotal' => 'moedaLine']),
        $this->FormDefault(['observacao' => 'textArea']),
    "</td><td style='vertical-align: middle; width:20%;'>", PHP_EOL,
        $this->FormDefault(['calcula'=>'submit']),
    "</td>", PHP_EOL,        
        
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['enviar','getpdf','fecha'],'submits');

$this->FormDefault([],'fim');

?> 
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    var varVazio = ''; //Var para testar se campo cnpj ou cpf esta vazio
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        var cnpj = document.getElementById('cnpjLoc');
        var cpf  = document.getElementById('cpfLoc');
        var tipo = document.getElementById('tipoLoc');
        var vali = document.getElementsByName('validade');
        var niver = document.getElementById('mesNiver');
        if((tipo.value == 'fisica')&&(cpf.value == "")){
            alert('Deve ser digitado o numero do CPF do locador!');
            return false;
        }
        if((tipo.value == 'juridica')&&(cnpj.value == "")){
            alert('Deve ser digitado o numero do CNPJ do locador!');
            return false;
        }
        if('Apartamento' == document.getElementById('atividadeDesc').value){
            if('' == document.getElementById('apto').value){
                alert('Deve ser digitado o numero do Apartamento!');
                return false;
            }
        }
        var cnpj = document.getElementById('cnpj');
        var cpf  = document.getElementById('cpf');
        var tipo = document.getElementById('tipo');
        if((tipo.value == 'fisica')&&(cpf.value == "")){
            alert('Deve ser digitado o numero do CPF do locatario!');
            return false;
        }
        if((tipo.value == 'juridica')&&(cnpj.value == "")){
            alert('Deve ser digitado o numero do CNPJ do locatario!');
            return false;
        }
        //Se for mensal obrigatorio mes de aniversario
        for(i=0; i<vali.length; i++){
            if((vali[i].checked)&&(vali[i].value == 'mensal')){
                if(niver.value == ''){
                    alert('Deve ser escolhido o mês de aniversário!');
                    niver.focus();
                    return false;
                }
            }
        }
        var ides = new Array('tipoLoc','tipo');
        if(!valida(ides)){
            return false;
        }
        
        envia(tar,'salvar',formName);
        return false;
    }

    function calcular(){
        envia(tar,'calcular',formName);
        return false;
    }

    function fechar(){
        envia(tar,'fechar',formName,'new');
        setTimeout("envia('/admin/fechados','','"+ formName +"','')",1000);
        return false;
    }

    function printProposta(){
        envia("/admin/orcamentos/printProposta",'printProposta',formName,'new');
        return false;
    }

    function cleanCoberturas(){
        cleanInputAll('incendio');
        cleanInputAll('conteudo');
        cleanInputAll('aluguel');
        cleanInputAll('eletrico');
        cleanInputAll('vendaval');
    }

    function autoCompAtividade(){
        var ocup = document.getElementsByName('ocupacao');
        var teste = false;
        for(i=0; i<ocup.length; i++){
            if(ocup[i].checked){
                teste = ocup[i].value;
                break;
            }
        }
        if(!teste){
            alert('Antes de escolher a atividade deve-se a ocupação!!');
            return;
        }
        document.getElementById('autoComp').value = teste;
        var filtros = 'ocupacao,atividadeDesc,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'atividades','action'=>'autoComp')); ?>";
        var returns = Array('atividade','atividadeDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popAtividade',returns,'2',functionCall);
    }

    function travaResidencial(){
        var ocup = document.getElementsByName('ocupacao');
        if(ocup[1].checked){
            var tcob = document.getElementById('tipoCobertura');
            tcob.selectedIndex = 2 ;
        }
    }

    function cleanAtividade(){
        cleanInputAll('atividade');
        cleanInputAll('atividadeDesc');
    }

    function buscaSeguradora(){
        envia(tar,'buscar',formName);
    }

    function autoCompLocador(){
        var locador = document.getElementById('locador');
        if(locador.value !== ''){
            locador.value = '';
            document.getElementById('tipoLoc').value = '';
            document.getElementById('cpfLoc').value = '';
            document.getElementById('cnpjLoc').value = '';
        }
        document.getElementById('autoComp').value = '';
        var filtros = 'locadorNome,administradora';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'locadors','action'=>'autoComp')); ?>";
        var returns = Array('locador','locadorNome','tipoLoc','cpfLoc');
        var functionCall = 'setCpfOrCnpjLoc()';
        autoComp2(filtros,servico,'popLocador',returns,'4',functionCall,'tipo2');
    }

    function setCpfOrCnpjLoc(){
        var tipo = document.getElementById('tipoLoc').value ;
        var cpf  = document.getElementById('cpfLoc')  ;
        var cnpj = document.getElementById('cnpjLoc') ;
        if(tipo == 'fisica'){
            cnpj.value = '';
        }
        if(tipo == 'juridica'){
            cnpj.value = cpf.value;
            cpf.value = '';
        }
        showTipoLoc();
    }

    function autoCompLocatario(){
        var locatario = document.getElementById('locatario');
        if(locatario.value !== ''){
            locatario.value = '';
            document.getElementById('tipo').value = '';
            document.getElementById('cpf').value = '';
            document.getElementById('cnpj').value = '';
        }
        document.getElementById('autoComp').value = 'locatarioNome';
        var filtros = 'locatarioNome,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'locatarios','action'=>'autoComp')); ?>";
        var returns = Array('locatario','locatarioNome','tipo','cpf');
        var functionCall = 'setCpfOrCnpj()';
        autoComp2(filtros,servico,'popLocatario',returns,'4',functionCall,'tipo2');
    }

    function setCpfOrCnpj(){
        var tipo = document.getElementById('tipo').value ;
        var cpf  = document.getElementById('cpf')  ;
        var cnpj = document.getElementById('cnpj') ;
        if(tipo == 'fisica'){
            cnpj.value = '';
        }
        if(tipo == 'juridica'){
            cnpj.value = cpf.value;
            cpf.value = '';
        }
        showTipo();
    }

    function autoCompImoveis(){
        var tst = document.getElementById('locador').value;
        if(tst == ""){
            alert('O locador deve ser selecionado da lista');
            return;
        }
        document.getElementById('autoComp').value = 'locador';
        var filtros = 'locador,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'imovels','action'=>'autoComp')); ?>";
        var returns = Array('imovel','idEnde','cep','rua','numero','bloco','apto','compl','bairro','bairroDesc','cidade','cidadeDesc','estado','pais','imovelTel','imovelStatus');
        var functionCall = '';
        autoComp2(filtros,servico,'popImoveis',returns,'7',functionCall);
    }

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
        showTipoLoc();
    }

    function showTipoLoc(){
        var cnpj = document.getElementById('popcnpjLoc');
        var cpf  = document.getElementById('popcpfLoc');
        var tipo = document.getElementById('tipoLoc');
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
 
    function setButtonFechaOrc(){
        if(tar.indexOf('edit') === -1){
            document.getElementById('fecha').style.display = 'none';
            document.getElementById('getpdf').style.display = 'none';
        }
    }

    function limpaImovel(){
        var imovel = document.getElementById('imovel');
        if(imovel.value !== ''){
            imovel.value = '';
            document.getElementById('cep').value = '';
            document.getElementById('rua').value = '';
            document.getElementById('numero').value = '';
            document.getElementById('bloco').value = '';
            document.getElementById('apto').value = '';
            document.getElementById('compl').value = '';
            document.getElementById('bairro').value = '';
            document.getElementById('bairroDesc').value = '';
            document.getElementById('cidade').value = '';
            document.getElementById('cidadeDesc').value = '';
            document.getElementById('estado').value = '';
            document.getElementById('pais').value = '';
        }
    }

    //Funcao jquey para janela de flash mensagem rolar conforme o scrool
    $(document).ready(function(){
        try{
            var y_fixo = $("#mensagen").offset().top;
        }catch(e){
            return ;
        }
        $(window).scroll(function () {
            $("#mensagen").stop().animate({
                top: y_fixo+$(document).scrollTop()+"px"
                },{duration:500,queue:false}
            );
        });
    });

    function setOcultar(){
        document.getElementById('poppais').style.display = 'none';
    }

    // Verificar cpf ou cnpj do locador e locatario
    // Se não tiver salvo o orçamento não exibe o botao de fechar
    // Oculta select pais.
    setTimeout('showTipo();setButtonFechaOrc();setOcultar()',500);
    window.setTimeout("scroll(document.getElementById('scrolX').value,document.getElementById('scrolY').value)", 500);
</script>