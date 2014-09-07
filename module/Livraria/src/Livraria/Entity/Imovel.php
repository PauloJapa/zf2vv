<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Imovel
 * 
 * Guarda os imoveis e quem é o locador.
 *
 * @ORM\Table(name="imovel")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\ImovelRepository")
 */
class Imovel extends Filtro
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
     * @var string $tel
     *
     * @ORM\Column(name="tel", type="string", length=255, nullable=true)
     */
    protected $tel;

    /**
     * @var string $refImovel
     *
     * @ORM\Column(name="ref_imovel", type="string", length=30, nullable=true)
     */
    protected $refImovel;

    /**
     * @var string $rua
     *
     * @ORM\Column(name="rua", type="string", length=150, nullable=true)
     */
    protected $rua;

    /**
     * @var string $numero
     *
     * @ORM\Column(name="numero", type="string", length=15, nullable=true)
     */
    protected $numero;

    /**
     * @var string $bloco
     *
     * @ORM\Column(name="bloco", type="string", length=25, nullable=true)
     */
    protected $bloco;

    /**
     * @var string $apto
     *
     * @ORM\Column(name="apto", type="string", length=15, nullable=true)
     */
    protected $apto;

    /**
     * @var string $cep
     *
     * @ORM\Column(name="cep", type="string", length=10, nullable=true)
     */
    protected $cep;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    protected $status;
    
    /**
     * @var Atividade
     *
     * @ORM\ManyToOne(targetEntity="Atividade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="atividade_id", referencedColumnName="id")
     * })
     */
    protected $atividade;

    /**
     * @var Endereco
     *
     * @ORM\ManyToOne(targetEntity="Endereco")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     * })
     */
    protected $endereco;

    /**
     * @var Locador
     *
     * @ORM\ManyToOne(targetEntity="Locador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locador_id", referencedColumnName="id")
     * })
     */
    protected $locador;

    /**
     * @var Locatario
     *
     * @ORM\ManyToOne(targetEntity="Locatario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locatario_id", referencedColumnName="id")
     * })
     */
    protected $locatario;

    /**
     * @var integer $fechadoId
     *
     * @ORM\Column(name="fechados_id", type="integer", nullable=true)
     */
    protected $fechadoId;

    /**
     * @var integer $fechadoAno
     *
     * @ORM\Column(name="fechados_ano", type="integer", nullable=true)
     */
    protected $fechadoAno;

    /**
     * @var float $vlrAluguel
     *
     * @ORM\Column(name="vlr_aluguel", type="decimal", nullable=true)
     */
    protected $vlrAluguel;

    /**
     * @var \DateTime $fechadoFim
     *
     * @ORM\Column(name="fechado_fim", type="datetime", nullable=true)
     */
    protected $fechadoFim;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $compl;

 
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
     * Codigo de referencia em outro sistema
     * @return string
     */
    public function getRefImovel() {
        return is_null($this->refImovel) ? '' : $this->refImovel;
    }

    /**
     * Codigo de referencia em outro sistema
     * @param string $refImovel
     * @return \Livraria\Entity\Imovel
     */
    public function setRefImovel($refImovel) {
        $this->refImovel = $refImovel;
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
     * @return \Livraria\Entity\Locatario
     */
    public function setTel($tel) {
        $this->tel = $tel;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRua() {
        return $this->rua;
    }

    /**
     * 
     * @param string $rua
     * @return \Livraria\Entity\Imovel
     */
    public function setRua($rua) {
        $this->rua = $rua;
        return $this;
    }

    /**
     * numero no formato string
     * @return string 
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * Guarda o numeroo no formato string
     * @param string $numero
     * @return \Livraria\Entity\Imovel
     */
    public function setNumero($numero) {
        $this->numero = $numero;
        return $this;
    }

    /**
     * No caso de predio qual bloco
     * @return string
     */
    public function getBloco() {
        return $this->bloco;
    }

    /**
     * 
     * @param string $bloco
     * @return \Livraria\Entity\Imovel
     */
    public function setBloco($bloco) {
        $this->bloco = $bloco;
        return $this;
    }

    /**
     * No caso de predio qual apartamento
     * @return string
     */
    public function getApto() {
        return $this->apto;
    }

    /**
     * 
     * @param string $apto
     * @return \Livraria\Entity\Imovel
     */
    public function setApto($apto) {
        $this->apto = $apto;
        return $this;
    }

    /**
     * CEP do imovel 
     * @return string
     */
    public function getCep() {
        return $this->cep;
    }

    /**
     * 
     * @param string $cep
     * @return \Livraria\Entity\Imovel
     */
    public function setCep($cep) {
        $this->cep = $cep;
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
     * @return \Livraria\Entity\Locatario
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Entity Atividades para identificar a atual atividade desse imovel locado
     * @return \Livraria\Entity\Atividade
     */
    public function getAtividade() {
        return $this->atividade;
    }

    /**
     * Entity Atividades para identificar a atual atividade desse imovel locado
     * @param \Livraria\Entity\Atividade $atividade
     * @return \Livraria\Entity\Imovel
     */
    public function setAtividade(Atividade $atividade) {
        $this->atividade = $atividade;
        return $this;
    }

    /**
     * Pegar o restante do endereço do imovel na entity Endereco
     * @return \Livraria\Entity\Endereco
     */
    public function getEndereco() {
        return $this->endereco;
    }

    /**
     * Setar o restante do endereço do imovel na entity Endereco
     * @param \Livraria\Entity\Endereco $endereco
     * @return \Livraria\Entity\Imovel
     */
    public function setEndereco(Endereco $endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    /**
     * Pega entity locador com os dados do dono desse imovel
     * @return \Livraria\Entity\Locador
     */
    public function getLocador() {
        return $this->locador;
    }

    /**
     * 
     * @param \Livraria\Entity\Locador $locador
     * @return \Livraria\Entity\Imovel
     */
    public function setLocador(Locador $locador) {
        $this->locador = $locador;
        return $this;
    }

    /**
     * Locatario Atual ou ultimo a loca-lo do imovel
     * @return \Livraria\Entity\Locatario
     */
    public function getLocatario() {
        return $this->locatario;
    }

    /**
     * Locatario que esta locando o imovel
     * @param \Livraria\Entity\Locatario $locatario
     * @return \Livraria\Entity\Imovel
     */
    public function setLocatario(Locatario $locatario) {
        $this->locatario = $locatario;
        return $this;
    }
    
    /**
     * Codigo do seguro Atual para este imovel
     * @return integer
     */
    public function getFechadoId() {
        return $this->fechadoId;
    }

    /**
     * Codigo do seguro Atual para este imovel
     * @param integer $fechadoId
     * @return \Livraria\Entity\Imovel
     */
    public function setFechadoId($fechadoId) {
        $this->fechadoId = $fechadoId;
        return $this;
    }

    /**
     * Ano do seguro Atual para este imovel
     * @return integer
     */
    public function getFechadoAno() {
        return $this->fechadoAno;
    }

    /**
     * Ano do seguro Atual para este imovel
     * @param integer $fechadoAno
     * @return \Livraria\Entity\Imovel
     */
    public function setFechadoAno($fechadoAno) {
        $this->fechadoAno = $fechadoAno;
        return $this;
    }

    /**
     * Valor Atual do aluguel para este imovel
     * @return float
     */
    public function getVlrAluguel() {
        return $this->vlrAluguel;
    }

    /**
     * Valor Atual do aluguel para este imovel
     * @param float $vlrAluguel
     * @return \Livraria\Entity\Imovel
     */
    public function setVlrAluguel($vlrAluguel) {
        $this->vlrAluguel = $this->trataFloat($vlrAluguel);
        return $this;
    }

    /**
     * Data em que vence o seguro atual
     * @param string $op
     * @return \DateTime|'d/m/Y'
     */
    public function getFechadoFim($op = null) {
        if($op == 'obj'){
            return $this->fechadoFim;
        }
        return $this->trataData($this->fechadoFim, '-');
    }

    /**
     * Data em que vence o seguro atual
     * @param \DateTime $fechadoFim
     * @return \Livraria\Entity\Imovel
     */
    public function setFechadoFim(\DateTime $fechadoFim) {
        $this->fechadoFim = $fechadoFim;
        return $this;
    }

    public function getCompl() {
        return $this->compl;
    }

    public function setCompl($compl) {
        $this->compl = $compl;
        return $this;
    }


    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data              = $this->getEndereco()->toArray();
        $data['id']        = $this->getId();
        $data['tel']       = $this->getTel();
        $data['refImovel'] = $this->getRefImovel();
        $data['bloco']     = $this->getBloco();
        $data['apto']      = $this->getApto();
        $data['atividadeDesc'] = $this->getAtividade();
        $data['atividade']     = $this->getAtividade()->getId();
        $data['locadorDesc']   = $this->getLocador();
        $data['locatarioNome'] = $this->getLocatario();
        $data['locador']       = $this->getLocador()->getId();
        $data['locatario']     = $this->getLocatario()->getId();
        $data['status']        = $this->getStatus();
        $data['fechadoId']     = $this->getFechadoId();
        $data['fechadoAno']    = $this->getFechadoAno();
        $data['vlrAluguel']    = $this->floatToStr('VlrAluguel');
        $data['fechadoFim']    = $this->getFechadoFim();
        $data['compl']         = $this->getCompl();
        return $data ;
    }
    
    /**
     * Metodo magico para retornar o endereço do imovel
     * @return string
     */
    public function __toString() {
        if($this->numero == 0){
            return $this->rua . ' ' . $this->bloco . ' ' . $this->apto . ' ' . $this->compl . ' CEP ' . $this->cep;
        }else{
            return $this->rua . ' n:' . $this->numero . ' ' . $this->bloco . ' ' . $this->apto . ' ' . $this->compl . ' CEP ' . $this->cep;
        }
    }


}
