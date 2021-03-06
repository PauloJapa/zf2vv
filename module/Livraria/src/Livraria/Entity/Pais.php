<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="paises")
 * @ORM\Entity(repositoryClass="Livraria\Entity\PaisRepository")
 */
class Pais {

    public function __construct($options = null) {
        Configurator::configure($this,$options);
        $this->enderecos = new ArrayCollection();
    }
    
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
    protected $sigla;
    
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $codigo;
    
    /**
     * @ORM\OneToMany(targetEntity="Livraria\Entity\Endereco", mappedBy="pais")
     */
    protected $enderecos;  
    
    
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
    
    public function getSigla() {
        return $this->sigla;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
        return $this;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
        return $this;
    }
    
    public function __toString() {
        return $this->nome;
    }
    
    public function toArray() {
        return array(
            'id'=>$this->getId(),
            'nome'=>$this->getNome(),
            'sigla'=>$this->getSigla(),
            'codigo'=>$this->getCodigo()
        );
    }

    public function getEnderecos() {
        return $this->enderecos;
    }
    
}
