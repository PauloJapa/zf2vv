/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


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