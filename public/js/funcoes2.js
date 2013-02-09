sltxt = "";

//function noRightClick()
//{
//if (event.button==2)
//{
//alert('Desculpe, Acesso Negado !!!')
//}
//}

//document.onmousedown=noRightClick

function txtSel(e) {
    e.select();
    e.focus();
    sltxt = e.name;
    return true;
}

function proxCampo(campo) {
    for (i = 0; i < 150; i++) {
        if (campo.form.elements[i].name == campo.name) {
            if (document.forms[0].elements[i + 1].value != "")
                document.forms[0].elements[i + 1].select();
            document.forms[0].elements[i + 1].focus();
            break;
        }
    }
}
function LG(obj) {
    document.getElementById(obj).value = document.getElementById(obj).value.toUpperCase();
}
function currencyFormat(fld, e, tam) {
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    var ctrl = false;
    var whichCode = (window.Event) ? e.which : e.keyCode;
    if (sltxt == fld.name) {
        sltxt = "";
        fld.value = "";
        ctrl = true;
    }
    if (whichCode == 13) {
        btn = 0;
        proxCampo(fld); // manda foco para proximo campo
        return false;
    }
    key = String.fromCharCode(whichCode);  // Get key value from key code
    if (strCheck.indexOf(key) == -1 && whichCode != 8)
        return false;  // Not a valid key
    len = fld.value.length;
    for (i = 0; i < len; i++)
        if ((fld.value.charAt(i) != '0') && (fld.value.charAt(i) != ","))
            break;
    aux = '';
    for (; i < len; i++)
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    if (whichCode == 8) {
        if (aux.length > 0)
            aux = aux.substr(0, aux.length - 1);
    } else {
        aux += key;
    }
    len = aux.length;
    if (len > tam)
        return false;
    if (len == 0)
        fld.value = '';
    if (len == 1) {
        if (ctrl) {
            fld.value = '';
            return true;
        }
        fld.value = '0' + "," + '0' + aux;
    }
    if (len == 2)
        fld.value = '0' + "," + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += ".";
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        fld.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
            fld.value += aux2.charAt(i);
        fld.value += "," + aux.substr(len - 2, len);
    }
    return false;
}

//     <--------------ALTERADO EM 30-07-2009 BY PAULO------------------>
var QtdBackspace = 0;
var NomeBackspace = '';
function setBack(fld) {
    if (NomeBackspace == '') {
        NomeBackspace = fld.name;
    } else
    if (NomeBackspace != fld.name) {
        NomeBackspace = fld.name;
        QtdBackspace = 0;
    }
    if ((event.keyCode == 8) || (event.keyCode == 46)) {
        var len = fld.value.length;
        if (fld.value.substr(len - 1, 1) != "/") {
            QtdBackspace++;
        }
    }
    if (QtdBackspace >= 8)
        QtdBackspace = 0;
}
function dateFormat(fld, e) {
    if (QtdBackspace > 0) {
        if (fld.value != "") {
            QtdBackspace--;
            return true;
        } else {
            QtdBackspace = 0;
        }
    }
    var key = '';
    var dia = 0;
    var mes = 0;
    var i = 0;
    var len = 0;
    var strCheck = '0123456789';
    var aux = '';
    var ctrl = false;
    var whichCode = (window.Event) ? e.which : e.keyCode;
    key = String.fromCharCode(whichCode);
    if (sltxt == fld.name) {
        sltxt = "";
        fld.value = "";
        ctrl = true;
    }
    key = String.fromCharCode(whichCode);  // Get key value from key code
    if (strCheck.indexOf(key) == -1 && whichCode != 8)
        return false;  // Not a valid key
    len = fld.value.length;
    aux = '';
    for (; i < len; i++) // retira barras da data
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    if (whichCode == 8) {
        if (aux.length > 0)
            aux = aux.substr(0, aux.length - 1);
    } else {
        aux += key;
    }
    len = aux.length;
    if (len == 0)
        fld.value = '';
    if (len == 1) {
        if (key > 3) {
            return false;
        }
        if (ctrl) {
            return true;
        }
        fld.value = aux;
    }
    if (len == 2) {
        if (aux.substr(0, 1) == 3 && key > 1) {
            return false;
        }
        if (aux.substr(0, 1) == 0 && key == 0) {
            return false;
        }
        fld.value = aux + '/';
    }
    if (len == 3) {
        if (key > 1) {
            return false;
        }
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 1);
    }
    if (len == 4) {
        if (aux.substr(2, 1) == 1 && key > 2) {
            return false;
        }
        if (aux.substr(2, 1) == 0 && key == 0) {
            return false;
        }
        dia = parseInt(aux.substr(0, 2), 10);
        mes = parseInt(aux.substr(2, 2), 10);
        if (mes == 4 || mes == 6 || mes == 9 || mes == 11) {
            if (dia == 31) {
                return false;
            }
        }
        if (mes == 2 && dia > 29) {
            return false;
        }
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/';
    }
    if (len == 5) {
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/' + aux.substr(4, 1);
    }
    if (len == 6) {
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/' + aux.substr(4, 2);
        if (aux.substr(4, 1) == 0) {
            fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/20' + aux.substr(4, 2);
        }
    }
    if (len == 7) {
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/' + aux.substr(4, 3);
    }
    if (len == 8) {
        fld.value = aux.substr(0, 2) + '/' + aux.substr(2, 2) + '/' + aux.substr(4, 4);
    }
    return false;
}
//     <----------FIM-ALTERADO EM 30-07-2009 BY PAULO------------------>

function horaFormat(fld, e) {
    var key = '';
    var hora = 0;
    var minuto = 0;
    var i = 0;
    var len = 0;
    var strCheck = '0123456789';
    var aux = '';
    var ctrl = false;
    var whichCode = (window.Event) ? e.which : e.keyCode;
    window.status = '';
    if (sltxt == fld.name) {
        sltxt = "";
        fld.value = "";
        ctrl = true;
    }
    if (whichCode == 13) {
        btn = 0;
        return false;  // Enter
    }
    key = String.fromCharCode(whichCode);  // Get key value from key code
    if (strCheck.indexOf(key) == -1 && whichCode != 8)
        return false;  // Not a valid key
    len = fld.value.length;
    aux = '';
    for (; i < len; i++) // retira : da hora
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    if (whichCode == 8) {
        if (aux.length > 0)
            aux = aux.substr(0, aux.length - 1);
    } else {
        aux += key;
    }
    len = aux.length;
    if (len == 0)
        fld.value = '';
    if (len == 1) {
        if (key > 2) {
            window.status = "hora invalida";
            return false;
        }
        if (ctrl) {
            ctrl = false;
            return true;
        }
        fld.value = aux;
    }
    if (len == 2) {
        hora = parseInt(aux, 10);
        if (hora > 23) {
            window.status = "data invalida";
            return false;
        }
        fld.value = aux + ":";
    }
    if (len == 3) {
        if (key > 5) {
            window.status = "hora invalida";
            return false;
        }
        fld.value = aux.substr(0, 2) + ':' + aux.substr(2, 1);
    }
    if (len == 4) {
        minuto = parseInt(aux.substr(2, 2), 10);
        if (minuto > 59) {
            window.status = "data invalida";
            return false;
        }
        fld.value = aux.substr(0, 2) + ':' + aux.substr(2, 2);
        proxCampo(fld);
    }
    return false;
}

function chkData(fld) {
    var dia = 0;
    var mes = 0;
    var i = 0;
    var len = 0;
    var strCheck = '0123456789';
    var aux = '';
    len = fld.value.length;
    aux = '';
    for (; i < len; i++) // retira barras da data
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    len = aux.length;
    if (len == 6)
        aux = aux.substr(0, 4) + "20" + aux.substr(6, 2);
    if (len != 8) {
        return false;
    }
    dia = parseInt(aux.substr(0, 2), 10);
    mes = parseInt(aux.substr(2, 2), 10);
    if (dia == 0) {
        return false;
    }
    if (dia > 31) {
        return false;
    }
    if (mes == 0) {
        return false;
    }
    if (mes > 12) {
        return false;
    }
    if (mes == 4 || mes == 6 || mes == 9 || mes == 11) {
        if (dia == 31) {
            return false;
        }
    }
    if (mes == 2 && dia > 29) {
        return false;
    }
    fld.value = aux.substr(0, 2) + "/" + aux.substr(2, 2) + "/" + aux.substr(4, 4);
    return true;
}

function chkHora(fld) {
    var hora = 0;
    var minuto = 0;
    var i = 0;
    var len = 0;
    var strCheck = '0123456789';
    var aux = '';
    len = fld.value.length;
    aux = '';
    for (; i < len; i++) // retira barras da data
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    len = aux.length;
    if (len != 4) {
        return false;
    }
    hora = parseInt(aux.substr(0, 2), 10);
    minuto = parseInt(aux.substr(2, 2), 10);
    if (hora > 23) {
        return false;
    }
    if (minuto > 59) {
        return false;
    }
    fld.value = aux.substr(0, 2) + ":" + aux.substr(2, 2);
    return true;
}

function cepFormat(fld, e) {
    var key = '';
    var i = 0;
    var len = 0;
    var strCheck = '0123456789';
    var aux = '';
    var ctrl = false;
    var whichCode = (window.Event) ? e.which : e.keyCode;
    window.status = '';
    if (sltxt == fld.name) {
        sltxt = "";
        fld.value = "";
        ctrl = true;
    }
    if (whichCode == 13) {
        btn = 0;
        return false;  // Enter
    }
    key = String.fromCharCode(whichCode);  // Get key value from key code
    if (strCheck.indexOf(key) == -1 && whichCode != 8)
        return false;  // Not a valid key
    len = fld.value.length;
    aux = '';
    for (; i < len; i++) // retira traco do cep
        if (strCheck.indexOf(fld.value.charAt(i)) != -1)
            aux += fld.value.charAt(i);
    if (whichCode == 8) {
        if (aux.length > 0)
            aux = aux.substr(0, aux.length - 1);
    } else {
        aux += key;
    }
    len = aux.length;
    if (len == 1) {
        if (ctrl) {
            ctrl = false;
            return true;
        }
    }
    if (len < 5)
        fld.value = aux;
    if (len == 5)
        fld.value = aux + "-";
    if (len > 5) {
        fld.value = aux.substr(0, 5) + '-' + aux.substr(5, aux.length - 5);
        if (len == 8) {
            proxCampo(fld);
        }
    }
    return false;
}

function SoNumeros(fld, e, tam) {
    var key = '';
    var i = 0;
    var len = 0;
    var ctrl = false;
    var strCheck = '0123456789';
    var whichCode = (window.Event) ? e.which : e.keyCode;
    window.status = '';
    if (sltxt == fld.name) {
        sltxt = "";
        fld.value = "";
        ctrl = true;
    }
    if (whichCode == 0)
        return true;
    if (whichCode == 13) {
        btn = 0;
        proxCampo(fld); // salta para proximo campo
        return false;  // Enter
    }
    key = String.fromCharCode(whichCode);  // Get key value from key code
    if (strCheck.indexOf(key) == -1 && whichCode != 8)
        return false;  // Not a valid key
    if (whichCode == 8) {
        if (fld.value.length > 0)
            fld.value = fld.value.substr(0, fld.value.length - 1);
    } else {
        if (ctrl) {
            return true;
        }
        if (fld.value.length < tam)
            fld.value += key;
    }
    return false;
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

function LimpaCampo(sValor, iBase) {
    var tam = sValor.length
    var saida = new String
    for (i = 0; i < tam; i++)
        if (!isNaN(parseInt(sValor.substr(i, 1), iBase)))
            saida = saida + String(sValor.substr(i, 1));
    return (saida);
}

function CNPJFormat(fld) {
    var NI;
    var conteudo = fld.value;
    NI = LimpaCampo(conteudo, 14);
    var tam = NI.length;
    if (tam > 12)
    {
        fld.value = NI.substr(0, 2) + "." + NI.substr(2, 3) + "." + NI.substr(5, 3) + "/" + NI.substr(8, 4) + "-" + NI.substr(12, 2);
        return;
    }
    if (tam > 8)
    {
        fld.value = NI.substr(0, 2) + "." + NI.substr(2, 3) + "." + NI.substr(5, 3) + "/" + NI.substr(8, 4);
        return;
    }
    if (tam > 5)
    {
        fld.value = NI.substr(0, 2) + "." + NI.substr(2, 3) + "." + NI.substr(5, 3);
        return;
    }
    if (tam > 2)
    {
        fld.value = NI.substr(0, 2) + "." + NI.substr(2, 3);
        return;
    }
}

function CPFFormat(fld) {
    var NI;
    var conteudo = fld.value;
    NI = LimpaCampo(conteudo, 11);
    var tam = NI.length;
    if (tam > 9)
    {
        fld.value = NI.substr(0, 3) + "." + NI.substr(3, 3) + "." + NI.substr(6, 3) + "-" + NI.substr(9, 2);
        return;
    }
    if (tam > 6)
    {
        fld.value = NI.substr(0, 3) + "." + NI.substr(3, 3) + "." + NI.substr(6, 3);
        return;
    }
    if (tam > 3)
    {
        fld.value = NI.substr(0, 3) + "." + NI.substr(3, 3);
        return;
    }
}

function chkCNPJ(fld)
{
    var NI;
    var iTipo = 3;
    var conteudo = fld.value;
    NI = LimpaCampo(conteudo, 10);
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
                alert('O n�mero do CNPJ informado est� incorreto');
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
                alert('O n�mero do CPF informado est� incorreto');
                fld.select();
                fld.focus();
                return(false);
            }

            if (NI.substr(9, 2) != CalcularDV(NI.substr(0, 9), 11)) {
                alert('O n�mero do CPF informado est� incorreto');
                fld.select();
                fld.focus();
                return(false);
            } else {
                fld.value = NI.substr(0, 3) + "." + NI.substr(3, 3) + "." + NI.substr(6, 3) + "-" + NI.substr(9, 2);
            }
            break;

        case 3:
            alert('O n�mero do CNPJ/CPF informado est� incorreto');
            fld.select();
            fld.focus();
            return(false);
    }
    return (true);
}

function tstDt(campo)
{
    if (campo.value != "") {
        if (!chkData(campo)) {
            alert("Data invalida");
            campo.select();
            campo.focus();
            return false;
        }
    }
}
function tstBranco(campo)
{
    if (campo.value == "" && window.event.keyCode != 20)
    {
//    alert("Preenchimento Obrigat�rio");
//    return false;
    }
    if (window.event.keyCode != 16 && window.event.keyCode != 9 && campo.value != "")
    {
        AutoSkip(campo); // manda foco para proximo campo
    }
}
function tstCNPJ(campo)
{
    var maxChars = campo.maxLength;
    var diferenca = maxChars - campo.value.length;
    if (diferenca == 0)
    {
        chkCNPJ(campo);
    }
    if (window.event.keyCode == 13)
    {
        chkCNPJ(campo);
        proxCampo(campo); // manda foco para proximo campo
    }
}

function tstHora(campo)
{
    if (campo.value != "") {
        if (!chkHora(campo)) {
            alert("Hora invalida");
            campo.select();
            campo.focus();
            return false;
        }
    }
}

// Funcao para limitar quantidade de texto

function AutoSkip(CampObj)
{
    var maxChars = CampObj.maxLength;
    var diferenca = maxChars - CampObj.value.length;
    if (diferenca == 0)
    {
        CampObj.value = CampObj.value.substring(0, maxChars);
        if (window.event.keyCode != 16 && window.event.keyCode != 9 && window.event.keyCode != 20)
        {
            proxCampo(CampObj); // manda foco para proximo campo
        }
    }
    if (window.event.keyCode == 13)
    {
        proxCampo(CampObj); // manda foco para proximo campo
    }
}

function LimiteTexto(CampObj, maxChars)
{
    var result = true;
    if (CampObj.value.length >= maxChars)
        result = false;

    if (window.event)
        window.event.returnValue = result;
    return result;
}

ie = document.all ? 1 : 0
olditem = "";
olditemclass = "";

function hi(E)
{
    if (ie)
    {
        while (E.tagName != "TR")
        {
            E = E.parentElement;
        }
    } else {
        while (E.tagName != "TR")
        {
            E = E.parentNode;
        }
    }
    if (olditem != "")
        olditem.className = olditemclass;
    olditemclass = E.className;
    E.className = "bgtr";
    olditem = E;
}

function hiover(E)
{
    if (ie)
    {
        while (E.tagName != "TR")
        {
            E = E.parentElement;
        }
    } else {
        while (E.tagName != "TR")
        {
            E = E.parentNode;
        }
    }
    if (olditem != "")
        olditem.className = olditemclass;
    olditemclass = E.className;
    E.className = "bgtrover";
    olditem = E;
}

//--> MUDAR A COR DA COLUNA DE UMA TABELA
//--> by Paulo 18-06-2009
var cel = "";
var name1 = "";
var oldname = "";
function hi1(E, nome) {
    cel = E;
    name1 = nome;
    setTimeout("hi2()", 500);
}
function hi2() {
    if (ie) {   //PROCURA A TAG CHAVE TABLE
        while (cel.tagName != "TABLE") {
            cel = cel.parentElement;
        }
    } else {
        while (cel.tagName != "TABLE")
        {
            cel = cel.parentNode;
        }
    }
    var p = cel;
    var filhos = p.childNodes;
    // PERCORRE DENTRO DA TABLE AS TAGS PROCURANDO PELA TAG TBODY
    for (i = filhos.length - 1; i >= 0; i--) {
        if (filhos[i].tagName == 'TBODY') {
            cel = filhos[i];
            break;
        }
    }
    var p = cel;
    var filhos = p.childNodes;
    // PERCORRE LINHA A LINHA (TR) DENTRO DO TBODY
    for (i = filhos.length - 1; i >= 0; i--) {
        if (filhos[i].tagName == 'TR') {
            cel = filhos[i];
            filhos2 = cel.childNodes;
            //PERCORRE TODAS AS CEDULA DENTRO DA LINHA PARA MUDAR O backgroundColor
            for (y = filhos2.length - 1; y >= 0; y--) {
                if (filhos2[y].className == name1) {
                    filhos2[y].style.backgroundColor = '#ffff00';
                }
                if (filhos2[y].className == oldname) {
                    filhos2[y].style.backgroundColor = '';
                }
            }
        }
    }
    if (oldname != name1)
        oldname = name1;
    else
        oldname = "";
}
//--> FIM MUDAR A COR DA COLUNA DE UMA TABELA



//-->
//--> INICIO CALENDARIO
// construindo o calend�rio
function popdate(obj, div, tam, ddd)
{
    pintar = ddd;
    if (ddd)
    {
        day = ""
        mmonth = ""
        ano = ""
        c = 1
        char = ""
        for (s = 0; s < parseInt(ddd.length); s++)
        {
            char = ddd.substr(s, 1)
            if (char == "/")
            {
                c++;
                s++;
                char = ddd.substr(s, 1);
            }
            if (c == 1)
                day += char
            if (c == 2)
                mmonth += char
            if (c == 3)
                ano += char
        }
        ddd = mmonth + "/" + day + "/" + ano
    }

    if (!ddd) {
        today = new Date()
    } else {
        today = new Date(ddd)
    }
    date_Form = eval(obj)
    if (date_Form.value == "") {
        date_Form = new Date()
    } else {
        date_Form = new Date(date_Form.value)
    }

    ano = today.getFullYear();
    mmonth = today.getMonth();
    day = today.toString().substr(8, 2)

    umonth = new Array("Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro")
    days_Feb = (!(ano % 4) ? 29 : 28)
    days = new Array(31, days_Feb, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31)

    if ((mmonth < 0) || (mmonth > 11))
        alert(mmonth)
    if ((mmonth - 1) == -1) {
        month_prior = 11;
        year_prior = ano - 1
    } else {
        month_prior = mmonth - 1;
        year_prior = ano
    }
    if ((mmonth + 1) == 12) {
        month_next = 0;
        year_next = ano + 1
    } else {
        month_next = mmonth + 1;
        year_next = ano
    }
    txt = "<table onMouseOut=\"javascript:tdDataClean();\" bgcolor='#efefff' style='border:solid #330099; border-width:2' cellspacing='0' cellpadding='3' border='0' width='" + tam + "' height='" + tam * 1.1 + "'>"
    txt += "<tr bgcolor='#FFFFFF'><td colspan='7' align='center'><table border='0' cellpadding='0' width='100%' bgcolor='#FFFFFF'><tr>"
    txt += "<td width=20% align=center><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + ((mmonth + 1).toString() + "/01/" + (ano - 1).toString()) + "') class='Cabecalho_Calendario' title='Ano Anterior'><<</a></td>"
    txt += "<td width=20% align=center><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + ("01/" + (month_prior + 1).toString() + "/" + year_prior.toString()) + "') class='Cabecalho_Calendario' title='M�s Anterior'><</a></td>"
    txt += "<td width=20% align=center><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + ("01/" + (month_next + 1).toString() + "/" + year_next.toString()) + "') class='Cabecalho_Calendario' title='Pr�ximo M�s'>></a></td>"
    txt += "<td width=20% align=center><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + ((mmonth + 1).toString() + "/01/" + (ano + 1).toString()) + "') class='Cabecalho_Calendario' title='Pr�ximo Ano'>>></a></td>"
    txt += "<td width=20% align=right><a href=javascript:force_close('" + div + "') class='Cabecalho_Calendario' title='Fechar Calend�rio'><b><img src='/icons/tcm/botaoFechar2.jpg'  width=20 height=20 border=0></b></a></td></tr></table></td></tr>"
    txt += "<tr><td colspan='7' align='right' bgcolor='#ccccff' class='mes'><a href=javascript:pop_year('" + obj + "','" + div + "','" + tam + "','" + (mmonth + 1) + "') class='mes'>" + ano.toString() + "</a>"
    txt += " <a href=javascript:pop_month('" + obj + "','" + div + "','" + tam + "','" + ano + "') class='mes'>" + umonth[mmonth] + "</a> <div id='popd' style='position:absolute'></div></td></tr>"
    txt += "<tr bgcolor='#330099'><td width='14%' class='dia' align=center><b>Dom</b></td><td width='14%' class='dia' align=center><b>Seg</b></td><td width='14%' class='dia' align=center><b>Ter</b></td><td width='14%' class='dia' align=center><b>Qua</b></td><td width='14%' class='dia' align=center><b>Qui</b></td><td width='14%' class='dia' align=center><b>Sex<b></td><td width='14%' class='dia' align=center><b>Sab</b></td></tr>"
    today1 = new Date((mmonth + 1).toString() + "/01/" + ano.toString());
    diainicio = today1.getDay() + 1;
    week = d = 01
    start = false;

    for (n = 1; n <= 42; n++)
    {
        if (week == 1)
            txt += "<tr bgcolor='#efefff' align=center>"
        if (week == diainicio) {
            start = true
        }
        if (d > days[mmonth]) {
            start = false
        }
        if (start)
        {
            dat = new Date((mmonth + 1).toString() + "/" + d + "/" + ano.toString())
            day_dat = dat.toString().substr(0, 10)
            day_today = date_Form.toString().substr(0, 10)
            year_dat = dat.getFullYear()
            year_today = date_Form.getFullYear()
            colorcell = ((day_dat == day_today) && (year_dat == year_today) ? " bgcolor='#FFCC00' " : "")
            dmascara = d;
            mmascara = (mmonth + 1);
            if (d < 10)
            {
                dmascara = "0" + d;
            }
            if (mmascara < 10)
            {
                mmascara = "0" + mmascara;
            }
            escolhido = dmascara + "/" + (mmascara).toString() + "/" + ano.toString();
            if (pintar == escolhido)
                colorcell = " bgcolor='#FFCC00' ";
            //txt += "<td"+colorcell+" align=center><a href=javascript:block('" + dmascara + "/" + (mmascara).toString() + "/" + ano.toString() +"','"+ obj +"','" + div +"') class='data'>"+ d.toString() + "</a></td>"
            txt += "<td" + colorcell + " align=center onMouseOver=\"tdData(this)\" class='data' onClick=\"block('" + dmascara + "/" + (mmascara).toString() + "/" + ano.toString() + "','" + obj + "','" + div + "');\">" + d.toString() + "</td>"
            d++
        }
        else
        {
            txt += "<td class='data' align=center> </td>"
        }
        week++
        if (week == 8)
        {
            week = 1;
            txt += "</tr>"
        }
    }
    txt += "</table>"
    div2 = eval(div)
    div2.innerHTML = txt
}
tdold = "";
tdoldclass = "";
function tdData(E)
{
    if (ie) {
        while (E.tagName != "TD")
        {
            E = E.parentElement;
        }
    } else {
        while (E.tagName != "TD")
        {
            E = E.parentNode;
        }
    }
    if (tdold != "")
        tdold.className = tdoldclass;
    tdoldclass = E.className;
    E.className = "data2";
    tdold = E;
}
function tdDataClean() {
    if (tdold != "") {
        tdold.className = tdoldclass;
        tdold = "";
    }
}
// fun��o para exibir a janela com os meses
function pop_month(obj, div, tam, ano)
{
    txt = "<table class=tb0 border='0' width=80>"
    for (n = 0; n < 12; n++) {
        txt += "<tr><td class=td1 align=center><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + ("01/" + (n + 1).toString() + "/" + ano.toString()) + "')>" + umonth[n] + "</a></td></tr>"
    }
    txt += "</table>"
    popd.innerHTML = txt
}

// fun��o para exibir a janela com os anos
function pop_year(obj, div, tam, umonth)
{
    txt = "<table border='0' class=tb0 width=60>"
    l = 1
    for (n = 1999; n < 2015; n++)
    {
        if (l == 1)
            txt += "<tr>"
        txt += "<td align=center class=td1><a href=javascript:popdate('" + obj + "','" + div + "','" + tam + "','" + (umonth.toString() + "/01/" + n) + "')>" + n + "</a></td>"
        l++
        if (l == 2)
        {
            txt += "</tr>";
            l = 1
        }
    }
    txt += "</tr></table>"
    popd.innerHTML = txt
}

// fun��o para fechar o calend�rio
function force_close(div)
{
    div2 = eval(div);
    div2.innerHTML = ''
}

// fun��o para fechar o calend�rio e setar a data no campo de data associado
function block(data, obj, div)
{
    force_close(div)
    obj2 = eval(obj)
    obj2.value = data
}

//--> FIM DO CALENDARIO


//--> INICIO DAS FUN�OES PARA CARREGAR AS SELECTS COM UM VETOR

function matriz() {
    return new matriz2();
}

function matriz2() {
    args = matriz2.caller.arguments;
    l = args.length;
    m = parseInt(l / 2);
    p = new Array();
    v = new Array();
    for (i = 0; i < l; i++) {
        if (i < m) {
            p[i] = args[i];
        } else {
            j = (i - m);
            v[j] = args[i];
        }
    }
    for (i = 0; i < m; i++) {
        this[p[i]] = v[i];
    }
}

function carregaSelect(vet, sel, vlr) {
    selec = document.getElementById(sel);
    for (i = 0; i < eval(vet).length; i++) {
        selec.options[i] = new Option(eval(vet)[i].exi, eval(vet)[i].cod);
    }
    if (vlr == "") {
        selec.selectedIndex = 0;
    } else {
        selec.value = vlr;
    }
}

//--> FIM SELECT VETOR
function exibe_oculta(wobj, seta)
{
    if (document.getElementById(wobj).className == 'oculto') {
        document.getElementById(wobj).className = 'visivel';
        document.getElementById(seta).className = 'setafecha';
    }
    else {
        document.getElementById(wobj).className = 'oculto';
        document.getElementById(seta).className = 'setaabre';
    }
}

function exibe_oculta_sinal(wobj, seta)
{
    if (document.getElementById(wobj).className == 'oculto') {
        document.getElementById(wobj).className = 'visivel';
        document.getElementById(seta).className = 'sinalmenos';
    }
    else {
        document.getElementById(wobj).className = 'oculto';
        document.getElementById(seta).className = 'sinalmais';
    }
}

function exibe_oculta_iapad(wobj, seta)
{
    document.getElementById(wobj).className = seta;
}

function atualizarDataHora()
{
    dataAtual = new Date();
    dia = dataAtual.getDate();
    diaSemana = getDiaExtenso(dataAtual.getDay());
    mes = getMesExtenso(dataAtual.getMonth());
    ano = dataAtual.getYear();
    hora = dataAtual.getHours();
    minuto = dataAtual.getMinutes();
    if (hora < 10) {
        hora = "0" + hora;
    }
    if (minuto < 10) {
        minuto = "0" + minuto;
    }
    horaImprime = hora + ":" + minuto + "hs";
    mostrarDataHora(dia, mes, ano, horaImprime, diaSemana);
}
function mostrarDataHora(dia, mes, ano, hora, diaSemana)
{
    retornodatahora = +dia + " de " + mes + " de " + ano + " " + hora + " " + diaSemana;

    document.getElementById("SidDataHoraSistema").innerHTML = retornodatahora;
}
function getMesExtenso(mes)
{
    return this.arrayMes[mes];
}
function getDiaExtenso(dia)
{
    return this.arrayDia[dia];
}
function construirArray(qtdElementos)
{
    this.length = qtdElementos
}
var arrayDia = new construirArray(7);
arrayDia[0] = "Domingo";
arrayDia[1] = "Segunda-Feira";
arrayDia[2] = "Ter�a-Feira";
arrayDia[3] = "Quarta-Feira";
arrayDia[4] = "Quinta-Feira";
arrayDia[5] = "Sexta-Feira";
arrayDia[6] = "S�bado";

var arrayMes = new construirArray(12);
arrayMes[0] = "Janeiro";
arrayMes[1] = "Fevereiro";
arrayMes[2] = "Mar�o";
arrayMes[3] = "Abril";
arrayMes[4] = "Maio";
arrayMes[5] = "Junho";
arrayMes[6] = "Julho";
arrayMes[7] = "Agosto";
arrayMes[8] = "Setembro";
arrayMes[9] = "Outubro";
arrayMes[10] = "Novembro";
arrayMes[11] = "Dezembro";

function cleanInputsForm(ind) {
    if (ind == null)
        ind = 0;
    FORM = document.getElementsByTagName('FORM')[ind]
    for (var i = 0; i < FORM.elements.length; i++) {
        var obj = FORM.elements[i];
        cleanInput(obj);
    }
}
function cleanInput(obj) {
    if (!isObject(obj))
        obj = document.getElementById(obj);
    switch (obj.type) {
        case "radio":
        case "checkbox":
            obj.checked = false;
            break;
        case "select":
            obj.selectedIndex = 0;
            break;
        case "hidden":
        case "button":
            obj.value = obj.value;
            break;
        default :
            obj.value = "";
            break;
    }
}
//Fun��o para saber se o parametro � objeto
function isObject(what) {
    return (typeof what == 'object');
}
