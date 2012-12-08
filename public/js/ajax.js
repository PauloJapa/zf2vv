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