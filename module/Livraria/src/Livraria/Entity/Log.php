<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Log
 * Entity para manipular os registros da tabela
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 *
 * @ORM\Table(name="logs")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\LogRepository")
 */
class Log
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
     * @var string $tabela
     *
     * @ORM\Column(name="tabela", type="string", length=30, nullable=false)
     */
    private $tabela;

    /**
     * @var string $controller
     *
     * @ORM\Column(name="controller", type="string", length=30, nullable=false)
     */
    private $controller;

    /**
     * @var string $action
     *
     * @ORM\Column(name="action", type="string", length=30, nullable=false)
     */
    private $action;

    /**
     * @var integer $idDoReg
     *
     * @ORM\Column(name="id_do_reg", type="integer", nullable=false)
     */
    private $idDoReg;

    /**
     * @var string $dePara
     *
     * @ORM\Column(name="de_para", type="text", nullable=false)
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
     * @var Users
     *
     * @ORM\OneToOne(targetEntity="Livraria\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $user;

    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * Tambem carrega as data de criadoEm e alteradoEm atuais 
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->data = new \DateTime('now');
        $this->data->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->userIdCriado = 1 ;
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
    public function getTabela() {
        return $this->tabela;
    }

    /**
     * Em qual tabela foi feita a operação 
     * @param type $tabela
     * @return \Livraria\Entity\Log
     */
    public function setTabela($tabela) {
        $this->tabela = $tabela;
        return $this;
    }

    /**
     * Retorna o controller que solicitou a operação
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Controller que esta fazendo a operação
     * @param string $controller
     * @return \Livraria\Entity\Log
     */
    public function setController($controller) {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Action que estava sendo executada
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Action que esta sendo executada
     * @param string $action
     * @return \Livraria\Entity\Log
     */
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    /**
     * Identificação do registro manipulado
     * @return int
     */
    public function getIdDoReg() {
        return $this->idDoReg;
    }

    /**
     * Identificação do registro manipulado
     * @param int $idDoReg
     * @return \Livraria\Entity\Log
     */
    public function setIdDoReg($idDoReg) {
        $this->idDoReg = $idDoReg;
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
     * @return \Livraria\Entity\Log
     */
    public function setDePara($dePara) {
        $this->dePara = $dePara;
        return $this;
    }


    /**
     * 
     * @param Boolean $op
     * @return String data formatada em dia/mes/ano
     * @return \DateTime data em que foi incluido no BD
     */
    public function getData($op = null) {
        if(is_null($op)){
            return $this->data->format('d/m/Y');
        }
        return $this->data;
    }

    /** 
     * Setar quando foi criado o registro
     * @param \DateTime $criadoEm
     * @return \Livraria\Entity\Log
     */ 
    public function setData(\DateTime $data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Numero do ip do usuario no momento do acesso
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * Numero do ip do usuario no momento do acesso
     * @param string $ip
     * @return \Livraria\Entity\Log
     */
    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Dados do usuario que efetuou a operação
     * @return \Livraria\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Entity do usuario que efetuou a ação
     * @param \Livraria\Entity\User $user
     * @return \Livraria\Entity\Log
     */
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']         = $this->getId();
        $data['tabela']     = $this->getTabela();
        $data['controller'] = $this->getController();
        $data['action']     = $this->getAction();
        $data['idDoReg']    = $this->getIdDoReg();
        $data['dePara']     = $this->getDePara();
        $data['data']       = $this->getData();
        $data['ip']         = $this->getIp();
        $data['user']       = $this->getUser()->getId(); 
        return $data ;
    }


}
