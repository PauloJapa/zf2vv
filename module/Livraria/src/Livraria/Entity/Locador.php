<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Locador
 * 
 * Donos dos imoveis.
 *
 * @ORM\Table(name="locador")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\LocadorRepository")
 */
class Locador extends Filtro
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
     * @var string $nome
     *
     * @ORM\Column(name="nome", type="string", length=255, nullable=false)
     */
    protected $nome;

    /**
     * @var string $tipo
     *
     * @ORM\Column(name="tipo", type="string", length=5, nullable=false)
     */
    protected $tipo;

    /**
     * @var string $cpf
     *
     * @ORM\Column(name="cpf", type="string", length=45, nullable=true)
     */
    protected $cpf;

    /**
     * @var string $cnpj
     *
     * @ORM\Column(name="cnpj", type="string", length=45, nullable=true)
     */
    protected $cnpj;

    /**
     * @var string $tel
     *
     * @ORM\Column(name="tel", type="string", length=255, nullable=true)
     */
    protected $tel;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    protected $status;

    /**
     * @var Endereco
     *
     * @ORM\ManyToOne(targetEntity="Endereco")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $endereco;

    /**
     * @var Administradora
     *
     * @ORM\ManyToOne(targetEntity="Administradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     * })
     */
    protected $administradora;

 
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
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Nome do locador
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * 
     * @param string $nome
     * @return \Livraria\Entity\Locador
     */
    public function setNome($nome) {
        $this->nome = $nome;
        return $this;
    }

    /**
     * Valores Fisica|Juridica
     * @return String
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Valores Fisica|Juridica
     * @param string $tipo
     * @return \Livraria\Entity\Locador
     */
    public function setTipo($tipo) {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * Retorna cpf com ou sem formatação dos pontos
     * @param bolean $formatado
     * @return string
     */
    public function getCpf($formatado=true) {
        if($formatado){
            return $this->formatarCPF_CNPJ($this->cpf);
        }
        return $this->cpf;
    }

    /**
     * CPF no formato 00000000000
     * @param string $cpf
     * @return \Livraria\Entity\Locador
     */
    public function setCpf($cpf) {
        $this->cpf = $this->cleanDocFomatacao($cpf);
        return $this;
    }

    /**
     * Retorna cnpj com ou sem formatação dos pontos
     * @param bolean $formatado
     * @return string
     */
    public function getCnpj($formatado=true) {
        if($formatado){
            return $this->formatarCPF_CNPJ($this->cnpj);
        }
        return $this->cnpj;
    }

    /**
     * CNPJ no formato 00000000000000
     * @param string $cnpj
     * @return \Livraria\Entity\Locador
     */
    public function setCnpj($cnpj) {
        $this->cnpj = $this->cleanDocFomatacao($cnpj);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getTel() {
        return $this->tel;
    }

    /**
     * 
     * @param string $tel
     * @return \Livraria\Entity\Locador
     */
    public function setTel($tel) {
        $this->tel = $tel;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * 
     * @param string $email
     * @return \Livraria\Entity\Locador
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * A = ativo, B = bloqueado, C = cancelado
     * @return string 
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * A = ativo, B = bloqueado, C = cancelado
     * @param string $status
     * @return \Livraria\Entity\Locador
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Entity Endereco
     * Dados do endereço do locador se houver
     * @return \Livraria\Entity\Endereco
     */
    public function getEndereco() {
        return $this->endereco;
    }

    /**
     * Entity Endereco
     * @param \Livraria\Entity\Endereco $endereco
     * @return \Livraria\Entity\Locador
     */
    public function setEndereco(Endereco $endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    /**
     * Entity Administradora
     * @return \Livraria\Entity\Administradora
     */
    public function getAdministradora() {
        return $this->administradora;
    }

    /**
     * Entity Administradora
     * @param \Livraria\Entity\Administradora $administradora
     * @return \Livraria\Entity\Locador
     */
    public function setAdministradora(Administradora $administradora) {
        $this->administradora = $administradora;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']    = $this->getId();
        $data['nome']  = $this->getNome();
        $data['tipo']  = $this->getTipo();
        $data['cpf']   = $this->getCpf();
        $data['cnpj']  = $this->getCnpj();
        $data['tel']   = $this->getTel();
        $data['email'] = $this->getEmail();
        $data['status'] = $this->getStatus();
        $data['administradora'] = $this->getAdministradora()->getId();
        $data['administradoraDesc'] = $this->getAdministradora();
        return $data ;
    }
    
    /**
     * Metodo magico para retornar o nome do locador
     * @return string
     */
    public function __toString() {
        return $this->nome;
    }

}
