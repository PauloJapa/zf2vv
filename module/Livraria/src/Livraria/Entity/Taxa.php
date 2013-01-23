<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Taxa
 * 
 * Contém taxas e a vigencias das mesmas para calculos dos seguros
 *
 * @ORM\Table(name="taxa")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\TaxaRepository")
 */
class Taxa
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=false)
     */
    private $inicio;

    /**
     * @var \DateTime $fim
     *
     * @ORM\Column(name="fim", type="datetime", nullable=true)
     */
    private $fim;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    private $status;

    /**
     * @var float $incendio
     *
     * @ORM\Column(name="incendio", type="decimal", nullable=true)
     */
    private $incendio;

    /**
     * @var float $incendioConteudo
     *
     * @ORM\Column(name="incendio_conteudo", type="decimal", nullable=true)
     */
    private $incendioConteudo;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", nullable=true)
     */
    private $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", nullable=true)
     */
    private $eletrico;

    /**
     * @var float $desastres
     *
     * @ORM\Column(name="desastres", type="decimal", nullable=true)
     */
    private $desastres;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=false)
     */
    private $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    private $criadoEm;

    /**
     * @var integer $userIdAlterado
     *
     * @ORM\Column(name="user_id_alterado", type="integer", nullable=true)
     */
    private $userIdAlterado;

    /**
     * @var \DateTime $alteradoEm
     *
     * @ORM\Column(name="alterado_em", type="datetime", nullable=true)
     */
    private $alteradoEm;

    /**
     * @var Classe
     *
     * @ORM\ManyToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id")
     * })
     */
    private $classe;


 
    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * Tambem carrega as data de criadoEm e alteradoEm atuais 
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->criadoEm = new \DateTime('now');
        $this->criadoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo')); 
        $this->userIdCriado = 1 ;
    }
     
    /**
     * Executa antes de salvar o registro atualizando assim a data de alteradoEm
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    }
    
    public function getId() {
        return $this->id;
    }


    /** 
     * Setar o id do registro
     * @param Int $id
     * @return this 
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getInicio() {
        return $this->inicio;
    }


    /** 
     * Setar o inicio da vigência da taxa
     * @param \DateTime $inicio
     * @return this 
     */ 
    public function setInicio(\DateTime $inicio) {
        $this->inicio = $inicio;
        return $this;
    }

    public function getFim() {
        return $this->fim;
    }


    /** 
     * Setar terminino da vigência da taxa para manter historico
     * @param \DateTime $fim
     * @return this 
     */ 
    public function setFim(\DateTime $fim) {
        $this->fim = $fim;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    /** 
     * Setar o status do registro ativo bloqueado inativo
     * @param String $status
     * @return this 
     */ 
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getIncendio() {
        return $this->incendio;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio
     * @param Float $incendio
     * @return this 
     */ 
    public function setIncendio($incendio) {
        $this->incendio = $this->strToFloat($incendio);
        return $this;
    }

    public function getIncendioConteudo() {
        return $this->incendioConteudo;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio + conteudo
     * @param Float $incendioConteudo
     * @return this 
     */ 
    public function setIncendioConteudo($incendioConteudo) {
        $this->incendioConteudo = $this->strToFloat($incendioConteudo);
        return $this;
    }

    public function getAluguel() {
        return $this->aluguel;
    }

    /** 
     * Setar a taxa cobrada para seguro do aluguel
     * @param Float $aluguel
     * @return this 
     */ 
    public function setAluguel($aluguel) {
        $this->aluguel = $this->strToFloat($aluguel);
        return $this;
    }

    public function getEletrico() {
        return $this->eletrico;
    }

    /** 
     * Setar a taxa cobrada para seguro de danos eletrico
     * @param Float $incendioConteudo
     * @return this 
     */ 
    public function setEletrico($eletrico) {
        $this->eletrico = $this->strToFloat($eletrico);
        return $this;
    }

    public function getDesastres() {
        return $this->desastres;
    }

    /** 
     * Setar a taxa cobrada para seguro de desastres naturais
     * @param Float $desastres
     * @return this 
     */
    public function setDesastres($desastres) {
        $this->desastres = $this->strToFloat($desastres);
        return $this;
    }

    public function getUserIdCriado() {
        return $this->userIdCriado;
    }

    /** 
     * Setar o id do user que criou o registro
     * @param Int $userIdCriado
     * @return this 
     */ 
    public function setUserIdCriado($userIdCriado) {
        $this->userIdCriado = $userIdCriado;
        return $this;
    }

    public function getCriadoEm() {
        return $this->criadoEm;
    }

    /** 
     * Setar quando foi criado o registro
     * @param \DateTime $criadoEm
     * @return this 
     */ 
    public function setCriadoEm(\DateTime $criadoEm) {
        $this->criadoEm = $criadoEm;
        return $this;
    }

    public function getUserIdAlterado() {
        return $this->userIdAlterado;
    }

    /** 
     * Setar o id do user que alterou da ultima vez o registro
     * @param Int $userIdAlterado
     * @return this 
     */ 
    public function setUserIdAlterado($userIdAlterado) {
        $this->userIdAlterado = $userIdAlterado;
        return $this;
    }

    public function getAlteradoEm() {
        return $this->alteradoEm;
    }

    /** 
     * Setar quando foi alterado o registro
     * @param \DateTime $alteradoEm
     * @return this 
     */ 
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $this->alteradoEm = $alteradoEm;
        return $this;
    }

    public function getClasse() {
        return $this->classe;
    }

    /** 
     * Setar a qual entidade classe que pertence esta taxa
     * @param \Livraria\Entity\Classe $classe
     * @return this 
     */ 
    public function setClasse(Classe $classe) {
        $this->classe = $classe;
        return $this;
    }

    public function toArray() {
        $data['id']               = $this->getId();
        $data['inicio']           = $this->getInicio()->format('d/m/Y');
        $data['fim']              = $this->getFim()->format('d/m/Y');
        $data['status']           = $this->getStatus();
        $data['incendio']         = $this->floatToStr('Incendio');
        $data['incendioConteudo'] = $this->floatToStr('IncendioConteudo');
        $data['aluguel']          = $this->floatToStr('Aluguel');
        $data['eletrico']         = $this->floatToStr('Eletrico');
        $data['desastres']        = $this->floatToStr('Desastres');
        $data['userIdCriado']     = $this->getUserIdCriado();
        $data['criadoEm']         = $this->getCriadoEm();
        $data['userIdAlterado']   = $this->getUserIdAlterado();
        $data['alteradoEm']       = $this->getAlteradoEm();
        $data['classe']           = $this->getClasse()->getId(); 
        return $data ;
    }
 
    /** 
     * Converte a variavel do tipo float para string para exibição
     * @param String $get com nome do metodo a ser convertido
     * @param Int $dec quantidade de casas decimais
     * @return String do numero no formato brasileiro padrão com 2 casas decimais
     */    
    public function floatToStr($get,$dec = 2){
        if($get == ""){
            return "vazio!!";
        }
        $getter  = 'get' . ucwords($get);
        if(!method_exists($this,$getter)){
            return "Erro no metodo!!";
        }
        $float = call_user_func(array($this,$getter));
        return number_format($float, $dec, ',','.');
    }
 
    /** 
     * Faz tratamento na variavel string se necessario antes de converte em float
     * @param String $check variavel a ser convertida se tratada se necessario
     * @return Float da variavel de entrada convertido
     */    
    public function strToFloat($check){
        if(is_string($check)){
            $check = preg_replace("/[^0-9,]/", "", $check);
            $check = str_replace(",", ".", $check);
        }
        return floatval($check);
    }

}
