<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classe
 * 
 * Onde fica agrupada as taxas da seguradora com suas devidas vigencias. 
 *
 * @ORM\Table(name="classe")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\ClasseRepository")
 */
class Classe
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
     * @var string $cod
     *
     * @ORM\Column(name="cod", type="string", length=45, nullable=false)
     */
    protected $cod;

    /**
     * @var string $descricao
     *
     * @ORM\Column(name="descricao", type="string", length=100, nullable=false)
     */
    protected $descricao;

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
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    protected $status;


 
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
    
    /** 
     * Pega o id do registro
     * @return id 
     */ 
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

    public function getCod() {
        return $this->cod;
    }

    /** 
     * Setar o codigo da classe
     * @param String $cod
     * @return this 
     */ 
    public function setCod($cod) {
        $this->cod = $cod;
        return $this;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function __toString() {
        return $this->descricao;
    }


    /** 
     * Setar o descricao da Classe do registro
     * @param String $descricao
     * @return this 
     */ 
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
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

    public function getStatus() {
        return $this->status;
    }

    /** 
     * Setar o status do registro ativo bloqueado inativo
     * @param String $status
     * @return \Livraria\Entity\Seguradora 
     */ 
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }


    public function toArray() {
        $data['id']             = $this->getId();
        $data['cod']            = $this->getCod();
        $data['descricao']      = $this->getDescricao();
        $data['userIdCriado']   = $this->getUserIdCriado();
        $data['criadoEm']       = $this->getCriadoEm();
        $data['userIdAlterado'] = $this->getUserIdAlterado();
        $data['alteradoEm']     = $this->getAlteradoEm();
        $data['seguradora']     = $this->getSeguradora()->getId(); 
        $data['status']         = $this->getStatus();
        return $data ;
    }


}
