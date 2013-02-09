<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $id;

    /**
     * @var string $descricao
     *
     * @ORM\Column(name="descricao", type="string", length=150, nullable=false)
     */
    private $descricao;

    /**
     * @var string $codSeguradora
     *
     * @ORM\Column(name="cod_seguradora", type="string", length=45, nullable=false)
     */
    private $codSeguradora;

    /**
     * @var string $ocupacao
     *
     * @ORM\Column(name="ocupacao", type="string", length=45, nullable=false)
     */
    private $ocupacao;

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
    public function getOcupacao() {
        return $this->ocupacao;
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
        return $data ;
    }
    
    /**
     * Metodo magico para retornar o nome do locador
     * @return string
     */
    public function __toString() {
        return $this->descricao;
    }

}
