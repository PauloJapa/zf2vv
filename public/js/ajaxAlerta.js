// VARIAVEIS GLOBAIS
var RECEBIDOS = new Object();
var JANELA = false;
var JANOPEN = false;
var ESPERAR = false;
var word=/ /g;
var qtdMsg = 0; 
var DIRETORIO = "";

//FUNCOES PARA EXIBIR MENSAGENS PERSONALIZADAS BY PAULO WATAKABE 19-12-2009
function criaJanela(){
    if(JANELA)return ;
    var auxJanela = "<div class='caixa' id='mensagem'>";
    auxJanela += "<table width='750' border='0' cellspacing='0' cellpadding='0'>";
    auxJanela += "<tr><td class='infotitulo'><span id='titulo'>titulo</span></td>";
    auxJanela += "<td width='23' class='fechar' title='fechar a Janela' onclick='javascript:fecharMsg();'>&nbsp;</td>";
    auxJanela += "</tr><tr><td colspan='2' class='info'><br><div id='conteudomsg'>mensagem</div><br></td>";
    auxJanela += "</tr></table></div>";
    auxJanela += "<bgsound src='' id='som'>";
    auxJanela += "<div id='debug'></div>";
    var auxJan = document.createElement("DIV");
    auxJan.innerHTML = auxJanela ;
    document.body.appendChild(auxJan);
    JANELA = true ;
    getDiretorio();
    setForm();
}
function getDiretorio(){
    var params = "SubOpcao=Diretorio";
    params += "&LNKTRANSPORTE=" + document.getElementById("LNKTRANSPORTE").value ;
    var url = "AJAXRECADO.EXE";
    executaAjax(url,'setDiretorio',params);
}
function setDiretorio(texto){
    DIRETORIO = "/cgi-bin/" + cleanStr(texto) + "/" ;
    alert(DIRETORIO);
}
function setForm(input){
    var form = document.getElementsByTagName('FORM')[0];
    if((input === null)||(input === "")){
        input = "" ;
    }else{
        if(eval("!form." + input))
            input = "<input id='" + input + "' name='" + input + "' type='hidden'>" ;
        else
            input = "" ;
    }
    if(!form.LNKTRANSPORTE) input += "<input id='LNKTRANSPORTE' name='LNKTRANSPORTE' type='hidden'>";
    if(!form.SubOpcao) input += "<input id='SubOpcao' name='SubOpcao' type='hidden'>";
    if(!form.SubOpcao2)input += "<input id='SubOpcao2' name='SubOpcao2' type='hidden'>";
    if(!form.TxtEnvia)input += "<input id='TxtEnvia' name='TxtEnvia' type='hidden'>";
    if(!form.HidTarget)input += "<input id='HidTarget' name='HidTarget' type='hidden'>";
    if(!form.HidKeyRecado)input += "<input id='HidKeyRecado' name='HidKeyRecado' type='hidden'>";
    if(!form.SubResp)input += "<input id='SubResp' name='SubResp' type='hidden'>";
    if(input !== ""){
        var auxFrm = document.createElement("DIV");
        auxFrm.innerHTML = input ;
        form.appendChild(auxFrm);
    }
}
function sendForm(op1,op2,prg,tar){
    var form = document.getElementsByTagName('FORM')[0];
    if((prg === null)||(prg === ""))prg = form.name ;
    if((tar === null)||(tar === ""))tar = "CENTRO" ;
    if(op1 === null)op1 = "" ;
    if(op2 === null)op2 = "" ;
    form.SubOpcao.value = op1;
    form.SubOpcao2.value = op2;
    form.action = DIRETORIO + prg ;
    form.target = tar ;
    form.submit() ;

}

function showDebug(msg){
    document.getElementById('debug').innerHTML = msg;
}
function play(musica){
    if (musica)  {
        document.getElementById('som').src = "/icons/sons/" + musica;
    } else {
        alert('Sem musica') ;
    }
}
function exibirMsg(tit,msg){
    criaJanela() ;
    if (navigator.appName === "Microsoft Internet Explorer"){
        pwidth = window.document.body.offsetWidth;
        pheight = window.document.body.offsetHeight;
        top_pos = document.body.scrollTop;
        left_pos = document.body.scrollLeft;
    }else{
        pwidth = window.innerWidth;
        pheight = window.innerHeight;
        top_pos = window.pageYOffset;
        left_pos = window.pageXOffset;
    }
    document.getElementById('titulo').innerHTML = tit;
    document.getElementById('conteudomsg').innerHTML = msg;
    var dda = document.getElementById('mensagem');
    dda.style.top = (top_pos+100)+"px";
    dda.style.left = (left_pos+100)+"px";
    document.getElementById('mensagem').style.display = 'block';
    JANOPEN = true ;
}
function fecharMsg(som){
      if(som === null)play("sound_2.mp3");
      document.getElementById('mensagem').style.display = 'none';
      JANOPEN = false ;
      setEspera(true);
      setTimeout("setEspera(false)",180000);
}
function setEspera(op){
    if(op)
        ESPERAR = true ;
    else    
        ESPERAR = false ;       
}
function verMsg(){
    setTimeout("verMsg()",10000);
    if(ESPERAR){
        return ;
    }
    var params = "SubOpcao=Verificar";
    params += "&SubOpcao2=" ;
    for (d in RECEBIDOS) {
        params += d.replace(word,"_") + ";" ;
    }
    params += "&LNKTRANSPORTE=" + document.getElementById("LNKTRANSPORTE").value ;
    var url = "AJAXRECADO.EXE";
    executaAjax(url,'trataRetorno',params);
}
function trataRetorno(texto){
    var arrayRet = texto.split("|s|");
    if(arrayRet[0].indexOf("ok") !== -1){
        RECEBIDOS[cleanStr(arrayRet[1])] = cleanStr(arrayRet[2]) + "|s|" + cleanStr(arrayRet[3]) + "|s|" + cleanStr(arrayRet[4]) + "|s|" + cleanStr(arrayRet[5]) + "|s|" ;
        exibirRecados();
    }else if(arrayRet[0].indexOf("erro") !== -1){
        exibirMsg(arrayRet[1],arrayRet[2]);
    }else if(qtdMsg !== 0){
        if(!JANOPEN)exibirRecados();
    }
}
function exibirRecados(som){
    var allRecados = "";
    var i = 0;
    for (d in RECEBIDOS) {
        var arrayRet = RECEBIDOS[d].split("|s|");
        allRecados += "<table border=0 class=tb0 cellspacing=0 cellpadding=0 width=100%><tr class=bg0>";
        allRecados += "<td class=td1 align=center width='70px'><font class=f2c>Ação</font></td>";
        allRecados += "<td class=td1 align=center width='40px'><font class=f2c>De</font></td>";
        allRecados += "<td class=td1 align=center width='80px'><font class=f2c>Envio</font></td>";
        allRecados += "<td class=td1 align=center width='40px'><font class=f2c>Hora</font></td>";
        allRecados += "<td class=td1 align=center><font class=f2c>Recado</font></td></tr><tr>";
        allRecados += "<td class=td12><a href=\"javascript:responder('" + d + "');\"><span class='repond'></span><br>Responder</a><br>";
        allRecados += "<a href=\"javascript:arqRecado('" + d + "');\"><span class='arquiv'></span><br>Arquivar</a></td>";
        allRecados += "<td class=td12><font class=f1>" + arrayRet[0] + "</font></td>";
        allRecados += "<td class=td12><font class=f1>" + arrayRet[1] + "</font></td>";
        allRecados += "<td class=td12><font class=f1>" + arrayRet[2] + "</font></td>";
        allRecados += "<td class=td1><font class=f1> " + arrayRet[3] + "</font></td></tr></table>";
        i++;
    }
    qtdMsg = i ;
    if(i === 0) return ;
    exibirMsg("Você tem " + i + " Recados recebidos!",allRecados);
    if((som === "")||(som === null)) play("sound_1.mp3");
}

function responder(Key){
    play("sound_4.mp3");
    var arrayRet = RECEBIDOS[d].split("|s|");
    docWindow=window.open('','newResponde','width=1024px,height=450px,toolbar=no,location=no,diretories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');
    document.getElementById('TxtEnvia').value = arrayRet[0] ;
    document.getElementById('HidTarget').value = 'newResponde' ;
    document.getElementById('HidKeyRecado').value = Key ;
    document.getElementById('SubResp').value = "Responder >>" ;
    sendForm("Responder","","REARQV1.EXE","newResponde");
    setTimeout("arqRecado('" + Key + "')",2000);
}
function arqRecado(key){
    var params = "SubOpcao=Arquivar";
    params += "&SubOpcao2=" + key + ";";
    params += "&LNKTRANSPORTE=" + document.getElementById("LNKTRANSPORTE").value.replace(word,"_") ;
    var url = "AJAXRECADO.EXE";
    executaAjax(url ,'delVetor', params);
    if(qtdMsg === 1){
        RECEBIDOS = new Object() ;
        fecharMsg('semSom');
        qtdMsg = 0 ;
    }else{
        delete RECEBIDOS[key];
        exibirRecados("semSom");
    }
}
function delVetor(ret){
    if(ret === "")return ;
    var retArry = ret.split("|s|");
    if(cleanStr(retArry[0]) !== "ok"){
        play("sound_6.mp3");
    }else{
        play("sound_5.mp3");
    }
}
setTimeout("verMsg()",15000);