<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Taxa
 * 
 * Contém taxas e a vigencias das mesmas para calculos dos seguros
 *
 * @ORM\Table(name="taxa")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="\Livraria\Entity\TaxaRepository")
 */
class Taxa extends Filtro
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
     * @var float $incendio
     *
     * @ORM\Column(name="incendio", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $incendio;

    /**
     * @var float $incendioConteudo
     *
     * @ORM\Column(name="incendio_conteudo", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $incendioConteudo;

    /**
     * @var float $aluguel
     *
     * @ORM\Column(name="aluguel", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $aluguel;

    /**
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $eletrico;

    /**
     * @var float $vendaval
     *
     * @ORM\Column(name="vendaval", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $vendaval;

    /**
     * @var float $respcivil
     *
     * @ORM\Column(name="respcivil", type="decimal", precision=20, scale=8, options={"default" = 0})
     */
    protected $respcivil;

    /**
     * @var string $validade
     *
     * @ORM\Column(name="validade", type="string", length=10, nullable=false)
     */
    protected $validade;

    /**
     * @var string $ocupacao
     *
     * @ORM\Column(name="ocupacao", type="string", length=10, nullable=false)
     */
    protected $ocupacao;

    /**
     * @var float $comissao
     *
     * @ORM\Column(name="comissao", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
    protected $comissao;

    /**
     * @var string $seq
     *
     * @ORM\Column(name="seq", type="string", length=1, nullable=true)
     */
    protected $seq;

    /**
     * @var string $tipoCobertura
     *
     * @ORM\Column(name="tipo_cobertura", type="string", length=2, nullable=false)
     */
    protected $tipoCobertura;

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
     * @var \Livraria\Entity\Classe
     * @ORM\ManyToOne(targetEntity="\Livraria\Entity\Classe")
     * @ORM\JoinColumn(name="classe_id", referencedColumnName="id")
     */
    protected $classe;

    /**
     * @var \Livraria\Entity\Seguradora
     * @ORM\ManyToOne(targetEntity="\Livraria\Entity\Seguradora")
     * @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     */
    protected $seguradora;


 
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
     * Verifica a data e seta o status do registro conforme valor de fim
     * @ORM\PreUpdate
     */
    function preUpdate(){
        $this->alteradoEm = new \DateTime('now');
        $this->alteradoEm->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        if($this->inicio < $this->fim)
            $this->status = 'C';
        else
            $this->status = 'A';
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

    public function getStatus() {
        return $this->status;
    }

    /** 
     * Setar o status do registro ativo bloqueado inativo
     * @param String $status
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Taxa cobrada para seguro incendio
     * @return float
     */
    public function getIncendio() {
        return $this->incendio;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio
     * @param Float $incendio
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setIncendio($incendio) {
        $this->incendio = $this->trataFloat($incendio);
        return $this;
    }

    public function getIncendioConteudo() {
        return $this->incendioConteudo;
    }

    /** 
     * Setar a taxa cobrada para seguro incendio + conteudo
     * @param Float $incendioConteudo
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setIncendioConteudo($incendioConteudo) {
        $this->incendioConteudo = $this->trataFloat($incendioConteudo);
        return $this;
    }

    public function getAluguel() {
        return $this->aluguel;
    }

    /** 
     * Setar a taxa cobrada para seguro do aluguel
     * @param Float $aluguel
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setAluguel($aluguel) {
        $this->aluguel = $this->trataFloat($aluguel);
        return $this;
    }

    public function getEletrico() {
        return $this->eletrico;
    }

    /** 
     * Setar a taxa cobrada para seguro de danos eletrico
     * @param Float $incendioConteudo
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setEletrico($eletrico) {
        $this->eletrico = $this->trataFloat($eletrico);
        return $this;
    }

    public function getVendaval() {
        return $this->vendaval;
    }

    /** 
     * Setar a taxa cobrada para seguro de vendaval naturais
     * @param Float $vendaval
     * @return \Livraria\Entity\Taxa 
     */
    public function setVendaval($vendaval) {
        $this->vendaval = $this->trataFloat($vendaval);
        return $this;
    }

    public function getRespcivil() {
        return $this->respcivil;
    }

    /** 
     * Setar a taxa cobrada para seguro de resp. civil naturais
     * @param Float $respcivil
     * @return \Livraria\Entity\Taxa 
     */
    public function setRespcivil($respcivil) {
        $this->respcivil = $this->trataFloat($respcivil);
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
     * @return \Livraria\Entity\Taxa 
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
     * @return \Livraria\Entity\Taxa 
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
     * @return \Livraria\Entity\Taxa 
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
            if($this->alteradoEm){
                return $this->alteradoEm->format('d/m/Y');
            }
            return '-';
        }
        return $this->alteradoEm;
    }

    /** 
     * Setar quando foi alterado o registro
     * @param \DateTime $alteradoEm
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setAlteradoEm(\DateTime $alteradoEm) {
        $this->alteradoEm = $alteradoEm;
        return $this;
    }

    /**
     * 
     * @return \Livraria\Entity\Classe
     */
    public function getClasse() {
        return $this->classe;
    }

    /** 
     * Setar a qual entidade classe que pertence esta taxa
     * @param \Livraria\Entity\Classe $classe
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setClasse(Classe $classe) {
        $this->classe = $classe;
        return $this;
    }
    
    /**
     * Taxa para seguro anual ou mensal
     * @return string
     */
    public function getValidade() {
        return $this->validade;
    }

    /**
     * Taxa para seguro anual ou mensal
     * @param string $validade
     * @return \Livraria\Entity\Taxa
     */
    public function setValidade($validade) {
        $this->validade = $validade;
        return $this;
    }
    
    /**
     * ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria']
     * @return string
     */
    public function getOcupacao($op='') {
        if(empty($op))
            return $this->ocupacao;
        
        switch ($this->ocupacao) {
            case '01':
                return 'Comércio e Serviços';
                break;
            case '02':
                return 'Residencial';
                break;
            case '03':
                return 'Industria';
                break;

            default:
                return 'Desconhecido';
                break;
        }
    }
    
    /**
     * ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria']
     * @param string $ocupacao
     * @return \Livraria\Entity\Taxa
     */
    public function setOcupacao($ocupacao){
        $this->ocupacao = $ocupacao;
        return $this;
        
    }

    /**
     * Comissão base para as taxas para o calculo
     * @return float
     */
    public function getComissao() {
        return $this->comissao;
    }

    /** 
     * Comissão base para as taxas para o calculo
     * @param Float $comissao
     * @return \Livraria\Entity\Taxa 
     */ 
    public function setComissao($comissao) {
        $this->comissao = $this->trataFloat($comissao);
        return $this;
    }
    
    /**
     * Campo para manter compatibilidade com BD antigo
     * @return string
     */
    public function getSeq() {
        return $this->seq;
    }

    /**
     * Campo para manter compatibilidade com BD antigo
     * @param string $seq
     * @return \Livraria\Entity\Taxa
     */
    public function setSeq($seq) {
        $this->seq = $seq;
        return $this;
    }
    
    /**
     * Tipo de cobertura 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @return string
     */
    public function getTipoCobertura($str=null) {
        if (is_null($str)) {
            return $this->tipoCobertura;
        }
        switch ($this->tipoCobertura) {
            case '01':
                return "Prédio";
                break;
            case '02':
                return "Prédio + Conteúdo";
                break;
            case '03':
                return "Conteúdo";
                break;
            default:
                return "";
                break;
        }
    }

    /**
     * Tipo de cobertura 01=Predio, 02=Predio + conteudo, 03=Conteudo
     * @param string $tipoCobertura
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setTipoCobertura($tipoCobertura) {
        $this->tipoCobertura = $tipoCobertura;
        return $this;
    }

    /**
     * 
     * @return \Livraria\Entity\Seguradora
     */
    public function getSeguradora() {
        return $this->seguradora;
    }

    /**
     * 
     * @param \Livraria\Entity\Seguradora $seguradora
     * @return \Livraria\Entity\Taxa
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data['id']               = $this->getId();
        $data['inicio']           = $this->getInicio();
        $data['fim']              = $this->getFim();
        $data['status']           = $this->getStatus();
        $data['incendio']         = $this->floatToStr('Incendio',6);
        $data['incendioConteudo'] = $this->floatToStr('IncendioConteudo',6);
        $data['aluguel']          = $this->floatToStr('Aluguel',6);
        $data['eletrico']         = $this->floatToStr('Eletrico',6);
        $data['vendaval']         = $this->floatToStr('Vendaval',6);
        $data['respcivil']        = $this->floatToStr('Respcivil',6);
        $data['validade']         = $this->getValidade();
        $data['ocupacao']         = $this->getOcupacao();
        $data['comissao']         = $this->floatToStr('Comissao');
        $data['seq']              = $this->getSeq();
        $data['userIdCriado']     = $this->getUserIdCriado();
        $data['criadoEm']         = $this->getCriadoEm();
        $data['userIdAlterado']   = $this->getUserIdAlterado();
        $data['alteradoEm']       = $this->getAlteradoEm();
        $data['classe']           = $this->getClasse()->getId(); 
        $data['seguradora']       = $this->getSeguradora()->getId(); 
        $data['tipoCobertura']    = $this->getTipoCobertura();
        return $data ;
    }

}
