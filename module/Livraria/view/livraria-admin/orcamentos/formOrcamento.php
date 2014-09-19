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
                <a href="javascript:fecharPop('mensagen');">Fechar <i class="icon-remove-circle"></i></a>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>

<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
<?php
$user = $this->UserIdentity('LivrariaAdmin');

$form->prepare();
//var_dump($form);
echo 
$this->FormDefault(['legend' => 'Dados sobre o seguro ADM: ' . $this->administradora['nome'], 'hidden' => 'id'],'inicio',$this, $form),
    "<td>",
        $this->FormDefault(['comissaoEnt','administradora','administradoraDesc','ajaxStatus','autoComp','subOpcao','locador','imovel','imovelTel','imovelStatus','locatario','atividade','taxa','canceladoEm','codano','numeroParcela','premio','premioLiquido','fechadoId','taxaIof','user','status','multiplosMinimos','scrolX','scrolY','fechadoOrigemId','mensalSeq','orcaReno','gerado'],'hidden'),
        $this->FormDefault(['proposta' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['seguroEmNome' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['criadoEm' => 'calend']),
    "</td>", PHP_EOL;
        
//    if($user->getNome() == 'Paulo Cordeiro Watakabe'){
//        echo
//    "</tr><tr>",
//        "<td>\n",
//            $this->formRow($form->get('content')),
//        "</td><td>",
//            $this->FormDefault(['importar'], 'submitOnly'),
//        "</td><td>",
//        "</td>\n";
//    }        
echo 
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
"</table>", PHP_EOL,
        
$this->FormDefault(['legend' => 'Dados do Imovel:', 'hidden' => 'idEnde'],'fieldIni'),

    "<td colspan='3' nowrap>", PHP_EOL,
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
        '<a class="btn btn-success" href="javascript:autoCompImoveis();">Exibir Imoveis cadastrados desse locador <i class="icon-search"></i></a>',
        '<br /><span id="popImoveis" style="position:absolute"></span>',
        // Usuario Administrador pode alterar imovel
        ($user->getTipo() == 'admin') ? $this->FormDefault(['edImovel' => 'buttonOnly']) : '',
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
"</table>", PHP_EOL;
        
    // Usuario Administrador pode alterar seguradora e valor da comissão    
    if ($user->getTipo() != 'admin') {
        echo $this->FormDefault(['seguradora', 'comissao','assist24'], 'hidden');
    } else {
        echo 
        $this->FormDefault(['legend' => 'Parametros do Administrador:'],'fieldIni'),
            "<td>",
                $this->FormDefault(['comissao' => 'select']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['seguradora' => 'select']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['assist24' => 'radio']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['logOrca' => 'buttonOnly']),
            "</td>", PHP_EOL,
        $this->FormDefault([],'fieldFim');
    }

echo 
        
$this->FormDefault(['legend' => 'Coberturas'],'fieldIni'),
    "<td>",
        $this->FormDefault(['tipoCobertura' => 'selectLine']),
        $this->FormDefault(['formaPagto' => 'selectLine']),
        $this->FormDefault(['valorAluguel' => 'floatLine']),
        
        $this->FormDefault(['name' => 'incendio','icone' => 'icon-pencil','js' => "setEmpty('incendio')"],'iconeLine'),
        $this->FormDefault(['name' => 'conteudo','icone' => 'icon-pencil','js' => "setEmpty('conteudo')"],'iconeLine'),
        $this->FormDefault(['name' => 'aluguel','icone' => 'icon-pencil','js' => "setEmpty('aluguel')"],'iconeLine'),
        $this->FormDefault(['name' => 'eletrico','icone' => 'icon-pencil','js' => "setEmpty('eletrico')"],'iconeLine'),
        $this->FormDefault(['name' => 'vendaval','icone' => 'icon-pencil','js' => "setEmpty('vendaval')"],'iconeLine'),
        
        $this->FormDefault(['premioTotal' => 'moedaLine']),
        $this->FormDefault(['parcelaVlr' => 'moedaLine']),
    "</td><td style='vertical-align: middle; width:20%;'>", PHP_EOL,
        $this->FormDefault(['calcula'=>'submit']),
        "<br /><br /><br /><p class='btn btn-warning' id='aviso'>Lembre-se de Salvar <span class='icon-ok-circle'></span></p>",
        $this->FormDefault(['enviar'=>'submit']),
    "</td>", PHP_EOL,        
    "</tr><tr>",
    "<td colspan='2'>",
        $this->FormDefault(['observacao' => 'textArea']),
    "</td>", PHP_EOL,        
        
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['getpdf','fecha','novoOrca'],'submits');

$this->FormDefault([],'fim');

$log = isset($this->param['log']) ? $this->param['log'] : 'logOrcamento';
$tar = isset($this->param['tar']) ? $this->param['tar'] : '/admin/orcamentos/escolheAdm';
$prt = isset($this->param['prt']) ? $this->param['prt'] : '/admin/orcamentos/printProposta';
$bak = isset($this->param['bak']) ? $this->param['bak'] : 'listarOrcamentos';
?> 
<script language="javascript">
    function setEmpty(id){
        $('#' + id).val('Não Calcular');
    }
    var dateFormat = 'dd/mm/yyyy';
    var varVazio = ''; //Var para testar se campo cnpj ou cpf esta vazio
//    var imprime = '<?php echo $this->imprimeProp ?>';
    var imprime = '0';
    var avisa = '<?php echo $this->avisaCalc ?>';
    var param = <?php echo json_encode($this->param); ?>;
    var user = '<?php echo $user->getTipo(); ?>';
    
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
//        if(!isValido()){
//            return false;
//        }
        
        envia(tar,'salvar',formName,'');
        return false;
    }

    function checkPrintProp(){
        if(imprime == '1'){
            printProposta(true);
        }
    }

    function checkAvisoCalc(){
        if(avisa == '1'){
            $('#aviso').show();
        }else{                
            $('#aviso').hide();
        }
    }

    setTimeout("checkPrintProp()",500);

    function viewLogsOrcamento(){
        var user = document.getElementById('user').value;
        document.getElementById('user').value = '';
        var target = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'logs','action'=>$log)); ?>";
        envia(target,'',formName,'');
        document.getElementById('user').value = user;
        return false;
    }

    function calcular(){
//        if(!isValido()){
//            return false;
//        }
        envia(tar,'calcular',formName,'');
        return false;
    }
    
    function isValido(){
        if($('#tipoLoc').val() == 'fisica'){
            if(!isCpf($('#cpfLoc'))){
                alert('CPF do locador invalido!!!');
                return false;
            }
        }else{
            if(!isCnpj($('#cnpjLoc'))){
                alert('CNPJ do locador invalido!!!');
                return false;
            }            
        }
        if($('#tipo').val() == 'fisica'){
            if(!isCpf($('#cpf'))){
                alert('CPF do locatario invalido!!!');
                return false;
            }
        }else{
            if(!isCnpj($('#cnpj'))){
                alert('CNPJ do locatario invalido!!!');
                return false;
            }            
        }        
        return true;
    }

    function fechar(){
        envia(tar,'fechar',formName,'new');
        setTimeout("envia('<? echo $tar ?>','editar','"+ formName +"','')",1000);
        return false;
    }

    function newOrcamento(){
        envia('<? echo $tar ?>','editar',formName,'');
        return false;
    }

    function printProposta(verificaPopup){
        if(verificaPopup){
            hasPopupBlocker();
            setTimeout("sleepPrintProp()",tempoPopup);
            return false;
        }
        doPrintProp();
        return false;
    }

    function sleepPrintProp(){
        if(blockTest){
            doPrintProp();        
        }
    }

    function doPrintProp(){
        envia("<? echo $prt ?>",'print',formName,'new');        
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
            alert('Antes de escolher a atividade deve-se escolher a ocupação!!');
            return;
        }
        document.getElementById('autoComp').value = teste;
        var filtros = 'seguradora,ocupacao,atividadeDesc,autoComp';
        var servico = "<?php echo $this->url('livraria-admin',array('controller'=>'atividades','action'=>'autoComp')); ?>";
        var returns = Array('atividade','atividadeDesc');
        var functionCall = 'setPerdaAluguel()';
        autoComp2(filtros,servico,'popAtividade',returns,'2',functionCall);
    }
    
    function setPerdaAluguel(){
        switch($('#atividade').val()){
            case '89':
            case '312':
                setEmpty('aluguel');
                break; 
            default:
                if($('#aluguel').val() == 'Não Calcular'){
                    $('#aluguel').val('');
                }
        }
    }

    function setMesNiverOfMensal(click){
        if(($("input[name=validade]:checked").val() == 'anual') || ($("#inicio").val() == '')){
            $("#mesNiver").val('');
            if(click){
                alert('Não se preocupe vamos preencher para você!!!\n\n Porém é necessario colocar o inicio da vigência!!');
            }
            document.getElementById('popmesNiver').style.display = 'none';
            return;
        }
        document.getElementById('popmesNiver').style.display = 'block';
        var data = $('#inicio').val().split('/');
        $('#mesNiver').val(data[1]);
    }

    function showIncOrIncCon(){
        switch($('#tipoCobertura').val()){
            case '01':
                $('#popincendio').show();
                $('#popconteudo').hide();
                $('#conteudo').val('');
                break;
            case '02':
                $('#popincendio').hide();
                $('#incendio').val('');
                $('#popconteudo').show();
                break;
            default:  
                $('#popincendio').show();
                $('#popconteudo').hide();  
        }
    }

    function travaFormaPagto(){
        var vldd = document.getElementsByName('validade');
        var fmPagto = document.getElementById('formaPagto');
        if(vldd[0].checked){
            fmPagto.selectedIndex = 1 ;
            fmPagto.options[1].text = "Mensal";
        }else{
            fmPagto.options[1].text = "A vista(no ato)";            
        }
        if(fmPagto.selectedIndex > 1){
            document.getElementById('popparcelaVlr').style.display = 'block';
            var parcView = document.getElementById('parcelaVlr');
            var parc = fmPagto.selectedIndex;
            var strClean = document.getElementById('premioTotal').value.replace(/[^0-9\,]+/g,"");
            var total = Number(strClean.replace(',','.'));
            if(total > 0){
                var parcVlr = total / parc;
                formatarCampo(parcVlr,parcView,100);
            }else{
                parcView.value = '';
            }
        }else{
            document.getElementById('popparcelaVlr').style.display = 'none';
        }
    }
    
    
    function formatarCampo(valor,dest,fator){
        valor = Math.round (valor*fator)/fator ;
        if(typeof(dest) === "string"){
            destino = document.getElementById(dest);
        }else{
            destino = dest;
        }
        destino.value = valor;
        for(i = 1; i < destino.value.length; i++){ //Coloca um zero no final Ex 1.5 para 1.50
            if ((destino.value.charAt(i) == ".")&&(destino.value.charAt(i+2) == "")){
                destino.value = destino.value + "0" ;
            }
        }
        destino.value = destino.value.replace(".",",");
    }

    function cleanAtividade(){
        cleanInputAll('atividade');
        cleanInputAll('atividadeDesc');
    }

    function buscaSeguradora(){
        envia(tar,'buscar',formName,'');
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
        var returns = Array('imovel','idEnde','cep','rua','numero','bloco','apto','compl','bairro','bairroDesc','cidade','cidadeDesc','estado','pais','imovelTel','imovelStatus','refImovel');
        var functionCall = '';
        autoComp2(filtros,servico,'popImoveis',returns,'12',functionCall);
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

    function pressEnterOrTab(obj,e){
        n = obj.name;
        switch(n){
            case 'cep':
                buscarEndCep();
                break;
            case 'valorAluguel':
                calcular();
                break;
        }
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
            document.getElementById('pais').value = '1';
        }
    }

    //Funcao jquey para janela de flash mensagem rolar conforme o scrool
    $(document).ready(function(){       
       // Atribuindo a função validate para o formulário form-contato
        $('#orcamento').validate({
            // Função que determinada as regras de validação do formulário
             rules:{
                // Pegando o campo CPF para inserir regras de validação
                cpf: {
                    // o required faz com que o preenchimento do campo sejá obrigatório
                    required : true,
                    // o cpf faz com que o cpf digitado seja um cpf valido
                    cpf      : 'both'
                },
                // Pegando o campo CNPJ para inserir regras de validação
                cnpj: {
                    // o required faz com que o preenchimento do campo sejá obrigatório
                    required : true,
                    // o CNPJ faz com que o cpf digitado seja um CNPJ valido
                    cnpj     : 'both'
                },
                // Pegando o campo CPF para inserir regras de validação
                cpfLoc: {
                    // o required faz com que o preenchimento do campo sejá obrigatório
                    required : true,
                    // o cpf faz com que o cpf digitado seja um cpf valido
                    cpf      : 'both'
                },
                // Pegando o campo CNPJ para inserir regras de validação
                cnpjLoc: {
                    // o required faz com que o preenchimento do campo sejá obrigatório
                    required : true,
                    // o CNPJ faz com que o cpf digitado seja um CNPJ valido
                    cnpj     : 'both'
                },
            },
            // Atribuindo mensagens personalizadas para as validações
            messages:{
                // Seleciona as mensagens do campo CPF
                cpf: {
                    // Atribui uma mensagem padrão para o required do CPF
                    required : "O CPF é obrigatório.",
                    // Atribui uma mensagem padrão para a função CPF do campo CPF
                    cpf      : "O CPF digitado é invalido"
                },
                // Seleciona as mensagens do campo CNPJ
                cnpj: {
                    // Atribui uma mensagem padrão para o required do CNPJ
                    required : "O CNPJ é obrigatório.",
                    // Atribui uma mensagem padrão para a função CNPJ do campo CNPJ
                    cnpj     : "O CNPJ digitado é invalido"
                },
                cpfLoc: {
                    // Atribui uma mensagem padrão para o required do CPF
                    required : "O CPF é obrigatório.",
                    // Atribui uma mensagem padrão para a função CPF do campo CPF
                    cpf      : "O CPF digitado é invalido"
                },
                // Seleciona as mensagens do campo CNPJ
                cnpjLoc: {
                    // Atribui uma mensagem padrão para o required do CNPJ
                    required : "O CNPJ é obrigatório.",
                    // Atribui uma mensagem padrão para a função CNPJ do campo CNPJ
                    cnpj     : "O CNPJ digitado é invalido"
                },
            } 
        });
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

    function fecharPop(id){
        document.getElementById(id).style.display = 'none';
    }

    function setOcultar(){
        document.getElementById('poppais').style.display = 'none';
    }

    function voltar(){
        $('#id').val('');
        $('#refImovel').val('');
        $('#locador').val('');
        $('#locadorNome').val('');
        $('#locatario').val('');
        $('#locatarioNome').val('');
        $('#administradoraDesc').val('<? echo $this->administradora['nome']; ?>');
        var target = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>$bak )); ?>";
        envia(target,'',formName,'');
    }
    function importarFile(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
        envia(tar,'',formName,'');
        return false;
    } 
    function editImovel(){
        var imovel = document.getElementById('imovel');
        if(imovel.value == ''){
            alert("Não existe nenhum imovel selecionado!!");
            return;
        }
        var auxid = document.getElementById('id').value;
        document.getElementById('id').value = imovel.value;
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'imovels','action'=>'edit')); ?>";
        envia(tar,'editar',formName,'imovel');
        document.getElementById('id').value = auxid ;
    }  

    function formataDoc(){
        $('#cpf').val(cpfCnpj($('#cpf').val()));
        $('#cpfLoc').val(cpfCnpj($('#cpfLoc').val()));
    }
    
    function getComissao(){
        $('#comissao option').remove();
        seg = $('#seguradora').val();
        $.each(param, function(index, value) {
            if(index === seg){
                $.each(value, function(key, vlr){
                    $('#comissao').append(new Option(vlr, key));
                });
            }
	});
    }

    function setCobertura(obj){
        if((user === 'admin') && (obj === true)){
            return;
        }
        if(typeof obj === 'undefined'){
            obj = {'name': ''};
        }
        if((user === 'admin') && (obj.name === 'tipoCobertura')){
            return;
        }
        var ocup = document.getElementsByName('ocupacao');
        var tcob = document.getElementById('tipoCobertura');
        if(ocup[0].checked){ // Caso Comercial
            $.each(param, function(index, value) {
                if(index === 'coberturaComercial'){
                    if(value != ''){
                        tcob.selectedIndex = value ;                    
                    }
                }
            });
        }
        if(ocup[1].checked){ // Caso residencial
            $.each(param, function(index, value) {
                if(index === 'coberturaResidencial'){
                    if(value != ''){
                        tcob.selectedIndex = value ;                    
                    }
                }
            });
        }
        showIncOrIncCon();
    }
    
    function checkValidade(){
        if(user === 'admin') {
            return;
        }
        // Caso residencial
        $.each(param, function(index, value) {
            if(index === 'validade'){
                if(value != ''){
                    $('input:radio[name="validade"][value="' + value + '"]').prop('checked', true);
                }
            }
        });
        
    }
    
    function setComissao(obj){
        if(typeof obj === 'undefined'){
            obj = {'name': ''};
        }
        if((user === 'admin') && (obj.name === 'comissao')){
            return;
        }
        if($('#orcaReno').val() === 'reno'){
            return; // não mexer na comissão de renovação
        }
        seg = $('#seguradora').val();
        flag =true;
        $.each(param, function(index, value) {
            if(index === 'seguradora' && seg != value){
                flag = false; // seguradora não é a mesma que foi parametrizada                  
            }
        });
        if(!flag){
            return;
        }
        switch($("input[name=ocupacao]:checked").val()){
        case '01':                
            $.each(param, function(index, value) {
                if((index === 'comissaoComercial') && (value !== '')){
                    $('#comissao').val(value);
                }
            });
            break;
        case '02':              
            $.each(param, function(index, value) {
                if((index === 'comissaoResidencial') && (value !== '')){
                    $('#comissao').val(value);
                }
            });
            break;
        }
    }
    
    
    // Ocultar campos
    document.getElementById('popcodigoGerente').style.display = 'none';
    document.getElementById('popmesNiver').style.display = 'none';
    document.getElementById('popparcelaVlr').style.display = 'none';
    // Verificar cpf ou cnpj do locador e locatario
    // Se não tiver salvo o orçamento não exibe o botao de fechar
    // Oculta select pais.
    setTimeout('showTipo();setButtonFechaOrc();setOcultar();showIncOrIncCon();setComissao();setCobertura(true);formataDoc();travaFormaPagto();setMesNiverOfMensal();checkAvisoCalc()',500);
    window.setTimeout("scroll(document.getElementById('scrolX').value,document.getElementById('scrolY').value)", 600);
    
    var com = '<?php echo $this->comissao ; ?>';
    if(com != ''){
         window.setTimeout("$('#comissao').val(com)",1000);        
    }
</script>