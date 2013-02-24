<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogOrcamento
 * 
 * Todo historico do orçamento como inclusão, alterações, cancelamento motivo.
 * 
 * @ORM\Table(name="log_orcamento")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\OrcamentoRepository")
 */
class LogOrcamento extends Filtro
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
     * @var string $controller
     *
     * @ORM\Column(name="controller", type="string", length=30, nullable=true)
     */
    private $controller;

    /**
     * @var string $action
     *
     * @ORM\Column(name="action", type="string", length=30, nullable=true)
     */
    private $action;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=true)
     */
    private $userIdCriado;

    /**
     * @var string $mensagem
     *
     * @ORM\Column(name="mensagem", type="string", length=255, nullable=true)
     */
    private $mensagem;

    /**
     * @var string $dePara
     *
     * @ORM\Column(name="de_para", type="text", nullable=true)
     */
    private $dePara;

    /**
     * @var \DateTime $data
     *
     * @ORM\Column(name="data", type="datetime", nullable=false)
     */
    private $data;

    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=20, nullable=false)
     */
    private $ip;

    /**
     * @var Orcamento
     *
     * @ORM\ManyToOne(targetEntity="Orcamento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="orcamento_id", referencedColumnName="id")
     * })
     */
    private $orcamento;

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
     * @return int $id do registro
     */
    public function getId() {
        return $this->id;
    }

    /** 
     * Setar o id do registro
     * @param Int $id
     * @return \Livraria\Entity\LogOrcamento 
     */ 
    public function setId($id) {
        $this->id = $id;
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
     * @return \Livraria\Entity\LogOrcamento 
     */ 
    public function setUserIdCriado($userIdCriado) {
        $this->userIdCriado = $userIdCriado;
        return $this;
    }

    /**
     * Observação do log
     * @return string
     */
    public function getMensagem() {
        return $this->mensagem;
    }

    /**
     * Observação do log
     * @param type $mensagem
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
        return $this;
    }

    /**
     * string separada por ponto e virgula com campos alterados 
     * Formato de campo_nome; valor_antes; valor_depois;
     * @return string
     */
    public function getDePara() {
        return $this->dePara;
    }

    /**
     * Formato de campo_nome; valor_antes; valor_depois;
     * @param string $dePara
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setDePara($dePara) {
        $this->dePara = $dePara;
        return $this;
    }

    /**
     * Entiry Orcamento a qual se referencia esse log
     * @return \Livraria\Entity\Orcamento
     */
    public function getOrcamento() {
        return $this->orcamento;
    }

    /**
     * Entiry Orcamento a qual se referencia esse log
     * @param \Livraria\Entity\Orcamento $orcamento
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setOrcamento(Orcamento $orcamento) {
        $this->orcamento = $orcamento;
        return $this;
    }

    /**
     * nome do Controller 
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Nome do controller
     * @param string $controller
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setController($controller) {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Nome da ação que gerou o log
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Nome da ação que gerou o log
     * @param string $action
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    /**
     * Retorna o objeto data ou uma string formatada da data
     * @return \DateTime | string
     */
    public function getData($op='') {
        if($op == 'obj'){
            return $this->data;
        }
        return $this->trataData($this->data);
    }

    public function setData(\DateTime $data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Com numerp do ip 
     * @return string 
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * 
     * @param string $ip
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }


    
    
    
    

}
