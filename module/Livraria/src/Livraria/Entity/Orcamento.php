<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orcamento
 * 
 * Orçamento de seguros para se realizar calculos e decidir melhor preço para o fechamento
 * Parte principal onde faz a junção de todos os parametros e validações dos calculos
 *
 * @ORM\Table(name="orcamento")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Livraria\Entity\OrcamentoRepository")
 */
class Orcamento extends AbstractSeguro {
 
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
     * @ORM\Column(name="valor_aluguel", type="decimal", precision=20, scale=8, nullable=false)
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
     * @ORM\Column(name="incendio", type="decimal", precision=20, scale=8, nullable=true)
     */
    protected $incendio;

    /**
     * @var float $conteudo
     *
     * @ORM\Column(name="conteudo", type="decimal", precision=20, scale=8, nullable=true)
     */
    protected $conteudo;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $eletrico;

    /**
     * @var float $vendaval
     *
     * @ORM\Column(name="vendaval", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $vendaval;

    /**
     * @var float $respcivil
     *
     * @ORM\Column(name="respcivil", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $respcivil;

    /**
     * @var integer $numeroParcela
     *
     * @ORM\Column(name="numero_parcela", type="integer", nullable=true)
     */
    protected $numeroParcela;

    /**
     * @var float $premioLiquido
     *
     * @ORM\Column(name="premio_liquido", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $premioLiquido;

    /**
     * @var float $premio
     *
     * @ORM\Column(name="premio", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $premio;

    /**
     * @var float $premioTotal
     *
     * @ORM\Column(name="premio_total", type="decimal", precision=20, scale=8, nullable=false)
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
     * @ORM\Column(name="observacao", type="string", length=400, nullable=true)
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
     * @ORM\Column(name="comissao", type="decimal", precision=10, scale=8, nullable=true)
     */
    protected $comissao;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=5, nullable=true)
     */
    protected $status;

    /**
     * @var integer $codFechado
     *
     * @ORM\Column(name="fechado_id", type="integer", nullable=false)
     */
    protected $fechadoId;

    /**
     * @var integer $mesNiver
     *
     * @ORM\Column(name="mes_niver", type="integer", nullable=false)
     */
    protected $mesNiver;

    /**
     * @var string $validade
     *
     * @ORM\Column(name="validade", type="string", nullable=false)
     */
    protected $validade;

    /**
     * @var string $ocupacao
     *
     * @ORM\Column(name="ocupacao", type="string", length=2, nullable=false)
     */
    protected $ocupacao;

    /**
     * @var float $taxaIof
     *
     * @ORM\Column(name="taxa_iof", type="decimal", precision=10, scale=8, nullable=false)
     */
    protected $taxaIof;

    /**
     * @var float $cobIncendio
     *
     * @ORM\Column(name="cob_incendio", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobIncendio;

    /**
     * @var float $cobConteudo
     *
     * @ORM\Column(name="cob_conteudo", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobConteudo;

    /**
     * @var float $cobAluguel
     *
     * @ORM\Column(name="cob_aluguel", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobAluguel;

    /**
     * @var float $cobEletrico
     *
     * @ORM\Column(name="cob_eletrico", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobEletrico;

    /**
     * @var float $cobVendaval
     *
     * @ORM\Column(name="cob_vendaval", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobVendaval;

    /**
     * @var float $cobRespcivil
     *
     * @ORM\Column(name="cob_respcivil", type="decimal", precision=20, scale=8, nullable=false)
     */
    protected $cobRespcivil;
    
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
     * @ORM\ManyToOne(targetEntity="Comissao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="comissao_id", referencedColumnName="id")
     * })
     */
    protected $comissaoEnt;

    /**
     * @var integer $FechadoOrigemId
     *
     * @ORM\Column(name="fechado_origem_id", type="integer", nullable=true)
     */
    protected $fechadoOrigemId;

    /**
     * @var integer $mensalSeq
     *
     * @ORM\Column(name="mensal_seq", type="integer", nullable=true)
     */
    protected $mensalSeq;

    /**
     * @var string $orcaReno
     * Indica se registro é um orcamento ou renovação
     * @ORM\Column(name="orca_reno", type="string", length=5, nullable=true)
     */
    protected $orcaReno;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $assist24;

    /**
     * @var float $taxaAjuste
     *
     * @ORM\Column(name="taxa_ajuste", type="decimal", precision=10, scale=8, nullable=false)
     */
    protected $taxaAjuste;

    /**
     * @var string 
     * Codigo de Identificação no cliente que poder ser numero ou letras
     * @ORM\Column(name="referencia", type="string", length=30, nullable=true)
     */
    protected $referencia;
    
    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * Tambem carrega as data de criadoEm e alteradoEm atuais 
     * @param Array $options
     */    
    public function __construct($options = null) {
        $this->criadoEm = new \DateTime('now');
        $this->criadoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo')); 
        Configurator::configure($this, $options);
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
     * Campo com id do seguro fechado
     * @return int 
     */
    public function getFechadoId() {
        return $this->fechadoId;
    }

    /**
     * Se fechar o orçamento preencher este campo com id do seguro fechado
     * @param string 
     * @return \Livraria\Entity\Orcamento
     */
    public function setFechadoId($codFechado) {
        $this->fechadoId = $codFechado;
        return $this;
    }
    
    /**
     * Indica se o registro é um orcamento(orca) ou renovação(reno)
     * @return string
     */
    public function getOrcaReno() {
        return $this->orcaReno;
    }

    /**
     * Setar se o registro é um orcamento(orca) ou renovação(reno)
     * @param string $orcaReno
     * @return \Livraria\Entity\Orcamento
     */
    public function setOrcaReno($orcaReno) {
        $this->orcaReno = $orcaReno;
        return $this;
    }

    /**
     * 
     * @return string da situação do registro
     */
    public function getStatus($op='') {
        if (empty($op)){
            return $this->status;            
        }
        switch ($this->status) {
            case 'A':
            case 'R':
                if($this->getOrcaReno() == 'orca'){
                    return 'Orçamento';                     
                }else{
                    return 'Renovação';                     
                }
            case 'C':
                if($this->getOrcaReno() == 'orca'){
                    return 'Cancelado Orça';                     
                }else{
                    return 'Cancelado Reno';                     
                }
            case 'F':
                if($this->getOrcaReno() == 'orca'){
                    return 'Fechou Orça';                     
                }else{
                    return 'Fechou Reno';                     
                }
            default:
                return 'Desconhecido'; 
        }
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data = parent::toArray();
        $data['fechadoId']     = $this->getFechadoId();
        $data['orcaReno']      = $this->getOrcaReno();
        return $data;
    }
    
    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArrayWithObj() {
        $data = parent::toArrayWithObj();
        $data['fechadoId']     = $this->getFechadoId();
        $data['orcaReno']      = $this->getOrcaReno();
        return $data;
    }
 
}
