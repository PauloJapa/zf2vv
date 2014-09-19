<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="administradoras")
 * @ORM\Entity(repositoryClass="Livraria\Entity\AdministradoraRepository")
 */
class Administradora extends Filtro {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $nome;
    
    /**
     * @ORM\Column(name="codigo_col", type="integer")
     * @var int
     */
    protected $codigoCol;

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
     * @var string $formaPagto
     *
     * @ORM\Column(name="forma_pagto", type="string", length=10, nullable=false)
     */
    protected $formaPagto;

    /**
     * @var string $validade
     *
     * @ORM\Column(name="validade", type="string", nullable=false)
     */
    protected $validade;

    /**
     * O tipo de cobertura do seguro quando do tipo comercial
     * @var string $tipoCobertura
     *
     * @ORM\Column(name="tipo_cobertura", type="string", length=2, nullable=false)
     */
    protected $tipoCobertura;

    /**
     * O tipo de cobertura do seguro quando do tipo residencial
     * @var string $tipoCoberturaRes
     *
     * @ORM\Column(name="tipo_cobertura_res", type="string", length=2, nullable=false)
     */
    protected $tipoCoberturaRes;

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
    protected $createdAt;

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
    protected $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     */
    protected $endereco;

    /**
     * @ORM\OneToOne(targetEntity="Livraria\Entity\Seguradora")
     * @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     */
    protected $seguradora;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $assist24;

    /**
     * @ORM\Column(name="prop_pag", type="string", length=2)
     * @var string
     */
    protected $propPag;
    

    
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

    /**
     * Nome da Administradora
     * @return string
     */
    public function __toString() {
        return $this->nome;
    }
    
    public function getObjeto(){
        return $this;
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
    
    public function getCodigoCol() {
        return $this->codigoCol;
    }

    public function setCodigoCol($codigoCol) {
        $this->codigoCol = $codigoCol;
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

    /**
     * Params 01=A vista(no ato), 02=2vezes(1+1), 03=3vezes(1+2)
     * @return string
     */
    public function getFormaPagto() {
        return $this->formaPagto;
    }
    
    /**
     * Params 01=A vista(no ato), 02=2vezes(1+1), 03=3vezes(1+2)
     * @param string $formaPagto
     * @return \Livraria\Entity\Administradora
     */
    public function setFormaPagto($formaPagto) {
        $this->formaPagto = $formaPagto;
        return $this;
    }
    
    /**
     * 'mensal'|'anual'
     * @return string
     */
    public function getValidade(){
        return $this->validade;
    }
    
    /**
     * 'mensal'|'anual'
     * @param string $validade
     * @return \Livraria\Entity\Administradora
     */
    public function setValidade($validade){
        $this->validade = $validade;
        return $this;
        
    }
    
    /**
     * Tipo de cobertura para comercial 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @return string
     */
    public function getTipoCobertura() {
        return $this->tipoCobertura;
    }

    /**
     * Tipo de cobertura para comercial 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @param string $tipoCobertura
     * @return \Livraria\Entity\Administradora
     */
    public function setTipoCobertura($tipoCobertura) {
        $this->tipoCobertura = $tipoCobertura;
        return $this;
    }
    
    /**
     * Tipo de cobertura para residencial 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @return string
     */
    public function getTipoCoberturaRes() {
        return $this->tipoCoberturaRes;
    }

    /**
     * Tipo de cobertura para residencial 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @param string $tipoCobertura
     * @return \Livraria\Entity\Administradora
     */
    public function setTipoCoberturaRes($tipoCoberturaRes) {
        $this->tipoCoberturaRes = $tipoCoberturaRes;
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
    
    /**
     * Retorna a Entity endereço
     * @return \Livraria\Entity\Endereco
     */
    public function getEndereco() {
        return $this->endereco;
    }

    /**
     * Entity Endereço da Administradora
     * @param \Livraria\Entity\Endereco $endereco
     * @return \Livraria\Entity\Administradora
     */
    public function setEndereco(Endereco $endereco) {
        $this->endereco = $endereco;
        return $this;
    }
    
    /**
     * Retorna a entity Seguradora
     * @return \Livraria\Entity\Seguradora
     */
    public function getSeguradora() {
        return $this->seguradora;
    }

    /**
     * Seguradora preferencial desta Administradora
     * @param \Livraria\Entity\Seguradora $seguradora
     * @return \Livraria\Entity\Administradora
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }
    
    /**
     * Assistencia da asseguradora 24 horas para o cliente
     * @return string
     */
    public function getAssist24() {
        return $this->assist24;
    }

    /**
     * Assistencia da asseguradora 24 horas para o cliente
     * @param string $assist24
     * @return \Livraria\Entity\Administradora
     */
    public function setAssist24($assist24) {
        $this->assist24 = $assist24;
        return $this;
    }
        
    /**
     * Proposta da administradora só será exibido uma unica forma de pagamento
     * @return string
     */
    public function getPropPag() {
        if(empty($this->propPag)){
            return 'N';
        }
        return $this->propPag;
    }

    /**
     * Pega se na proposta da administradora só será exibido uma unica forma de pagamento
     * @param string $propPag
     * @return \Livraria\Entity\Administradora
     */
    public function setPropPag($propPag) {
        $this->propPag = $propPag;
        return $this;
    }

    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']             = $this->getId();
        $data['nome']           = $this->getNome();
        $data['codigoCol']      = $this->getCodigoCol();
        $data['apelido']        = $this->getApelido();
        $data['cnpj']           = $this->getCnpj();
        $data['tel']            = $this->getTel();
        $data['email']          = $this->getEmail();
        $data['status']         = $this->getStatus();
        $data['formaPagto']     = $this->getFormaPagto();
        $data['validade']       = $this->getValidade();
        $data['tipoCobertura']  = $this->getTipoCobertura();
        $data['tipoCoberturaRes']  = $this->getTipoCoberturaRes();
        $data['userIdCriado']   = $this->getUserIdCriado();
        $data['CreatedAt']      = $this->getCreatedAt();
        $data['userIdAlterado'] = $this->getUserIdAlterado(); 
        $data['seguradora']     = $this->getSeguradora()->getId(); 
        $data['assist24']       = $this->getAssist24(); 
        $data['propPag']        = $this->getPropPag(); 
        return $data ;
    }

}
