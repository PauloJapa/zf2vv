<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParametroSis
 *
 * Paramentros para configurar os progrmas do sistema preencimento de selects
 * @ORM\Table(name="parametro_sis")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\ParametroSisRepository")
 */
class ParametroSis
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
     * @var string $key
     *
     * @ORM\Column(name="chave", type="string", length=20, nullable=false)
     */
    private $key;

    /**
     * @var string $conteudo
     *
     * @ORM\Column(name="conteudo", type="string", length=255, nullable=false)
     */
    private $conteudo;

    /**
     * @var string $descricao
     *
     * @ORM\Column(name="descricao", type="string", length=255, nullable=true)
     */
    private $descricao;
 
    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
    }

    /**
     * ID do registro
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * ID do registro
     * @param integer $id
     * @return \Livraria\Entity\ParametroSis
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Key do paramentro definido pelo programador
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Key do paramentro definido pelo programador
     * @param string $key
     * @return \Livraria\Entity\ParametroSis
     */
    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    /**
     * Conteudo da key que será de fato o parametro do prog.
     * @return string
     */
    public function getConteudo() {
        return $this->conteudo;
    }

    /**
     * Conteudo da key que será de fato o parametro do prog.
     * @param type $conteudo
     * @return \Livraria\Entity\ParametroSis
     */
    public function setConteudo($conteudo) {
        $this->conteudo = $conteudo;
        return $this;
    }

    /**
     * Descricao do conteudo do parametro do prog.
     * @return string
     */
    public function getDescricao() {
        return $this->descricao;
    }

    /**
     * Descricao do conteudo do parametro do prog.
     * @param type $descricao
     * @return \Livraria\Entity\ParametroSis
     */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
        return $this;
    }

    public function toArray(){
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'conteudo' => $this->getConteudo(),
            'descricao' => $this->getDescricao()
        ];
    }

}
