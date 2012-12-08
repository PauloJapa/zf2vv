<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="administradoras")
 * @ORM\Entity(repositoryClass="Livraria\Entity\AdministradoraRepository")
 */
class Administradora {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $nome;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $apelido;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $cnpj;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $tel;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(name="user_id_criado", type="integer")
     * @var int
     */
    protected $userIdCriado;
     
    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="criado_em", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="user_id_alterado", type="integer")
     * @var int
     */
    protected $userIdAlterado;
 
    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="alterado_em", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     */
    protected $endereco;
    

    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->createdAt = new \DateTime('now');
        $this->createdAt->setTimezone(new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now');
        $this->updatedAt->setTimezone(new \DateTimeZone('UTC')); 
        $this->userIdCriado = 1 ;
    }
     
    /**
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->updatedAt = new \DateTime('now');
        $this->updatedAt->setTimezone(new \DateTimeZone('UTC'));
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
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $createdAt->setTimezone(new \DateTimeZone('UTC'));
        $this->createdAt = $createdAt;
    }
 
    /**
     * @return \DateTime
     */
    public function getCreatedAt($time_zone = null)
    {
        $dateTime = new \DateTime($this->createdAt->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        if(is_null($time_zone)){
            $time_zone = date_default_timezone_get();
        }
        $dateTime->setTimezone(new \DateTimeZone($time_zone));
        return $dateTime;
    }

    public function getUserIdAlterado() {
        return $this->userIdAlterado;
    }

    public function setUserIdAlterado($userIdAlterado) {
        $this->userIdAlterado = $userIdAlterado;
        return $this;
    }
 
    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $updatedAt->setTimezone(new \DateTimeZone('UTC'));
        $this->updatedAt = $updatedAt;
    }
 
    /**
     * @param string $time_zone
     *
     * @return \DateTime
     */
    public function getUpdatedAt($time_zone = null)
    {
        $dateTime = new \DateTime($this->updatedAt->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        if(is_null($time_zone)){
            $time_zone = date_default_timezone_get();
        }
        $dateTime->setTimezone(new \DateTimeZone($time_zone));
        return $dateTime;
    }
    
    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
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
        $data['status']         = $this->getStatus();
        $data['userIdCriado']   = $this->getUserIdCriado();
        $data['CreatedAt']      = $this->getCreatedAt();
        $data['userIdAlterado'] = $this->getUserIdAlterado(); 
        return $data ;
    }

}
