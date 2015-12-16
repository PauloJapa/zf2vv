<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Livraria\Entity\UserRepository")
 */
class User {

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
    protected $email;
    
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $tipo;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $salt;
    
    /**
     * @ORM\Column(name="is_admin", type="boolean")
     * @var boolean
     */
    protected $isAdmin;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     */
    protected $endereco;

    /**
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Administradora")
     * @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     */
    protected $administradora;  

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $email2;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $menu;  

    public function __construct($options = null) {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        Configurator::configure($this, $options);
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

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    /**
     * Retorna parametro da base de dados ou Descrição do paramentro
     * @param string $op
     * @return string
     */
    public function getTipo($op='') {
        if(empty($op))
            return $this->tipo;
        
        switch ($this->tipo){
            case 'admin':
                return 'Vila Velha';
                break;
            case 'user':
                return 'Imobiliaria';
                break;
            case 'gest':
                return 'Visitante';
                break;
            default:
                return $this->tipo;
        }
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
        return $this;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {

        $hashSenha = $this->encryptPassword($password);
        $this->password = $hashSenha;
        return $this;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function encryptPassword($password) {
        $hashSenha = hash('sha512', $password . $this->salt);
        for ($i = 0; $i < 64000; $i++)
            $hashSenha = hash('sha512', $hashSenha);
        
        return $hashSenha;
    }

    public function getIsAdmin() {
        return $this->isAdmin;
    }

    public function setIsAdmin($isAdmin) {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    /** 
     * Setar o status do registro ativo bloqueado inativo
     * @param String $status
     * @return \Livraria\Entity\User
     */ 
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    public function getAdministradora() {
        return $this->administradora;
    }

    public function setAdministradora($administradora) {
        $this->administradora = $administradora;
        return $this;
    }

    public function getEmail2() {
        return $this->email2;
    }

    public function setEmail2($email) {
        $this->email2 = $email;
        return $this;
    }
    
    public function getMenu() {
        return $this->menu;
    }

    public function setMenu($menu) {
        $this->menu = $menu;
        return $this;
    }

    
    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']             = $this->getId();
        $data['nome']           = $this->getNome();
        $data['email']          = $this->getEmail();
        $data['tipo']           = $this->getTipo();
        $data['password']       = $this->getPassword();
        $data['salt']           = $this->getSalt();
        $data['isAdmin']        = $this->getIsAdmin();
        $data['status']         = $this->getStatus();
        $data['administradora'] = $this->getAdministradora()->getId();
        $data['administradoraDesc'] = $this->getAdministradora()->getNome();
        $data['email2']          = $this->getEmail2();
        $data['menu']          = $this->getMenu();
        return $data ;
    }
    
    public function __toString() {
        return $this->nome;
    }

}
