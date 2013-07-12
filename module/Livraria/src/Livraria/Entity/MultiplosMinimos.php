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
     * @var float $minPremioAnual
     *
     * @ORM\Column(name="min_premio_anual", type="decimal", nullable=true)
     */
    private $minPremioAnual;

    /**
     * @var float $minPremioMensal
     *
     * @ORM\Column(name="min_premio_mensal", type="decimal", nullable=true)
     */
    private $minPremioMensal;

    /**
     * @var float $minApoliceAnual
     *
     * @ORM\Column(name="min_apolice_anual", type="decimal", nullable=true)
     */
    private $minApoliceAnual;

    /**
     * @var float $minApoliceMensal
     *
     * @ORM\Column(name="min_apolice_mensal", type="decimal", nullable=true)
     */
    private $minApoliceMensal;

    /**
     * @var float $minParcelaAnual
     *
     * @ORM\Column(name="min_parcela_anual", type="decimal", nullable=true)
     */
    private $minParcelaAnual;

    /**
     * @var float $minParcelaMensal
     *
     * @ORM\Column(name="min_parcela_mensal", type="decimal", nullable=true)
     */
    private $minParcelaMensal;

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

    public function getMinPremioAnual() {
        return $this->minPremioAnual;
    }

    public function setMinPremioAnual($minPremioAnual) {
        $this->minPremioAnual = $this->trataFloat($minPremioAnual);
        return $this;
    }

    public function getMinPremioMensal() {
        return $this->minPremioMensal;
    }

    public function setMinPremioMensal($minPremioMensal) {
        $this->minPremioMensal = $this->trataFloat($minPremioMensal);
        return $this;
    }

    public function getMinApoliceAnual() {
        return $this->minApoliceAnual;
    }

    public function setMinApoliceAnual($minApoliceAnual) {
        $this->minApoliceAnual = $this->trataFloat($minApoliceAnual);
        return $this;
    }

    public function getMinApoliceMensal() {
        return $this->minApoliceMensal;
    }

    public function setMinApoliceMensal($minApoliceMensal) {
        $this->minApoliceMensal = $this->trataFloat($minApoliceMensal);
        return $this;
    }

    public function getMinParcelaAnual() {
        return $this->minParcelaAnual;
    }

    public function setMinParcelaAnual($minParcelaAnual) {
        $this->minParcelaAnual = $this->trataFloat($minParcelaAnual);
        return $this;
    }

    public function getMinParcelaMensal() {
        return $this->minParcelaMensal;
    }

    public function setMinParcelaMensal($minParcelaMensal) {
        $this->minParcelaMensal = $this->trataFloat($minParcelaMensal);
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
        $data['minPremioAnual']     = $this->floatToStr('minPremioAnual') ; 
        $data['minPremioMensal']    = $this->floatToStr('minPremioMensal') ; 
        $data['minApoliceAnual']    = $this->floatToStr('minApoliceAnual') ; 
        $data['minApoliceMensal']   = $this->floatToStr('minApoliceMensal') ; 
        $data['minParcelaAnual']    = $this->floatToStr('minParcelaAnual') ; 
        $data['minParcelaMensal']   = $this->floatToStr('minParcelaMensal') ; 
        $data['minAluguel']         = $this->floatToStr('minAluguel') ; 
        $data['minIncendio']        = $this->floatToStr('minIncendio') ; 
        $data['minConteudo']        = $this->floatToStr('minConteudo') ; 
        $data['minEletrico']        = $this->floatToStr('minEletrico') ; 
        $data['minVendaval']        = $this->floatToStr('minVendaval') ; 
        $data['maxAluguel']         = $this->floatToStr('maxAluguel') ; 
        $data['maxIncendio']        = $this->floatToStr('maxIncendio') ; 
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
