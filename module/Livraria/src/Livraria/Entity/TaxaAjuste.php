<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaxaAjuste
 * 
 * Contém taxas de Ajuste e a vigencias das mesmas para calculos dos seguros
 * Essa taxa é parametrizada por
 * Adminitradora(lello, Oma)
 * ocupacao(residencia,comercial,industria)
 * classe(1,2,3,4,5,6.....) somente para comercial e industria
 * validade(anual,mensal)
 *
 * @author PauloWatakabe <watakabe05@gmailcom>
 * @ORM\Table(name="taxaAjuste")
 * @ORM\Entity(repositoryClass="Livraria\Entity\TaxaAjusteRepository")
 */
class TaxaAjuste extends Filtro
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
     * @var \Livraria\Entity\Classe
     *
     * @ORM\ManyToOne(targetEntity="\Livraria\Entity\Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id")
     * })
     */
    protected $classe;
    
    /**
     * @var string $validade
     *
     * @ORM\Column(name="validade", type="string", nullable=false)
     */
    protected $validade;	


    /**
     * @var string $ocupacao  Tipo atividade
     *
     * @ORM\Column(name="ocupacao", type="string", length=2, nullable=false)
     */
    protected $ocupacao;
		
    /**
     * com conteudo e com dano eletrico	
     * @var float $contEle
     *
     * @ORM\Column(name="cont_ele", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $contEle = 0;
	
    /**
     * com conteudo e sem dano ele
     * @var float $conteudo
     *
     * @ORM\Column(name="conteudo", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $conteudo = 0;
	
    /**
     * sem conteudo e com dano ele
     * @var float $eletrico
     *
     * @ORM\Column(name="eletrico", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $eletrico = 0;
	
    /**
     * sem conteudo e sem dano ele
     * @var float $semContEle
     *
     * @ORM\Column(name="sem_cont_ele", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
    protected $semContEle = 0;
	
    /**
     * com dano ele
     * @var float $comEletrico
     *
     * @ORM\Column(name="com_eletrico", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $comEletrico = 0;
	
    /**
     * sem dano ele
     * @var float $semEletrico
     *
     * @ORM\Column(name="sem_eletrico", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $semEletrico = 0;
	
    /**
     * com dano eletrico SOMENTE para renovação
     * @var float $comEleRen
     *
     * @ORM\Column(name="com_ele_ren", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $comEleRen = 0;
	
    /**
     * sem dano eletrico SOMENTE para renovação
     * @var float $semEleRen
     *
     * @ORM\Column(name="sem_ele_ren", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $semEleRen = 0;

    /**
     * unica
     * @var float $unica taxa unica
     *
     * @ORM\Column(name="unica", type="decimal", precision=10, scale=4, options={"default" = 0})
     */
     protected $unica = 0;
    
    /**
     * @var \Livraria\Entity\Administradora
     *
     * @ORM\ManyToOne(targetEntity="\Livraria\Entity\Administradora", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administradoras_id", referencedColumnName="id")
     * })
     */
    protected $administradora;
    
    /**
     * @var \Livraria\Entity\Seguradora
     *
     * @ORM\ManyToOne(targetEntity="\Livraria\Entity\Seguradora", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seguradora_id", referencedColumnName="id")
     * })
     */
    protected $seguradora;

    /**
     * @var float $comissao
     *
     * @ORM\Column(name="comissao", type="decimal", precision=10, scale=4, nullable=true, options={"default" = 0})
     */
    protected $comissao;

 
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
     * @return \Livraria\Entity\TaxaAjuste 
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
     * @return \Livraria\Entity\TaxaAjuste 
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
     * @return \Livraria\Entity\TaxaAjuste 
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
     * @return \Livraria\Entity\TaxaAjuste 
     */ 
    public function setStatus($status) {
        $this->status = $status;
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
     * @return \Livraria\Entity\TaxaAjuste 
     */ 
    public function setClasse(Classe $classe) {
        $this->classe = $classe;
        return $this;
    }
    
    /**
     * TaxaAjuste para seguro anual ou mensal
     * @return string
     */
    public function getValidade() {
        return $this->validade;
    }

    /**
     * TaxaAjuste para seguro anual ou mensal
     * @param string $validade
     * @return \Livraria\Entity\TaxaAjuste
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
            case '04':
                return 'Apto';
                break;

            default:
                return '-';
                break;
        }
    }
    
    /**
     * ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria']
     * @param string $ocupacao
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setOcupacao($ocupacao){
        $this->ocupacao = $ocupacao;
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
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setSeguradora(Seguradora $seguradora) {
        $this->seguradora = $seguradora;
        return $this;
    }
    
    /**
     * 
     * @return \Livraria\Entity\Administradora | NULL
     */
    public function getAdministradora() {
        return $this->administradora;
    }

    /**
     * 
     * @param \Livraria\Entity\Administradora $administradora
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setAdministradora(\Livraria\Entity\Administradora $administradora = NULL) {
        $this->administradora = $administradora;
        return $this;
    }
    
    public function getContEle() {
        return $this->contEle;
    }
    
    /**
     * 
     * @param string | float $contEle
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setContEle($contEle) {
        $this->contEle = $this->trataFloat($contEle);
        return $this;
    }

    public function getConteudo() {
        return $this->conteudo;
    }

    /**
     * 
     * @param string | float $conteudo
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setConteudo($conteudo) {
        $this->conteudo = $this->trataFloat($conteudo);
        return $this;
    }

    public function getEletrico() {
        return $this->eletrico;
    }

    /**
     * 
     * @param string | float $eletrico
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setEletrico($eletrico) {
        $this->eletrico = $this->trataFloat($eletrico);
        return $this;
    }

    public function getSemContEle() {
        return $this->semContEle;
    }

    /**
     * 
     * @param string | float $semContEle
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setSemContEle($semContEle) {
        $this->semContEle = $this->trataFloat($semContEle);
        return $this;
    }

    public function getComEletrico() {
        return $this->comEletrico;
    }

    /**
     * 
     * @param string | float $comEletrico
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setComEletrico($comEletrico) {
        $this->comEletrico = $this->trataFloat($comEletrico);
        return $this;
    }

    public function getSemEletrico() {
        return $this->semEletrico;
    }

    /**
     * 
     * @param string | float $semEletrico
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setSemEletrico($semEletrico) {
        $this->semEletrico = $this->trataFloat($semEletrico);
        return $this;
    }

    public function getComEleRen() {
        return $this->comEleRen;
    }

    /**
     * 
     * @param string | float $comEleRen
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setComEleRen($comEleRen) {
        $this->comEleRen = $this->trataFloat($comEleRen);
        return $this;
    }

    public function getSemEleRen() {
        return $this->semEleRen;
    }

    /**
     * 
     * @param string | float $semEleRen
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setSemEleRen($semEleRen) {
        $this->semEleRen = $this->trataFloat($semEleRen);
        return $this;
    }

    public function getUnica() {
        return $this->unica;
    }

    /**
     * 
     * @param string | float $unica
     * @return \Livraria\Entity\TaxaAjuste
     */
    public function setUnica($unica) {
        $this->unica = $this->trataFloat($unica);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setComissao($comissao) {
        $this->comissao = $this->trataFloat($comissao,8);
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
        $data['contEle']        = $this->floatToStr('contEle');
        $data['conteudo']       = $this->floatToStr('conteudo');
        $data['eletrico']       = $this->floatToStr('eletrico');
        $data['semContEle']     = $this->floatToStr('semContEle');
        $data['comEletrico']    = $this->floatToStr('comEletrico');
        $data['semEletrico']    = $this->floatToStr('semEletrico');
        $data['comEleRen']      = $this->floatToStr('comEleRen');
        $data['semEleRen']      = $this->floatToStr('semEleRen');
        $data['unica']          = $this->floatToStr('unica');
        $data['comissao']       = $this->floatToStr('comissao');
        $data['validade']       = $this->getValidade();
        $data['ocupacao']       = $this->getOcupacao();
        $data['administradora'] = is_null($this->getAdministradora()) ? '': $this->getAdministradora()->getId(); 
        $data['classe']         = is_null($this->getClasse()        ) ? '': $this->getClasse()->getId(); 
        $data['seguradora']     = $this->getSeguradora()->getId(); 
        return $data ;
    }

}
