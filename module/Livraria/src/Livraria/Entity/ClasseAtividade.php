<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClasseAtividade
 * 
 * Faz ligação entre as taxas(classes) e atividades e mantem o historico de vigencia.
 *
 * @ORM\Table(name="classe_atividade")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\ClasseAtividadeRepository")
 */
class ClasseAtividade
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
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

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
     *   @ORM\JoinColumn(name="classe_taxas_id", referencedColumnName="id")
     * })
     */
    private $classeTaxas;

    /**
     * @var Atividade
     *
     * @ORM\ManyToOne(targetEntity="Atividade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="atividade_id", referencedColumnName="id")
     * })
     */
    private $atividade;

    /**
     * @var integer $codOld
     *
     * @ORM\Column(name="cod_old", type="integer", nullable=true)
     */
    private $codOld;

    /**
     * @var integer $codciaOld
     *
     * @ORM\Column(name="codcia_old", type="integer", nullable=true)
     */
    private $codciaOld;

    /**
     * @var string $seq
     *
     * @ORM\Column(name="seq", type="string", length=1, nullable=true)
     */
    private $seq;
 
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
    }
     
    /**
     * Executa antes de salvar o registro atualizando assim a data de alteradoEm
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
     * Altera o valor do id do registro
     * @param int $id 
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /** 
     * Retorna o inicio da vigência da atividade com essa classe
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto data 
     */ 
    public function getInicio($op = null) {
        if(is_null($op)){
            return $this->inicio->format('d/m/Y');
        }
        return $this->inicio;
    }
    
    /** 
     * Setar o inicio da vigência da ligação entre classe e atividade
     * @param \DateTime $inicio
     * @return \Livraria\Entity\ClasseAtividade 
     */ 
    public function setInicio(\DateTime $inicio) {
        $this->inicio = $inicio;
        return $this;
    }

    /** 
     * Retorna o fim da vigência entre classe e atividade
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto  
     */ 
    public function getFim($op = null) {
        if($op == 'obj'){
            return $this->fim;
        }
        $check = $this->fim->format('d/m/Y');
        if($check == '01/01/1000'){
            return "vigente";
        }else{
            return $check;
        }
    }


    /** 
     * Setar terminino da vigência da taxa para manter historico
     * @param \DateTime $fim
     * @return \Livraria\Entity\ClasseAtividade 
     */ 
    public function setFim(\DateTime $fim) {
        $this->fim = $fim;
        return $this;
    }

    /**
     * 
     * @return string da situação do registro
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * 
     * @param string $status da situação do registro
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setStatus($status) {
        $this->status = $status;
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
     * @return \Livraria\Entity\ClasseAtividade 
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
     * @return \Livraria\Entity\ClasseAtividade 
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
     * @return \Livraria\Entity\ClasseAtividade 
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
     * @return \Livraria\Entity\ClasseAtividade 
     */ 
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $this->alteradoEm = $alteradoEm;
        return $this;
    }

    /**
     * Retorna as entitys Classe correspondente
     * @return \Livraria\Entity\Classe
     */
    public function getClasseTaxas() {
        return $this->classeTaxas;
    }

    /**
     * Define a ligação com a classe(taxas)
     * @param \Livraria\Entity\Classe $classeTaxas
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setClasseTaxas(Classe $classeTaxas) {
        $this->classeTaxas = $classeTaxas;
        return $this;
    }

    /**
     * Retorna as entitys Atividade correspondente
     * @return \Livraria\Entity\Atividade
     */
    public function getAtividade() {
        return $this->atividade;
    }

    /**
     * Define a ligação com a entity Atividade 
     * @param \Livraria\Entity\Atividade $atividade
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setAtividade(Atividade $atividade) {
        $this->atividade = $atividade;
        return $this;
    }
    
    /**
     * Manter compatibilidade com BD antigo
     * @return integer
     */
    public function getCodOld() {
        return $this->codOld;
    }

    /**
     * Manter compatibilidade com BD antigo
     * @param integer $codOld
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setCodOld($codOld) {
        $this->codOld = $codOld;
        return $this;
    }
    
    /**
     * Manter compatibilidade com BD antigo
     * @return integer
     */
    public function getCodciaOld() {
        return $this->codciaOld;
    }

    /**
     * Manter compatibilidade com BD antigo
     * @param integer $codiciaOld
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setCodciaOld($codciaOld) {
        $this->codciaOld = $codciaOld;
        return $this;
    }

    /**
     * Manter compatibilidade com BD antigo
     * @return string
     */
    public function getSeq() {
        return $this->seq;
    }

    /**
     * Manter compatibilidade com BD antigo
     * @param string $seq
     * @return \Livraria\Entity\ClasseAtividade
     */
    public function setSeq($seq) {
        $this->seq = $seq;
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
        $data['userIdCriado']     = $this->getUserIdCriado();
        $data['criadoEm']         = $this->getCriadoEm();
        $data['userIdAlterado']   = $this->getUserIdAlterado();
        $data['alteradoEm']       = $this->getAlteradoEm();
        $data['classeTaxas']      = $this->getClasseTaxas()->getId(); 
        $data['atividade']        = $this->getAtividade()->getId(); 
        $data['atividadeDesc']    = $this->getAtividade(); 
        $data['codOld']           = $this->getCodOld(); 
        $data['codciaOld']        = $this->getCodciaOld(); 
        $data['seq']              = $this->getSeq(); 
        return $data ;
    }

}
