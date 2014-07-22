<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogFaturaCol
 * Entity gerar log de exportação para o sistema COL
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 *
 * @ORM\Table(name="log_fatura_col")
 * @ORM\Entity(repositoryClass="Livraria\Entity\LogFaturaColRepository")
 */
class LogFaturaCol extends Filtro
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
     * @var integer $administradoraId
     *
     * @ORM\Column(name="administradora_id", type="integer", nullable=false)
     */
    protected $administradoraId;

    /**
     * @var integer $mes
     *
     * @ORM\Column(name="mes", type="integer", nullable=false)
     */
    protected $mes;

    /**
     * @var integer $ano
     *
     * @ORM\Column(name="ano", type="integer", nullable=false)
     */
    protected $ano;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=true)
     */
    protected $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=true)
     */
    protected $criadoEm;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getAdministradoraId() {
        return $this->administradoraId;
    }

    public function setAdministradoraId($administradoraId) {
        $this->administradoraId = $administradoraId;
        return $this;
    }

    public function getMes() {
        return $this->mes;
    }

    public function setMes($mes) {
        $this->mes = $mes;
        return $this;
    }

    public function getAno() {
        return $this->ano;
    }

    public function setAno($ano) {
        $this->ano = $ano;
        return $this;
    }

    public function getUserIdCriado() {
        return $this->userIdCriado;
    }

    public function setUserIdCriado($userIdCriado) {
        $this->userIdCriado = $userIdCriado;
        return $this;
    }

    public function getCriadoEm() {
        return $this->criadoEm;
    }

    public function setCriadoEm(\DateTime $criadoEm) {
        $this->criadoEm = $criadoEm;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data                      = $this->getEndereco()->toArray();
        $data['id']                = $this->getId();
        $data['administradoraId']  = $this->getAdministradoraId();
        $data['mes']               = $this->getMes();
        $data['ano']               = $this->getAno();
        $data['userIdCriado']      = $this->getUserIdCriado();
        $data['criadoEm']          = $this->getCriadoEm();
        return $data ;
    }


}
