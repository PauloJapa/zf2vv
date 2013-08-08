/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/*
 * Funções para validar e formatar cpf e cnpj
 */


function limpaCampo(sValor, iBase) {
    var tam = sValor.length
    var saida = new String
    for (i = 0; i < tam; i++)
        if (!isNaN(parseInt(sValor.substr(i, 1), iBase)))
            saida = saida + String(sValor.substr(i, 1));
    return (saida);
}

function checkCPF_CNPJ(fld)
{
    var NI;
    var iTipo = 3;
    var conteudo = fld.value;
    NI = limpaCampo(conteudo, 10);
    var tam = NI.length;

    if (tam == 0) {
        return;
    }

    if (NI.length == 10 || NI.length == 11)
        iTipo = 2;
    if (NI.length == 14)
        iTipo = 1;
    temacesso = "nao";
    repetido = Array("");
    for (i = 0; i < NI.length; i++) {
        if (NI.substr(i, 1) != "0") {
            temacesso = "sim";
        }
        if (repetido[repetido.length - 1] != "") {
            repetido[repetido.length] = "";
        }
        for (y = 0; y < repetido.length; y++) {
            if (repetido[y] == NI.substr(i, 1)) {
                y = 100;
            }
            if ((repetido[y] != NI.substr(i, 1)) && (repetido[y] == "")) {
                repetido[y] = NI.substr(i, 1);
            }
        }
    }
    if (repetido.length == 2) {
        iTipo = 3;
    }
    if (temacesso != "sim") {
        iTipo = 3;
    }
    switch (iTipo) {
        case 1:
            if (NI.substr(12, 2) != CalcularDV(NI.substr(0, 12), 9)) {
                alert('O número do CNPJ informado está incorreto');
                fld.select();
                fld.focus();
                return(false);
            } else {    
                fld.value = NI.substr(0, 2) + "." + NI.substr(2, 3) + "." + NI.substr(5, 3) + "/" + NI.substr(8, 4) + "-" + NI.substr(12, 2);
            }
            break;

        case 2:
            if (NI.length == 10)
                NI = "0" + NI;
            if (NI.length != 11) {
                alert('O número do CPF informado está incorreto');
                fld.select();
                fld.focus();
                return(false);
            }

            if (NI.substr(9, 2) != CalcularDV(NI.substr(0, 9), 11)) {
                alert('O número do CPF informado está incorreto');
                fld.select();
                fld.focus();
                return(false);
            } else {
                fld.value = NI.substr(0, 3) + "." + NI.substr(3, 3) + "." + NI.substr(6, 3) + "-" + NI.substr(9, 2);
            }
            break;

        case 3:
            alert('O número do CNPJ/CPF informado está incorreto');
            fld.select();
            fld.focus();
            return(false);
    }
    return (true);
}

function CalcularDV(sCampo, iPeso) {

    var iTamCampo;
    var iPosicao, iDigito;
    var iSoma1 = 0;
    var iSoma2 = 0;
    var iDV1, iDV2;

    iTamCampo = sCampo.length;

    for (iPosicao = 1; iPosicao <= iTamCampo; iPosicao++) {
        iDigito = sCampo.substr(iPosicao - 1, 1);
        iSoma1 = parseInt(iSoma1, 10) + parseInt((iDigito * Calcular_Peso(iTamCampo - iPosicao, iPeso)), 10);
        iSoma2 = parseInt(iSoma2, 10) + parseInt((iDigito * Calcular_Peso(iTamCampo - iPosicao + 1, iPeso)), 10);
    }

    iDV1 = 11 - (iSoma1 % 11);
    if (iDV1 > 9)
        iDV1 = 0;

    iSoma2 = iSoma2 + (iDV1 * 2);
    iDV2 = 11 - (iSoma2 % 11);
    if (iDV2 > 9)
        iDV2 = 0;

    Ret = (parseInt(iDV1 * 10, 10) + parseInt(iDV2));

    Ret = "0" + Ret;
    Ret = Ret.substr(Ret.length - 2, Ret.length);

    return(Ret);
}

function Calcular_Peso(iPosicao, iPeso) {
    //Pesos CPF 11 CNPJ 9
    return (iPosicao % (iPeso - 1)) + 2;
}

function mascaraMutuario(o,f){
    v_obj=o;
    v_fun=f;
    setTimeout('execmascara()',1);
}

function execmascara(){
    v_obj.value=v_fun(v_obj.value);
}

function cpfCnpj(v){
    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"");
    if (v.length <= 13) { //CPF
        //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d)/,"$1.$2");
        //Coloca um ponto entre o terceiro e o quarto dígitos
        //de novo (para o segundo bloco de números)
        v=v.replace(/(\d{3})(\d)/,"$1.$2");
        //Coloca um hífen entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    } else { //CNPJ
        //Coloca ponto entre o segundo e o terceiro dígitos
        v=v.replace(/^(\d{2})(\d)/,"$1.$2");
        //Coloca ponto entre o quinto e o sexto dígitos
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
        //Coloca uma barra entre o oitavo e o nono dígitos
        v=v.replace(/\.(\d{3})(\d)/,".$1/$2");
        //Coloca um hífen depois do bloco de quatro dígitos
        v=v.replace(/(\d{4})(\d)/,"$1-$2");
    }
    return v;
}
// FIM Funções para validar e formatar cpf e cnpj

function retira_acentos(palavra) {
    com_acento = 'áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÖÔÚÙÛÜÇ';
    sem_acento = 'aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC';
    nova='';
    for(i=0;i<palavra.length;i++) {
        if (com_acento.search(palavra.substr(i,1))>=0) {
            nova+=sem_acento.substr(com_acento.search(palavra.substr(i,1)),1);
        }else{
            nova+=palavra.substr(i,1);
        }
    }
    return nova;
}

function cleanInputsForm(ind){
    if(ind == null) ind = 0 ;
    FORM = document.getElementsByTagName('FORM')[ind]
    for (var i = 0; i < FORM.elements.length; i++) {
        var obj = FORM.elements[i];
        cleanInput(obj);
    }   
}

function cleanInput(obj){
    if(obj == '')return ;
    if(!isObject(obj)) obj = document.getElementById(obj) ;
    switch (obj.type) {
        case "radio":
        case "checkbox":
            obj.checked = false ;
            break;
        case "select":
            obj.selectedIndex = 0 ;            
        break;
        case "hidden":
        case "button":
            obj.value = obj.value;
            break;
        default :
            obj.value = "" ;
            break;
    }   
}

function cleanInputAll(obj){
    if(obj == '')return ;
    if(!isObject(obj)) obj = document.getElementById(obj) ;
    switch (obj.type) {
        case "radio":
        case "checkbox":
            obj.checked = false ;
            break;
        case "select":
            obj.selectedIndex = 0 ;            
        break;
        case "hidden":
        case "button":
            obj.value = "";
            break;
        default :
            obj.value = "" ;
            break;
    }   
}

function setInputDisabledMulti(name){
    var inp = document.getElementsByName(name);
    for(i=0; i<inp.length; i++){
        inp[i].disabled = true;
    }
}
//Função para saber se o parametro é objeto
function isObject( what ){
    return (typeof what == 'object');
}

function setSelect(ide,vlr){
    var Select = document.getElementById(ide);
    qtd  = Select.options.length ;
    for (i = 0; i < qtd; i++) {
	if(Select.options[i].label == vlr){
            Select.selectedIndex = i;
            break;
        }
    }    
}
// verifica se o valor esta no array
function in_Array(array,vlr){
    for (key in array) {
        if (array[key] == vlr) {
            return true;
        }
    }
    return false ;
}

// Valida Campos do form passados em uma lista de Array
function valida(ids){
    for(i=0; i<ids.length; i++){
        var obj = document.getElementById(ids[i]);
        if(obj.value == ""){
            alert("O campo " + ids[i] + " não pode ficar vazio!!");
            obj.focus();
            return false;
        }
    }
    return true;
}

var janelaAberta = null;
function envia(action,opc,frm,tar){
    if(frm == null)frm = document.getElementById('form'); else frm = document.getElementById(frm);
    if((action == null)||(action == ""))action = "/admin/orcamentos";
    if(opc == null)opc = "";
    if(tar != null){
        if(tar != ''){
            //checkJanela(tar); Abandonado por não funcionario como esperado
        }
        frm.target = tar;
    }
    frm.subOpcao.value = opc;
    frm.action = action ;
    try{
        frm.scrolX.value = (document.all)?document.body.scrollLeft: window.pageXOffset;
        frm.scrolY.value = (document.all)?document.body.scrollTop: window.pageYOffset;
    }catch(e){
        erro = true;
    }
    frm.submit() ;
}

function checkJanela(tar){
    // verifica se a janela está aberta
    if(janelaAberta != null && !janelaAberta.closed){
        janelaAberta.close();
    }  
    janelaAberta = null;
    janelaAberta = window.open('',tar);
}

/**
 * Verifica se existe um bloqueador de popup ativo
 * blockTest deve ser uma var Global onde será guardado os status do popup
 */

var blockTest  = false;
var tempoPopup = 0;

function hasPopupBlocker(){
    blockTest = false;
    tempoPopup = 0;
    var myPopup = window.open("popupTest", "popupTest", "directories=no,height=150,width=150,menubar=no,resizable=no,scrollbars=no,status=no,titlebar=no,top=0,location=no");
    if (!myPopup){
        blockTest = false;
    }else{
        if (navigator.userAgent.indexOf("Chrome") != -1) { 
            tempoPopup = 500;
            myPopup.onload = function() {
                setTimeout(function() {
                    if (myPopup.screenX === 0) {
                        blockTest = false;
                    } else {
                        blockTest = true;
                        myPopup.close();  
                    }
                }, 100);
            };
        }else{
            blockTest = true;
            myPopup.close(); 
        } 
    }

    setTimeout("checkBlocker()",tempoPopup)
//checkBlocker();
}

function checkBlocker(){
    if(!blockTest)
        alert("Você possui um bloqueador de popup ativo, para exibir a proposta você desabilita-lo por favor !!");    
}

// Alterar valor digitado para maiusculo
function toUp(obj) {
    try{
        obj.value = obj.value.toUpperCase();
    }catch(e){
        try{
            obj = document.getElementById(obj);
            obj.value = obj.value.toUpperCase();
        }catch(e){
            alert('erro ao converter para maiusculo');
        }
    }
}

// Evita que Acidentalmente a tecla backspace execute a função voltar do navegador
$(function () {
    var rx = /INPUT|TEXTAREA/i;
    var rxT = /RADIO|CHECKBOX|SUBMIT/i;

    $(document).bind("keydown keypress", function (e) {
        var preventKeyPress;
        if (e.keyCode == 8) {
            var d = e.srcElement || e.target;
            if (rx.test(e.target.tagName)) {
                var preventPressBasedOnType = false;
                if (d.attributes["type"]) {
                    preventPressBasedOnType = rxT.test(d.attributes["type"].value);
                }
                preventKeyPress = d.readOnly || d.disabled || preventPressBasedOnType;
            } else {preventKeyPress = true;}
        } else { preventKeyPress = false; }

        if (preventKeyPress) e.preventDefault();
    });
}); 

// Modificar a tecla enter para tab e 
// Verificar se tem função a ser executada
function changeEnterToTab(obj,e){
    var keycode;
    if (window.event){ 
        keycode = window.event.keyCode;
    }else if (e){ 
        keycode = e.which;
    }else{
        return true;
    } 
    //toUp(obj);
    // alert(keycode);
    if((keycode == 13)||(keycode == 9)){
        pressEnterOrTab(obj,e);
    }
    if(keycode == 9){
        nextFocus(obj);
        pressTab(obj,e);
        return false;
    }
    if(keycode == 13){
        nextFocus(obj);
        pressEnter(obj,e);
        return false;
    }
    return true;
    //e.preventDefault();
}

function pressEnterOrTab(obj,e){
    return true;
}

function pressEnter(obj,e){
    return true;
}

function pressTab(obj,e){
    return true;
}

function nextFocus(obj){
    var inputs = $(obj).closest('form').find(':input:visible');
    var ind    = inputs.index(obj);
    var i      = 1;
    var flag   = true;
    while(flag){
        ele = inputs.eq(ind + i);
        tp = ele.prop('type');
        switch(tp){
            case 'button':    
            case 'submit':
                i++;
                break;
            default:    
                ele.focus();
                flag = false;          
        }
    }
    return;
}

function getNextElement(field){
    var form = field.form;
    for (var e = 0; e < form.elements.length; e++){
        if(field == form.elements[e]){
            break;
        }
    }
    return form.elements[++e % form.elements.length];
}
