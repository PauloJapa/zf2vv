//GLOBAIS
var OCUPADO = false;
var TEMPO   = "";
var CACHE   = false;

//FUNCOES AJAX
function iniciaAjax(){
    //verifica se o browser tem suporte a ajax
    try {
        ajax = new ActiveXObject("Microsoft.XMLHTTP");
    }catch(e){
        try {
            ajax = new ActiveXObject("Msxml2.XMLHTTP");
        }catch(ex){
            try {
                ajax = new XMLHttpRequest();
            }catch(exc){
                alert("Esse browser no tem recursos para uso do Ajax");
                ajax = false ;
            }
        }
    }
    return ajax ;
}


function executaAjax(url,ret,param){
    if(OCUPADO)return;
    setOCUPADO(true) ;
    mreq = iniciaAjax() ;
    if(!mreq) return ;
    mreq.onreadystatechange = function(){
        if(mreq.readyState === 4){
            eval(ret + "(mreq.responseText)");
            setOCUPADO(false) ;
        }
    };
    if(!CACHE){
        if((param === null)||(param === "")){
            param =  Math.ceil ( Math.random() * 100000 );
        }else{
            param +=  "&" + Math.ceil ( Math.random() * 100000 );
        }
    }
    mreq.open("POST", url, true);
    mreq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
    mreq.send(param);
}

function setCACHE(vlr){
    if(vlr)
        CACHE = true ;
    else
        CACHE = false;
}

function setOCUPADO(vlr){
    if(vlr){
        OCUPADO = true ;
        TEMPO = setTimeout("setOCUPADO('false')",3000);
    }else{
        OCUPADO = false ;
        clearTimeout(TEMPO);
    }
}


//funcao para juntar tudo em um paramentro para envio via get ou ajax
//escolha entre pega um campo, obj ou array de campos.
function getParams(nome){
    if(isArray(nome)){
        var par = "" ;
        var sep = "" ;
        for(var i in nome){
            par += sep + setInput(document.getElementById(nome[i]));
            sep = "&" ;
        }
        return par ;
    }
    if(isObject(nome)){
        return setInput(nome) ;
    }
    return setInput(document.getElementById(nome)) ;
}

//Função que determina o tipo do obj e retorna em formato para envio Get ou AJAX
function setInput(obj){
    switch (obj.type) {
        case "radio":
        case "checkbox":
            if(obj.checked != true) return ;
            return obj.name + "=" + encodeURI(obj.value); 
        break;
        case "select":
            var valor = exam.options[obj.selectedIndex].value ;            
            return obj.name + "=" + encodeURI(valor); 
        break;
        case "button":
            return obj.name + "=" + encodeURI(obj.value); 
        break;
        default :
            return obj.name + "=" + encodeURI(obj.value); 
        break;
    }
}
