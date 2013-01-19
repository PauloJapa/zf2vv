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
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     */
    protected $endereco;

    /**
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Administradora")
     * @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     */
    private $administradora;    

    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
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
    
    public function getTipo() {
        return $this->tipo;
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

    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']             = $this->getId();
        $data['nome']           = $this->getNome();
        $data['email']          = $this->getEmail();
        $data['tipo']           = $this->getTipo();
        $data['password']       = $this->getPassword();
        $data['salt']           = $this->getSalt();
        return $data ;
    }

}
