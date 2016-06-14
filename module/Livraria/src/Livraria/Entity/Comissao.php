<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comissao
 * Registra a comissão da Administradora e os periodos de vigencia.
 * 
 * @ORM\Table(name="comissao")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="\Livraria\Entity\ComissaoRepository")
 */
class Comissao extends Filtro
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
     * @var float $comissao
     *
     * @ORM\Column(name="Comissao", type="decimal", precision=10, scale=4, precision=10, scale=4, nullable=false)
     */
    protected $comissao;

    /**
     * @var float $comissao
     *
     * @ORM\Column(name="comissao_res", type="decimal", precision=10, scale=4, precision=10, scale=4, nullable=false)
     */
    protected $comissaoRes;

    /**
     * @var \DateTime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=false)
     */
    protected $inicio;

    /**
     * @var \DateTime $fim
     *
     * @ORM\Column(name="fim", type="datetime", nullable=true)
     */
    protected $fim;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    protected $status;

    /**
     * @var integer $multAluguel
     *
     * @ORM\Column(name="mult_aluguel", type="integer", nullable=false)
     */
    protected $multAluguel;

    /**
     * @var integer $multConteudo
     *
     * @ORM\Column(name="mult_conteudo", type="integer", nullable=true)
     */
    protected $multConteudo;

    /**
     * @var integer $multIncendio
     *
     * @ORM\Column(name="mult_incendio", type="integer", nullable=true)
     */
    protected $multIncendio;

    /**
     * @var integer $multEletrico
     *
     * @ORM\Column(name="mult_eletrico", type="integer", nullable=true)
     */
    protected $multEletrico;

    /**
     * @var integer $multVendaval
     *
     * @ORM\Column(name="mult_vendaval", type="integer", nullable=true)
     */
    protected $multVendaval;

    /**
     * ALTER TABLE comissao ADD mult_aluguel_res INT DEFAULT 0, ADD mult_conteudo_res INT DEFAULT 0, ADD mult_incendio_res INT DEFAULT 0, ADD mult_eletrico_res INT DEFAULT 0, ADD mult_vendaval_res INT DEFAULT 0;
     * @var integer $multAluguelRes
     *
     * @ORM\Column(name="mult_aluguel_res", type="integer", nullable=true, options={"default" = "0"})
     */
    protected $multAluguelRes;

    /**
     * @var integer $multConteudoRes
     *
     * @ORM\Column(name="mult_conteudo_res", type="integer", nullable=true, options={"default" = "0"})
     */
    protected $multConteudoRes;

    /**
     * @var integer $multIncendioRes
     *
     * @ORM\Column(name="mult_incendio_res", type="integer", nullable=true, options={"default" = "0"})
     */
    protected $multIncendioRes;

    /**
     * @var integer $multEletricoRes
     *
     * @ORM\Column(name="mult_eletrico_res", type="integer", nullable=true, options={"default" = "0"})
     */
    protected $multEletricoRes;

    /**
     * @var integer $multVendavalRes
     *
     * @ORM\Column(name="mult_vendaval_res", type="integer", nullable=true, options={"default" = "0"})
     */
    protected $multVendavalRes;

    /**
     * @var integer $userIdCriado
     *
     * @ORM\Column(name="user_id_criado", type="integer", nullable=false)
     */
    protected $userIdCriado;

    /**
     * @var \DateTime $criadoEm
     *
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    protected $criadoEm;

    /**
     * @var integer $userIdAlterado
     *
     * @ORM\Column(name="user_id_alterado", type="integer", nullable=true)
     */
    protected $userIdAlterado;

    /**
     * @var \DateTime $alteradoEm
     *
     * @ORM\Column(name="alterado_em", type="datetime", nullable=true)
     */
    protected $alteradoEm;

    /**
     * @var Administradoras
     *
     * @ORM\ManyToOne(targetEntity="Administradora")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     * })
     */
    protected $administradora;


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
     * Setar o id do registro
     * @param Int $id
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Valor da comissão Seguro Comerciais
     * @return float 
     */
    public function getComissao() {
        return $this->comissao;
    }

    /**
     * Recebe uma string para converter em float
     * @param string $comissao
     * @return \Livraria\Entity\Comissao
     */
    public function setComissao($comissao) {
        $this->comissao = $this->trataFloat($comissao);
        return $this;
    }
    
    /**
     * Valor da comissão Seguros Residenciais
     * @return float 
     */
    public function getComissaoRes() {
        return $this->comissaoRes;
    }

    /**
     * Recebe uma string para converter em float
     * @param string $comissaoRes
     * @return \Livraria\Entity\Comissao
     */
    public function setComissaoRes($comissaoRes) {
        $this->comissaoRes = $this->trataFloat($comissaoRes);
        return $this;
    }

    /** 
     * Retorna o inicio da vigência da taxa
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto  
     */ 
    public function getInicio($op = null) {
        if(is_null($op)){
            return $this->inicio->format('d/m/Y');
        }
        return $this->inicio;
    }
    
    /** 
     * Setar o inicio da vigência da taxa
     * @param \DateTime $inicio
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setInicio(\DateTime $inicio) {
        $this->inicio = $inicio;
        return $this;
    }

    /** 
     * Retorna o fim da vigência da taxa
     * @param String $op para retornar o objeto data
     * @return Sring da data no formato dd/mm/aaaa
     * @return \DateTime Objeto  
     */ 
    public function getFim($op = null) {
        if($op == 'obj'){
            return $this->fim;
        }
        return $this->trataData($this->fim);
    }

    /** 
     * Setar terminino da vigência da taxa para manter historico
     * @param \DateTime $fim
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setFim(\DateTime $fim) {
        $this->fim = $fim;
        return $this;
    }

    /**
     * A = ativo, C = cancelado, B = bloqueado
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Status A = ativo, C = cancelado, B = bloqueado
     * @param string $status
     * @return \Livraria\Entity\Comissao
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    
    /**
     * 
     * @return integer
     */
    public function getMultAluguel() {
        return $this->multAluguel;
    }

    /**
     * 
     * @param integer $multAluguel
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultAluguel($multAluguel) {
        $this->multAluguel = $this->trataFloat($multAluguel);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultConteudo() {
        return $this->multConteudo;
    }

    /**
     * 
     * @param integer $multConteudo
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultConteudo($multConteudo) {
        $this->multConteudo = $this->trataFloat($multConteudo);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultIncendio() {
        return $this->multIncendio;
    }

    public function setMultIncendio($multIncendio) {
        $this->multIncendio = $this->trataFloat($multIncendio);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultEletrico() {
        return $this->multEletrico;
    }

    /**
     * 
     * @param integer $multEletrico
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultEletrico($multEletrico) {
        $this->multEletrico = $this->trataFloat($multEletrico);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultVendaval() {
        return $this->multVendaval;
    }

    /**
     * 
     * @param integer $multVendaval
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultVendaval($multVendaval) {
        $this->multVendaval = $this->trataFloat($multVendaval);
        return $this;
    }
    
    /**
     * 
     * @return integer
     */
    public function getMultAluguelRes() {
        return ($this->multAluguelRes) ;
    }

    /**
     * 
     * @param integer $multAluguelRes
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultAluguelRes($multAluguelRes) {
        $this->multAluguelRes = $this->trataFloat($multAluguelRes);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultConteudoRes() {
        return ($this->multConteudoRes)  ;
    }

    /**
     * 
     * @param integer $multConteudoRes
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultConteudoRes($multConteudoRes) {
        $this->multConteudoRes = $this->trataFloat($multConteudoRes);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultIncendioRes() {
        return ($this->multIncendioRes)  ;
    }

    public function setMultIncendioRes($multIncendioRes) {
        $this->multIncendioRes = $this->trataFloat($multIncendioRes);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultEletricoRes() {
        return ($this->multEletricoRes)  ;
    }

    /**
     * 
     * @param integer $multEletricoRes
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultEletricoRes($multEletricoRes) {
        $this->multEletricoRes = $this->trataFloat($multEletricoRes);
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getMultVendavalRes() {
        return ($this->multVendavalRes)  ;
    }

    /**
     * 
     * @param integer $multVendavalRes
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function setMultVendavalRes($multVendavalRes) {
        $this->multVendavalRes = $this->trataFloat($multVendavalRes);
        return $this;
    }

    /**
     * Id do usuario que cadastrou o registro
     * @return int 
     */
    public function getUserIdCriado() {
        return $this->userIdCriado;
    }

    /** 
     * Setar o id do user que criou o registro
     * @param Int $userIdCriado
     * @return \Livraria\Entity\Comissao 
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
     * @return \Livraria\Entity\Comissao 
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
     * @return \Livraria\Entity\Comissao 
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
     * @return \Livraria\Entity\Comissao 
     */ 
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $this->alteradoEm = $alteradoEm;
        return $this;
    }

    /**
     * 
     * @return \Livraria\Entity\Administradoras
     */
    public function getAdministradora() {
        return $this->administradora;
    }

    /**
     * 
     * @param \Livraria\Entity\Administradoras $administradoras
     * @return \Livraria\Entity\Comissao
     */
    public function setAdministradora(Administradora $administradora) {
        $this->administradora = $administradora;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']               = $this->getId();
        $data['comissao']         = $this->floatToStr('comissao');
        $data['comissaoRes']      = $this->floatToStr('comissaoRes');
        $data['inicio']           = $this->getInicio();
        $data['fim']              = $this->getFim();
        $data['status']           = $this->getStatus();
        $data['multAluguel']      = $this->floatToStr('multAluguel') ; 
        $data['multIncendio']     = $this->floatToStr('multIncendio') ; 
        $data['multConteudo']     = $this->floatToStr('multConteudo') ; 
        $data['multEletrico']     = $this->floatToStr('multEletrico') ; 
        $data['multVendaval']     = $this->floatToStr('multVendaval') ; 
        $data['multAluguelRes']   = $this->floatToStr('multAluguelRes') ; 
        $data['multIncendioRes']  = $this->floatToStr('multIncendioRes') ; 
        $data['multConteudoRes']  = $this->floatToStr('multConteudoRes') ; 
        $data['multEletricoRes']  = $this->floatToStr('multEletricoRes') ; 
        $data['multVendavalRes']  = $this->floatToStr('multVendavalRes') ; 
        $data['userIdCriado']     = $this->getUserIdCriado();
        $data['criadoEm']         = $this->getCriadoEm();
        $data['userIdAlterado']   = $this->getUserIdAlterado();
        $data['administradora']   = $this->getAdministradora()->getId(); 
        return $data ;
    }

 
}
