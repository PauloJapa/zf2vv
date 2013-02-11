<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogOrcamento
 * 
 * Todo historico do orçamento como inclusão, alterações, cancelamento motivo.
 * 
 * @ORM\Table(name="log_orcamento")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\OrcamentoRepository")
 */
class LogOrcamento
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
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=true)
     */
    private $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=true)
     */
    private $criadoEm;

    /**
     * @var string $mensagem
     *
     * @ORM\Column(name="mensagem", type="string", length=255, nullable=true)
     */
    private $mensagem;

    /**
     * @var string $dePara
     *
     * @ORM\Column(name="de_para", type="text", nullable=true)
     */
    private $dePara;

    /**
     * @var Orcamento
     *
     * @ORM\OneToOne(targetEntity="Orcamento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="orcamento_id", referencedColumnName="id")
     * })
     */
    private $orcamento;
    
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
     * @return \Livraria\Entity\LogOrcamento 
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @return int Id do usuario que cadastrou o registro
     */
    public function getUserIdCriado() {
        return $this->userIdCriado;
    }

    /** 
     * Setar o id do user que criou o registro
     * @param Int $userIdCriado
     * @return \Livraria\Entity\LogOrcamento 
     */ 
    public function setUserIdCriado($userIdCriado) {
        $this->userIdCriado = $userIdCriado;
        return $this;
    }

    /**
     * 
     * @param Boolean $op
     * @return String data formatada em dia/mes/ano
     * @return \DateTime data em que foi incluido no BD
     */
    public function getCriadoEm($op = null) {
        if(is_null($op)){
            return $this->criadoEm->format('d/m/Y');
        }
        return $this->criadoEm;
    }

    /** 
     * Setar quando foi criado o registro
     * @param \DateTime $criadoEm
     * @return \Livraria\Entity\LogOrcamento 
     */ 
    public function setCriadoEm(\DateTime $criadoEm) {
        $this->criadoEm = $criadoEm;
        return $this;
    }

    /**
     * Observação do log
     * @return string
     */
    public function getMensagem() {
        return $this->mensagem;
    }

    /**
     * Observação do log
     * @param type $mensagem
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
        return $this;
    }

    /**
     * string separada por ponto e virgula com campos alterados 
     * Formato de campo_nome; valor_antes; valor_depois;
     * @return string
     */
    public function getDePara() {
        return $this->dePara;
    }

    /**
     * Formato de campo_nome; valor_antes; valor_depois;
     * @param string $dePara
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setDePara($dePara) {
        $this->dePara = $dePara;
        return $this;
    }

    /**
     * Entiry Orcamento a qual se referencia esse log
     * @return \Livraria\Entity\Orcamento
     */
    public function getOrcamento() {
        return $this->orcamento;
    }

    /**
     * Entiry Orcamento a qual se referencia esse log
     * @param \Livraria\Entity\Orcamento $orcamento
     * @return \Livraria\Entity\LogOrcamento
     */
    public function setOrcamento(Orcamento $orcamento) {
        $this->orcamento = $orcamento;
        return $this;
    }

}
