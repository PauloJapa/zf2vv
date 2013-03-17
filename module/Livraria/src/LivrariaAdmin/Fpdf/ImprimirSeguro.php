<?php



namespace LivrariaAdmin\Fpdf;



/**
 * Description of Testepdf
 *
 * @author user
 */
class ImprimirSeguro extends FPDF{
    
    private $logoSeguradora;
    private $numSeguro;
    private $B;
    private $I;
    private $U;
    private $HREF;

    public function __construct($numSeguro='',$log='logoMaritima.png') {
        $this->logoSeguradora = $log;
        $this->numSeguro =  $numSeguro;
        $this->FPDF();
        $this->AliasNbPages();
        $this->AddPage();
    }
    
    public function setL1($refImovel,$iniVig){
        $linha = ['Referência do Imóvel:',$refImovel,'Início da Vigência:',$iniVig];
        $this->set2Cell($linha, 41, 35);
    }
    
    public function setL2($adm){
        $l2 = ['Adm.:',$adm];
        $this->setCell($l2, 13);
    }
    
    public function setL3($locatario,$doc){
        $l3 = ['Locatário:',$locatario,'CGC/CPF:',$doc];
        $this->set2Cell($l3, 20, 21);
    }
    
    public function setL4($locador,$doc){
        $l4 = ['Locador:',$locador,'CGC/CPF:',$doc];
        $this->set2Cell($l4, 20, 21);
    }
    public function setL5($end){
        $l5 = ['End. do Imóvel:',$end];
        $this->setCell($l5, 31);
    }
    public function setL6($ocupacao){
        $l6 = ['Ocupação:',$ocupacao];
        $this->setCell($l6, 20);
    }
    public function setL7($obs){
        $l7 = ['Observações:',$obs];
        $this->setCell($l7, 25);
    }
    public function setL8($aluguel){
        $l8 = ['Valor do Aluguel:',$aluguel];
        $this->setCell($l8, 35);
    }
    public function setL9($ue,$ui){
        $l9 = ['U.E.:',$ue,'U.I.:',$ui];
        $this->set2Cell($l9, 11, 10);
        $this->Ln();
    }
    public function setL10(){
        $this->SetFont('Times','B',12);
        $this->Cell(87, 7, 'Cobertura',1,0,'C');
        $this->Cell(63, 7, 'Importância Segurada',1,0,'C');
        $this->Cell(40, 7, 'Prêmio',1,1,'C');
    }
    /**
     * Valores segurados e valores dos prêmios
     * @param array $vlr
     * @param string $label 'Prédio + Conteudo'
     */
    public function setL11(array $vlr,$label=''){
        $this->set3Cell(['Incêndio Locação' . $label,$vlr[0],$vlr[1]]);
        $this->set3Cell(['Danos Eletricos',$vlr[2],$vlr[3]]);
        $this->set3Cell(['Perda Aluguel',$vlr[4],$vlr[5]]);
        $this->set3Cell(['Vendaval',$vlr[6],$vlr[7]]);
        $this->Ln();
    }
    /**
     * Valores totais do seguro
     * @param array $vlr
     * @param string $iof
     */
    public function setL12(array $vlr,$iof='7,38'){
        $this->set4Cell(['Total liquido',$vlr[0]]);
        if($vlr[0] != $vlr[1]){
            $this->set4Cell(['Prêmio mínimo por emissão',$vlr[1]]);
        }
        $this->set4Cell(['IOF ('. $iof .'%)',$vlr[2]]);
        $this->set4Cell(['Total a Vista',$vlr[3]],['B','B']);
        $this->Cell(0, 7,'',1,1);
    }
    /**
     * Valores para preencher os valores de pagamentos
     * @param array $vlr
     * @param boolean $mensal Decide se exibe ou não valor para pagamento mensal 
     */
    public function setL13(array $vlr,$mensal=TRUE){
        $this->set5Cell(['Foma de Pagamento 1(ato)','Parcela(s) de',$vlr[0]]);
        $this->set5Cell(['Forma de Pagamento 2 (1-1)','Parcela(s) de',$vlr[1]], 8, 5);
        $this->set5Cell(['Forma de Pagamento 3 (1-2)','Parcela(s) de',$vlr[2]], 8, 5);
        if(!$mensal)
            $this->set5Cell(['Forma de Pagamento 12 (mensal)','Parcela(s) de',$vlr[3]], 8, 5);
        $this->Ln();
    }
    /**
     * Textos da Franquia por enquanto são Fixos
     */
    public function setL14(){
        $this->SetFont('Times','B',12);
        $this->Write(7, 'Franquias');
        $this->Ln();
        $this->set4Cell(['Coberturas','Limites'], ['B','B'], ['C','C'], 50);
        $this->set4Cell(['Queda de Raio','10% dos prejuízos indenizáveis, limitado ao mínim o de R$ 700,00'], ['',''], ['','C'], 50, 10);
        $this->set4Cell(['Danos Elétricos e Curto Circuito','10% dos prejuízos indenizáveis, limitado ao mínim o de R$ 700,00'], ['',''], ['','C'], 50, 10);
        $this->set4Cell(['Vendaval / Granizo / Fumaça','10% dos prejuízos indenizáveis, limitado ao mínim o de R$ 700,00'], ['',''], ['','C'], 50, 10);
        $this->Ln();
    }
    /**
     * Coloca Observação do seguros caso houver
     * @param text $obs
     */
    public function setObs($obs){
        $this->SetFont('Times','B',12);
        $this->Cell(0, 7, 'Observação', 1,1);
        $this->SetFont('Times','',10);
        $this->MultiCell(0, 4, $obs, 1);
    }

    public function setCell(array $txt,$w1=95){
        $this->SetFont('Times','B',12);
        $this->Cell($w1, 7, $txt[0], 'LTB',0);
        $this->SetFont('Times','',12);
        $this->Cell(190 - $w1, 7, $txt[1], 'RTB',1);
    }
    
    public function set2Cell(array $txt,$w1=45,$w2=45){
        $this->SetFont('Times','B',12);
        $this->Cell($w1, 7, $txt[0], 'LTB',0);
        $this->SetFont('Times','',12);
        $this->Cell(125 - $w1, 7, $txt[1], 'RTB',0);
        $this->SetFont('Times','B',12);
        $this->Cell($w2, 7, $txt[2], 'LTB',0);
        $this->SetFont('Times','',12);
        $this->Cell(65 - $w2, 7, $txt[3], 'RTB',1);
    }
    
    public function set3Cell(array $txt){
        $this->SetFont('Times','B',12);
        $this->Cell(87, 7, $txt[0],1,0);
        $this->SetFont('Times','',12);
        $this->Cell(63, 7, $txt[1],1,0,'R');
        $this->Cell(40, 7, $txt[2],1,1,'R');
    }
    
    public function set4Cell(array $txt,array $bold=['B',''],array $align=['','R'],$w=150,$fs=12){
        $this->SetFont('Times',$bold[0],$fs);
        $this->Cell($w, 7, $txt[0],1,0,$align[0]);
        $this->SetFont('Times',$bold[1],$fs);
        $this->Cell(190 - $w, 7, $txt[1],1,1,$align[1]);
    }
    
    public function set5Cell(array $txt,$f=12,$h=7,array $bold=['B','B',''],array $align=['','R','R']){
        $this->SetFont('Times',$bold[0],$f);
        $this->Cell(110, $h, $txt[0],'LTB',0,$align[0]);
        $this->SetFont('Times',$bold[1],$f);
        $this->Cell(40, $h, $txt[1],'RTB',0,$align[1]);
        $this->SetFont('Times',$bold[2],$f);
        $this->Cell(40, $h, $txt[2],1,1,$align[2]);
    }

        // Page header
    public function Header(){
        $sep = DIRECTORY_SEPARATOR ;
        $diretorio = getcwd().$sep.'module'.$sep.'Livraria'.$sep.'src'.$sep.'LivrariaAdmin'.$sep.'Fpdf'.$sep;
        // Logo
        $this->Image($diretorio . 'logoVilaVelha.png',10,6,30);
        $this->Image($diretorio . $this->logoSeguradora,170,15,30);
        // Arial bold 15
        $this->SetFont('Arial','B',14);
        // Move to the right
        $this->Cell(35);
        // Title
        $this->Cell(120,25,'PLANILHA DE SEGURO INCÊNDIO LOCAÇÃO',0,0);
        // Line break
        $this->Ln();
    }

    // Page footer
    public function Footer() {
        // Position at 1 cm from bottom
        $this->SetY(-10);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $date = new \DateTime('now');
        $this->Cell(95,7, $date->format('d/m/Y') . '  http://sistemas.vilavelha.com.br/incendio_locacao/imprimirSeguro/' . $this->numSeguro,0,0);
        $this->Cell(95,7,'Page '.$this->PageNo().'/{nb}',0,0,'R');
    }
    
    function WriteHTML($html) {
        // HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write(5, $e);
            }
            else {
                // Tag
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    // Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }
    
    function OpenTag($tag, $attr) {
        // Opening tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = $attr['HREF'];
        if ($tag == 'BR')
            $this->Ln(5);
    }
    
    function CloseTag($tag) {
        // Closing tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
    }

    function SetStyle($tag, $enable) {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style .= $s;
        }
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt) {
        // Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }
    
}