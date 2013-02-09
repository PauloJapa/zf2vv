/*
        FUNCOES DO RECURSO AUTOCOMPLETAR VERSAO 2.2B  
        CRIADO   EM 06-05-2010
        ALTERADO EM 25-05-2011 ATRASO DA EXECUÇÃO 
        ALTERADO EM 25-07-2011 EXIBER MSG CARREGANDO E DIV COM SCROLL
        AUTOR  PAULO C W 
        EMAIL  watakabe05@gmail.com
          
*/
var retorno = "";   //Para onde vai o retorno pode ser uma lista
var exiQtd  = 1;   //Exibicao Qtd de descriçoes que irão ser visualizadas
var executar = "";  //Funcao a ser executada depois de escolher a opcao
var tela     = "";  //Direcionar resultado para esta tela

function autoComp2(txt,prog,jan,ret,exQt,func,opc){
    try{
        var statu = document.getElementById('ajaxStatus');
    }catch(e){
        alert("Campo não definido status " + e.description );
    }
    if(statu.value === 'INICIANDO'){
      return;
    }
    document.getElementById(jan).innerHTML = "";
    statu.value = 'INICIANDO' ;
    var txt = txt.split(",");  
    if((ret === "" )||(ret === null ))retorno = txt[0] ; else retorno = ret         ;
    if((opc === "" )||(opc === null ))opc = "Pesquisar";
    var tempo = 1000;
    if(opc === 'ALL') tempo = 10;
    //ATRASANDO A EXECUÇÃO DA FUNCAO EM 1 segundos
    setTimeout("autoComp(\""+txt+"\",\""+prog+"\",\""+jan+"\",\""+exQt+"\",\""+func+"\",\""+opc+"\")",tempo);
}

function autoComp(txt,prog,jan,exQt,func,opc){
    var statu = document.getElementById('ajaxStatus');
    var txt = txt.split(",");  
    if((exQt === "")||(exQt === null))exiQtd = 1       ; else exiQtd  = Number(exQt);
    if((func === "")||(func === null))executar = ""    ; else executar  = func      ;
    if((jan === "" )||(jan === null ))tela = ""        ; else tela  = jan           ;
    var qtdGrupos = exiQtd + retorno.length;
    var params = "subOpcao=" + opc; 
    for( i = 0 ; i < txt.length ; i++){
        params += "&" + txt[i] + "=" + document.getElementById(txt[i]).value;  
    }
    params += "&qtdGrupos=" + qtdGrupos ;
    //Mensagem de carregamento 
    Saida  = "<div style='background-color: #fefefe; width: auto;overflow: auto;margin: 0 auto;border: 1px solid #00F;'>";
    Saida += "<table width='100%'>";
    Saida += "<tr>";
    Saida += "<td>Carregando Aguarde!</td>";
    Saida += "<td align=right>";
    Saida += "<a href=\"javascript:RetEsco2('fechar','" + tela + "');setOCUPADO(false);\">";
    Saida += "<span class='icon-remove'></span></a></td>";
    Saida += "</tr></table></div>" ;
    
    janela = document.getElementById(tela);
    janela.innerHTML = Saida;
    //Caso falhe ele aborta a requisição
    setTimeout("closeMsg('" + tela + "')", 10000 );
    //executa o ajax
    executaAjax(prog,'processXMLauto2',params); 
}
function closeMsg(tela){
    var statu = document.getElementById('ajaxStatus');
    if(statu.value === 'INICIANDO'){
        RetEsco2('fechar',tela);
    }
    setOCUPADO(false);
    setStatus();
}

function processXMLauto2(texto){
    var statu = document.getElementById('ajaxStatus');
    //pega a tag opcoes
    var dataArray   = texto.split("|s|");
    statu.value = 'OK' ; 
    //total de elementos contidos na tag opcoes
    if(dataArray[0] === "vazio") { //exibir mensagem de nao encontrados
        Saida = "<div style='background-color:#fefefe; width:auto; max-height:350px; overflow:auto; margin:0; border:1px solid #00F;'>";
        Saida += "<table width='100%'>";
        Saida += "<tr class='auto1'><td class='td10'>Nenhum resultado encontrado!</td>";
        Saida += "<td align=right class='td10'><a href=\"javascript:RetEsco2('fechar','" + tela + "');\"><span class='icon-remove'></span></a></td>";
        Saida +="</tr></table></div>" ;
        janela = document.getElementById(tela);
        janela.innerHTML = Saida;
        setTimeout("RetEsco2('fechar','" + tela + "')",1500);
        return ;
    }
    if(dataArray[0] !== "ok") {
        statu.value = 'problemas no retorno' ;
        janela.innerHTML = texto;
        return ;
    } 
    //percorre o arquivo texto paara extrair os dados da tag opcoes
    Saida = "<div style='background-color: #fefefe; min-width:300px; width:auto; max-height: 450px;overflow: auto;margin: 0 auto;border: 1px solid #ddd;'>";
    Saida += "<table class='table table-striped table-bordered table-hover table-condensed'><thead><tr style='background-color: #d9d9d9;'>";
    Saida += "<td><a href=\"javascript:RetEsco2('fechar','" + tela + "');\">";
    Saida += "<span class='icon-remove'></span></a></td>";
    Saida += "<td style='text-align:right;' colspan='" + exiQtd + "'><a href=\"javascript:RetEsco2('fechar','";
    Saida += tela + "');\"><span class='icon-remove'></span></a></td></tr></thead>";
    for(var i = 1 ; i < dataArray.length ; i++) {
        //conteudo dos campos no arquivo texto
        try{
            var item = dataArray[i].split("|c|");
            Saida += "<tr>" ;
            //Montar funcao
            funcao = "onClick=\"javascript:RetEsco2(" ;
            auxseq = 0 ; 
            if(!isArray(retorno)){
                funcao +=  "'" + item[0] + "','" ;
            }else{
                auxseq = retorno.length ;
                funcao +=  "Array('" ;
                for(var y = 0 ; y < auxseq ; y++){
                    funcao +=  item[y] ;
                    if(y + 1 === auxseq)funcao +=  "'),'" ; else funcao +=  "','" ;
                }
            }
            funcao += tela + "');\"" ; 
            if(exiQtd === 1){
                Saida += "<td nowrap " + funcao +  ">"
                    +  item[auxseq] + "</td>";
            }else{
                for(var y = 0 ; y < exiQtd ; y++){
                    Saida += "<td nowrap " + funcao +  ">"
                        +  item[auxseq + y] + "</td>";
                }
            }              
            Saida += "</tr>";
        }catch(e){
            if(i < 3){ 
                alert(i +  "  erro  " + e.description +' '+ dataArray.length);
            }
        }
    }
    Saida += "<tr><td style='text-align:center;' colspan='" + exiQtd + "' onClick=\"javascript:RetEsco2('fechar','" + tela + "');\">Fechar </td></tr>";
    Saida +="</table></div>" ;
    janela = document.getElementById(tela);
    janela.innerHTML = Saida;
} 

function setStatus(vlr){
        if (vlr === null) vlr = "";
        if(vlr === "") setOCUPADO(false) ;
        document.getElementById('ajaxStatus').value = vlr ;
}

function RetEsco2(op,obj){  
    if(op === "fechar"||op === "Criterio"){
        document.getElementById(obj).innerHTML = "";
        return ;
    }  
    if(!isArray(retorno)){
        if((op === "x")||(op === "X")) return ;
        document.getElementById(retorno).value = op ;
    }else{
        if((op[0] === "x")||(op[0] === "X")) return ;
        for(i = 0 ; i < retorno.length ; i++){
            document.getElementById(retorno[i]).value = op[i] ;
        }
    }  
    document.getElementById(obj).innerHTML = "";
    if(executar !== "")eval(executar);
}
function isArray(o){
    return(typeof(o.length)=="undefined")?false:true;
}

trocaOld = "";
trocaOldClass = "";
function trocaCl(E)
{
  if (trocaOld !== "")trocaOld.className = trocaOldClass ;
  trocaOldClass = E.className;
  E.className = "auto3";
  trocaOld = E;
}
function trocaCorClean(){
  if(tela === "") return ;
  document.getElementById(tela).innerHTML = "";
}  
  