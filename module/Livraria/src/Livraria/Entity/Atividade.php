<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Atividade
 *
 * Contém todas os tipos de Atividades(Ocupações) dos imoveis 
 * Ex: Residencial, apartamento, cabelereiro
 * @ORM\Table(name="atividade")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\AtividadeRepository")
 */
class Atividade
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $descricao
     *
     * @ORM\Column(name="descricao", type="string", length=150, nullable=false)
     */
    protected $descricao;

    /**
     * @var string $codSeguradora
     *
     * @ORM\Column(name="cod_seguradora", type="string", length=45, nullable=false)
     */
    protected $codSeguradora;

    /**
     * @var string $ocupacao
     *
     * @ORM\Column(name="ocupacao", type="string", length=45, nullable=false)
     */
    protected $ocupacao;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=false)
     */
    protected $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    protected $criadoEm;

    /**
     * @var integer $userIdAlterado
     *
     * @ORM\Column(name="user_id_alterado", type="integer", nullable=true)
     */
    protected $userIdAlterado;

    /**
     * @var \DateTime $alteradoEm
     *
     * @ORM\Column(name="alterado_em", type="datetime", nullable=true)
     */
    protected $alteradoEm;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    protected $status;

    /**
     * @var string $danosEletricos
     *
     * @ORM\Column(name="danos_eletricos", type="string", length=1, nullable=false)
     */
    protected $danosEletricos;

    /**
     * @var string $equipEletro
     *
     * @ORM\Column(name="equip_eletro", type="string", length=1, nullable=false)
     */
    protected $equipEletro;

    /**
     * @var string $vendavalFumaca
     *
     * @ORM\Column(name="vendaval_fumaca", type="string", length=1, nullable=false)
     */
    protected $vendavalFumaca;

    /**
     * @var string $basica
     *
     * @ORM\Column(name="basica", type="string", length=10, nullable=false)
     */
    protected $basica;

    /**
     * @var string $roubo
     *
     * @ORM\Column(name="roubo", type="string", length=10, nullable=false)
     */
    protected $roubo;

    /**
     * @var integer $seguradoraId
     *
     * @ORM\Column(name="seguradora_id", type="integer", nullable=true)
     */
    protected $seguradoraId;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $classeAtividades
     *
     * @ORM\OneToMany(targetEntity="ClasseAtividade", mappedBy="atividade")
     */
    protected $classeAtividades;

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
        $this->classeAtividades = new ArrayCollection();
    }

    /**
     * Executa antes de salvar o registro atualizando assim a data de alteradoEm
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    }
    
    /**
     * 
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @param int $id
     * @return \Atividade
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @return string da Atividade do imovel
     */
    public function getDescricao() {
        return $this->descricao;
    }

    /**
     * 
     * @param string $descricao da Atividade do imovel
     * @return \Atividade
     */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * 
     * @return string codigo referencia na seguradora
     */
    public function getCodSeguradora() {
        return $this->codSeguradora;
    }

    /**
     * 
     * @param string $codSeguradora codigo referencia na seguradora
     * @return \Atividade
     */
    public function setCodSeguradora($codSeguradora) {
        $this->codSeguradora = $codSeguradora;
        return $this;
    }

    /**
     * 
     * @return string da categoria de ocupação
     */
    public function getOcupacao($op='') {
        if(empty($op))
            return $this->ocupacao;
        
        switch ($this->ocupacao) {
            case '01':
                return 'Comércio e Serviços';
                break;
            case '02':
                return 'Residencial';
                break;
            case '03':
                return 'Industria';
                break;

            default:
                return 'Desconhecido';
                break;
        }
    }

    /**
     * 
     * @param type $ocupacao da categoria de ocupação
     * @return \Atividade
     */
    public function setOcupacao($ocupacao) {
        $this->ocupacao = $ocupacao;
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
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Paramentro para franquia diferenciada 
     * @return string
     */
    public function getDanosEletricos() {
        return $this->danosEletricos;
    }

    public function setDanosEletricos($danosEletricos) {
        $this->danosEletricos = $danosEletricos;
        return $this;
    }

    /**
     * Paramentro para franquia diferenciada 
     * @return string
     */
    public function getEquipEletro() {
        return $this->equipEletro;
    }

    public function setEquipEletro($equipEletro) {
        $this->equipEletro = $equipEletro;
        return $this;
    }

    /**
     * Paramentro para franquia diferenciada 
     * @return string
     */
    public function getVendavalFumaca() {
        return $this->vendavalFumaca;
    }

    public function setVendavalFumaca($vendavalFumaca) {
        $this->vendavalFumaca = $vendavalFumaca;
        return $this;
    }

    public function getBasica() {
        return $this->basica;
    }

    public function setBasica($basica) {
        $this->basica = $basica;
        return $this;
    }

    public function getRoubo() {
        return $this->roubo;
    }

    public function setRoubo($roubo) {
        $this->roubo = $roubo;
        return $this;
    }
    
    
    public function getSeguradoraId() {
        return $this->seguradoraId;
    }

    public function setSeguradoraId($seguradoraId) {
        $this->seguradoraId = $seguradoraId;
        return $this;
    }

        /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']             = $this->getId();
        $data['descricao']      = $this->getDescricao();
        $data['codSeguradora']  = $this->getCodSeguradora();
        $data['ocupacao']       = $this->getOcupacao();
        $data['userIdCriado']   = $this->getUserIdCriado();
        $data['criadoEm']       = $this->getCriadoEm();
        $data['userIdAlterado'] = $this->getUserIdAlterado();
        $data['alteradoEm']     = $this->getAlteradoEm();
        $data['status']         = $this->getStatus();
        $data['danosEletricos'] = $this->getDanosEletricos();
        $data['equipEletro']    = $this->getEquipEletro();
        $data['vendavalFumaca'] = $this->getVendavalFumaca();
        $data['basica']         = $this->getBasica();
        $data['roubo']          = $this->getRoubo();
        $data['seguradoraId']   = $this->getSeguradoraId();
        return $data ;
    }
    
    /**
     * Metodo magico para retornar o nome do locador
     * @return string
     */
    public function __toString() {
        return $this->descricao;
    }
    
    public function listClasseAtividade() {
        return $this->classeAtividades->toArray();
    }
    
    public function addClasseAtividade($classeAtividade) {
        $this->classeAtividades->add($classeAtividade);
    }
    
    public function findClasseFor(\DateTime $date = null) {
        /* @var $classeAtividade \Livraria\Entity\ClasseAtividade */
        $classeAtividades = $this->listClasseAtividade();
        // procura uma ativa e dentro do periodo
        foreach ($classeAtividades as $key => $classeAtividade) {
            if($classeAtividade->getStatus() == 'A' AND $classeAtividade->getInicio('obj') <= $date){
                return $classeAtividade->getClasseTaxas();
            }
        }
        // procura apensa no periodo
        foreach ($classeAtividades as $key => $classeAtividade) {
            if($classeAtividade->getStatus() == 'A'){
                continue;
            }
            if($classeAtividade->getInicio('obj') <= $date AND $classeAtividade->getFim('obj') >= $date){
                return $classeAtividade->getClasseTaxas();
            }
        }
        
        return FALSE;
    }

}
