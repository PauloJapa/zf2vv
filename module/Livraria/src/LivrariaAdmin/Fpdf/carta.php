<?php

namespace LivrariaAdmin\Fpdf;


class PDF extends FPDF
{
    var $B;
    var $I;
    var $U;
    var $HREF;
    
    function PDF($orientation='P', $unit='mm', $size='A4')
    {
        // Call parent constructor
        $this->FPDF($orientation,$unit,$size);
        // Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
    }
    
    function WriteHTML($html)
    {
        // HTML parser
        $html = str_replace("\n",' ',$html);
        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                // Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,$e);
            }
            else
            {
                // Tag
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    // Extract attributes
                    $a2 = explode(' ',$e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }
    
    function OpenTag($tag, $attr)
    {
        // Opening tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF = $attr['HREF'];
        if($tag=='BR')
            $this->Ln(5);
    }
    
    function CloseTag($tag)
    {
        // Closing tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF = '';
    }
    
    function SetStyle($tag, $enable)
    {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s)
        {
            if($this->$s>0)
                $style .= $s;
        }
        $this->SetFont('',$style);
    }
    
    function PutLink($URL, $txt)
    {
        // Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
    
    // Better table
    function ImprovedTable($header, $data)
    {
        // Column widths
        $w = array(90, 31, 31, 31, 45, 31);
        // Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        // Data
        foreach($data as $row)
        {
            switch($row[6]){
            case 'T':
                $this->SetStyle('B',true);
                $this->SetStyle('U',true);
                $this->Cell($w[0],5,$row[0],'LR');
                $this->SetStyle('B',false);
                $this->SetStyle('U',false);
                $this->Cell($w[1],5,'','LR',0,'R');
                $this->Cell($w[2],5,'','LR',0,'R');
                $this->Cell($w[3],5,'','LR',0,'R');
                $this->Cell($w[4],5,'','LR',0,'R');
                $this->Cell($w[5],5,'','LR',0,'R');
                $this->Ln();
            break;    
            case 'D':
                $this->Cell($w[0],5,$row[0],'LR');
                $this->SetFillColor(191,191,191);
                $color = array();
                foreach($row as $vlr){
	                if($vlr == ""){
		                $color[] = true ;
	                }else{
		                $color[] = false ;
	                }
                }
                $this->Cell($w[1],5,number_format($row[1],1,'.',','),'LR',0,'R',$color[1]);
                $this->Cell($w[2],5,number_format($row[2],1,'.',','),'LR',0,'R',$color[2]);
                $this->Cell($w[3],5,number_format($row[3],1,'.',','),'LR',0,'R',$color[3]);
                $this->Cell($w[4],5,number_format($row[4],1,'.',','),'LR',0,'R',$color[4]);
                $this->Cell($w[5],5,number_format($row[5],1,'.',','),'LR',0,'R',$color[5]);
                $this->Ln();
            break;  
            default :   
                $this->Cell($w[0],5,$row[0],'LR');
                $this->SetFillColor(191,191,191);
                $color = array();
                foreach($row as $vlr){
	                if($vlr == ""){
		                $color[] = true ;
	                }else{
		                $color[] = false ;
	                }
                }                
                $this->Cell($w[1],5,$row[1],'LR',0,'R',$color[1]);
                $this->Cell($w[2],5,$row[2],'LR',0,'R',$color[2]);
                $this->Cell($w[3],5,$row[3],'LR',0,'R',$color[3]);
                $this->Cell($w[4],5,$row[4],'LR',0,'R',$color[4]);
                $this->Cell($w[5],5,$row[5],'LR',0,'R',$color[5]);
                $this->Ln();
            }
        }
        // Closing line
        $this->Cell(array_sum($w),0,'','T');
    }   
    
}   
//função que formata a data
function formata_data($data){
    //recebe o parâmetro e armazena em um array separado por -
    $data = explode('-', $data);
    //armazena na variavel data os valores do vetor data e concatena /
    $data = $data[2].'/'.$data[1].'/'.$data[0];
    
    //retorna a string da ordem correta, formatada
    return $data;
}
function consultar($array,$conexao1){
    if(isset($array[0]))$tabel = $array[0] ; else return false ;
    if(isset($array[1]))$camps = $array[1] ; else $camps = "*";
    if(isset($array[2]))$where = $array[2] ; else $where = "";
    if(isset($array[3]))$order = $array[3] ; else $order = "";
    if(isset($array[4]))$opt   = $array[4] ; else $opt   = "";
    $query  = "select " . $camps . " from " . $tabel . " where " . $where . " " . $order . " " . $opt;
    //echo "<p>" . $query . "</p>" ;
    $select = $conexao1->sql(utf8_encode($query));
    if($select == false){
    echo "<p>" . $query . "</p>" ;
        return false ;
    }else{
        $reg = mysql_fetch_array($select, MYSQL_NUM);
        return $reg ;
    }
}

    define("FPDF_FONTPATH", "font/");
   
    $conex = new ClassConexaoMysql();
    $arr_meses = array(
        '01' => 'Janeiro',
        '02' => 'Fevereiro',
        '03' => 'Março',
        '04' => 'Abril',
        '05' => 'Maio',
        '06' => 'Junho',
        '07' => 'Julho',
        '08' => 'Agosto',
        '09' => 'Setembro',
        '10' => 'Outubro',
        '11' => 'Novembro',
        '12' => 'Dezembro'
    );    
    //PEGAR A DATA DO ULTIMO ULPLOAD
    $busca = consultar(array('LogEventos', "`Data`", "Data IS NOT NULL", "ORDER BY Data ASC ", "LIMIT 1" ),$conex);
    if($busca){ 
        //$data = date("d/m/Y H:i:s", strtotime($busca[0]));
        $data = date("d/m/Y", strtotime($busca[0]));
        $arr_data = explode('/', $data);
    }   
    
    
    if(isset($_POST['SubOpcao' ])) $SubOpcao  = $_POST['SubOpcao' ]; else if(isset($_GET['SubOpcao' ])) $SubOpcao  = $_GET['SubOpcao' ] ; else $SubOpcao  = NULL ;
    if(isset($_POST['SelMuncip'])) $SelMuncip = $_POST['SelMuncip']; else if(isset($_GET['SelMuncip'])) $SelMuncip = $_GET['SelMuncip'] ; else $SelMuncip = "" ;
    if(isset($_POST['SelAnoCon'])) $SelAnoCon = $_POST['SelAnoCon']; else if(isset($_GET['SelAnoCon'])) $SelAnoCon = $_GET['SelAnoCon'] ; else $SelAnoCon = "" ;
    if(isset($_POST['HidTipCat'])) $HidTipCat = $_POST['HidTipCat']; else if(isset($_GET['HidTipCat'])) $HidTipCat = $_GET['HidTipCat'] ; else $HidTipCat = "" ;
    
    $AnoAnteri = (int)$SelAnoCon - 1 ;
    $Exibir = array();    
    $Exibir['vazio']                      = "<div style=\"background-color:rgb(191,191,191);width:95%;\">&nbsp;</div>";
    $Exibir['vazio2']                     = "";
    
    $Campos = array();
    $Campos[] = 'TxIncidenciaTb' . $AnoAnteri;          //Buscar taxa de incidencia por 100mil hab. ano anterior
    $Campos[] = 'TotalCasosNovos2001a05'     ;          
    $Campos[] = 'TotalCasosNovos2006a10'     ;          
    $Campos[] = 'CasosNovosTb2011'           ;          
    $Campos[] = 'CasosNovosTb2012'           ;          
    $Campos[] = 'TxIncidenciaTbMedia2001a05' ;          
    $Campos[] = 'TxIncidenciaTbMedia2006a10' ;          
    $Campos[] = 'TxIncidenciaTb2011'         ;          
    $Campos[] = 'TxIncidenciaTb2012'         ;          
    $Campos[] = 'CasosNovosTb' . $SelAnoCon  ;          
    $Campos = "`" . implode("`,`", $Campos) . "`" ;
    $where =  "`" . $HidTipCat . "`=\"" . $SelMuncip . "\" AND TIPO=\"" . $HidTipCat . "\"";
    $busca = consultar(array('Incidencia', $Campos, $where ),$conex);
    if($busca){        
        $Exibir['TxIncidAnoAnterior']         = $busca[0]; 
        if($busca[0] <= 29 ){
            $Exibir['ClaTxIncidAnoAnterior'] = "Baixa" ;
        }elseif (($busca[0] > 29 )&&($busca[0] <= 49 )){ 
            $Exibir['ClaTxIncidAnoAnterior'] = "Média" ;
        }else{
            $Exibir['ClaTxIncidAnoAnterior'] = "Alta" ;         
        }
        $Exibir['TotalCasosNovos2001a05']     = $busca[1];  
        $Exibir['TotalCasosNovos2006a10']     = $busca[2];  
        $Exibir['CasosNovosTb2011']           = $busca[3];  
        $Exibir['CasosNovosTb2012']           = $busca[4];  
        $Exibir['TxIncidenciaTbMedia2001a05'] = $busca[5];  
        $Exibir['TxIncidenciaTbMedia2006a10'] = $busca[6];  
        $Exibir['TxIncidenciaTb2011']         = $busca[7];  
        $Exibir['TxIncidenciaTb2012']         = $busca[8];  
        $Exibir['CasosNovosAnoCoorte']        = $busca[9];  
    }
    
    $Campos = array();
    $Campos[] = 'TxIncidenciaTb' . $AnoAnteri;          //Buscar (campo TxIncidAnoAnterior referente ao total do Estado)
    $Campos[] = 'CasosNovosTb' . $AnoAnteri;          //Buscar (campo referente ao total do Estado)
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $busca = consultar(array('Incidencia',$Campos,"TIPO='ESTADO'"),$conex);
    if($busca){        
        $Exibir['TxIncidAnoAnteriorTOT']     =       $busca[0];   
        $Exibir['CasosNovosTbAnoAnteriorUF'] =       $busca[1];   
    }      

    
    $Campos = array();
    $Campos[] = 'Cura2011'                 ;         
    $Campos[] = 'Cura2012'                 ;         
    $Campos[] = 'Abandono2011'             ;         
    $Campos[] = 'Abandono2012'             ;         
    $Campos[] = 'Obitos2011'               ;         
    $Campos[] = 'Obitos2012'               ;         
    $Campos[] = 'Outros2011'               ;         
    $Campos[] = 'Outros2012'               ;         
    $Campos[] = 'NaoEncerr2011'            ;         
    $Campos[] = 'NaoEncerr2012'            ;         
    $Campos[] = 'PercCuraMedia2001a05'     ;         
    $Campos[] = 'Cura' . $SelAnoCon        ;         
    $Campos[] = 'PercentCura' . $SelAnoCon ;         
    $Campos[] = 'Abandono' . $SelAnoCon    ;         
    $Campos[] = 'PercentAband' . $SelAnoCon;         
    $Campos[] = 'CasosNovos' . $AnoAnteri  ;            
    $Campos[] = 'CasosNovos' . $SelAnoCon  ;            
    $Campos[] = 'PercCuraMedia2001a05'     ;            
    $Campos[] = 'PercCuraMedia2006a10'     ;            
    $Campos[] = 'Cura' . $AnoAnteri        ;         
    $Campos[] = 'PercentCura' . $AnoAnteri ;         
    $Campos[] = 'Abandono' . $AnoAnteri    ;         
    $Campos[] = 'PercentAband' . $AnoAnteri;         
    $Campos[] = 'PercentTDO2011'           ;         
    $Campos[] = 'PercentTDO2012'           ;         
    $Campos[] = 'ClassifTrat' . $AnoAnteri ;         
    $Campos[] = 'Periodo de Referencia'    ;         
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $where =  "`" . $HidTipCat . "`=\"" . $SelMuncip . "\" AND TIPO=\"" . $HidTipCat . "\"";
    $busca = consultar(array('TratamentoCasosNovos',$Campos, $where),$conex);
    if($busca){        
        $Exibir['Cura2011']                    =     $busca[0];     
        $Exibir['Cura2012']                    =     $busca[1];     
        $Exibir['Abandono2011']                =     $busca[2];     
        $Exibir['Abandono2012']                =     $busca[3];     
        $Exibir['Obitos2011']                  =     $busca[4];     
        $Exibir['Obitos2012']                  =     $busca[5];     
        $Exibir['Outros2011']                  =     $busca[6];     
        $Exibir['Outros2012']                  =     $busca[7];     
        $Exibir['NaoEncerr2011']               =     $busca[8];     
        $Exibir['NaoEncerr2012']               =     $busca[9];  
        if($busca[10] <= 74 ){
            $Exibir['ClaPercCuraMedia2001a05'] = "Baixa" ;  
        }elseif(($busca[10] > 74 )&&($busca[10] <= 84 )){ 
            $Exibir['ClaPercCuraMedia2001a05'] = "Média" ;  
        }else{ 
            $Exibir['ClaPercCuraMedia2001a05'] = "Alta" ;
        }   
        $Exibir['NumCurasAnoCoorte']           =     $busca[11];  
        $Exibir['PercCurasAnoCoorte']          =     $busca[12];  
        $Exibir['NumAbandAnoCoorte']           =     $busca[13];  
        $Exibir['PercAbandAnoCoorte']          =     $busca[14];  
        $Exibir['CasosNovosAnoAnterior']       =     $busca[15]; 
        $Exibir['CasosNovosAnoAtual']          =     $busca[16];
        $Exibir['PercCuraMedia2001a05']        =     $busca[17]; 
        $Exibir['PercCuraMedia2006a10']        =     $busca[18];
        $Exibir['CuraAnoAnterior']             =     $busca[19];
        $Exibir['PercentCuraAnterior']         =     $busca[20];
        $Exibir['AbandonoAnterior']            =     $busca[21];
        if($busca[22] < 75){
	        $Exibir['ClaPercCura'] = "Para aumentar o sucesso no tratamento, recomenda-se analisar os casos que não foram encerrados por cura. Uma primeira providência é verificar se as informações no sistema TBweb sobre o desfecho dos casos foram registradas em tempo oportuno. Se há alto percentual de óbitos, a causa provavelmente é a descoberta tardia dos casos. Se o problema for abandono de tratamento, será preciso investir mais no tratamento diretamente observado" ;   
        }else if($busca[21] < 85){
	        $Exibir['ClaPercCura'] = "Neste local, para atingir 85% de casos encerrados com sucesso, será preciso verificar em que grupos não houve a cura, estruturando ações específicas para eles e garantindo o tratamento supervisionado de todos os casos descobertos. " ;
        }else{
	        $Exibir['ClaPercCura'] = "Para manter os resultados alcançados, recomenda-se buscar sua sustentabilidade, por meio de apoio institucional e divulgação das ações realizadas, de forma a evitar o abandono de tratamento." ;
        }     
        $Exibir['PercAbandonoAnterior']        =     $busca[22];
        $Exibir['PercentTDOInd2011']           =     $busca[23];
        $Exibir['PercentTDOInd2012']           =     $busca[24];
        $Exibir['ClassifTratAnterior']         =     $busca[25];
        $Exibir['Periodo de Referencia']       =     $busca[26];  
    } 
    
    $Campos = array();
    $Campos[] = 'PercentCura' . $AnoAnteri;      //Buscar (campo PercCuraAnoAnterior referente ao total do Estado)
    $Campos[] = 'PercentAband' . $AnoAnteri;     //Buscar (campo PercAbandAnoAnterior  referente ao total do Estado)
    $Campos[] = 'PercentObitos' . $AnoAnteri;    //Buscar (campo PercentObitos  referente ao total do Estado)
    $Campos[] = 'PercentOutros' . $AnoAnteri;    //Buscar (campo PercentOutros  referente ao total do Estado)
    $Campos[] = 'PercentNaoEncerr' . $AnoAnteri; //Buscar (campo PercentNaoEncerr  referente ao total do Estado)
    $Campos[] = 'PercentTDO' . $AnoAnteri;       //Buscar (campo PercentTDOInd  referente ao total do Estado)
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $busca = consultar(array('TratamentoCasosNovos',$Campos,"TIPO='ESTADO'"),$conex);
    if($busca){        
        $Exibir['PercCuraAnoAnteriorTOT']     =     $busca[0]; 
        $Exibir['PercAbandAnoAnteriorTOT']    =     $busca[1];    
        $Exibir['PercentObitosAnoAnteriorUF'] =     $busca[2]; 
        $Exibir['PercentOutros']              =     $busca[3]; 
        $Exibir['PercentNaoEncerrAnoAnteriorUF'] =  $busca[4];    
        $Exibir['PercentTDOIndAnoAnteriorUF'] =     $busca[5];    
    }  
    
    $Campos = array();
    $Campos[] = 'TotalObitos2001a05';                 //Buscar Mortalidade Media 2001-2005
    $Campos[] = 'TotalObitos2006a10';                 //Buscar Mortalidade Media 2006-2010
    $Campos[] = 'ObitosTb2011';                       //Buscar Mortalidade Media 2011
    $Campos[] = 'ObitosTb2012';                       //Buscar Mortalidade Media 2012
    $Campos[] = 'TxMortalidMedia2001a05';             //Buscar Mortalidade tax 2001-2005
    $Campos[] = 'TxMortalidMedia2006a10';             //Buscar Mortalidade tax 2006-2010
    $Campos[] = 'TxMortalid2011';                     //Buscar Mortalidade tax 2011
    $Campos[] = 'TxMortalid2012';                     //Buscar Mortalidade tax 2012
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $where =  "`" . $HidTipCat . "`=\"" . $SelMuncip . "\" AND TIPO=\"" . $HidTipCat . "\"";
    $busca = consultar(array('Mortalidade', $Campos, $where),$conex);
    if($busca){        
        $Exibir['TotalObitos2001a05']          =      $busca[0];     
        $Exibir['TotalObitos2006a10']          =      $busca[1];     
        $Exibir['ObitosTb2011']                =      $busca[2];     
        $Exibir['ObitosTb2012']                =      $busca[3];     
        $Exibir['TxMortalidMedia2001a05']      =      $busca[4];     
        $Exibir['TxMortalidMedia2006a10']      =      $busca[5];     
        $Exibir['TxMortalid2011']              =      $busca[6];     
        $Exibir['TxMortalid2012']              =      $busca[7];     
    }   
    
    $Campos = array();
    $Campos[] = 'ObitosTb' . $AnoAnteri;                //Buscar (campo referente ao total do Estado)
    $Campos[] = 'TxMortalid' . $AnoAnteri;                //Buscar (campo referente ao total do Estado)
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $busca = consultar(array('Mortalidade',$Campos,"TIPO='ESTADO'"),$conex);
    if($busca){        
        $Exibir['ObitosTbAnoAnteriorUF']   =      $busca[0]; 
        $Exibir['TxMortalidAnoAnteriorUF'] =      $busca[1]; 
    }  
        
    $Campos = array();
    $Campos[] = 'PercentMeta2003a05'  ;         
    $Campos[] = 'PercentMeta2006a10'  ;         
    $Campos[] = 'PercentMeta2011'          ;         
    $Campos[] = 'PercentMeta2012'          ;         
    $Campos[] = 'SrExaminadosMedia2003a05' ;         
    $Campos[] = 'SrExaminadosMedia2006a10' ;         
    $Campos[] = 'SrExaminados2011'         ;         
    $Campos[] = 'SrExaminados2012'         ;         
    $Campos[] = 'SrEstimados' . $AnoAnteri ;         
    $Campos[] = 'SrExaminados' . $AnoAnteri;         
    $Campos[] = 'PercentMeta' . $AnoAnteri ;         
    $Campos[] = 'SrExaminados' . $SelAnoCon;         
    $Campos[] = 'PercentMeta'  . $SelAnoCon;         
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $where =  "`" . $HidTipCat . "`=\"" . $SelMuncip . "\" AND TIPO=\"" . $HidTipCat . "\"";
    $busca = consultar(array('Busca', $Campos, $where),$conex);
    if($busca){        
        $Exibir['SrEstimadosMedia2003a05']     =     $busca[0];     
        $Exibir['SrEstimadosMedia2006a10']     =     $busca[1];     
        $Exibir['SrEstimados2011']             =     $busca[2];     
        $Exibir['SrEstimados2012']             =     $busca[3];     
        $Exibir['SrExaminadosMedia2003a05']    =     $busca[4];     
        $Exibir['SrExaminadosMedia2006a10']    =     $busca[5];     
        $Exibir['SrExaminados2011']            =     $busca[6];     
        $Exibir['SrExaminados2012']            =     $busca[7];     
        $Exibir['MetaSrAnoAnterior']           =     $busca[8];     
        $Exibir['SR_AnoAnterior']              =     $busca[9];     
        $Exibir['PercMetaSR_AnoAnterior']      =     $busca[10];     
        $Exibir['SR_AnoAtual']                 =     $busca[11];
        if($busca[12] >= 70){
	        $Exibir['ClaPercMetaSr'] = "Para manter os resultados alcançados é necessário que os  funcionários estejam atentos e motivados para busca ativa de tossidores nos serviços de saúde e instituições de longa permanência (asilos, albergues, prisões) e, onde há equipes de saúde da família, organizar também a busca de sintomáticos casa a casa. " ;   
        }else{
	        $Exibir['ClaPercMetaSr'] = "Neste local, é necessário intensificar a busca ativa de casos nos serviços de saúde e instituições de longa permanência (asilos, albergues, prisões), perguntando a cada usuário sobre a presença de tosse por mais de duas a três semanas. Onde há equipes de saúde da família, deve-se também realizar essa procura casa a casa. " ;
        }     
    } 
    $Campos = array();
    $Campos[] = 'PercentMeta' . $AnoAnteri;                //Buscar (campo SrExaminadosAnterior referente ao total do Estado)
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $busca = consultar(array('Busca',$Campos,"TIPO='ESTADO'"),$conex);
    if($busca){        
        $Exibir['SR_AnoAnteriroEstado'] =      $busca[0]; 
    }  
    
    $Campos = array();
    $Campos[] = 'PercNovosBkposCuraMedia2001a05'         ;              
    $Campos[] = 'PercNovosBkposCuraMedia2006a10'         ;              
    //$Campos[] = ; 
    $Campos = "`" . implode("`,`", $Campos) . "`" ;    
    $where =  "`" . $HidTipCat . "`=\"" . $SelMuncip . "\" AND TIPO=\"" . $HidTipCat . "\"";
    $busca = consultar(array('TratamentoCasosNovosBK', $Campos, $where ),$conex);
    if($busca){        
        $calculaTax = $busca[1] - $busca[0];
        if ($calculaTax <= 2){ 
            $Exibir['PercCuraMedia2001a05_2006a10'] = "est&aacute; relativamente est&aacute;vel";
        }elseif ($busca[0] > $busca[1]){
            $Exibir['PercCuraMedia2001a05_2006a10'] = "diminuiu";
        }else{
            $Exibir['PercCuraMedia2001a05_2006a10'] = "aumentou";
        }
    }
    //SE NULO É PARA FAZER CARTA EM HTML SE NÃO FAZ EM PDF
    if($SubOpcao == null){ 
        goto fim_php ;
    }  
    
    $pdf = new PDF();   
    $pdf->Open();   
    $pdf->AddPage();    
    $pdf->SetFont("Arial", "", 10 );    
    $texto = "Relatório - resumo dos indicadores de Tuberculose" ;
    $pdf->Cell(60, 8, $texto,'' , 0 , 'L');
    $pdf->Ln();
        
    $texto = "Dados atualizados em " . $arr_meses[$arr_data[1]] . "/" . $arr_data[2] . "." ;
    $pdf->SetFont("Arial", "B", 12 );   
    $pdf->SetTextColor(220,50,50);  
    $pdf->Cell(60, 8, $texto,'' , 0 , 'L');
    $pdf->Ln();
    $pdf->Ln();
    
    $pdf->SetFont("Arial", "", 10 );
    $pdf->SetTextColor(0);  

    $texto = "Local: <b>(" . $HidTipCat . ": " . $SelMuncip . ")</b><br><br><br>" ;
    $pdf->SetFont("Arial", "", 10 );
    $pdf->WriteHTML($texto);
        
    $texto  = "Neste local, no ano de " . $AnoAnteri . ", foram notificados ";
    $texto .= $Exibir['CasosNovosAnoAnterior'] . " casos novos de tuberculose, o que corresponde a uma taxa de incidência de " ;
    $texto .= $Exibir['TxIncidAnoAnterior'] . " casos por 100 mil habitantes. Neste mesmo ano, a taxa de incidência do Estado de SP foi de " ; 
    $texto .= $Exibir['TxIncidAnoAnteriorTOT'] . " casos por 100 mil habitantes. De " ;
    $texto .= $Exibir['Periodo de Referencia'] . " foram notificados " ;
    $texto .= $Exibir['CasosNovosAnoAtual'] . " casos novos.<br><br>" ;
    $pdf->WriteHTML($texto);
        
    $texto  = "Estima-se que, ao longo do ano, 1% da população apresente tosse por mais de 3 semanas." ; 
    $texto .= "Essa é a meta de Sintomáticos Respiratórios (SR) a serem investigados para tuberculose." ; 
    $texto .= "Para este local, cerca de " ;
    $texto .= $Exibir['MetaSrAnoAnterior'] . " SR deveriam realizar baciloscopia de escarro no ano." ; 
    $texto .= "Em  " . $AnoAnteri . ",  foram examinados " ;
    $texto .= $Exibir['SR_AnoAnterior'] . ", o que corresponde a " ;
    $texto .= $Exibir['PercMetaSR_AnoAnterior'] . " % da meta, sendo que o Estado de SP atingiu " ; 
    $texto .= $Exibir['SR_AnoAnteriroEstado'] . " %.  De " ;
    $texto .= $SelAnoCon . " foram registrados " ;
    $texto .= $Exibir['SR_AnoAtual'] . " SR.<br /><br />" ;
    $pdf->WriteHTML($texto);
        
    $texto  = "O número de SR examinados é extraído das informações provenientes dos laboratórios no sistema " ;
    $texto .= "LAB-TB, que registram as baciloscopias de diagnóstico." ;
    $texto .= $Exibir['ClaPercMetaSr'] . "<br /><br />" ;
    $pdf->WriteHTML($texto);
    
    $texto  = "Um dos principais indicadores de desempenho no programa de Tuberculose é a taxa de cura. " ; 
    $texto .= "A meta é curar pelo menos 85% dos casos novos diagnosticados.<br /><br />" ;
    $pdf->WriteHTML($texto);
    
    $texto  = "Neste local - " . $HidTipCat . " - " . $SelMuncip . " - a taxa média de cura dos " ;
    $texto .= "casos novos de tuberculose que iniciaram tratamento entre 2006 e 2010 foi de " ;
    $texto .= $Exibir['PercCuraMedia2006a10'] . " %.  Em relação ao período de 2001 a 2005 (" ;
    $texto .= $Exibir['PercCuraMedia2001a05'] . " %), percebe-se que a taxa de cura " ;
    $texto .= $Exibir['PercCuraMedia2001a05_2006a10'] . ".<br /><br />" ; 
    $pdf->WriteHTML($texto);
    
    $texto .= "Dos " . $Exibir['CasosNovosAnoAnterior'] . " casos novos com início de tratamento em " ;
    $texto .= $AnoAnteri . ", foram registradas " ;
    $texto .= $Exibir['CuraAnoAnterior'] . " curas (" ;
    $texto .= $Exibir['PercentCuraAnterior'] . " %), que pode ser considerada " ;
    $texto .= $Exibir['ClassifTratAnterior'] . " em relação à meta. " ;
    $texto .= $Exibir['ClaPercCura'] . "<br /><br />" ;
    $pdf->WriteHTML($texto);
    
    $texto  = "O número de abandonos foi de " ;
    $texto .= $Exibir['AbandonoAnterior'] . " (" ;
    $texto .= $Exibir['PercAbandonoAnterior'] . " %). Considera-se aceitável um percentual máximo de 5% de abandonos de tratamento. Em " ;
    $texto .= $AnoAnteri . ",  a taxa de cura dos casos novos  no Estado foi de " ;
    $texto .= $Exibir['PercCuraAnoAnteriorTOT'] . " %, com percentual de abandonos de " ;
    $texto .= $Exibir['PercAbandAnoAnteriorTOT'] . "%." ; 
    $pdf->WriteHTML($texto);
        
    $pdf->addPage( 'L' );   
    
    $texto = "Indicadores de tuberculose" ;
    $pdf->SetFont("Arial", "B", 14 );   
    $pdf->Cell(0, 8, $texto,'' , 0 , 'C');
    $pdf->Ln();
    $pdf->Ln();
    
    // Column headings
    $header = array('', 'Média 2001-2005*', 'Média 2006-2010', 'Ano 2011', 'De ' . $Exibir['Periodo de Referencia'] , 'Estado ' . $AnoAnteri );
    // Data loading
    $data = array() ;
    $data[] = array('Mortalidade'                        ,''                                    ,''                                    ,''                                     ,''                                     ,''                                       ,'T') ;
    $data[] = array('Número de óbitos'                   ,$Exibir['TotalObitos2001a05']         ,$Exibir['TotalObitos2006a10']         ,$Exibir['ObitosTb2011']                ,$Exibir['ObitosTb2012']                ,$Exibir['ObitosTb2012']                  ,'' ) ;
    $data[] = array('Taxa de mortalidade(por 100 000 hab.)',$Exibir['TxMortalidMedia2001a05']   ,$Exibir['TxMortalidMedia2006a10']     ,$Exibir['TxMortalid2011']              ,$Exibir['TxMortalid2012']              ,$Exibir['TxMortalid2012']                ,'D') ;
                                                                                                                                                                                                                                                                 
    $data[] = array('Incidência'                         ,''                                    ,''                                    ,''                                     ,''                                     ,''                                       ,'T') ;
    $data[] = array('Número de casos novos'              ,$Exibir['TotalCasosNovos2001a05']     ,$Exibir['TotalCasosNovos2006a10']     ,$Exibir['CasosNovosTb2011']            ,$Exibir['CasosNovosTb2012']            ,$Exibir['CasosNovosTbAnoAnteriorUF']     ,'' ) ;
    $data[] = array('Taxa de incidência(por 100 000 hab)',$Exibir['TxIncidenciaTbMedia2001a05'] ,$Exibir['TxIncidenciaTbMedia2006a10'] ,$Exibir['TxIncidenciaTb2011']          ,$Exibir['TxIncidenciaTb2012']          ,$Exibir['TxIncidAnoAnteriorTOT']         ,'D') ;
                                                                                                                                                                                                                                                                 
    $data[] = array('Busca de casos'                     ,''                                    ,''                                    ,''                                     ,''                                     ,''                                       ,'T') ;
    $data[] = array('Sint.resp.examinados (% meta)'      ,$Exibir['SrEstimadosMedia2003a05']    ,$Exibir['SrEstimadosMedia2006a10']    ,$Exibir['SrEstimados2011']             ,$Exibir['SrEstimados2012']             ,$Exibir['SR_AnoAnteriroEstado']          ,'' ) ;
                                                                                                                                                                                                                                                                 
    $data[] = array('Tratamento**'                         ,''                                    ,''                                    ,''                                     ,''                                     ,''                                       ,'T') ;
    $data[] = array('% Cura'                             ,$Exibir['PercCuraMedia2001a05']       ,$Exibir['PercCuraMedia2006a10']       ,$Exibir['Cura2011']                    ,$Exibir['Cura2012']                    ,$Exibir['PercCuraAnoAnteriorTOT']        ,'' ) ;
    $data[] = array('% Abandono'                         ,$Exibir['vazio2']                      ,$Exibir['vazio2']                      ,$Exibir['Abandono2011']                ,$Exibir['Abandono2012']                ,$Exibir['PercAbandAnoAnteriorTOT']       ,'' ) ;
    $data[] = array('% Óbitos'                           ,$Exibir['vazio2']                      ,$Exibir['vazio2']                      ,$Exibir['Obitos2011']                  ,$Exibir['Obitos2012']                  ,$Exibir['PercentObitosAnoAnteriorUF']    ,'' ) ;
    $data[] = array('% Outros'                           ,$Exibir['vazio2']                      ,$Exibir['vazio2']                      ,$Exibir['Outros2011']                  ,$Exibir['Outros2012']                  ,$Exibir['PercentOutros']                 ,'' ) ;
    $data[] = array('% Não    encerrados'                ,$Exibir['vazio2']                      ,$Exibir['vazio2']                      ,$Exibir['NaoEncerr2011']               ,$Exibir['NaoEncerr2012']               ,$Exibir['PercentNaoEncerrAnoAnteriorUF'] ,'' ) ;
    $data[] = array('% Tratamento diretamente observado indicado',$Exibir['vazio2']              ,$Exibir['vazio2']                      ,$Exibir['PercentTDOInd2011']           ,$Exibir['PercentTDOInd2012']           ,$Exibir['PercentTDOIndAnoAnteriorUF']    ,'' ) ;
                                                                                
    $pdf->SetFont('Arial','',10);
    $pdf->ImprovedTable($header,$data);
    
    $texto = "<br />*Para os indicadores de busca de casos, este período se refere aos dados de2003 a 2005.<br /> **Casos novos residentes no local." ;
    $pdf->SetFont("Arial", "", 10 );
    $pdf->WriteHTML($texto);
                                                                       
    //$pdf->MultiCell(0, 5, $texto);                                   
    //$pdf->Ln();

    $pdf->Output();  
    
    exit ;
    
fim_php:    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Carta <?php echo $SelMuncip; ?></title>
<link href="../css.css" rel="stylesheet" type="text/css" />

<style type="text/css">
<!--
.style1 {font-size: 10px}
-->
</style>
</head>

<body>
<div style="width : 200mm ; height : 180mm ; border : 1px solid #000 ;">
      <p align="right" class="texto"> <a href="carta.php?SubOpcao=PDF&SelMuncip=<?php echo $SelMuncip; ?>&SelAnoCon=<?php echo $SelAnoCon; ?>&HidTipCat=<?php echo $HidTipCat; ?>">Exportar para PDF <img src="pdf_button.png"/></a>
  <p align="center" class="texto"><span class="Titulo">Relat&oacute;rio de Situa&ccedil;&atilde;o<br />
    Resumo dos indicadores de Tuberculose
    </span></p>
  <p align="left" class="texto"><span class="style1">Dados  atualizados em <?php echo $arr_meses[$arr_data[1]] . "/" . $arr_data[2]  ; ?></span>.
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
    <br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
  <p class="texto"><span class="texto">Local: (<strong><?php echo $HidTipCat . ": " . $SelMuncip; ?></strong>)</span>  </p>
      <p align="justify" class="texto"><br />
          Neste local, no ano de <?php echo $AnoAnteri; ?>, foram notificados 
        <?php echo $Exibir['CasosNovosAnoAnterior']; ?> casos novos de tuberculose, o que corresponde a uma taxa de incidência de 
        <?php echo $Exibir['TxIncidAnoAnterior']; ?> casos por 100 mil habitantes. Neste mesmo ano, a taxa de incidência do Estado de SP foi de  
        <?php echo $Exibir['TxIncidAnoAnteriorTOT']; ?> casos por 100 mil habitantes. De 
        <?php echo $Exibir['Periodo de Referencia']; ?> foram notificados 
        <?php echo $Exibir['CasosNovosAnoAtual']; ?> casos novos.
        <br /><br />
          Estima-se que, ao longo do ano, 1% da população apresente tosse por mais de 3 semanas. 
        Essa é a meta de Sintomáticos Respiratórios (SR) a serem investigados para tuberculose. 
        Para este local, cerca de 
        <?php echo $Exibir['MetaSrAnoAnterior']; ?> SR deveriam realizar baciloscopia de escarro no ano. 
        Em <?php echo $AnoAnteri; ?>,  foram examinados 
        <?php echo $Exibir['SR_AnoAnterior']; ?>, o que corresponde a 
        <?php echo $Exibir['PercMetaSR_AnoAnterior']; ?> % da meta, sendo que o Estado de SP atingiu  
        <?php echo $Exibir['SR_AnoAnteriroEstado']; ?> %.  De 
        <?php echo $Exibir['Periodo de Referencia']; ?> foram registrados 
        <?php echo $Exibir['SR_AnoAtual']; ?> SR.
        <br /><br />
          O número de SR examinados é extraído das informações provenientes dos laboratórios no sistema 
        LAB-TB, que registram as baciloscopias de diagnóstico.
        <?php echo $Exibir['ClaPercMetaSr']; ?>
        <br /><br />
        Um dos principais indicadores de desempenho no programa de Tuberculose é a taxa de cura. 
        A meta é curar pelo menos 85% dos casos novos diagnosticados.  
        <br /><br />
        Neste local - <?php echo $HidTipCat . " - " . $SelMuncip; ?> - a taxa média de cura dos 
        casos novos de tuberculose que iniciaram tratamento entre 2006 e 2010 foi de 
        <?php echo $Exibir['PercCuraMedia2006a10']; ?> %.  Em relação ao período de 2001 a 2005 (
        <?php echo $Exibir['PercCuraMedia2001a05']; ?> %), percebe-se que a taxa de cura 
        <?php echo $Exibir['PercCuraMedia2001a05_2006a10']; ?>.<br /><br />Dos 
        <?php echo $Exibir['CasosNovosAnoAnterior']; ?> casos novos com início de tratamento em 
        <?php echo $AnoAnteri; ?>, foram registradas 
        <?php echo $Exibir['CuraAnoAnterior']; ?> curas (
        <?php echo $Exibir['PercentCuraAnterior']; ?> %), que pode ser considerada 
        <?php echo $Exibir['ClassifTratAnterior']; ?> em relação à meta.
        <?php echo $Exibir['ClaPercCura']; ?>  
        <br /><br />
        O número de abandonos foi de 
        <?php echo $Exibir['AbandonoAnterior']; ?> (
        <?php echo $Exibir['PercAbandonoAnterior']; ?> %). Considera-se aceitável um percentual máximo de 5% de abandonos de tratamento. Em 
        <?php echo $AnoAnteri; ?>,  a taxa de cura dos casos novos  no Estado foi de 
        <?php echo $Exibir['PercCuraAnoAnteriorTOT']; ?> %, com percentual de abandonos de 
        <?php echo $Exibir['PercAbandAnoAnteriorTOT']; ?>%.    
        
      </p>
</div>      
<div style="width : 271mm ; height : 200mm ; border : 1px solid #000 ;">
      
      <table border="1" cellspacing="0" cellpadding="0" width="100%">
        <tr>
          <td></td>
          <td colspan="5"><p align="center" class="texto"><strong>Indicadores de tuberculose</strong></p></td>
        </tr>
        <tr>
          <td></td>
          <td width="12%"><p align="center" class="texto">M&eacute;dia<br />2001-2005*</p></td>
          <td width="12%"><p align="center" class="texto">M&eacute;dia<br />2006-2010</p></td>
          <td width="12%"><p align="center" class="texto">Ano         <br />2011     </p></td>
          <td width="12%"><p align="center" class="texto">De          <?php echo  $Exibir['Periodo de Referencia']; ?></p></td>
          <td width="12%"><p align="center" class="texto">Estado      <br /><?php echo $AnoAnteri; ?></p></td>
        </tr>
        <tr>
          <td class="texto"><strong>Mortalidade</strong></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto">N&uacute;mero de &oacute;bitos</td>
          <td align="center" class="texto"><?php echo $Exibir['TotalObitos2001a05']   ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TotalObitos2006a10']   ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['ObitosTb2011']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['ObitosTb2012']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['ObitosTbAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td class="texto">Taxa de mortalidade (por 100 000 hab.)</td>
          <td align="center" class="texto"><?php echo $Exibir['TxMortalidMedia2001a05'] ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxMortalidMedia2006a10'] ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxMortalid2011']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxMortalid2012']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxMortalidAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto"><strong>Incid&ecirc;ncia</strong></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto">N&uacute;mero de casos novos</td>
          <td align="center" class="texto"><?php echo $Exibir['TotalCasosNovos2001a05']   ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TotalCasosNovos2006a10']   ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['CasosNovosTb2011']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['CasosNovosTb2012']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['CasosNovosTbAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td class="texto">Taxa de incid&ecirc;ncia (por 100 000 hab.)</td>
          <td align="center" class="texto"><?php echo $Exibir['TxIncidenciaTbMedia2001a05']; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxIncidenciaTbMedia2006a10']; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxIncidenciaTb2011']        ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxIncidenciaTb2012']        ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['TxIncidAnoAnteriorTOT']     ; ?></td>
        </tr>
        <tr>
          <td></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto"><strong>Busca de casos</strong></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto">Sint.resp.examinados (% meta)</td>
          <td align="center" class="texto"><?php echo $Exibir['SrEstimadosMedia2003a05']; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['SrEstimadosMedia2006a10']; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['SrEstimados2011']        ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['SrEstimados2012']        ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['SR_AnoAnteriroEstado']   ; ?></td>
        </tr>
        <tr>
          <td></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto"><strong>Tratamento**</strong></td>
          <td align="center"></td>
          <td align="center"></td>                   
          <td align="center"></td>                   
          <td align="center"></td>
          <td align="center"></td>
        </tr>
        <tr>
          <td class="texto">% Cura</td>
          <td align="center" class="texto"><?php echo $Exibir['PercCuraMedia2001a05']  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercCuraMedia2006a10']  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Cura2011']              ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Cura2012']              ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercCuraAnoAnteriorTOT']; ?></td>
        </tr>
        <tr>
          <td class="texto">% Abandono</td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Abandono2011']           ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Abandono2012']           ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercAbandAnoAnteriorTOT']; ?></td>
        </tr>
        <tr>
          <td class="texto">% &Oacute;bitos</td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                     ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                     ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Obitos2011']                ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Obitos2012']                ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentObitosAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td class="texto">% Outros</td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Outros2011']    ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['Outros2012']    ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentOutros'] ; ?></td>
        </tr>
        <tr>
          <td class="texto">% N&atilde;o    encerrados</td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['NaoEncerr2011']  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['NaoEncerr2012']  ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentNaoEncerrAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td class="texto">% Tratamento diretamente observado indicado</td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                     ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['vazio']                     ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentTDOInd2011']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentTDOInd2012']         ; ?></td>
          <td align="center" class="texto"><?php echo $Exibir['PercentTDOIndAnoAnteriorUF']; ?></td>
        </tr>
        <tr>
          <td></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
    </table>
    <p class="texto">*Para os indicadores de busca de casos, este período se refere aos dados de2003 a 2005.<br />
					 **Casos novos residentes no local.
    </p>
</div>
</body>
</html>