/*
        FUNCOES DO RECURSO SELECT SIMPLES COM INCLUSÃO E EXCLUSÃO
        CRIADO EM 03-02-2011
        AUTOR  PAULO C W
        EMAIL  watakabe05@gmail.com

*/
    //VAR GLOBAIS
var JAN = ''     ;   //Janela onde exibi os resultados
var INP = ''     ;   //Campo que vai receber o valor escolhido
var RES = false  ;   //Flag para saber se é necessario resetar o ajax
var executar = "";  //Funcao a ser executada depois de escolher a opcao
function getTabela(inp,tab,loc,jan,ope,func){
    if((inp == "" )||(inp == null )){ alert("input não definido.")                  ; return ; }
    if((tab == "" )||(tab == null )){ alert("Registo table não definido.")          ; return ; }
    if((loc == "" )||(loc == null )){ alert("localizaçao do registro não definido."); return ; }
    if((jan == "" )||(jan == null )){ alert("local de exibição não definido.")      ; return ; }
    if((func == "")||(func == null))executar = ""    ; else executar  = func      ;

    JAN = document.getElementById(jan) ;
    INP = document.getElementById(inp) ;
    var params  = "SubOpcao="   + ope ;
        params += "&SubTabela="  + tab ;
        params += "&SubLoc="     + loc ;
        params += "&LNKTRANSPORTE=" +  encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    //Caso falhe ele aborta a requisição
    setTimeout("resetar()", 10000 );
    RES = true ;
    //executa o ajax
    executaAjax('AC005.EXE','exibeTabela',params);
}
function resetar(){
    if(!RES) return ;
    JAN.innerHTML = "";
    setOCUPADO(false);
    RES = false ;
}
function exibeTabela(texto){
    JAN.innerHTML = texto ;
    RES = false ;
}
function addTabela(vlr,tab){
    if((vlr == "" )||(vlr == null )){ alert("input a adicionar não definido.")                  ; return ; }
    var params  = "SubOpcao=addNovo"    ;
        params += "&" + vlr + "="  + trataAcentos(document.getElementById(vlr).value) ;
        params += "&SubTabela="  + encodeURI(tab) ;
        params += "&LNKTRANSPORTE=" +  encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    executaAjax('AC005.EXE','analisarAdd',params);
}

function addTab2(key,vlr,tab,loc){
    if((vlr == "" )||(vlr == null )){ alert("input a adicionar não definido.")                  ; return ; }
    var params  = "SubOpcao=addCodNovo"    ;
        params += "&" + vlr + "="  + trataAcentos(document.getElementById(vlr).value) ;
        params += "&" + key + "="  + trataAcentos(document.getElementById(key).value) ;
        params += "&SubTabela="  + encodeURI(tab) ;
        params += "&SubLoc="  + encodeURI(loc) ;
        params += "&LNKTRANSPORTE=" +  encodeURI(document.getElementById("LNKTRANSPORTE").value) ;
    executaAjax('AC005.EXE','analisarAdd',params);
}

function analisarAdd(texto){
    texto = cleanStr(texto) ;
    if(texto == "ok"){
        setValor("fechar");
        alert(texto);
    }else{
        alert("Não foi possivel incluir uma nova descrição!");
    }
}

function setValor(vrl){
    if(vrl == "fechar"){
        INP.focus();
    }else{  
        INP.value = vrl ;
    }
    JAN.innerHTML = "";
    setOCUPADO(false);
    if(executar != "")eval(executar);
}