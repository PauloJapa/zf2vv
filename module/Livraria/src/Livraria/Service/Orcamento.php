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
        $this->idToReference('locador', 'Livraria\Entity\Locador');
        $this->idToReference('locatario', 'Livraria\Entity\Locatario');
        $this->idToReference('taxa', 'Livraria\Entity\Taxa');
        $this->idToReference('atividade', 'Livraria\Entity\Atividade');
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('user', 'Livraria\Entity\User');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('criadoEm');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */   
    public function insert(array $data) { 
        $this->data = $data;
        
        //Pegando o servico endereco e inserindo ou alterando o imovel
        if(empty($this->data['imovel'])){
            $this->data['imovel'] = (new Imovel($this->em))->insert($this->data);
            if(is_array($this->data['imovel'])){
                if(substr($this->data['imovel'][0], 0, 45) == "Já existe um imovel neste endereço  registro:"){
                    echo 'ok'; 
                }
            }
        }else{
            //Pegando o servico endereco e atualizando endereco do imovel do locador
            $serviceImove = new Imovel($this->em);
            $this->data['imovel'] = $serviceImove->update($this->data);
            if($this->data['imovel'] === TRUE){
                $this->idToReference('imovel', 'Livraria\Entity\Imovel');
                $this->deParaImovel = $serviceImove->getDePara();
            }else{
                return ['Houve um erro ao tentar atulizar o cadastro do Imovel desse Locador!!'];
            }
        }

        $this->setReferences();
       
        $this->data['codano'] = $this->data['criadoEm']->format('Y');
        
        $this->data['fim'] = $this->data['inicio'];
        if($this->data['validade'] == 'mensal'){
            $interval_spec = 'P1M'; 
        } 
        if($this->data['validade'] == 'anual'){
            $interval_spec = 'P1Y'; 
        } 
        $this->data['fim']->add(new \DateInterval($interval_spec)); 
        
        $this->data['taxa'] = $this->em
             ->getRepository('Livraria\Entity\Taxa')
             ->findTaxaVigente($this->data['seguradora']->getId(), $this->data['atividade']->getId());
        
        $this->data['premio'] = '0';
        $this->data['premioLiquido'] = '0';
        $this->data['premioTotal'] = '0';
        $this->data['codFechado'] = '0';
        $this->data['status'] = 'A';
        
        if (!isset($this->data['user']))
            $this->data['user'] = $this->getIdentidade()->getId();
        
        $this->idToReference('user', 'Livraria\Entity\User');
        
        
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
        parent::logForNew('orcamento');
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        //Pegando o servico endereco e inserindo ou alterando o imovel
        if(!empty($this->data['imovel'])){
            //Pegando o servico endereco e atualizando endereco do imovel do locador
            $serviceImove = new Imovel($this->em);
            $this->data['imovel'] = $serviceImove->update($this->data);
            if($this->data['imovel'] === TRUE){
                $this->idToReference('imovel', 'Livraria\Entity\Imovel');
                $this->deParaImovel = $serviceImove->getDePara();
            }else{
                return ['Houve um erro ao tentar atulizar o cadastro do Imovel desse Locador!!'];
            }
        }else{
            return ['Referencia do imovel não encontrada !!!!'];
        }
        $this->setReferences();
       
        $this->data['codano'] = $this->data['criadoEm']->format('Y');
        
        $this->data['fim'] = $this->data['inicio'];
        if($this->data['validade'] == 'mensal'){
            $interval_spec = 'P1M'; 
        } 
        if($this->data['validade'] == 'anual'){
            $interval_spec = 'P1Y'; 
        } 
        $this->data['fim']->add(new \DateInterval($interval_spec)); 
        
        
        
        
        $this->data['premio'] = '0';
        $this->data['premioLiquido'] = '0';
        $this->data['premioTotal'] = '0';
        $this->data['codFechado'] = '0';
        $this->data['status'] = 'A';
        
        
        
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
        parent::logForEdit('orcamento');
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
            return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if(empty($this->data['atividade']))
            return array('Atividade não pode estar vazia!!');
            
        $filtro['atividade'] = $this->data['atividade']->getId();
        $filtro['seguradora'] = $this->data['seguradora']->getId();
        //$filtro['classeTaxas'] = $this->data['classeTaxas']->getId();
        
        $entitys = $repository->findBy($filtro);
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($entity->getFim() == 'vigente') and ($this->data['fim']->format('d/m/Y') == '30/11/-0001')){
                    $erro[] = "Alerta! Já existe uma classe com esta Atividade para esta seguradora com data vigente! ID = " . $entity->getId() ;
                }
                $fim = $entity->getFim('obj');
                if($fim >= $this->data['inicio']){
                    $erro[] = "Alerta! Data de inicio conflita com data de registro existente! ID = " . $entity->getId() ;
                    $erro[] = "Data de inicio não pode ser menor ou igual a data final de vigencia<br>";
                }
                $diff = $fim->diff($this->data['inicio']);
                if($diff->days < $diferenca){
                    $diferenca = $diff->days ;
                }
            }
        }
        if(($diferenca > 3) and ($this->data['fim']->format('d/m/Y') == '30/11/-0001') and ($diferenca != 3650)){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima taxa valida! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
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
}
