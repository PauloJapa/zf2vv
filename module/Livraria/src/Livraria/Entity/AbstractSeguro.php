<?php

namespace Livraria\Entity;
/**
 * Description of AbstractSeguro
 * 
 * Metodos comuns entre as entitys Orçamentos, Fechados, Renovação
 *
 * @author user
 */
class AbstractSeguro  extends Filtro {
    
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setValorAluguel($valorAluguel) {
        $this->valorAluguel = $this->trataFloat($valorAluguel,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setIncendio($incendio) {
        $this->incendio = $this->trataFloat($incendio,8);
        return $this;
    }

    /**
     * Cobertura para conteudo baseado no multiplo conteudo da seguradora vezes aluguel
     * @return float
     */
    public function getConteudo() {
        return $this->incendio;
    }

    /**
     * Cobertura para conteudo baseado no multiplo conteudo da seguradora vezes aluguel
     * @param string $incendio
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setConteudo($conteudo) {
        $this->conteudo = $this->trataFloat($conteudo,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setAluguel($aluguel) {
        $this->aluguel = $this->trataFloat($aluguel,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setEletrico($eletrico) {
        $this->eletrico = $this->trataFloat($eletrico,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setVendaval($vendaval) {
        $this->vendaval = $this->trataFloat($vendaval,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setPremioLiquido($premioLiquido) {
        $this->premioLiquido = $this->trataFloat($premioLiquido,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setPremio($premio) {
        $this->premio = $this->trataFloat($premio,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setPremioTotal($premioTotal) {
        $this->premioTotal = $this->trataFloat($premioTotal,8);
        return $this;
    }

    /**
     * Data em que foi cancelado o seguro 
     * @return \DateTime | string
     */
    public function getCanceladoEm($op = null) {
        if(is_null($op)){
            return $this->trataData($this->canceladoEm, '01/01/1000');
        }
        return $this->canceladoEm;
    }

    /**
     * Data em que foi cancelado o seguro 
     * @param \DateTime $canceladoEm
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setComissao($comissao) {
        $this->comissao = $this->trataFloat($comissao,8);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setMesNiver($mesNiver) {
        $this->mesNiver = $mesNiver;
        return $this;
    }
    
    /**
     * 'mensal'|'anual'
     * @return string
     */
    public function getValidade(){
        return $this->validade;
    }
    
    /**
     * 'mensal'|'anual'
     * @param string $validade
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setValidade($validade){
        $this->validade = $validade;
        return $this;
        
    }
    
    /**
     * ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria']
     * @return string
     */
    public function getOcupacao(){
        return $this->ocupacao;
    }
    
    /**
     * ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria']
     * @param string $ocupacao
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setOcupacao($ocupacao){
        $this->ocupacao = $ocupacao;
        return $this;
        
    }
    
    /**
     * Taxa do IOF
     * @return string (com taxa no formato float)
     */
    public function getTaxaIof() {
        return $this->taxaIof;
    }

    /**
     * Taxa do IOF
     * @param string $taxaIof
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setTaxaIof($taxaIof) {
        $this->taxaIof = $this->trataFloat($taxaIof,4);
        return $this;
    }
    
    /**
     * Valor a ser pago para cobertura de Incendio
     * @return string
     */
    public function getCobIncendio() {
        return $this->cobIncendio;
    }

    /**
     * Valor a ser pago para cobertura de Incendio
     * @param string $cobIncendio
     * @return \Livraria\Entity\AbstractSeguro
     */
    public function setCobIncendio($cobIncendio) {
        $this->cobIncendio = $this->trataFloat($cobIncendio,6);
        return $this;
    }

    /**
     * Valor a ser pago para cobertura de Conteudo
     * @return string
     */
    public function getCobConteudo() {
        return $this->cobConteudo;
    }

    /**
     * Valor a ser pago para cobertura de Conteudo
     * @param string $cobConteudo
     * @return \Livraria\Entity\AbstractSeguro
     */
    public function setCobConteudo($cobConteudo) {
        $this->cobConteudo = $this->trataFloat($cobConteudo,6);
        return $this;
    }

    /**
     * Valor a ser pago para cobertura de Aluguel
     * @return string
     */
    public function getCobAluguel() {
        return $this->cobAluguel;
    }

    /**
     * Valor a ser pago para cobertura de Aluguel
     * @param string $cobAluguel
     * @return \Livraria\Entity\AbstractSeguro
     */
    public function setCobAluguel($cobAluguel) {
        $this->cobAluguel = $this->trataFloat($cobAluguel,6);
        return $this;
    }

    /**
     * Valor a ser pago para cobertura de Eletrico
     * @return string
     */
    public function getCobEletrico() {
        return $this->cobEletrico;
    }

    /**
     * Valor a ser pago para cobertura de Eletrico
     * @param string $cobEletrico
     * @return \Livraria\Entity\AbstractSeguro
     */
    public function setCobEletrico($cobEletrico) {
        $this->cobEletrico = $this->trataFloat($cobEletrico,6);
        return $this;
    }

    /**
     * Valor a ser pago para cobertura de Vendaval
     * @return string
     */
    public function getCobVendaval() {
        return $this->cobVendaval;
    }

    /**
     * Valor a ser pago para cobertura de Vendaval
     * @param string $cobVendaval
     * @return \Livraria\Entity\AbstractSeguro
     */
    public function setCobVendaval($cobVendaval) {
        $this->cobVendaval = $this->trataFloat($cobVendaval,6);
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
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
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }
    
    /**
     * Todos os dados da entity MultiplosMinimos
     * @return \Livraria\Entity\MultiplosMinimos
     */
    public function getMultiplosMinimos() {
        return $this->multiplosMinimos;
    }

    /**
     * Entity do MultiplosMinimos
     * @param \Livraria\Entity\MultiplosMinimos $multiplosMinimos
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados
     */
    public function setMultiplosMinimos(MultiplosMinimos $multiplosMinimos) {
        $this->multiplosMinimos = $multiplosMinimos;
        return $this;
    }
    
    
    /**
     * Todos os dados da entity Comissao
     * @return \Livraria\Entity\Comissao
     */
    public function getComissaoEnt() {
        return $this->comissaoEnt;
    }

    /**
     * Entity do MultiplosMinimos
     * @param \Livraria\Entity\Comissao $multiplosMinimos
     * @return \Livraria\Entity\Orcamento|Renovacao|Fechados|Renovacao|Fechados
     */
    public function setComissaoEnt(Comissao $comissaoEnt) {
        $this->comissaoEnt = $comissaoEnt;
        return $this;
    }
    
    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data                   = $this->getImovel()->toArray();
        $data['id']             = $this->getId();
        $data['proposta']       = $this->getId() . '/' . $this->getCodano();
        $data['inicio']         = $this->getInicio();
        $data['fim']            = $this->getFim();
        $data['status']         = $this->getStatus();
        $data['criadoEm']       = $this->getCriadoem();
        $data['alteradoEm']     = $this->getAlteradoem();
        $data['codano']         = $this->getCodano();
        $data['locador']        = $this->getLocador()->getId();
        $data['locadorNome']    = $this->getLocadornome();
        $data['tipoLoc']        = $this->getLocador()->getTipo();
        $data['cpfLoc']         = $this->getLocador()->getCpf();
        $data['cnpjLoc']        = $this->getLocador()->getCnpj();
        $data['locatario']      = $this->getLocatario()->getId();
        $data['locatarioNome']  = $this->getLocatarionome();
        $data['tipo']           = $this->getLocatario()->getTipo();
        $data['cpf']            = $this->getLocatario()->getCpf();
        $data['cnpj']           = $this->getLocatario()->getCnpj();
        $data['valorAluguel']   = $this->floatToStr('valorAluguel');
        $data['tipoCobertura']  = $this->getTipoCobertura();
        $data['seguroEmNome']   = $this->getSeguroemnome();
        $data['codigoGerente']  = $this->getCodigogerente();
        $data['refImovel']      = $this->getRefimovel();
        $data['formaPagto']     = $this->getFormapagto();
        $data['incendio']       = $this->floatToStr('incendio');
        $data['conteudo']       = $this->floatToStr('conteudo');
        $data['aluguel']        = $this->floatToStr('aluguel');
        $data['eletrico']       = $this->floatToStr('eletrico');
        $data['vendaval']       = $this->floatToStr('vendaval');
        $data['numeroParcela']  = $this->getNumeroparcela();
        $data['premioLiquido']  = $this->floatToStr('premioLiquido');
        $data['premio']         = $this->floatToStr('premio');
        $data['premioTotal']    = $this->floatToStr('premioTotal');
        $data['canceladoEm']    = $this->getCanceladoEm();
        $data['observacao']     = $this->getObservacao();
        $data['gerado']         = $this->getGerado();
        $data['comissao']       = $this->floatToStr('comissao');
        $data['mesNiver']       = $this->getMesniver();
        $data['imovel']         = $this->getImovel()->getId();
        $data['imovelTel']      = $this->getImovel()->getTel();
        $data['imovelStatus']   = $this->getImovel()->getStatus();
        $data['taxa']           = $this->getTaxa()->getId();
        $data['atividade']      = $this->getAtividade()->getId();
        $data['atividadeDesc']  = $this->getAtividade();
        $data['seguradora']     = $this->getSeguradora()->getId();
        $data['administradora'] = $this->getAdministradora()->getId();
        $data['multiplosMinimos'] = $this->getMultiplosMinimos()->getId();
        $data['comissaoEnt']    = $this->getComissaoEnt()->getId();
        $data['user']           = $this->getUser()->getId();
        $data['mesNiver']       = $this->getMesNiver();
        $data['validade']       = $this->getValidade();
        $data['ocupacao']       = $this->getOcupacao();
        $data['taxaIof']        = $this->floatToStr('taxaIof',4);
        $data['cobIncendio']    = $this->floatToStr('cobIncendio');
        $data['cobConteudo']    = $this->floatToStr('cobConteudo');
        $data['cobAluguel']     = $this->floatToStr('cobAluguel');
        $data['cobEletrico']    = $this->floatToStr('cobEletrico');
        $data['cobVendaval']    = $this->floatToStr('cobVendaval');
        return $data ;
    }
    
}
