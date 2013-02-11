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
class Orcamento
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
     * @var integer $codano
     *
     * @ORM\Column(name="codano", type="integer", nullable=false)
     */
    private $codano;

    /**
     * @var string $locadorNome
     *
     * @ORM\Column(name="locador_nome", type="string", length=100, nullable=false)
     */
    private $locadorNome;

    /**
     * @var string $locatarioNome
     *
     * @ORM\Column(name="locatario_nome", type="string", length=100, nullable=false)
     */
    private $locatarioNome;

    /**
     * @var float $valorAluguel
     *
     * @ORM\Column(name="valor_aluguel", type="decimal", nullable=false)
     */
    private $valorAluguel;

    /**
     * @var string $tipoCobertura
     *
     * @ORM\Column(name="tipo_cobertura", type="string", length=2, nullable=false)
     */
    private $tipoCobertura;

    /**
     * @var \DateTime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=false)
     */
    private $inicio;

    /**
     * @var \DateTime $fim
     *
     * @ORM\Column(name="fim", type="datetime", nullable=false)
     */
    private $fim;

    /**
     * @var string $seguroEmNome
     *
     * @ORM\Column(name="seguro_em_nome", type="string", length=2, nullable=false)
     */
    private $seguroEmNome;

    /**
     * @var string $codigoGerente
     *
     * @ORM\Column(name="codigo_gerente", type="string", length=10, nullable=true)
     */
    private $codigoGerente;

    /**
     * @var string $refImovel
     *
     * @ORM\Column(name="ref_imovel", type="string", length=20, nullable=true)
     */
    private $refImovel;

    /**
     * @var string $formaPagto
     *
     * @ORM\Column(name="forma_pagto", type="string", length=10, nullable=false)
     */
    private $formaPagto;

    /**
     * @var float $incendio
     *
     * @ORM\Column(name="incendio", type="decimal", nullable=false)
     */
    private $incendio;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", nullable=false)
     */
    private $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", nullable=false)
     */
    private $eletrico;

    /**
     * @var float $vendaval
     *
     * @ORM\Column(name="vendaval", type="decimal", nullable=false)
     */
    private $vendaval;

    /**
     * @var integer $numeroParcela
     *
     * @ORM\Column(name="numero_parcela", type="integer", nullable=true)
     */
    private $numeroParcela;

    /**
     * @var float $premioLiquido
     *
     * @ORM\Column(name="premio_liquido", type="decimal", nullable=false)
     */
    private $premioLiquido;

    /**
     * @var float $premio
     *
     * @ORM\Column(name="premio", type="decimal", nullable=false)
     */
    private $premio;

    /**
     * @var float $premioTotal
     *
     * @ORM\Column(name="premio_total", type="decimal", nullable=false)
     */
    private $premioTotal;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    private $criadoEm;

    /**
     * @var \DateTime $canceladoEm
     *
     * @ORM\Column(name="cancelado_em", type="datetime", nullable=true)
     */
    private $canceladoEm;

    /**
     * @var \DateTime $alteradoEm
     *
     * @ORM\Column(name="alterado_em", type="datetime", nullable=true)
     */
    private $alteradoEm;

    /**
     * @var string $observacao
     *
     * @ORM\Column(name="observacao", type="string", length=255, nullable=true)
     */
    private $observacao;

    /**
     * @var string $gerado
     *
     * @ORM\Column(name="gerado", type="string", length=1, nullable=true)
     */
    private $gerado;

    /**
     * @var float $comissao
     *
     * @ORM\Column(name="comissao", type="decimal", nullable=true)
     */
    private $comissao;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=5, nullable=true)
     */
    private $status;

    /**
     * @var integer $codFechado
     *
     * @ORM\Column(name="cod_fechado", type="integer", nullable=false)
     */
    private $codFechado;

    /**
     * @var integer $mesNiver
     *
     * @ORM\Column(name="mes_niver", type="integer", nullable=false)
     */
    private $mesNiver;

    /**
     * @var Locador
     *
     * @ORM\OneToOne(targetEntity="Locador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locador_id", referencedColumnName="id")
     * })
     */
    private $locador;

    /**
     * @var Locatario
     *
     * @ORM\OneToOne(targetEntity="Locatario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locatario_id", referencedColumnName="id")
     * })
     */
    private $locatario;

    /**
     * @var Imovel
     *
     * @ORM\OneToOne(targetEntity="Imovel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imovel_id", referencedColumnName="id")
     * })
     */
    private $imovel;

    /**
     * @var Taxa
     *
     * @ORM\OneToOne(targetEntity="Taxa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taxa_id", referencedColumnName="id")
     * })
     */
    private $taxa;

    /**
     * @var Atividade
     *
     * @ORM\OneToOne(targetEntity="Atividade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="atividade_id", referencedColumnName="id")
     * })
     */
    private $atividade;

    /**
     * @var Seguradora
     *
     * @ORM\OneToOne(targetEntity="Seguradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     * })
     */
    private $seguradora;

    /**
     * @var Administradora
     *
     * @ORM\OneToOne(targetEntity="Administradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     * })
     */
    private $administradora;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $user;
 
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
     * 
     * @return int $id do registro
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Altera o valor do id do registro
     * @param int $id 
     * @return \Livraria\Entity\orcamento
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /** 
     * Retorna o inicio da vigência da atividade com essa classe
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto data 
     */ 
    public function getInicio($op = null) {
        if(is_null($op)){
            return $this->inicio->format('d/m/Y');
        }
        return $this->inicio;
    }
    
    /** 
     * Setar o inicio da vigência da ligação entre classe e atividade
     * @param \DateTime $inicio
     * @return \Livraria\Entity\orcamento 
     */ 
    public function setInicio(\DateTime $inicio) {
        $this->inicio = $inicio;
        return $this;
    }

    /** 
     * Retorna o fim da vigência entre classe e atividade
     * @param String $op para retornar o objeto data ou string data
     * @return \DateTime | string data no formato dd/mm/aaaa 
     */ 
    public function getFim($op = null) {
        if($op == 'obj'){
            return $this->fim;
        }
        return $this->fim->format('d/m/Y');
    }


    /** 
     * Setar terminino da vigência da orcamento para manter historico
     * @param \DateTime $fim
     * @return \Livraria\Entity\orcamento 
     */ 
    public function setFim(\DateTime $fim) {
        $this->fim = $fim;
        return $this;
    }

    /**
     * 
     * @return string da situação do registro
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * 
     * @param string $status da situação do registro
     * @return \Livraria\Entity\orcamento
     */
    public function setStatus($status) {
        $this->status = $status;
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
     * @return \Livraria\Entity\orcamento 
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
     * @return \Livraria\Entity\orcamento 
     */ 
    public function setCriadoEm(\DateTime $criadoEm) {
        $this->criadoEm = $criadoEm;
        return $this;
    }

    /**
     * 
     * @return int Id do usuario que alterou o registro
     */
    public function getUserIdAlterado() {
        return $this->userIdAlterado;
    }

    /** 
     * Setar o id do user que alterou da ultima vez o registro
     * @param Int $userIdAlterado
     * @return \Livraria\Entity\orcamento 
     */ 
    public function setUserIdAlterado($userIdAlterado) {
        $this->userIdAlterado = $userIdAlterado;
        return $this;
    }

    /**
     * 
     * @param Boolean $op
     * @return String data formatada em dia/mes/ano
     * @return \DateTime data da ultima alteração
     */
    public function getAlteradoEm($op = null) {
        if(is_null($op)){
            return $this->alteradoEm->format('d/m/Y');
        }
        return $this->alteradoEm;
    }

    /** 
     * Setar quando foi alterado o registro
     * @param \DateTime $alteradoEm
     * @return \Livraria\Entity\orcamento 
     */ 
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $this->alteradoEm = $alteradoEm;
        return $this;
    }
    
    /**
     * Ano do orcamento
     * @return int
     */
    public function getCodano() {
        return $this->codano;
    }

    /**
     * Ano do orcamento
     * @param int $codano
     * @return \Livraria\Entity\Orcamento
     */
    public function setCodano($codano) {
        $this->codano = $codano;
        return $this;
    }

    /**
     * Nome do locador
     * @return string 
     */
    public function getLocadorNome() {
        return $this->locadorNome;
    }

    /**
     * Nome do locador
     * @param string $locadorNome
     * @return \Livraria\Entity\Orcamento
     */
    public function setLocadorNome($locadorNome) {
        $this->locadorNome = $locadorNome;
        return $this;
    }

    /**
     * Nome do Locatario
     * @return string
     */
    public function getLocatarioNome() {
        return $this->locatarioNome;
    }

    /**
     * Nome do Locatario
     * @param string $locatarioNome
     * @return \Livraria\Entity\Orcamento
     */
    public function setLocatarioNome($locatarioNome) {
        $this->locatarioNome = $locatarioNome;
        return $this;
    }

    /**
     * Valor do aluguel base de todo calculo
     * @return float
     */
    public function getValorAluguel() {
        return $this->valorAluguel;
    }

    /**
     * Valor do aluguel base de todo calculo
     * @param string $valorAluguel
     * @return \Livraria\Entity\Orcamento
     */
    public function setValorAluguel($valorAluguel) {
        $this->valorAluguel = $this->strToFloat($valorAluguel);
        return $this;
    }
    
    /**
     * Tipo de cobertura 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @return string
     */
    public function getTipoCobertura() {
        return $this->tipoCobertura;
    }

    /**
     * Tipo de cobertura 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @param string $tipoCobertura
     * @return \Livraria\Entity\Orcamento
     */
    public function setTipoCobertura($tipoCobertura) {
        $this->tipoCobertura = $tipoCobertura;
        return $this;
    }

        /**
     * Para Locador 01 para locatario 02
     * @return string
     */
    public function getSeguroEmNome() {
        return $this->seguroEmNome;
    }

    /**
     * Para Locador 01 para locatario 02
     * @param string $seguroEmNome
     * @return \Livraria\Entity\Orcamento
     */
    public function setSeguroEmNome($seguroEmNome) {
        $this->seguroEmNome = $seguroEmNome;
        return $this;
    }

    /**
     * Campo de pouco uso e não esta definido no processo provavel para calculo de comissão
     * @return string
     */
    public function getCodigoGerente() {
        return $this->codigoGerente;
    }

    /**
     * Campo de pouco uso e não esta definido no processo provavel para calculo de comissão
     * @param string $codigoGerente
     * @return \Livraria\Entity\Orcamento
     */
    public function setCodigoGerente($codigoGerente) {
        $this->codigoGerente = $codigoGerente;
        return $this;
    }

    /**
     * Codigo do imovel na Administradora para busca rapida
     * @return string
     */
    public function getRefImovel() {
        return $this->refImovel;
    }

    /**
     * Codigo do imovel na Administradora para busca rapida
     * @param string $refImovel
     * @return \Livraria\Entity\Orcamento
     */
    public function setRefImovel($refImovel) {
        $this->refImovel = $refImovel;
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
     * @return \Livraria\Entity\Orcamento
     */
    public function setFormaPagto($formaPagto) {
        $this->formaPagto = $formaPagto;
        return $this;
    }

    /**
     * Cobertura para incendio baseado no multiplo incendio da seguradora vezes aluguel
     * @return float
     */
    public function getIncendio() {
        return $this->incendio;
    }

    /**
     * Cobertura para incendio baseado no multiplo incendio da seguradora vezes aluguel
     * @param string $incendio
     * @return \Livraria\Entity\Orcamento
     */
    public function setIncendio($incendio) {
        $this->incendio = $this->strToFloat($incendio);
        return $this;
    }

    /**
     * Cobertura para aluguel baseado no multiplo aluguel da seguradora vezes aluguel
     * @return float
     */
    public function getAluguel() {
        return $this->aluguel;
    }

    /**
     * Cobertura para aluguel baseado no multiplo aluguel da seguradora vezes aluguel
     * @param string $aluguel
     * @return \Livraria\Entity\Orcamento
     */
    public function setAluguel($aluguel) {
        $this->aluguel = $this->strToFloat($aluguel);
        return $this;
    }

    /**
     * Cobertura para danos eletrico baseado no multiplo eletrico da seguradora vezes aluguel
     * @return float
     */
    public function getEletrico() {
        return $this->eletrico;
    }

    /**
     * Cobertura para danos eletrico baseado no multiplo eletrico da seguradora vezes aluguel
     * @param string $eletrico
     * @return \Livraria\Entity\Orcamento
     */
    public function setEletrico($eletrico) {
        $this->eletrico = $this->strToFloat($eletrico);
        return $this;
    }

    /**
     * Cobertura para desastres naturais baseado no multiplo vendaval da seguradora vezes aluguel
     * @return string
     */
    public function getVendaval() {
        return $this->vendaval;
    }

    /**
     * Cobertura para desastres naturais baseado no multiplo vendaval da seguradora vezes aluguel
     * @param string $vendaval
     * @return \Livraria\Entity\Orcamento
     */
    public function setVendaval($vendaval) {
        $this->vendaval = $this->strToFloat($vendaval);
        return $this;
    }

    /**
     * nao esta bem definido sua utilização
     * @return type
     */
    public function getNumeroParcela() {
        return $this->numeroParcela;
    }

    /**
     * nao esta bem definido sua utilização
     * @param int $numeroParcela
     * @return \Livraria\Entity\Orcamento
     */
    public function setNumeroParcela($numeroParcela) {
        $this->numeroParcela = $numeroParcela;
        return $this;
    }

    /**
     * Valor liquido do seguro
     * @return float
     */
    public function getPremioLiquido() {
        return $this->premioLiquido;
    }

    /**
     * Valor liquido do seguro
     * @param string $premioLiquido
     * @return \Livraria\Entity\Orcamento
     */
    public function setPremioLiquido($premioLiquido) {
        $this->premioLiquido = $this->strToFloat($premioLiquido);
        return $this;
    }

    /**
     * Valor do seguro
     * @return float
     */
    public function getPremio() {
        return $this->premio;
    }

    /**
     * Valor do seguro
     * @param string $premio
     * @return \Livraria\Entity\Orcamento
     */
    public function setPremio($premio) {
        $this->premio = $this->strToFloat($premio);
        return $this;
    }

    /**
     * Valor total do seguro
     * @return float
     */
    public function getPremioTotal() {
        return $this->premioTotal;
    }
         
    /**
     * Valor total do seguro
     * @param string $premioTotal
     * @return \Livraria\Entity\Orcamento
     */
    public function setPremioTotal($premioTotal) {
        $this->premioTotal = $this->strToFloat($premioTotal);
        return $this;
    }

    /**
     * Data em que foi cancelado o seguro 
     * @return \DateTime | string
     */
    public function getCanceladoEm($op = null) {
        if(is_null($op)){
            return $this->canceladoEm->format('d/m/Y');
        }
        return $this->canceladoEm;
    }

    /**
     * Data em que foi cancelado o seguro 
     * @param \DateTime $canceladoEm
     * @return \Livraria\Entity\Orcamento
     */
    public function setCanceladoEm(\DateTime $canceladoEm) {
        $this->canceladoEm = $canceladoEm;
        return $this;
    }

    /**
     * Observações do orcamento outro detalhes ficam no log de auditoria
     * @return string(255)
     */
    public function getObservacao() {
        return $this->observacao;
    }

    /**
     * Observações do orcamento outro detalhes ficam no log de auditoria
     * @param string $observacao
     * @return \Livraria\Entity\Orcamento
     */
    public function setObservacao($observacao) {
        $this->observacao = $observacao;
        return $this;
    }

    /**
     * Não esta bem definido possivel se gerou para exportação
     * @return string(1)
     */
    public function getGerado() {
        return $this->gerado;
    }

    /**
     * Não esta bem definido possivel se gerou para exportação
     * @param string(1) $gerado
     * @return \Livraria\Entity\Orcamento
     */
    public function setGerado($gerado) {
        $this->gerado = $gerado;
        return $this;
    }

    /**
     * Valor da comissão para Administradora 
     * @return float
     */
    public function getComissao() {
        return $this->comissao;
    }

    /**
     * Valor da comissão para Administradora 
     * Preenchido com base na comissão na entity de Comissao
     * @param string $comissao
     * @return \Livraria\Entity\Orcamento
     */
    public function setComissao($comissao) {
        $this->comissao = $this->strToFloat($comissao);
        return $this;
    }

    /**
     * Campo com id do seguro fechado
     * @return int
     */
    public function getCodFechado() {
        return $this->codFechado;
    }

    /**
     * Se fechar o orçamento preencher este campo com id do seguro fechado
     * @param type $codFechado
     * @return \Livraria\Entity\Orcamento
     */
    public function setCodFechado($codFechado) {
        $this->codFechado = $codFechado;
        return $this;
    }

    /**
     * Mes de aniversario paramentro para seguro fechado
     * @return int
     */
    public function getMesNiver() {
        return $this->mesNiver;
    }

    /**
     * Mes de aniversario paramentro para seguro fechado
     * @param int $mesNiver
     * @return \Livraria\Entity\Orcamento
     */
    public function setMesNiver($mesNiver) {
        $this->mesNiver = $mesNiver;
        return $this;
    }

    /**
     * Todos os dados da entity locador
     * @return \Livraria\Entity\Locador
     */
    public function getLocador() {
        return $this->locador;
    }

    /**
     * Entity do Locador
     * @param \Livraria\Entity\Locador $locador
     * @return \Livraria\Entity\Orcamento
     */
    public function setLocador(Locador $locador) {
        $this->locador = $locador;
        return $this;
    }

    /**
     * Todos os dados da entity Locatario
     * @return \Livraria\Entity\Locatario
     */
    public function getLocatario() {
        return $this->locatario;
    }

    /**
     * Entity do Locatario
     * @param \Livraria\Entity\Locatario $locatario
     * @return \Livraria\Entity\Orcamento
     */
    public function setLocatario(Locatario $locatario) {
        $this->locatario = $locatario;
        return $this;
    }

    /**
     * Todos os dados da entity Imovel
     * @return \Livraria\Entity\Imovel
     */
    public function getImovel() {
        return $this->imovel;
    }

    /**
     * Entity do Imovel
     * @param \Livraria\Entity\Imovel $imovel
     * @return \Livraria\Entity\Orcamento
     */
    public function setImovel(Imovel $imovel) {
        $this->imovel = $imovel;
        return $this;
    }

    /**
     * Todos os dados da entity Taxa
     * @return \Livraria\Entity\Taxa
     */
    public function getTaxa() {
        return $this->taxa;
    }

    /**
     * Entity do Taxa
     * @param \Livraria\Entity\Taxa $taxa
     * @return \Livraria\Entity\Orcamento
     */
    public function setTaxa(Taxa $taxa) {
        $this->taxa = $taxa;
        return $this;
    }

    /**
     * Todos os dados da entity Atividade
     * @return \Livraria\Entity\Atividade
     */
    public function getAtividade() {
        return $this->atividade;
    }

    /**
      * Entity do Atividade
     * @param \Livraria\Entity\Atividade $atividade
     * @return \Livraria\Entity\Orcamento
     */
    public function setAtividade(Atividade $atividade) {
        $this->atividade = $atividade;
        return $this;
    }

    /**
     * Todos os dados da entity Seguradora
     * @return \Livraria\Entity\Seguradora
     */
    public function getSeguradora() {
        return $this->seguradora;
    }

    /**
     * Entity do Seguradora
     * @param \Livraria\Entity\Seguradora $seguradora
     * @return \Livraria\Entity\Orcamento
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }

    /**
     * Todos os dados da entity Administradora
     * @return \Livraria\Entity\Administradora
     */
    public function getAdministradora() {
        return $this->administradora;
    }

    /**
     * Entity do Administradora
     * @param \Livraria\Entity\Administradora $administradora
     * @return \Livraria\Entity\Orcamento
     */
    public function setAdministradora(Administradora $administradora) {
        $this->administradora = $administradora;
        return $this;
    }

    /**
     * Todos os dados da entity User
     * @return \Livraria\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Entity do User
     * @param \Livraria\Entity\User $user
     * @return \Livraria\Entity\Orcamento
     */
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']             = $this->getId();
        $data['inicio']         = $this->getInicio();
        $data['fim']            = $this->getFim();
        $data['status']         = $this->getStatus();
        $data['userIdCriado']   = $this->getUseridcriado();
        $data['criadoEm']       = $this->getCriadoem();
        $data['userIdAlterado'] = $this->getUseridalterado();
        $data['alteradoEm']     = $this->getAlteradoem();
        $data['codano']         = $this->getCodano();
        $data['locadorNome']    = $this->getLocadornome();
        $data['locatarioNome']  = $this->getLocatarionome();
        $data['valorAluguel']   = $this->getValoraluguel();
        $data['tipoCobertura']  = $this->getTipoCobertura();
        $data['seguroEmNome']   = $this->getSeguroemnome();
        $data['codigoGerente']  = $this->getCodigogerente();
        $data['refImovel']      = $this->getRefimovel();
        $data['formaPagto']     = $this->getFormapagto();
        $data['incendio']       = $this->getIncendio();
        $data['aluguel']        = $this->getAluguel();
        $data['eletrico']       = $this->getEletrico();
        $data['vendaval']       = $this->getVendaval();
        $data['numeroParcela']  = $this->getNumeroparcela();
        $data['premioLiquido']  = $this->getPremioliquido();
        $data['premio']         = $this->getPremio();
        $data['premioTotal']    = $this->getPremiototal();
        $data['canceladoEm']    = $this->getCanceladoem();
        $data['observacao']     = $this->getObservacao();
        $data['gerado']         = $this->getGerado();
        $data['comissao']       = $this->getComissao();
        $data['codFechado']     = $this->getCodfechado();
        $data['mesNiver']       = $this->getMesniver();
        $data['locador']        = $this->getLocador();
        $data['locatario']      = $this->getLocatario();
        $data['imovel']         = $this->getImovel();
        $data['taxa']           = $this->getTaxa();
        $data['atividade']      = $this->getAtividade();
        $data['seguradora']     = $this->getSeguradora();
        $data['administradora'] = $this->getAdministradora();
        $data['user']           = $this->getUser();
        return $data ;
    }
 
    /** 
     * Converte a variavel do tipo float para string para exibição
     * @param String $get com nome do metodo a ser convertido
     * @param Int $dec quantidade de casas decimais
     * @return String do numero no formato brasileiro padrão com 2 casas decimais
     */    
    public function floatToStr($get,$dec = 2){
        if($get == ""){
            return "vazio!!";
        }
        $getter  = 'get' . ucwords($get);
        if(!method_exists($this,$getter)){
            return "Erro no metodo!!";
        }
        $float = call_user_func(array($this,$getter));
        return number_format($float, $dec, ',','.');
    }
 
    /** 
     * Faz tratamento na variavel string se necessario antes de converte em float
     * @param String $check variavel a ser convertida se tratada se necessario
     * @return String $check no formato float para gravação pelo doctrine
     */    
    public function strToFloat($check){
        if(is_string($check)){
            $check = preg_replace("/[^0-9,]/", "", $check);
            $check = str_replace(",", ".", $check);
        }
        return $check;
    }



}
