var TELA = "";
function readInvoice(key,tar,op){
    TELA = document.getElementById(tar) ;
    if(TELA == null){alert('Erro ao localizar janela do resultado'); return; }
    var prog   = "AC007.EXE" ; 
    if((op == null)||(op == ''))op = 'lerInvoice';
    var params = "&SubOpcao=" + op ; 
    params += "&SubOpcao2=" + key ;
    params += "&HidTarJan=" + tar ;
    params += "&LNKTRANSPORTE=" + encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    //executa o ajax
    executaAjax(prog,'showInvoice',params); 
}
function showInvoice(texto){
    TELA.innerHTML = texto ;
}
function closeInvoice(tar){
    document.getElementById(tar).innerHTML = "" ;
}
function saveInvoice(tar){
    if(!validaInv())return ;
    TELA = document.getElementById(tar) ;
    if(TELA == null){alert('Erro ao localizar janela do resultado'); return; }
    var prog   = "AC007.EXE" ; 
    var params = "&SubOpcao=addInvoice"  ; 
    params += "&HidTarJan=" + tar ;
    params += "&HidKeyInv=" + encodeURI(document.getElementById("HidKeyInv").value) ;
    params += "&HidKeyIn2=" + encodeURI(document.getElementById("HidKeyIn2").value) ;
    params += "&TxtAdiOrd=" + encodeURI(document.getElementById("TxtAdiOrd").value) ;
    params += "&TxtDatShi=" + encodeURI(document.getElementById("TxtDatShi").value) ;
    params += "&TxtDatInv=" + encodeURI(document.getElementById("TxtDatInv").value) ;
    params += "&TxtNumInv=" + encodeURI(document.getElementById("TxtNumInv").value) ;
    params += "&TxtPayTer=" + encodeURI(document.getElementById("TxtPayTer").value) ;
    params += "&TxtCarrie=" + encodeURI(document.getElementById("TxtCarrie").value) ;
    params += "&TxtRefeDn=" + encodeURI(document.getElementById("TxtRefeDn").value) ;
    params += "&TxtQtdShi=" + encodeURI(document.getElementById("TxtQtdShi").value) ;
    params += "&TxtWalBil=" + encodeURI(document.getElementById("TxtWalBil").value) ;
    params += "&TxtPlante=" + encodeURI(document.getElementById("TxtPlante").value) ;
    params += "&TxtFreigh=" + encodeURI(document.getElementById("TxtFreigh").value) ;
    params += "&TxtTaxa01=" + encodeURI(document.getElementById("TxtTaxa01").value) ;
    params += "&TxtProDat=" + encodeURI(document.getElementById("TxtProDat").value) ;
    params += "&TxtProQtd=" + encodeURI(document.getElementById("TxtProQtd").value) ;
    params += "&LNKTRANSPORTE=" + encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    executaAjax(prog,'showInvoice',params);   
    closeInvoice(tar);
}
// funcao que foi desativada
function saveProforma(tar){
    if(!validaInv())return ;
    TELA = document.getElementById(tar) ;
    if(TELA == null){alert('Erro ao localizar janela do resultado'); return; }
    var prog   = "AC007.EXE" ; 
    var params = "&SubOpcao=addProforma"  ; 
    params += "&HidTarJan=" + tar ;
    params += "&HidKeyInv=" + encodeURI(document.getElementById("HidKeyInv").value) ;
    params += "&TxtAdiOrd=" + encodeURI(document.getElementById("TxtAdiOrd").value) ;
    params += "&TxtProDat=" + encodeURI(document.getElementById("TxtProDat").value) ;
    params += "&TxtProQtd=" + encodeURI(document.getElementById("TxtProQtd").value) ;
    params += "&LNKTRANSPORTE=" + encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    executaAjax(prog,'showInvoice',params);   
    closeInvoice(tar);
}
function editInvoice(key){
    var prog   = "AC007.EXE" ; 
    var params = "&SubOpcao=editRegistro"  ; 
    params += "&SubOpcao2=" + encodeURI(key) ;
    params += "&LNKTRANSPORTE=" + encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    executaAjax(prog,'loadDados',params);   
}
function loadDados(dados){
    dados    = cleanStr(dados);
    if(dados =="ERRO"){
        alert("Erro ao carregar os dados"); return ;
    }    
    var arrayRet = dados.split("|s|");
    document.getElementById("TxtAdiOrd").value = rltrim(arrayRet[0]) ;
    document.getElementById("TxtDatShi").value = rltrim(arrayRet[1]) ;
    document.getElementById("TxtDatInv").value = rltrim(arrayRet[2]) ;
    document.getElementById("TxtNumInv").value = rltrim(arrayRet[3]) ;
    document.getElementById("TxtPayTer").value = rltrim(arrayRet[4]) ;
    document.getElementById("TxtCarrie").value = rltrim(arrayRet[5]) ;
    document.getElementById("TxtRefeDn").value = rltrim(arrayRet[6]) ;
    document.getElementById("TxtQtdShi").value = rltrim(arrayRet[7]) ;
    document.getElementById("TxtWalBil").value = rltrim(arrayRet[8]) ;
    document.getElementById("TxtPlante").value = rltrim(arrayRet[9]) ;
    document.getElementById("TxtFreigh").value = rltrim(arrayRet[10]) ;
    document.getElementById("TxtTaxa01").value = rltrim(arrayRet[11]) ; 
    document.getElementById("HidKeyInv").value = rltrim(arrayRet[12]) ;
    document.getElementById("TxtProDat").value = rltrim(arrayRet[13]) ;
    document.getElementById("TxtProQtd").value = rltrim(arrayRet[14]) ;
    document.getElementById("HidKeyIn2").value = rltrim(arrayRet[15]) ;
}
function delInvoice(tar, key){
    if(!confirm("Você tem certeza que deseja excluir esse registro?"))return ;
    if(!validaInv())return ;
    TELA = document.getElementById(tar) ;
    if(TELA == null){alert('Erro ao localizar janela do resultado'); return; }
    var prog   = "AC007.EXE" ; 
    var params = "&SubOpcao=delRegistro"  ; 
    params += "&SubOpcao2=" + encodeURI(key) ;
    params += "&HidKeyInv=" + encodeURI(document.getElementById("HidKeyInv").value) ;
    params += "&HidTarJan=" + tar ;
    params += "&LNKTRANSPORTE=" + encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    closeInvoice(tar);
    executaAjax(prog,'showInvoice',params);   
}
function validaInv(){
//    obj = document.getElementById("TxtAdiOrd"); 
//    if(obj.value == ""){
//        alert("Digite o numero do Sales Order para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtDatInv"); 
//    if(obj.value == ""){
//        alert("Digite a data da Invoice para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtNumInv"); 
//    if(obj.value == ""){
//        alert("Digite o numero da Invoice para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtRefeDn"); 
//    if(obj.value == ""){
//        alert("Digite o numero da DN para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtQtdShi"); 
//    if(obj.value == ""){
//        alert("Digite quantidade embarcada para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtWalBil"); 
//    if(obj.value == ""){
//        alert("Digite o numero do Waybill para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtPlante"); 
//    if(obj.value == ""){
//        alert("Digite o numero do Plant para Continuar.."); obj.focus(); return false ;
//    }
//    obj = document.getElementById("TxtFreigh"); 
//    if(obj.value == ""){
//        alert("Digite o Frete para Continuar.."); obj.focus(); return false;
//    }
    return true ;
}