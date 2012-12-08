/*
        ENVIAR UM FORMULARIO VIA AJAX  
        CRIADO EM 27-01-2010
        AUTOR  PAULO C W 
        EMAIL  watakabe05@gmail.com
        PS 
        - VALIDAÇAO DEVE SER FEITA ANTES DE ENVIAR O FORM COM JS
        - PROGRAMA COBOL DEVE ESTAR PREPARADO PARA ESTA OPERACAO
          
*/

/*
    VARIAVEIS GLOBAIS
*/
    var FORM   = "" ; //objeto formulario
    var CAMPOS = "" ; //todos os campos do formulario

function incluiVV(op1,op2,prg,tar){
    FORM = document.getElementsByTagName('FORM')[0] ;
    FORM.SubOpcao.value = op1 ;
    FORM.SubOpcao2.value = op2 ;   
    FORM.SubOpcao3.value = "AjaxTCM" ;   
    var url = "http://www.aemsistemas.com.br/cgi-bin/TCM/PCM0101.EXE"
    setarCampos();
    executaAjax(url,'respostaVV',CAMPOS);
}
    
//função que junta todo o conteudo a ser enviado por AJAX
function setarCampos() {
    CAMPOS =  "vazioinput=null";
    for (var i = 0; i < FORM.elements.length; i++) {
        var x = FORM.elements[i];
        valor = getInput(x);
        if(valor != "") CAMPOS += "&" + valor;
    }
}

//Função que determina o tipo do obj e retorna em formato para envio Get ou AJAX
function getInput(obj){
    switch (obj.type) {
        case "radio":
        case "checkbox":
            if(obj.checked != true) return "" ;
            return obj.name + "=" + encodeURI(obj.value); 
        break;
        case "select":
            var valor = exam.options[obj.selectedIndex].value ;            
            if(valor == "") return "" ;
            return obj.name + "=" + encodeURI(valor); 
        break;
        case "button":
            if(obj.value == "") return "" ;
            return obj.name + "=" + encodeURI(obj.value); 
        break;
        default :
            if(obj.value == "") return "" ;
            return obj.name + "=" + encodeURI(obj.value); 
        break;
    }
}

/*  funcoes para consulta de codigos
function incluirExame(){
  var exam = document.getElementById("SelExames");
  var indice = exam.selectedIndex ;
  if(exam.options[indice].value == '')return ;
  for(var i in exames)      {
      dados = exames[i].split(';',3);
      if(exam.options[indice].value == (dados[1]+';'+dados[2]))          {
          return ;
      }
  }
  exames.push(exam.options[indice].text + ";" + exam.options[indice].value) ;
  exibiExame();
}

*/