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
class Taxa extends Filtro
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
     * @ORM\Column(name="incendio", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $incendio;

    /**
     * @var float $incendioConteudo
     *
     * @ORM\Column(name="incendio_conteudo", precision=10, scale=8, options={"default" = 0})
     */
    private $incendioConteudo;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $eletrico;

    /**
     * @var float $desastres
     *
     * @ORM\Column(name="desastres", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $desastres;

    /**
     * @var float $incendioMen
     *
     * @ORM\Column(name="incendio_men", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $incendioMen;

    /**
     * @var float $incendioConteudoMen
     *
     * @ORM\Column(name="incendio_conteudo_men", precision=10, scale=8, options={"default" = 0})
     */
    private $incendioConteudoMen;

    /**
     * @var float $aluguelMen
     *
     * @ORM\Column(name="aluguel_men", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $aluguelMen;

    /**
     * @var float $eletricoMen
     *
     * @ORM\Column(name="eletrico_men", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $eletricoMen;

    /**
     * @var float $desastresMen
     *
     * @ORM\Column(name="desastres_men", type="decimal", precision=10, scale=8, options={"default" = 0})
     */
    private $desastresMen;

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
     * @ORM\OneToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id")
     * })
     */
    private $classe;


    /**
     * @var Seguradora
     *
     * @ORM\OneToOne(targetEntity="Seguradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     * })
     */
    private $seguradora;


 
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
     * Verifica a data e seta o status do registro conforme valor de fim
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        if($this->inicio < $this->fim)
            $this->status = 'C';
        else
            $this->status = 'A';
    }
    
    /**
     * 
     * @return int $id do registro
     */
    public function getId() {
        return $this->id;
    }


    /** 
     * Setar o id do registro
     * @param Int $id
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /** 
     * Retorna o inicio da vigência da taxa
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto  
     */ 
    public function getInicio($op = null) {
        if(is_null($op)){
            return $this->inicio->format('d/m/Y');
        }
        return $this->inicio;
    }
    
    /** 
     * Setar o inicio da vigência da taxa
     * @param \DateTime $inicio
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setInicio(\DateTime $inicio) {
        $this->inicio = $inicio;
        return $this;
    }

    /** 
     * Retorna o fim da vigência da taxa
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto  
     */ 
    public function getFim($op = null) {
        if($op == 'obj'){
            return $this->fim;
        }
        return $this->trataData($this->fim);
    }


    /** 
     * Setar terminino da vigência da taxa para manter historico
     * @param \DateTime $fim
     * @return \Livraria\Entity\Taxa 
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
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Taxa cobrada para seguro incendio Anual
     * @return float
     */
    public function getIncendio() {
        return $this->incendio;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio Anual
     * @param Float $incendio
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setIncendio($incendio) {
        $this->incendio = $this->trataFloat($incendio);
        return $this;
    }

    public function getIncendioConteudo() {
        return $this->incendioConteudo;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio + conteudo Anual
     * @param Float $incendioConteudo
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setIncendioConteudo($incendioConteudo) {
        $this->incendioConteudo = $this->trataFloat($incendioConteudo);
        return $this;
    }

    public function getAluguel() {
        return $this->aluguel;
    }

    /** 
     * Setar a taxa cobrada para seguro do aluguel Anual
     * @param Float $aluguel
     * @return this 
     */ 
    public function setAluguel($aluguel) {
        $this->aluguel = $this->trataFloat($aluguel);
        return $this;
    }

    public function getEletrico() {
        return $this->eletrico;
    }

    /** 
     * Setar a taxa cobrada para seguro de danos eletrico Anual
     * @param Float $incendioConteudo
     * @return this 
     */ 
    public function setEletrico($eletrico) {
        $this->eletrico = $this->trataFloat($eletrico);
        return $this;
    }

    public function getDesastres() {
        return $this->desastres;
    }

    /** 
     * Setar a taxa cobrada para seguro de desastres naturais Anual
     * @param Float $desastres
     * @return this 
     */
    public function setDesastres($desastres) {
        $this->desastres = $this->trataFloat($desastres);
        return $this;
    }
    
    /**
     * Taxa de seguro para calculo de incendio(Predio) mensal
     * @return float
     */
    public function getIncendioMen() {
        return $this->incendioMen;
    }

    /**
     * Taxa de seguro para calculo de incendio(Predio) mensal
     * @param flaot $incendioMen
     * @return \Livraria\Entity\Taxa
     */
    public function setIncendioMen($incendioMen) {
        $this->incendioMen = $this->trataFloat($incendioMen);
        return $this;
    }

    /**
     * Taxa de seguro para calculo de incendio+conteudo mensal
     * @return float
     */
    public function getIncendioConteudoMen() {
        return $this->incendioConteudoMen;
    }
    
    /**
     * Taxa de seguro para calculo de incendio+conteudo mensal
     * @param float $incendioConteudoMen
     * @return \Livraria\Entity\Taxa
     */
    public function setIncendioConteudoMen($incendioConteudoMen) {
        $this->incendioConteudoMen = $this->trataFloat($incendioConteudoMen);
        return $this;
    }

    /**
     * Taxa de seguro para calculo de Aluguel mensal
     * @return float
     */
    public function getAluguelMen() {
        return $this->aluguelMen;
    }

    /**
     * Taxa de seguro para calculo de Aluguel mensal
     * @param float $aluguelMen
     * @return \Livraria\Entity\Taxa
     */
    public function setAluguelMen($aluguelMen) {
        $this->aluguelMen = $this->trataFloat($aluguelMen);
        return $this;
    }

    /**
     * Taxa de seguro para calculo de eletrico mensal
     * @return float
     */
    public function getEletricoMen() {
        return $this->eletricoMen;
    }

    /**
     * Taxa de seguro para calculo de eletrico mensal
     * @param float $eletricoMen
     * @return \Livraria\Entity\Taxa
     */
    public function setEletricoMen($eletricoMen) {
        $this->eletricoMen = $this->trataFloat($eletricoMen);
        return $this;
    }

    /**
     * Taxa de seguro para calculo de desastres(Vendaval) mensal
     * @return float
     */
    public function getDesastresMen() {
        return $this->desastresMen;
    }

    /**
     * Taxa de seguro para calculo de desastres(Vendaval) mensal
     * @param float $desastresMen
     * @return \Livraria\Entity\Taxa
     */
    public function setDesastresMen($desastresMen) {
        $this->desastresMen = $this->trataFloat($desastresMen);
        return $this;
    }

    /**
     * 
     * @return int Id do usuario que cadastrou o registro
     */
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

    /**
     * 
     * @param Boolean $op
     * @return String data formatada em dia/mes/ano
     * @return \DateTime data em que foi incluido no BD
     */
    public function getCriadoEm($op = null) {
        if(is_null($op)){
            return $this->criadoEm->format('d/m/Y');
        }
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

    /**
     * 
     * @return int Id do usuario que alterou o registro
     */
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

    /**
     * 
     * @param Boolean $op
     * @return String data formatada em dia/mes/ano
     * @return \DateTime data da ultima alteração
     */
    public function getAlteradoEm($op = null) {
        if(is_null($op)){
            return $this->alteradoEm->format('d/m/Y');
        }
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

    /**
     * 
     * @return \Livraria\Entity\Classe
     */
    public function getClasse() {
        return $this->classe;
    }

    /** 
     * Setar a qual entidade classe que pertence esta taxa
     * @param \Livraria\Entity\Classe $classe
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setClasse(Classe $classe) {
        $this->classe = $classe;
        return $this;
    }
    
    /**
     * 
     * @return \Livraria\Entity\Seguradora
     */
    public function getSeguradora() {
        return $this->seguradora;
    }

    /**
     * 
     * @param \Livraria\Entity\Seguradora $seguradora
     * @return \Livraria\Entity\Taxa
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']               = $this->getId();
        $data['inicio']           = $this->getInicio();
        $data['fim']              = $this->getFim();
        $data['status']           = $this->getStatus();
        $data['incendio']         = $this->floatToStr('Incendio',4);
        $data['incendioConteudo'] = $this->floatToStr('IncendioConteudo',4);
        $data['aluguel']          = $this->floatToStr('Aluguel',4);
        $data['eletrico']         = $this->floatToStr('Eletrico',4);
        $data['desastres']        = $this->floatToStr('Desastres',4);
        $data['incendioMen']         = $this->floatToStr('IncendioMen',4);
        $data['incendioConteudoMen'] = $this->floatToStr('IncendioConteudoMen',4);
        $data['aluguelMen']          = $this->floatToStr('AluguelMen',4);
        $data['eletricoMen']         = $this->floatToStr('EletricoMen',4);
        $data['desastresMen']        = $this->floatToStr('DesastresMen',4);
        $data['userIdCriado']     = $this->getUserIdCriado();
        $data['criadoEm']         = $this->getCriadoEm();
        $data['userIdAlterado']   = $this->getUserIdAlterado();
        $data['alteradoEm']       = $this->getAlteradoEm();
        $data['classe']           = $this->getClasse()->getId(); 
        $data['seguradora']       = $this->getSeguradora()->getId(); 
        return $data ;
    }

}
