/* 
 * Script para calculo
 */

    var dateFormat = 'dd/mm/yyyy';
    var imprime = '0';
    var varVazio = ''; //Var para testar se campo cnpj ou cpf esta vazio
    
    function setEmpty(id){
        $('#' + id).val('Não Calcular');
    }
    
    function salvar(){
        
        if(!validaLocadorLocatarioImovel(false, 'salvar()')){
            return false;
        }
        
        var vali = document.getElementsByName('validade');
        var niver = document.getElementById('mesNiver');
        if('Apartamento' == document.getElementById('atividadeDesc').value){
            if('' == document.getElementById('apto').value){
                alert('Deve ser digitado o numero do Apartamento!');
                return false;
            }
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
        if(!userDateIsValid()){
            return false;
        }
        
        envia(tar,'salvar',formName,'');
        return false;
    }
    
    function validaLocadorLocatarioImovel(silent, fc){    
        if(!$('#locadorNome').prop('readonly')){
            if($('#locador').val() == '' && validaLocador(false)){
                saveLocador(true,fc);
                return;
            }
            return jAlertFalse('Por favor incluir um locador valido para este seguro!',null,silent);
        }
        if(!$('#locatarioNome').prop('readonly')){
            if($('#locatario').val() == '' && validaLocatario(false)){
                saveLocatario(true,fc);
                return;
            }
            return jAlertFalse('Por favor incluir um locatario valido para este seguro!',null,silent);
        }
        if(!$('#rua').prop('readonly')){
            if($('#imovel').val() == '' && $('#rua').val() != '' && validaImovel(false)){
                $('#imovelStatus').val('A');
                saveImovel(true,fc);
                return;
            }
            return jAlertFalse('Por favor incluir um imovel valido para este seguro!',null,silent);
        }
        return true;
    }
    
    function userDateIsValid(){
        if(user == 'admin'){
            return true;
        }
        var inicio = $('#inicio').val();
        var ini = inicio.split('/');
        var inicio = ini[2] + ini[1] +ini[0] ;
        if(inicio < hoje){
            alert('Não é permitido salvar ou fechar seguro com data retroativa caso precise entre em contato com Vila Velha.');
            return false;
        }
        return true;        
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
    
    
//=> Funçoes para locatario      <=======================================================================================// 

    function autoCompLocatario(){       
        if(statusLocatario == 'ini'){            
            $('#nwLocatario').show();
            statusLocatario = '';
        }            
        if ($('#locatarioNome').prop('disabled') && op != 'lupa'){
            return;
        }
        document.getElementById('autoComp').value = 'locatarioNome';
        var filtros = 'locatarioNome,autoComp';
        var servico = VARS_AMBIENTE['autoCompLocatario_servico'];
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
        checkShowLocatario(true);
    }
    
    function checkShowLocatario(show){
        if(show == 'ini' && $('#locatario').val() == ''){
            statusLocatario = show;
            $('#nwLocatario').hide();
            $('#svLocatario').hide(); 
            $('#ccLocatario').hide();
            $('#edLocatario').hide();
            return;
        }
        if($('#locatario').val() != '' || show){
            setLocatarioReadyOnly(); 
        }else{
            setLocatarioReadyOnly(false);
        }
    }
    
    function setLocatarioReadyOnly(op){
        if(op == null)op = true;
        var inputs = Array('locatarioNome', 'cpf', 'cnpj');
        for (i=0; i < inputs.length ; i++){
            $('#' + inputs[i]).prop('readonly', op);
        }
        $('#tipo').prop('disabled', op);  
        if(op){   
            $('#edLocatario').show();
            $('#nwLocatario').show();
            $('#svLocatario').hide(); 
            $('#ccLocatario').hide();  
        }else{        
            $('#edLocatario').hide();
            $('#nwLocatario').hide();
            $('#svLocatario').show(); 
            $('#ccLocatario').show(); 
        }
    }
    
    var statusLocatario = '';
    var oldLocatario = new Array();
    function editLocatario(){
        saveDataOfLocatario();
        statusLocatario = 'edit';
        setLocatarioReadyOnly(false);
    }
    
    function newLocatario(){
        saveDataOfLocatario();
        statusLocatario = 'new';
        if($('#locatario').val() == '' && validaLocatario(false)){
            saveLocatario();
            return;
        }
        setLocatarioReadyOnly(false);
        var inputs = Array('locatario', 'tipo', 'cpf', 'cnpj');
        for (i=0; i < inputs.length ; i++){
            cleanInput(inputs[i], true);
        }
        showTipo();
    }
    
    function cancelLocatario(){
        if(statusLocatario == 'edit' || statusLocatario == 'new'){
            $('#locatario').val(oldLocatario['locatario']);
            $('#locatarioNome').val(oldLocatario['locatarioNome']);
            $('#tipo').val(oldLocatario['tipo']);
            $('#cpf').val(oldLocatario['cpf']);
            $('#cnpj').val(oldLocatario['cnpj']);
        }
        statusLocatario = '';
        setLocatarioReadyOnly();
        showTipo();
    }
    
    function saveLocatario(silent,fc){
        if(silent == true){
            checkResulSilentOK       = true ;
            alt = false;
        }else{
            alt = true;            
        }
        if(!validaLocatario(alt)){
            return;
        }
        var url = VARS_AMBIENTE['saveLocatario_url'];
        var functionCallBack = 'chekResul';
        var campos = Array('administradora','locatario', 'locatarioNome', 'tipo', 'cpf', 'cnpj');
        var param = getParams(campos);
        checkResulCallBackOK = 'setLocatarioReadyOnly();';
        if(fc != null){
            checkResulCallBackOK += fc ;
        }
        executaAjax(url, functionCallBack, param);        
    }
    
    function validaLocatario(alt){
        if(alt == null){
            alt = true;
        }
        if($('#locatarioNome').val() == ''){
            return jAlertFalse('Por favor digite o Nome !!');
        }
        if($('#tipo').val() == ''){
            return jAlertFalse('Por favor escolha o tipo de documento !!');
            
        }  
        if($('#cpf').val() == '' && $('#cnpj').val() == ''){
            return jAlertFalse('Por favor digite o numero do documento!!');  
        }      
        if(alt == false){
            return true;
        }
        if($('#locatario').val() == ''){
            var msg = 'Tem certeza que deseja incluir este Locatario ? \n\n Tenha certeza que não exista na base para evitar duplicidade!!';
        }else{
            var msg = 'Tem certeza que deseja alterar este Locatario ? \n\n Tenha em mente que seguros fechados anteriormente com este Locatario também serão alterados !!';            
        }
        if(!confirm(msg)){
            cancelLocatario();
            return false;
        }
        return true;
        // Pendente acerto
        return jConfirmS(msg);
    }
    
    function saveDataOfLocatario(){
        oldLocatario['locatario'] = $('#locatario').val();
        oldLocatario['locatarioNome'] = $('#locatarioNome').val();
        oldLocatario['tipo'] = $('#tipo').val();
        oldLocatario['cpf'] = $('#cpf').val();
        oldLocatario['cnpj'] = $('#cnpj').val();        
    }

    function showTipo(){
        switch($('#tipo').val()){
            case 'fisica':
                $('#popcpf').show();
                $('#popcnpj').hide();
                $('#cnpj').val('');
                break;
            case 'juridica':
                $('#popcpf').hide();
                $('#cpf').val('');
                $('#popcnpj').show();
                break;
            default:    
                $('#popcpf').hide();
                $('#popcnpj').hide();
        }
        showTipoLoc();
    }

//=> Funçoes para locador    <=======================================================================================//   

    function autoCompLocador(op){        
        if(statusLocador == 'ini'){            
            $('#nwLocador').show();
            statusLocador = '';
        }            
        if ($('#locadorNome').prop('disabled') && op != 'lupa'){
            return;
        }
        document.getElementById('autoComp').value = '';
        var filtros = 'locadorNome,administradora';
        var servico = VARS_AMBIENTE['autoCompLocador_servico'];
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
        checkShowLocador(true);
    }
    
    function checkShowLocador(show){
        if(show == 'ini' && $('#locador').val() == ''){
            statusLocador = show;
            $('#nwLocador').hide();
            $('#svLocador').hide(); 
            $('#ccLocador').hide();
            $('#edLocador').hide();
            return;
        }
        if($('#locador').val() != '' || show){
            setLocadorReadyOnly(); 
        }else{
            setLocadorReadyOnly(false);
        }
    }
    
    function setLocadorReadyOnly(op){
        if(op == null)op = true;
        var inputs = Array('locadorNome', 'cpfLoc', 'cnpjLoc');
        for (i=0; i < inputs.length ; i++){
            $('#' + inputs[i]).prop('readonly', op);
        }
        $('#tipoLoc').prop('disabled', op);  
        if(op){   
            $('#edLocador').show();
            $('#nwLocador').show();
            $('#svLocador').hide(); 
            $('#ccLocador').hide();  
        }else{        
            $('#edLocador').hide();
            $('#nwLocador').hide();
            $('#svLocador').show(); 
            $('#ccLocador').show(); 
        }
    }
    
    var statusLocador = '';
    var oldLocador = new Array();
    function editLocador(){
        saveDataOfLocador();
        statusLocador = 'edit';
        setLocadorReadyOnly(false);
    }
    
    function newLocador(){
        saveDataOfLocador();
        statusLocador = 'new';
        if($('#locador').val() == '' && validaLocador(false)){
            saveLocador();
            return;
        }
        setLocadorReadyOnly(false);
        var inputs = Array('locador', 'tipoLoc', 'cpfLoc', 'cnpjLoc');
        for (i=0; i < inputs.length ; i++){
            cleanInput(inputs[i], true);
        }
        showTipoLoc();
    }
    
    function cancelLocador(){
        if(statusLocador == 'edit' || statusLocador == 'new'){
            $('#locador').val(oldLocador['locador']);
            $('#locadorNome').val(oldLocador['locadorNome']);
            $('#tipoLoc').val(oldLocador['tipoLoc']);
            $('#cpfLoc').val(oldLocador['cpfLoc']);
            $('#cnpjLoc').val(oldLocador['cnpjLoc']);
        }
        statusLocador = '';
        setLocadorReadyOnly();
        showTipoLoc();
    }
    
    function saveLocador(silent,fc){
        if(silent == true){
            checkResulSilentOK       = true ;
            alt = false;
        }else{
            alt = true;            
        }
        
        if(!validaLocador(alt)){
            return ;
        }
        var url = VARS_AMBIENTE['saveLocador_url'];
        var functionCallBack = 'chekResul';
        var campos = Array('administradora','locador', 'locadorNome', 'tipoLoc', 'cpfLoc', 'cnpjLoc');
        var param = getParams(campos);
        checkResulCallBackOK = 'setLocadorReadyOnly();';
        if(fc != null){
            checkResulCallBackOK += fc ;
        }
        executaAjax(url, functionCallBack, param);        
    }
    
    function validaLocador(alt){
        if(alt == null){
            alt = true;
        }
        if($('#locadorNome').val() == ''){
            return jAlertFalse('Por favor digite o Nome !!');
        }
        if($('#tipoLoc').val() == ''){
            return jAlertFalse('Por favor escolha o tipo de documento !!');
            
        }
        if($('#cpfLoc').val() == '' && $('#cnpjLoc').val() == ''){
            return jAlertFalse('Por favor digite o numero do documento!!');  
        }
        if(alt == false){
            return true;
        }
        if($('#locador').val() == ''){
            var msg = 'Tem certeza que deseja incluir este Locador ? \n\n Tenha certeza que não exista na base para evitar duplicidade!!';
        }else{
            var msg = 'Tem certeza que deseja alterar este Locador ? \n\n Tenha em mente que seguros fechados anteriormente com este Locador também serão alterados !!';            
        }
        if(!confirm(msg)){
            cancelLocador();
            return false;
        }
        return true;
        // Pendente acerto
        return jConfirmS(msg);
    }
    
    function saveDataOfLocador(){
        oldLocador['locador'] = $('#locador').val();
        oldLocador['locadorNome'] = $('#locadorNome').val();
        oldLocador['tipoLoc'] = $('#tipoLoc').val();
        oldLocador['cpfLoc'] = $('#cpfLoc').val();
        oldLocador['cnpjLoc'] = $('#cnpjLoc').val();        
    }

    function showTipoLoc(){
        switch($('#tipoLoc').val()){
            case 'fisica':
                $('#popcpfLoc').show();
                $('#popcnpjLoc').hide();
                $('#cnpjLoc').val('');
                break;
            case 'juridica':
                $('#popcpfLoc').hide();
                $('#cpfLoc').val('');
                $('#popcnpjLoc').show();
                break;
            default:    
                $('#popcpfLoc').hide();
                $('#popcnpjLoc').hide();
        }
    }
    
    
//=> Funçoes para Imovel        <=======================================================================================// 

    function checkShowImoveis(show){
        if($('#rua').val() != '' || show){
            $('#showImovel').show();
            setImoveisReadyOnly();
        }else{
            // Natalia da vila velha não quer esconder o imovel no inicio do calculo
            $('#showImovel').show();  
            setImoveisReadyOnly(false); 
            $('#nwImovel').show(); 
            $('#svImovel').hide();            
            $('#ccImovel').hide(); 
        }
    }
    
    var statusImovel = '';
    var oldImovel = new Array();
    function editImovel(){
        saveDataOfImovel();
        statusImovel = 'edit';
        setImoveisReadyOnly(false);        
    } 
    
    function newImoveis(){
        saveDataOfImovel();
        statusImovel = 'new';
        if($('#imovel').val() == '' && $('#rua').val() != '' && validaImovel(false)){
            $('#imovelStatus').val('A');
            saveImovel();
            return;
        }
        checkShowImoveis(true);
        setImoveisReadyOnly(false);  
        limpaImovel(); 
        $('#imovelStatus').val('A');
    }
    
    function cancelImovel(){
        if(statusImovel == 'edit' || statusImovel == 'new'){
            var campos = getCamposImovel();        
            for (i=0; i < campos.length ; i++){
                $('#' + campos[i]).val(oldImovel[campos[i]]);
            }
        }
        statusImovel = '';
        setImoveisReadyOnly();
    }
    
    function saveImovel(silent,fc){
        if(silent == true){
            checkResulSilentOK       = true ;
            alt = false;
        }else{
            alt = true;            
        }
        if ( validaImovel(alt) == false){
            return false ;
        }
        var url = VARS_AMBIENTE['saveimovel_url'];
        var functionCallBack = 'chekResul';
        var campos = getCamposImovel();
        var param = getParams(campos);
        checkResulCallBackOK = 'setImoveisReadyOnly();';
        if(fc != null){
            checkResulCallBackOK += fc ;
        }
        executaAjax(url, functionCallBack, param);
    }
    
    function getCamposImovel(){
        var campos = Array('locador', 'imovel','idEnde','cep','rua','numero','bloco','apto','compl','bairro','bairroDesc','cidade','cidadeDesc','estado','pais','imovelTel','imovelStatus','refImovel');
        return campos ;
    }
    
    function validaImovel(alt){
        if(alt == null){
            alt = true;
        }
        if($('#locador').val() == ''){
            return jAlertFalse('É necessario escolher um Locador ou Incluir se for o caso!!');
        }      
        if($('#cep').val() == ''){
            return jAlertFalse('Prencher o CEP por favor!!');
        }     
        if($('#rua').val() == ''){
            return jAlertFalse('Prencher a rua por favor!!');
        }   
        if($('#numero').val() == ''){
            return jAlertFalse('Prencher o numero por favor!!');
        } 
        if($('#bairroDesc').val() == ''){
            return jAlertFalse('Prencher o bairro por favor!!');
        }
        if($('#cidadeDesc').val() == ''){
            return jAlertFalse('Prencher a cidade por favor!!');
        }
        if($('#estado').val() == ''){
            return jAlertFalse('Prencher o estado por favor!!');
        }      
        if($('#imovel').val() == ''){
            var msg = 'Tem certeza que deseja incluir este imovel ? \n\n Tenha certeza que não exista na base para evitar duplicidade!!';
        }else{
            var msg = 'Tem certeza que deseja alterar este imovel ? \n\n Tenha em mente que seguros fechados anteriormente com este imovel também serão alterados !!';            
        }
        if(alt == false){
            return true;
        }
        if(!confirm(msg)){
            cancelImovel();
            return false;
        }
        
        return true;
    }
    
    function saveDataOfImovel(){
        var campos = getCamposImovel();        
        for (i=0; i < campos.length ; i++){
            oldImovel[campos[i]] = $('#' + campos[i]).val();
        }
    }
        
    function limpaImovel(){
        var inputs = Array('idEnde', 'imovel', 'refImovel', 'cep', 'rua', 'numero', 'apto', 'bloco', 'compl', 'bairro','bairroDesc', 'cidade','cidadeDesc');
        for (i=0; i < inputs.length ; i++){
            cleanInput(inputs[i], true);
        }
        $('#estado').val('27');
        $('#pais').val('1');
    }
    
    function setImoveisReadyOnly(op){
        if(op == null)op = true;
        var inputs = Array('imovel','cep', 'rua', 'numero', 'apto', 'bloco', 'compl', 'bairroDesc', 'cidadeDesc', 'refImovel');
        for (i=0; i < inputs.length ; i++){
            $('#' + inputs[i]).prop('readonly', op);
        }
        $('#estado').prop('disabled', op);  
        if(op){
            $('#nwImovel').show();
            if($('#imovel').val() == ''){
                $('#edImovel').hide();
            }else{
                $('#edImovel').show();
            }
            $('#svImovel').hide();            
            $('#ccImovel').hide();
        }else{
            $('#nwImovel').hide();
            $('#edImovel').hide();
            $('#svImovel').show();
            $('#ccImovel').show();
        }
    }
    
    function buscarEndCep(){
        cleanInputAll('bairro');
        cleanInputAll('cidade');
        buscar_cep();
    }

    function autoCompImoveis(){
        var tst = document.getElementById('locador').value;
        if(tst == ""){
            alert('O locador deve ser selecionado da lista');
            return;
        }
        document.getElementById('autoComp').value = 'locador';
        var filtros = 'locador,autoComp';
        var servico = VARS_AMBIENTE['autoCompImoveis_servico'];
        var returns = Array('imovel','idEnde','cep','rua','numero','bloco','apto','compl','bairro','bairroDesc','cidade','cidadeDesc','estado','pais','imovelTel','imovelStatus','refImovel');
        var functionCall = 'checkShowImoveis()';
        autoComp2(filtros,servico,'popImoveis',returns,'10',functionCall);
    }


    function autoCompBairro(){
        var filtros = 'bairroDesc';
        var servico = VARS_AMBIENTE['autoCompBairro_servico'];
        var returns = Array('bairro','bairroDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popBairro',returns,'2',functionCall);
    }

    function autoCompCidade(){
        var filtros = 'cidadeDesc';
        var servico = VARS_AMBIENTE['autoCompCidade_servico'];
        var returns = Array('cidade','cidadeDesc');
        var functionCall = '';
        autoComp2(filtros,servico,'popCidade',returns,'2',functionCall);
    }

    function viewLogsOrcamento(){
        var usuario = document.getElementById('user').value;
        document.getElementById('user').value = '';
        var target = VARS_AMBIENTE['viewLogsOrcamento_target'];
        envia(target,'',formName,'');
        document.getElementById('user').value = usuario;
        return false;
    }

    function calcular(){
        if(!validaLocadorLocatarioImovel(false, 'calcular()')){
            return false;
        }
        if(!userDateIsValid()){
            return false;
        }
        envia(tar,'calcular',formName,'');
        return false;
    }
    
    function fechar(){
        if(!userDateIsValid()){
            return false;
        }
        envia(tar,'fechar',formName,'new');
        setTimeout("envia(" + VARS_AMBIENTE['fechar_tar'] + ",'editar','"+ formName +"','')",1000);
        return false;
    }

    function newOrcamento(){
        envia(VARS_AMBIENTE['newOrcamento_tar'],'editar',formName,'');
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
        envia(VARS_AMBIENTE['doPrintProp_tar'],'print',formName,'new');        
    }

    function cleanCoberturas(){
        cleanInputAll('incendio');
        cleanInputAll('conteudo');
        cleanInputAll('aluguel');
        cleanInputAll('eletrico');
        cleanInputAll('vendaval');
        setPerdaAluguel();
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
        var servico = VARS_AMBIENTE['autoCompAtividade_servico'];
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
        if(click && user == 'admin'){
            return; // administrador pode alterar mes de aniversario
        }
        document.getElementById('popmesNiver').style.display = 'block';
        if($('#orcaReno').val() === 'reno' && mesNiver != '0'){
            if(mesNiver.length < 2){
                mesNiver = '0' + mesNiver;
            }
            $('#mesNiver').val(mesNiver);
            return; // não mexer no mes de aniversario
        }
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
 
    function setButtonFechaOrc(){
        if(tar.indexOf('edit') === -1){
            document.getElementById('fecha').style.display = 'none';
            document.getElementById('getpdf').style.display = 'none';
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
        $('#administradoraDesc').val(VARS_AMBIENTE['voltar_adm']);
        envia(VARS_AMBIENTE['voltar_tar'],'',formName,'');
    }
    function importarFile(){
        var tar = VARS_AMBIENTE['importarFile_tar'];
        envia(tar,'',formName,'');
        return false;
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

