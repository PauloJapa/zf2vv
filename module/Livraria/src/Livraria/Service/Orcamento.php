<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Orcamento
 * Faz o CRUD da tabela Orcamento no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Orcamento extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaImovel;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Orcamento";
    }
    
    /**
     * @ORM\OneToOne(targetEntity="Locador")
     * @ORM\OneToOne(targetEntity="Locatario")
     * @ORM\OneToOne(targetEntity="Imovel")
     * @ORM\OneToOne(targetEntity="Taxa")
     * @ORM\OneToOne(targetEntity="Atividade")
     * @ORM\OneToOne(targetEntity="Seguradora")
     * @ORM\OneToOne(targetEntity="Administradora")
     * @ORM\OneToOne(targetEntity="User")
     * @var \DateTime $inicio
     * @var \DateTime $fim
     * @var \DateTime $criadoEm
     * @var \DateTime $canceladoEm
     * @var \DateTime $alteradoEm
     
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToEntity('atividade', 'Livraria\Entity\Atividade');
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('user', 'Livraria\Entity\User');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('criadoEm');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');
        
        $locadorResul = $this->setLocador();
        if($locadorResul !== TRUE){
            return $locadorResul;
        }
        $imovelResul = $this->setImovel();
        if($imovelResul !== TRUE){
            return $imovelResul;
        }
        $locatarioResul = $this->setLocatario();
        if($locatarioResul !== TRUE){
            return $locatarioResul;
        }
        return TRUE;
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */   
    public function insert(array $data, $param='') { 
        $this->data = $data;
        
        if (empty($this->data['user']))
            $this->data['user'] = $this->getIdentidade()->getId();
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
       
        //Comissão da Administradora padrão
        $this->data['comissao'] = $this->em
            ->getRepository('Livraria\Entity\Comissao')
            ->findComissaoVigente($this->data['administradora']->getId())
            ->floatToStr('comissao');
        
        $this->data['taxa'] = $this->em
            ->getRepository('Livraria\Entity\Taxa')
            ->findTaxaVigente($this->data['seguradora']->getId(), $this->data['atividade']->getId());

        $this->data['multiplosMinimos'] = $this->em
            ->getRepository('Livraria\Entity\MultiplosMinimos')
            ->findMultMinVigente($this->data['seguradora']->getId());
        
        $resul = $this->CalculaPremio();
        
        $this->data['codFechado'] = '0';
        $this->data['status'] = 'A';
        
        
        if($param == 'OnlyCalc'){
            return ['Calculado com Sucesso !!!']; 
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }

        if(parent::insert())
            $this->logForNew();
        
        return array(TRUE,  $this->data['id']);      
    }   
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     */
    public function logForNew(){
        //parent::logForNew('orcamento');
        //serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'new';
        $dataLog['mensagem']   = 'Novo orçamento com numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }
    
    /**
     * Pegando o servico endereco e inserindo ou referenciando o imovel
     * @return boolean | array
     */
    public function setImovel(){
        if(empty($this->data['imovel'])){
            $serviceImovel = new Imovel($this->em);
            $resul = $serviceImovel->insert($this->data);
            if(is_array($resul)){
                if($resul[0] == "Já existe um imovel neste endereço  registro:"){
                    $this->data['imovel'] = $resul[1];
                    $this->idToReference('imovel', 'Livraria\Entity\Imovel');
                }else{
                    return array_merge(['Erro ao tentar incluir imovel no BD.'],$resul);
                }
            }else{
                $this->data['imovel'] = $serviceImovel->getEntity();
            }
        }else{
            $this->idToReference('imovel', 'Livraria\Entity\Imovel');
        }
        return TRUE;
    }
    
    /**
     * Faz um referencia ou tenta incluir o locador no BD
     * Caso não consiga retorna os erros 
     * @return boolean | array
     */
    public function setLocador(){
        if(empty($this->data['locador'])){
            $serviceLocador = new Locador($this->em);
            $data['id'] = '';
            $data['administradora'] = $this->data['administradora'];
            $data['nome'] = $this->data['locadorNome'];
            $data['tipo'] = $this->data['tipoLoc'];
            $data['cpf'] = $this->data['cpfLoc'];
            $data['cnpj'] = $this->data['cnpjLoc'];
            $data['status'] = 'A';
            $resul = $serviceLocador->insert($data);
            if($resul === TRUE){
                $this->data['locador'] = $serviceLocador->getEntity();
            }else{
                if(substr($resul[0], 0, 15) == 'Já existe esse'){
                    $this->data['locador'] = $resul[1];
                    $this->idToReference('locador', 'Livraria\Entity\Locador');
                }else{
                    return $resul;
                }
            }
        }else{
            $this->idToReference('locador', 'Livraria\Entity\Locador');
        }
        return TRUE;
    }
    
    /**
     * Faz um referencia ou tenta incluir o locatario no BD
     * Caso não consiga retorna os erros 
     * @return boolean | array
     */
    public function setLocatario(){
        if(empty($this->data['locatario'])){
            $serviceLocatario = new Locatario($this->em);
            $data['id'] = '';
            $data['nome'] = $this->data['locatarioNome'];
            $data['tipo'] = $this->data['tipo'];
            $data['cpf'] = $this->data['cpf'];
            $data['cnpj'] = $this->data['cnpj'];
            $data['status'] = 'A';
            $resul = $serviceLocatario->insert($data);
            if($resul === TRUE){
                $this->data['locatario'] = $serviceLocatario->getEntity();
            }else{
                if(substr($resul[0], 0, 13) == 'Já existe um'){
                    $this->data['locatario'] = $resul[1];
                    $this->idToReference('locatario', 'Livraria\Entity\Locatario');
                }else{
                    var_dump(substr($resul[0], 0, 13));
                    return $resul;
                }
            }
        }else{
            $this->idToReference('locatario', 'Livraria\Entity\Locatario');
        }
        return TRUE;
    }

    /**
     * Calcula a vigencia do seguro periodo mensal ou anual
     * @return boolean | array
     */
    public function calculaVigencia(){
        if(!isset($this->data['validade'])){
            return ['Campo validade não existe!!'];
        }
        $this->data['codano'] = $this->data['criadoEm']->format('Y');
        $this->data['fim'] = clone $this->data['inicio'];
        $interval_spec = ''; 
        if($this->data['validade'] == 'mensal'){
            $interval_spec = 'P1M'; 
        } 
        if($this->data['validade'] == 'anual'){
            $interval_spec = 'P1Y'; 
        } 
        if(empty($interval_spec)){
            return ['Campo validade com valor que não existe na lista!!'];
        }
        $this->data['fim']->add(new \DateInterval($interval_spec)); 
        return TRUE;
    }

    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data,$param='') {
        $this->data = $data;
        
        if($data['status'] != 'A')
            return ['Este orçamento não pode ser editado!','Pois já esta finalizado!!'];
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
        
        $this->idToEntity('taxa', 'Livraria\Entity\Taxa');
        
        $this->idToEntity('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');

        $resul = $this->CalculaPremio();
        
        $this->idToReference('user', 'Livraria\Entity\User');
        
        if($param == 'OnlyCalc'){
            return ['Calculado com Sucesso !!!']; 
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        //parent::logForEdit('orcamento');
        //serviço logorcamento
        if(empty($this->dePara)) 
            return ;
        
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'edit';
        $dataLog['mensagem']   = 'Alterou orçamento de numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $log->insert($dataLog);
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if(empty($this->data['imovel']))
            return array('Um imovel deve ser selecionado!');
        
        $inicio = $this->data['inicio'];
        if((empty($inicio)) or ($inicio < (new \DateTime('01/01/2000'))))
            return array('A data deve ser preenchida corretamente!');
            
        $filtro['imovel'] = $this->data['imovel']->getId();
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($inicio <= $entity->getFim('obj'))){
                    if($entity->getStatus() == "A"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia ' . $entity->getInicio() . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                    if($entity->getStatus() == "F"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia ' . $entity->getInicio() . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                }
            }
        }
        
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Orcamento $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        // 10 referencia a outra entity
        $this->dePara .= $this->diffAfterBefore('Locador', $ent->getLocador(), $this->data['locador']);
        $this->dePara .= $this->diffAfterBefore('Locatario', $ent->getLocatario(), $this->data['locatario']);
        $this->dePara .= $this->diffAfterBefore('Imovel bloco', $ent->getImovel()->getBloco(), $this->data['imovel']->getBloco());
        $this->dePara .= $this->diffAfterBefore('Imovel apto', $ent->getImovel()->getApto(), $this->data['imovel']->getApto());
        $this->dePara .= $this->diffAfterBefore('Imovel tel', $ent->getImovel()->getTel(), $this->data['imovel']->getTel());
        $this->dePara .= $this->diffAfterBefore('Taxa', $ent->getTaxa()->getId(), $this->data['taxa']->getId());
        $this->dePara .= $this->diffAfterBefore('Atividade', $ent->getAtividade(), $this->data['atividade']);
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora(), $this->data['seguradora']);
        $this->dePara .= $this->diffAfterBefore('Administradora', $ent->getAdministradora(), $this->data['administradora']);
        // 9 de valores float
        $this->dePara .= $this->diffAfterBefore('Valor do Aluguel', $ent->floatToStr('valorAluguel'), $this->strToFloat($this->data['valorAluguel']));
        $this->dePara .= $this->diffAfterBefore('Incêndio', $ent->floatToStr('incendio'), $this->strToFloat($this->data['incendio']));
        $this->dePara .= $this->diffAfterBefore('Cobertura aluguel', $ent->floatToStr('aluguel'), $this->strToFloat($this->data['aluguel']));
        $this->dePara .= $this->diffAfterBefore('Cobertura eletrico', $ent->floatToStr('eletrico'), $this->strToFloat($this->data['eletrico']));
        $this->dePara .= $this->diffAfterBefore('Cobertura vendaval', $ent->floatToStr('vendaval'), $this->strToFloat($this->data['vendaval']));
        $this->dePara .= $this->diffAfterBefore('Premio Liquido', $ent->floatToStr('premioLiquido'), $this->strToFloat($this->data['premioLiquido']));
        $this->dePara .= $this->diffAfterBefore('Premio', $ent->floatToStr('premio'), $this->strToFloat($this->data['premio']));
        $this->dePara .= $this->diffAfterBefore('Premio Total', $ent->floatToStr('premioTotal'), $this->strToFloat($this->data['premioTotal']));
        $this->dePara .= $this->diffAfterBefore('Comissao', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        // 3 de datas
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Data Fim', $ent->getFim(), $this->data['fim']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Cancelado Em', $ent->getCanceladoEm(), $this->data['canceladoEm']->format('d/m/Y'));
        // 15 campos comuns
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Ano Referência', $ent->getCodano(), $this->data['codano']);
        $this->dePara .= $this->diffAfterBefore('locadorNome', $ent->getLocadorNome(), $this->data['locadorNome']);
        $this->dePara .= $this->diffAfterBefore('locatarioNome', $ent->getLocatarioNome(), $this->data['locatarioNome']);
        $this->dePara .= $this->diffAfterBefore('tipoCobertura', $ent->getTipoCobertura(), $this->data['tipoCobertura']);
        $this->dePara .= $this->diffAfterBefore('seguroEmNome', $ent->getSeguroEmNome(), $this->data['seguroEmNome']);
        $this->dePara .= $this->diffAfterBefore('codigoGerente', $ent->getCodigoGerente(), $this->data['codigoGerente']);
        $this->dePara .= $this->diffAfterBefore('refImovel', $ent->getRefImovel(), $this->data['refImovel']);
        $this->dePara .= $this->diffAfterBefore('formaPagto', $ent->getFormaPagto(), $this->data['formaPagto']);
        $this->dePara .= $this->diffAfterBefore('numeroParcela', $ent->getNumeroParcela(), $this->data['numeroParcela']);
        $this->dePara .= $this->diffAfterBefore('observacao', $ent->getObservacao(), $this->data['observacao']);
        if(isset($this->data['gerado']))
            $this->dePara .= $this->diffAfterBefore('gerado', $ent->getGerado(), $this->data['gerado']);
        $this->dePara .= $this->diffAfterBefore('comissao', $ent->getComissao(), $this->data['comissao']);
        $this->dePara .= $this->diffAfterBefore('codFechado', $ent->getCodFechado(), $this->data['codFechado']);
        $this->dePara .= $this->diffAfterBefore('mesNiver', $ent->getMesNiver(), $this->data['mesNiver']);
        //Juntar as alterações no imovel se houver
        $this->dePara .= $this->deParaImovel;
    }
    
    
    public function calculaSeguro(){
        if(!empty($this->data['id'])){
            $this->refazCalculo();
        }else{
            $this->novoCalculo();
        }
    }
    
    public function CalculaPremio($data=[]){
        if(!empty($data)){
            $this->data = $data ;
        }
        
        //Base de todo calculo        
        $vlrAluguel = $this->strToFloat($this->data['valorAluguel'],'float');
        
        //Coberturas 
        $incendio = $this->strToFloat($this->data['incendio'], 'float');
        $conteudo = $this->strToFloat($this->data['conteudo'], 'float');            
        $aluguel  = $this->strToFloat($this->data['aluguel'],  'float');
        $eletrico = $this->strToFloat($this->data['eletrico'], 'float');
        $vendaval = $this->strToFloat($this->data['vendaval'], 'float');
        
        //Calcula de coberturas caso estejam zeradas do form
        if($incendio == 0.0)
            $incendio = $vlrAluguel * $this->data['multiplosMinimos']->getMultIncendio();
        
        if($conteudo == 0.0)
            $conteudo = $vlrAluguel * $this->data['multiplosMinimos']->getMultConteudo();
        
        if($aluguel == 0.0)
            $aluguel  = $vlrAluguel * $this->data['multiplosMinimos']->getMultAluguel();
        
        if($eletrico == 0.0)
            $eletrico = $vlrAluguel * $this->data['multiplosMinimos']->getMultEletrico();
        
        if($vendaval == 0.0)
            $vendaval = $vlrAluguel * $this->data['multiplosMinimos']->getMultVendaval();

        // Calcula cobertura premio = cobertura * (taxa / 100)       
        $total = 0.0 ;
        $txIncendio = $this->calcTaxaMultMinMax($incendio,'Incendio') ;
        $total += $txIncendio;
        
        $txConteudo = $this->calcTaxaMultMinMax($conteudo,'IncendioConteudo','Conteudo') ;
        $total += $txConteudo;
        
        $txAluguel = $this->calcTaxaMultMinMax($aluguel,'Aluguel') ;
        $total += $txAluguel;
        
        $txEletrico = $this->calcTaxaMultMinMax($eletrico,'Eletrico') ;
        $total += $txEletrico;
        
        $txVendaval = $this->calcTaxaMultMinMax($vendaval,'Desastres','Vendaval') ;
        $total += $txVendaval;
        
        //Verificar Se administradora tem total de cobertura minima e compara
        $coberturaMinAdm = $this->getParametroSis($this->data['administradora']->getId() . '_cob_min');
        $totalAntes = 0.0;
        if($coberturaMinAdm !== FALSE);{
            if($total < $coberturaMinAdm){
                $totalAntes = $total;
                $total = $coberturaMinAdm;
            }
        }
        
        $iof = floatval($this->getParametroSis('taxaIof')); 
        
        $this->data['taxaIof'] = $this->strToFloat($iof,'',4);
        
        $totalBruto = $total * (1 + $iof) ;
        
        if($totalAntes != 0.0){
            $this->data['premio']        = $this->strToFloat($totalAntes);
        }else{
            $this->data['premio']        = $this->strToFloat($total);
        }
        $this->data['premioLiquido'] = $this->strToFloat($total);
        $this->data['premioTotal']   = $this->strToFloat($totalBruto);
        $this->data['incendio']      = $this->strToFloat($incendio);
        $this->data['cobIncendio']   = $this->strToFloat($txIncendio);
        $this->data['conteudo']      = $this->strToFloat($conteudo);
        $this->data['cobConteudo']   = $this->strToFloat($txConteudo);
        $this->data['aluguel']       = $this->strToFloat($aluguel);
        $this->data['cobAluguel']    = $this->strToFloat($txAluguel);
        $this->data['eletrico']      = $this->strToFloat($eletrico);
        $this->data['cobEletrico']   = $this->strToFloat($txEletrico);
        $this->data['vendaval']      = $this->strToFloat($vendaval);
        $this->data['cobVendaval']   = $this->strToFloat($txVendaval);
        
        return array($total,$totalBruto,$incendio,$conteudo,$aluguel,$eletrico,$vendaval);
    }

    /**
     * Pega os inputs com dados calculados e trabalhados
     * @return array com inputs atualizados para colocar no form
     */
    public function getNewInputs() {
        return array(
            'premioTotal'=>$this->data['premioTotal'],
            'premioLiquido'=>$this->data['premioLiquido'],
            'premio'=>$this->data['premio'],
            'incendio'=>$this->data['incendio'],
            'conteudo'=>$this->data['conteudo'],
            'aluguel'=>$this->data['aluguel'],
            'eletrico'=>$this->data['eletrico'],
            'vendaval'=>$this->data['vendaval'],
        );
    }

    /**
     * Calcula o premio(vlr) do seguro no item da cobertura passada pelo paramentro
     * premio = cobertura * (taxa / 100)
     * Tipo de Taxa pode ser anual ou mensal
     * se premio for menor que o vlr minimo prevalece o minimo o mesmo acontece para o vlr maximo 
     * @param float $vlr     Valor da Cobertura
     * @param string $fTaxa  Parte do nome da funcao da entity taxa ex Incendio
     * @param string $fMin   Parte do nome da funcao da entity multiplosMinimos ex Incendio
     * @return real
     */
    public function calcTaxaMultMinMax($vlr, $fTaxa, $fMin='') {
        if($vlr == 0.0)    
            return 0.0;

        if (empty($fMin))
            $fMin = $fTaxa;

        //Gera o nome da função a ser chamada na entity taxa
        if($this->data['validade'] == 'anual')
            $fTaxa = 'get' . $fTaxa ;
        else
            $fTaxa = 'get' . $fTaxa . 'Men';
        //Gera o nome da função a ser chamada na entity multiplosMinimos
        $fMax = 'getMax' . $fMin ;
        $fMin = 'getMin' . $fMin ;
        
        $calc = $vlr * ($this->data['taxa']->$fTaxa() / 100);
        
        // Se calculado for menor que o minimo retorna o min
        $vlrMin = floatval($this->data['multiplosMinimos']->$fMin());
        if($calc < $vlrMin)
            return $vlrMin;
        
        // Se calculado for maior que o maximo retorna o max
        $vlrMax = floatval($this->data['multiplosMinimos']->$fMax());
        if(($vlrMax != 0.0)AND($calc > $vlrMax))
            return $vlrMax;
        // Valor calculado
        return $calc;
    }
}
