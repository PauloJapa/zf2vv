<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="cidades")
 * @ORM\Entity(repositoryClass="Livraria\Entity\CidadeRepository")
 */
class Cidade {
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
     * @ORM\OneToMany(targetEntity="Livraria\Entity\Endereco", mappedBy="cidade")
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

    public function __toString() {
        return $this->nome;
    }
    
    public function toArray() {
        return array('id'=>$this->getId(),'nome'=>$this->getNome());
    }

    public function getEnderecos() {
        return $this->enderecos;
    }



}
