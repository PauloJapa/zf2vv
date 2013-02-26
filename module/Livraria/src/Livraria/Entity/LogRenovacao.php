<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogRenovacao
 * 
 * Todo historico do orçamento como inclusão, alterações, cancelamento motivo.
 * 
 * @ORM\Table(name="log_renovacao")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\LogRenovacaoRepository")
 */
class LogRenovacao extends Filtro
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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_criado", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var Renovacao
     *
     * @ORM\OneToOne(targetEntity="Renovacao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="renovacao_id", referencedColumnName="id")
     * })
     */
    private $renovacao;

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
     * @return \Livraria\Entity\LogRenovacao 
     */ 
    public function setId($id) {
        $this->id = $id;
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
     * @return \Livraria\Entity\LogRenovacao
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
     * @return \Livraria\Entity\LogRenovacao
     */
    public function setDePara($dePara) {
        $this->dePara = $dePara;
        return $this;
    }

    /**
     * Entity User a qual se referencia esse log
     * @return \Livraria\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /** 
     * Entity User a qual se referencia esse log
     * @param \Livraria\Entity\User $user
     * @return \Livraria\Entity\LogRenovacao 
     */ 
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * Entity Renovacao a qual se referencia esse log
     * @return \Livraria\Entity\Renovacao
     */
    public function getRenovacao() {
        return $this->renovacao;
    }

    /**
     * Entiry Renovacao a qual se referencia esse log
     * @param \Livraria\Entity\Renovacao $renovacao
     * @return \Livraria\Entity\LogRenovacao
     */
    public function setRenovacao(Renovacao $renovacao) {
        $this->renovacao = $renovacao;
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
     * @return \Livraria\Entity\LogRenovacao
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
     * @return \Livraria\Entity\LogRenovacao
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
     * @return \Livraria\Entity\LogRenovacao
     */
    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Entity convertida em array
     * @return array
     */
    public function toArray(){
        return [
            'id' => $this->getId(), 
            'controller' => $this->getController(), 
            'action' => $this->getAction(), 
            'user' => $this->getUser(), 
            'mensagem' => $this->getMensagem(), 
            'dePara' => $this->getDePara(), 
            'data' => $this->getData(), 
            'ip' => $this->getId(), 
            'renovacao' => $this->getRenovacao()->getId()
        ];
    }

}
