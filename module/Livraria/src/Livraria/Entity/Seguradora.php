<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="seguradora")
 * @ORM\Entity(repositoryClass="Livraria\Entity\SeguradoraRepository")
 */
class Seguradora
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
     * @var string $nome
     *
     * @ORM\Column(name="nome", type="string", length=150, nullable=false)
     */
    private $nome;

    /**
     * @var string $apelido
     *
     * @ORM\Column(name="apelido", type="string", length=45, nullable=true)
     */
    private $apelido;

    /**
     * @var string $cnpj
     *
     * @ORM\Column(name="cnpj", type="string", length=45, nullable=false)
     */
    private $cnpj;

    /**
     * @var string $tel
     *
     * @ORM\Column(name="tel", type="string", length=255, nullable=true)
     */
    private $tel;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string $site
     *
     * @ORM\Column(name="site", type="string", length=255, nullable=true)
     */
    private $site;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=true)
     */
    private $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=true)
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
     * @var Enderecos
     *
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     */
    private $enderecos;
    

    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->criadoEm = new \DateTime('now');
        $this->criadoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo')); 
        $this->userIdCriado = 1 ;
    }
     
    /**
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
        return $this;
    }

    public function getApelido() {
        return $this->apelido;
    }

    public function setApelido($apelido) {
        $this->apelido = $apelido;
        return $this;
    }

    public function getCnpj() {
        return $this->cnpj;
    }

    public function setCnpj($cnpj) {
        $this->cnpj = $cnpj;
        return $this;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setTel($tel) {
        $this->tel = $tel;
        return $this;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function getSite() {
        return $this->site;
    }

    public function setSite($site) {
        $this->site = $site;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getUserIdCriado() {
        return $this->userIdCriado;
    }

    public function setUserIdCriado($userIdCriado) {
        $this->userIdCriado = $userIdCriado;
        return $this;
    }
 
    /**
     * @return \DateTime
     */
    public function getCriadoEm($time_zone = null) {
        $dateTime = new \DateTime($this->criadoEm->format('Y-m-d H:i:s'), new \DateTimeZone('America/Sao_Paulo'));
        if(is_null($time_zone)){
            $time_zone = date_default_timezone_get();
        }
        $dateTime->setTimezone(new \DateTimeZone($time_zone));
        return $dateTime;
    }       
    
    /**
     * @param \DateTime $criadoEm
     */
    public function setCriadoEm(\DateTime $criadoEm) {
        $criadoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->criadoEm = $criadoEm;
        return $this;
    }

    public function getUserIdAlterado() {
        return $this->userIdAlterado;
    }

    public function setUserIdAlterado($userIdAlterado) {
        $this->userIdAlterado = $userIdAlterado;
        return $this;
    }
 
    /**
     * @param string $time_zone
     *
     * @return \DateTime
     */
    public function getAlteradoEm($time_zone = null) {
        $dateTime = new \DateTime($this->alteradoEm->format('Y-m-d H:i:s'), new \DateTimeZone('America/Sao_Paulo'));
        if(is_null($time_zone)){
            $time_zone = date_default_timezone_get();
        }
        $dateTime->setTimezone(new \DateTimeZone($time_zone));
        return $dateTime;
    }
 
    /**
     * @param \DateTime $alteradoEm
     */
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->alteradoEm = $alteradoEm;
        return $this;
    }

    public function getEnderecos() {
        return $this->enderecos;
    }

    public function setEnderecos(Endereco $enderecos) {
        $this->enderecos = $enderecos;
        return $this;
    }

    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']             = $this->getId();            
        $data['nome']           = $this->getNome();          
        $data['apelido']        = $this->getApelido();       
        $data['cnpj']           = $this->getCnpj();          
        $data['tel']            = $this->getTel();           
        $data['email']          = $this->getEmail();         
        $data['site']           = $this->getSite();          
        $data['status']         = $this->getStatus();        
        $data['userIdCriado']   = $this->getUseridcriado();  
        $data['criadoEm']       = $this->getCriadoem();      
        $data['userIdAlterado'] = $this->getUseridalterado();
        $data['alteradoEm']     = $this->getAlteradoem();    
        return $data ;
    }


}
