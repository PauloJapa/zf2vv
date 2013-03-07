<?php



namespace LivrariaAdmin\Fpdf;



/**
 * Description of Testepdf
 *
 * @author user
 */
class Testepdf extends FPDF{
    
    public function __construct() {
        $this->FPDF();
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Times','',12);
        for($i=1;$i<=40;$i++)
            $this->Cell(0,10,'Printing line number '.$i,0,1);
    }
    
    // Page header
    public function Header(){
        $sep = DIRECTORY_SEPARATOR ;
        // Logo
        $this->Image(getcwd() . $sep . 'module'.$sep.'Livraria'.$sep.'src'.$sep.'LivrariaAdmin'.$sep.'Fpdf'.$sep.'logo.png',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(80,8,'Title',1,0,'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    public function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
}
