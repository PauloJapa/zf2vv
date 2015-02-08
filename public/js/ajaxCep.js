// INICIO DA BUSCA ENDEREÇO POR CEP
// ALTERA POR PAULO EMAIL watakabe05@gmail.com    24-12-2012
var ExibirMsg = true;
function setMsgExibir(op){
    ExibirMsg = op ;
}
// Abrir Tela Carregando    document.getElementById(div).innerHTML = msg;
function carregando_cep(div,msg){
    if(!ExibirMsg) return ;
    document.getElementById(div).innerHTML = msg;
}
// Busca do CEP (Requisição AJAX) -
function buscar_cep(cep){
    if(cep == null)cep = document.getElementById('cep');
    if(cep.readOnly){
        return;
    }
    carregando_cep('checar','Carregando');
    var params = "cep=" + cep.value; 
    var prog = "/admin/enderecos/buscaCep";
    executaAjax(prog,'popula_cep',params); 
}
//DEFINIR CAMPOS DA TELA A SEREM PREENCHIDOS
campo = new Array();
    campo[0] = "rua" ;
    campo[1] = "bairroDesc" ;
    campo[2] = "cidadeDesc" ;
    campo[3] = "estado" ;
    campo[4] = "pais" ;
function setCampos(rua,bairro,cidade,estado,pais){
    campo[0] = rua ;
    campo[1] = bairro ;
    campo[2] = cidade ;
    campo[3] = estado ;
    campo[4] = pais ;
}          
// Popula os Campos do Formulário
function popula_cep(resul){
    var dados = eval(resul);
    switch(dados[0].resultado){
    case '1':
        $('#'+campo[0]).val(getAbevLog(dados[0].tipo_logradouro) + ' ' + dados[0].logradouro);
        $('#'+campo[1]).val(dados[0].bairro);
        $('#'+campo[2]).val(dados[0].cidade);
        setSelect(campo[3],dados[0].uf);
        setSelect(campo[4],dados[0].pais);
        carregando_cep('checar','Encontrado com Sucesso!!');
        break;
    case '2':
        $('#'+campo[2]).val(dados[0].cidade);
        setSelect(campo[3],dados[0].uf);
        setSelect(campo[4],dados[0].pais);
        carregando_cep('checar','Cep de Cidade com logradouro único');
        break;
    default:
        carregando_cep('checar','CEP não encontrado!');
        break;
    }
    toUp('rua');
    toUp('bairroDesc');
    toUp('cidadeDesc');
    setTimeout("carregando_cep('checar','')", 3000);
}

function getAbevLog(tipo){
    switch(tipo){
        case "Avenida":             tipo2 = "Av.";      break;
        case "Rua":                 tipo2 = "R.";       break;
        case "Alameda":             tipo2 = "Al.";      break;
        case "Praça":               tipo2 = "Pç.";      break;
        case "Estação":             tipo2 = "Esta.";    break;
        case "Estrada":             tipo2 = "Estr.";    break;
        case "Aeroporto":           tipo2 = "Aer.";     break;
        case "Condomínio":          tipo2 = "Cond.";    break;
        case "Conjunto":            tipo2 = "Conj.";    break; 
        case "Distrito":            tipo2 = "Dist.";    break;
        case "Esplanada":           tipo2 = "Espl.";    break;
        case "Favela":              tipo2 = "Fav.";     break;
        case "Travessa":            tipo2 = "Tv.";      break;
        case "Largo":               tipo2 = "Lg.";      break;
        case "Jardim":              tipo2 = "Jd.";      break;
        case "Praia":               tipo2 = "Pr.";      break;
        case "Viaduto":             tipo2 = "Vd.";      break;
        case null:                  tipo2 = "Error.";   break;
        default :                   tipo2 = tipo;
    } 
    return tipo2;
}

// FIM DA BUSCA PELO CEP

// CONFIGURAR CAMPOS IMPUTS DE RETORNO INPUT RUA INPUT BAIRRO INPUT CIDADE INPUT UF
// setCampos("TxtRua","TxtBairro","TxtCidade","TxtEstado");

   
// CHAMANDO A BUSCA EXEMPLO
//    CAMPO INPUT
// <input class=tx1 id=Cep maxlength=8 name=Cep  size=9 value="">

//    CHAMANDO A FUNCAO COM UM HYPERLINK
// <a href="javascript:buscar_cep(document.getElementById('TxtCep').value);">Dig. cep Click aqui</a>

//    TAG QUE VAI CONTER MSG DE TODA A OPERAÇÃO
// <font class=f1c><span id="checar"></span></font>

//OBS PARA NAO EXIBIR AS MSG NA TELA USE O SEGUINTE COMANDO
// setMsgExibir(false);



