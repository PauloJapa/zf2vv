<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MultiplosMinimos
 * 
 * Parametros para calculos do seguros registra os multiplos para calculos e o 
 * valor minimo aceito pelas seguradoras
 *
 * @ORM\Table(name="multiplos_minimos")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\MultiplosMinimosRepository")
 */
class MultiplosMinimos extends Filtro
{
    /**
     * @var integer $idMultiplos
     *
     * @ORM\Column(name="id_multiplos", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMultiplos;

    /**
     * @var integer $multAluguel
     *
     * @ORM\Column(name="mult_aluguel", type="integer", nullable=false)
     */
    private $multAluguel;

    /**
     * @var integer $multConteudo
     *
     * @ORM\Column(name="mult_conteudo", type="integer", nullable=true)
     */
    private $multConteudo;

    /**
     * @var integer $multIncendio
     *
     * @ORM\Column(name="mult_incendio", type="integer", nullable=true)
     */
    private $multIncendio;

    /**
     * @var integer $multEletrico
     *
     * @ORM\Column(name="mult_eletrico", type="integer", nullable=true)
     */
    private $multEletrico;

    /**
     * @var integer $multVendaval
     *
     * @ORM\Column(name="mult_vendaval", type="integer", nullable=true)
     */
    private $multVendaval;

    /**
     * @var float $minAluguel
     *
     * @ORM\Column(name="min_aluguel", type="decimal", nullable=true)
     */
    private $minAluguel;

    /**
     * @var float $minIncendio
     *
     * @ORM\Column(name="min_incendio", type="decimal", nullable=true)
     */
    private $minIncendio;

    /**
     * @var float $minConteudo
     *
     * @ORM\Column(name="min_conteudo", type="decimal", nullable=true)
     */
    private $minConteudo;

    /**
     * @var float $minEletrico
     *
     * @ORM\Column(name="min_eletrico", type="decimal", nullable=true)
     */
    private $minEletrico;

    /**
     * @var float $minVendaval
     *
     * @ORM\Column(name="min_vendaval", type="decimal", nullable=true)
     */
    private $minVendaval;

    /**
     * @var float $maxAluguel
     *
     * @ORM\Column(name="max_aluguel", type="decimal", nullable=true)
     */
    private $maxAluguel;

    /**
     * @var float $maxIncendio
     *
     * @ORM\Column(name="max_incendio", type="decimal", nullable=true)
     */
    private $maxIncendio;

    /**
     * @var float $maxConteudo
     *
     * @ORM\Column(name="max_conteudo", type="decimal", nullable=true)
     */
    private $maxConteudo;

    /**
     * @var float $maxEletrico
     *
     * @ORM\Column(name="max_eletrico", type="decimal", nullable=true)
     */
    private $maxEletrico;

    /**
     * @var float $maxVendaval
     *
     * @ORM\Column(name="max_vendaval", type="decimal", nullable=true)
     */
    private $maxVendaval;

    /**
     * @var \DateTime $multVigenciaInicio
     *
     * @ORM\Column(name="mult_vigencia_inicio", type="date", nullable=false)
     */
    private $multVigenciaInicio;

    /**
     * @var \DateTime $multVigenciaFim
     *
     * @ORM\Column(name="mult_vigencia_fim", type="date", nullable=true)
     */
    private $multVigenciaFim;

    /**
     * @var string $multStatus
     *
     * @ORM\Column(name="mult_status", type="string", length=2, nullable=false)
     */
    private $multStatus;

    /**
     * @var Seguradora
     *
     * @ORM\ManyToOne(targetEntity="Seguradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     * })
     */
    private $seguradora;


    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
    }    
    
    /**
     * 
     * @return integer
     */
    public function getIdMultiplos() {
        return $this->idMultiplos;
    }
    public function getId(){
        return $this->getIdMultiplos();
    }

    /**
     * 
     * @param integer $idMultiplos
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setIdMultiplos($idMultiplos) {
        $this->idMultiplos = $idMultiplos;
        return $this;
    }
    public function setId($id){
        return $this->setIdMultiplos($id);
    }

    /**
     * 
     * @return integer
     */
    public function getMultAluguel() {
        return $this->multAluguel;
    }

    /**
     * 
     * @param integer $multAluguel
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultAluguel($multAluguel) {
        $this->multAluguel = $this->trataFloat($multAluguel);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultConteudo() {
        return $this->multConteudo;
    }

    /**
     * 
     * @param integer $multConteudo
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultConteudo($multConteudo) {
        $this->multConteudo = $this->trataFloat($multConteudo);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultIncendio() {
        return $this->multIncendio;
    }

    public function setMultIncendio($multIncendio) {
        $this->multIncendio = $this->trataFloat($multIncendio);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultEletrico() {
        return $this->multEletrico;
    }

    /**
     * 
     * @param integer $multEletrico
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultEletrico($multEletrico) {
        $this->multEletrico = $this->trataFloat($multEletrico);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultVendaval() {
        return $this->multVendaval;
    }

    /**
     * 
     * @param integer $multVendaval
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultVendaval($multVendaval) {
        $this->multVendaval = $this->trataFloat($multVendaval);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMinAluguel() {
        return $this->minAluguel;
    }

    /**
     * 
     * @param float $minAluguel
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMinAluguel($minAluguel) {
        $this->minAluguel = $this->trataFloat($minAluguel);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMinIncendio() {
        return $this->minIncendio;
    }

    /**
     * 
     * @param float $minIncendio
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMinIncendio($minIncendio) {
        $this->minIncendio = $this->trataFloat($minIncendio);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMinConteudo() {
        return $this->minConteudo;
    }

    /**
     * 
     * @param float $minConteudo
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMinConteudo($minConteudo) {
        $this->minConteudo = $this->trataFloat($minConteudo);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMinEletrico() {
        return $this->minEletrico;
    }

    /**
     * 
     * @param float $minEletrico
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMinEletrico($minEletrico) {
        $this->minEletrico = $this->trataFloat($minEletrico);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMinVendaval() {
        return $this->minVendaval;
    }

    /**
     * 
     * @param float $minVendaval
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMinVendaval($minVendaval) {
        $this->minVendaval = $this->trataFloat($minVendaval);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMaxAluguel() {
        return $this->maxAluguel;
    }

    /**
     * 
     * @param float $maxAluguel
     * @return \Livraria\Entity\MultiplosMaximos
     */
    public function setMaxAluguel($maxAluguel) {
        $this->maxAluguel = $this->trataFloat($maxAluguel);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMaxIncendio() {
        return $this->maxIncendio;
    }

    /**
     * 
     * @param float $maxIncendio
     * @return \Livraria\Entity\MultiplosMaximos
     */
    public function setMaxIncendio($maxIncendio) {
        $this->maxIncendio = $this->trataFloat($maxIncendio);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMaxConteudo() {
        return $this->maxConteudo;
    }

    /**
     * 
     * @param float $maxConteudo
     * @return \Livraria\Entity\MultiplosMaximos
     */
    public function setMaxConteudo($maxConteudo) {
        $this->maxConteudo = $this->trataFloat($maxConteudo);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMaxEletrico() {
        return $this->maxEletrico;
    }

    /**
     * 
     * @param float $maxEletrico
     * @return \Livraria\Entity\MultiplosMaximos
     */
    public function setMaxEletrico($maxEletrico) {
        $this->maxEletrico = $this->trataFloat($maxEletrico);
        return $this;
    }

    /**
     * 
     * @return float
     */
    public function getMaxVendaval() {
        return $this->maxVendaval;
    }

    /**
     * 
     * @param float $maxVendaval
     * @return \Livraria\Entity\MultiplosMaximos
     */
    public function setMaxVendaval($maxVendaval) {
        $this->maxVendaval = $this->trataFloat($maxVendaval);
        return $this;
    }

    /** 
     * Retorna o inicio da vigência 
     * @param String $op para retornar o objeto data ou string data
     * @return \DateTime | string data no formato dd/mm/aaaa 
     */ 
    public function getMultVigenciaInicio($op = null) {
        if($op == 'obj'){
            return $this->multVigenciaInicio;
        }
        return $this->multVigenciaInicio->format('d/m/Y');
    }

    /**
     * Data do inicio da vigencia desses parametros
     * @param \DateTime $multVigenciaInicio
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultVigenciaInicio(\DateTime $multVigenciaInicio) {
        $this->multVigenciaInicio = $multVigenciaInicio;
        return $this;
    }

    /** 
     * Retorna o fim da vigência 
     * @param String $op para retornar o objeto data ou string data
     * @return \DateTime | string data no formato dd/mm/aaaa 
     */ 
    public function getMultVigenciaFim($op = null) {
        if($op == 'obj'){
            return $this->multVigenciaFim;
        }
        $check = $this->multVigenciaFim->format('d/m/Y');
        if($check == '01/01/1000'){
            return "vigente";
        }else{
            return $check;
        }
    }

    /**
     * 
     * @param \DateTime $multVigenciaFim
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultVigenciaFim(\DateTime $multVigenciaFim) {
        $this->multVigenciaFim = $multVigenciaFim;
        return $this;
    }

    /**
     * A = Ativo, C = cancelado, B = bloqueado
     * @return string da situação do registro
     */
    public function getMultStatus() {
        return $this->multStatus;
    }

    /**
     * A = Ativo, C = cancelado, B = bloqueado
     * @param string $multStatus
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultStatus($multStatus) {
        $this->multStatus = $multStatus;
        return $this;
    }

    /**
     * Todos os dados da entity Seguradora
     * @return \Livraria\Entity\Seguradora
     */
    public function getSeguradora() {
        return $this->seguradora;
    }

    /**
     * Referencia para entity seguradora
     * @param \Livraria\Entity\Seguradora $seguradora
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }
    
    public function toArray(){
        $data['idMultiplos']        = $this->getIdMultiplos() ; 
        $data['multAluguel']         = $this->floatToStr('multAluguel') ; 
        $data['multIncendio']          = $this->floatToStr('multIncendio') ; 
        $data['multConteudo']        = $this->floatToStr('multConteudo') ; 
        $data['multEletrico']        = $this->floatToStr('multEletrico') ; 
        $data['multVendaval']        = $this->floatToStr('multVendaval') ; 
        $data['minAluguel']         = $this->floatToStr('minAluguel') ; 
        $data['minIncendio']          = $this->floatToStr('minIncendio') ; 
        $data['minConteudo']        = $this->floatToStr('minConteudo') ; 
        $data['minEletrico']        = $this->floatToStr('minEletrico') ; 
        $data['minVendaval']        = $this->floatToStr('minVendaval') ; 
        $data['maxAluguel']         = $this->floatToStr('maxAluguel') ; 
        $data['maxIncendio']          = $this->floatToStr('maxIncendio') ; 
        $data['maxConteudo']        = $this->floatToStr('maxConteudo') ; 
        $data['maxEletrico']        = $this->floatToStr('maxEletrico') ; 
        $data['maxVendaval']        = $this->floatToStr('maxVendaval') ; 
        $data['multVigenciaInicio'] = $this->getMultVigenciaInicio() ; 
        $data['multVigenciaFim']    = $this->getMultVigenciaFim() ; 
        $data['multStatus']         = $this->getMultStatus() ; 
        $data['seguradora']         = $this->getSeguradora()->getId() ; 
        return $data;
    }


}
