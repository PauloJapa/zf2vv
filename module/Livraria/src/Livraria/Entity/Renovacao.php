<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Renovacao
 * Registros para renovação são gerados com base o mes de vencimento dos fechados
 * Cliente analisa a lista de seguros a renovar e apos o aceite do cliente é
 * gerado os novos fechamentos ou log dos que não fecharam
 *
 * @ORM\Table(name="renovacao")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\RenovacaoRepository")
 */
class Renovacao  extends AbstractSeguro 
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
     * @var integer $codano
     *
     * @ORM\Column(name="codano", type="integer", nullable=false)
     */
    protected $codano;

    /**
     * @var string $locadorNome
     *
     * @ORM\Column(name="locador_nome", type="string", length=100, nullable=false)
     */
    protected $locadorNome;

    /**
     * @var string $locatarioNome
     *
     * @ORM\Column(name="locatario_nome", type="string", length=100, nullable=false)
     */
    protected $locatarioNome;

    /**
     * @var float $valorAluguel
     *
     * @ORM\Column(name="valor_aluguel", type="decimal", nullable=false)
     */
    protected $valorAluguel;

    /**
     * @var string $tipoCobertura
     *
     * @ORM\Column(name="tipo_cobertura", type="string", length=2, nullable=false)
     */
    protected $tipoCobertura;

    /**
     * @var \DateTime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=false)
     */
    protected $inicio;

    /**
     * @var \DateTime $fim
     *
     * @ORM\Column(name="fim", type="datetime", nullable=false)
     */
    protected $fim;

    /**
     * @var string $seguroEmNome
     *
     * @ORM\Column(name="seguro_em_nome", type="string", length=2, nullable=false)
     */
    protected $seguroEmNome;

    /**
     * @var string $codigoGerente
     *
     * @ORM\Column(name="codigo_gerente", type="string", length=10, nullable=true)
     */
    protected $codigoGerente;

    /**
     * @var string $refImovel
     *
     * @ORM\Column(name="ref_imovel", type="string", length=20, nullable=true)
     */
    protected $refImovel;

    /**
     * @var string $formaPagto
     *
     * @ORM\Column(name="forma_pagto", type="string", length=10, nullable=false)
     */
    protected $formaPagto;

    /**
     * @var float $incendio
     *
     * @ORM\Column(name="incendio", type="decimal", nullable=false)
     */
    protected $incendio;

    /**
     * @var float $conteudo
     *
     * @ORM\Column(name="conteudo", type="decimal", nullable=true)
     */
    protected $conteudo;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", nullable=false)
     */
    protected $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", nullable=false)
     */
    protected $eletrico;

    /**
     * @var float $vendaval
     *
     * @ORM\Column(name="vendaval", type="decimal", nullable=false)
     */
    protected $vendaval;

    /**
     * @var integer $numeroParcela
     *
     * @ORM\Column(name="numero_parcela", type="integer", nullable=true)
     */
    protected $numeroParcela;

    /**
     * @var float $premioLiquido
     *
     * @ORM\Column(name="premio_liquido", type="decimal", nullable=false)
     */
    protected $premioLiquido;

    /**
     * @var float $premio
     *
     * @ORM\Column(name="premio", type="decimal", nullable=false)
     */
    protected $premio;

    /**
     * @var float $premioTotal
     *
     * @ORM\Column(name="premio_total", type="decimal", nullable=false)
     */
    protected $premioTotal;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    protected $criadoEm;

    /**
     * @var \DateTime $canceladoEm
     *
     * @ORM\Column(name="cancelado_em", type="datetime", nullable=true)
     */
    protected $canceladoEm;

    /**
     * @var \DateTime $alteradoEm
     *
     * @ORM\Column(name="alterado_em", type="datetime", nullable=true)
     */
    protected $alteradoEm;

    /**
     * @var string $observacao
     *
     * @ORM\Column(name="observacao", type="string", length=255, nullable=true)
     */
    protected $observacao;

    /**
     * @var string $gerado
     *
     * @ORM\Column(name="gerado", type="string", length=1, nullable=true)
     */
    protected $gerado;

    /**
     * @var float $comissao
     *
     * @ORM\Column(name="comissao", type="decimal", nullable=true)
     */
    protected $comissao;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=5, nullable=true)
     */
    protected $status;

    /**
     * @var integer $FechadoId
     *
     * @ORM\Column(name="fechado_id", type="integer", nullable=false)
     */
    protected $FechadoId;

    /**
     * @var integer $mesNiver
     *
     * @ORM\Column(name="mes_niver", type="integer", nullable=false)
     */
    protected $mesNiver;

    /**
     * @var integer $FechadoOrigemId
     *
     * @ORM\Column(name="fechado_origem_id", type="integer", nullable=true)
     */
    protected $FechadoOrigemId;

    /**
     * @var string $validade
     *
     * @ORM\Column(name="validade", type="string", length=10, nullable=true)
     */
    protected $validade;

    /**
     * @var string $ocupacao
     *
     * @ORM\Column(name="ocupacao", type="string", length=2, nullable=true)
     */
    protected $ocupacao;

    /**
     * @var float $taxaIof
     *
     * @ORM\Column(name="taxa_iof", type="decimal", nullable=false)
     */
    protected $taxaIof;

    /**
     * @var float $cobIncendio
     *
     * @ORM\Column(name="cob_incendio", type="decimal", nullable=false)
     */
    protected $cobIncendio;

    /**
     * @var float $cobConteudo
     *
     * @ORM\Column(name="cob_conteudo", type="decimal", nullable=false)
     */
    protected $cobConteudo;

    /**
     * @var float $cobAluguel
     *
     * @ORM\Column(name="cob_aluguel", type="decimal", nullable=false)
     */
    protected $cobAluguel;

    /**
     * @var float $cobEletrico
     *
     * @ORM\Column(name="cob_eletrico", type="decimal", nullable=false)
     */
    protected $cobEletrico;

    /**
     * @var float $cobVendaval
     *
     * @ORM\Column(name="cob_vendaval", type="decimal", nullable=false)
     */
    protected $cobVendaval;

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
     * @var Imovel
     *
     * @ORM\ManyToOne(targetEntity="Imovel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imovel_id", referencedColumnName="id")
     * })
     */
    protected $imovel;

    /**
     * @var Taxa
     *
     * @ORM\ManyToOne(targetEntity="Taxa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taxa_id", referencedColumnName="id")
     * })
     */
    protected $taxa;

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
     * @var Seguradora
     *
     * @ORM\ManyToOne(targetEntity="Seguradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     * })
     */
    protected $seguradora;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var MultiplosMinimos
     *
     * @ORM\ManyToOne(targetEntity="MultiplosMinimos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="multiplos_minimos_id", referencedColumnName="id_multiplos")
     * })
     */
    protected $multiplosMinimos;

    /**
     * @var ComissaoEnt
     *
     * @ORM\OneToOne(targetEntity="Comissao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="comissao_id", referencedColumnName="id")
     * })
     */
    protected $comissaoEnt;
 
    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * Tambem carrega as data de criadoEm e alteradoEm atuais 
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
        $this->criadoEm = new \DateTime('now');
        $this->criadoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo')); 
    }
     
    /**
     * Executa antes de salvar o registro atualizando assim a data de alteradoEm
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    }
    
    /**
     * Key do registro fechado gerado apos o aceite do cliente
     * @return integer
     */
    public function getFechadoId() {
        return $this->FechadoId;
    }

    /**
     * Key do registro fechado gerado apos o aceite do cliente
     * @param integer $FechadoId
     * @return \Livraria\Entity\Renovacao
     */
    public function setFechadoId($FechadoId) {
        $this->FechadoId = $FechadoId;
        return $this;
    }

    /**
     * Key do registro fechados que originou esta renovação
     * @return integer
     */
    public function getFechadoOrigemId() {
        return $this->FechadoOrigemId;
    }

    /**
     * Key do registro fechados que originou esta renovação
     * @param integer $FechadoOrigemId
     * @return \Livraria\Entity\Renovacao
     */
    public function setFechadoOrigemId($FechadoOrigemId) {
        $this->FechadoOrigemId = $FechadoOrigemId;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data = parent::toArray();
        $data['fechadoId']      = $this->getFechadoId();
        $data['fechadoOrigemId']= $this->getFechadoOrigemId();
        return $data ;
    }
}