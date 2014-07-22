<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="enderecos")
 * @ORM\Entity(repositoryClass="Livraria\Entity\EnderecoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Endereco {

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
    protected $rua;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $numero;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $compl;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $cep;

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
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Bairro", inversedBy="endereco")
     * @ORM\JoinColumn(name="bairros_id", referencedColumnName="id")
     */
    protected $bairro;

    /**
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Cidade", inversedBy="endereco")
     * @ORM\JoinColumn(name="cidades_id", referencedColumnName="id")
     */
    protected $cidade;

    /**
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Estado", inversedBy="endereco")
     * @ORM\JoinColumn(name="estados_id", referencedColumnName="id")
     */
    protected $estado;

    /**
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Pais", inversedBy="endereco")
     * @ORM\JoinColumn(name="paises_id", referencedColumnName="id")
     */
    protected $pais;



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

    public function getRua() {
        return $this->rua;
    }

    public function setRua($rua) {
        $this->rua = $rua;
        return $this;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
        return $this;
    }

    public function getCompl() {
        return $this->compl;
    }

    public function setCompl($compl) {
        $this->compl = $compl;
        return $this;
    }

    public function getCep() {
        return $this->cep;
    }

    public function setCep($cep) {
        $this->cep = $cep;
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

    public function getBairro() {
        return $this->bairro;
    }

    public function setBairro($bairro) {
        $this->bairro = $bairro;
        return $this;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
        return $this;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
        return $this;
    }

    public function getPais() {
        return $this->pais;
    }

    public function setPais($pais) {
        $this->pais = $pais;
        return $this;
    }

        
    public function toArray() {
        return array(
            'idEnde' => $this->getId(),
            'rua'    => $this->getRua(),
            'numero' => $this->getNumero(),
            'compl'  => $this->getCompl(),
            'cep'    => $this->getCep(),
            'bairro'     => $this->getBairro()->getId(),
            'bairroDesc' => $this->getBairro()->getNome(),
            'cidade'     => $this->getCidade()->getId(),
            'cidadeDesc' => $this->getCidade()->getNome(),
            'estado'     => $this->getEstado()->getId(),
            'estadoDesc' => $this->getEstado()->getNome(),
            'pais'       => $this->getPais()->getId(),
            'paisDesc'   => $this->getPais()->getNome()
        );
    }

}
