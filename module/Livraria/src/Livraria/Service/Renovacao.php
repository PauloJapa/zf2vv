<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Renovacao
 * Faz o CRUD da tabela Renovacao no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Renovacao extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaImovel;
    
    /**
     * Entity Orcamento
     * @var type
     */
    protected $fechado;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Renovacao";
        $this->fechado = "Livraria\Entity\Fechados";
    }
    
    public function renovar(\Livraria\Entity\Fechados $fechado){
        $this->fechado = $fechado;
        //Montar dados para tabela de renovacao
        $this->data = $this->fechado->toArray();
        $this->data['fechadoOrigemId'] = $this->data['id'];
        unset($this->data['id']);
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "A";
        $this->data['criadoEm'] = new \DateTime('now');
        $this->data['inicio'] = $this->fechado->getInicio('obj');
        //Nova Vigência
        $resul = $this->recalculaVigencia();
        if($resul !== TRUE)
            return $resul;
        
        //Pegando o locatario atual desse imovel porque o locatario pode ter sido trocado no meio da vigencia do fechado
        //Quando a troca de locatario é apenas atualizado no imovel.
        $this->data['locatario'] = $fechado->getImovel()->getLocatario()->getId();
        $this->data['refImovel'] = $fechado->getImovel()->getRefImovel();

        //Novo calculo do premio
        //     Comissão da Administradora padrão
        $this->data['comissaoEnt'] = $this->em
            ->getRepository('Livraria\Entity\Comissao')
            ->findComissaoVigente($this->data['administradora'],  $this->data['criadoEm']);
        $this->data['comissao'] = $this->data['comissaoEnt']->floatToStr('comissao');
        
        $this->data['taxa'] = $this->em
            ->getRepository('Livraria\Entity\Taxa')
            ->findTaxaVigente($this->data['seguradora'], $this->data['atividade'],  $this->data['criadoEm']);

        if(!$this->data['taxa'])
            return ['Taxas para esta classe e atividade vigênte nao encontrada!!!'];
        
        $this->data['multiplosMinimos'] = $this->em
            ->getRepository('Livraria\Entity\MultiplosMinimos')
            ->findMultMinVigente($this->data['seguradora'],  $this->data['criadoEm']);
        
        $this->data['administradora'] = $this->fechado->getAdministradora()->getObjeto();
        $resul = $this->CalculaPremio();
        
        $this->data['fechadoId'] = '0';
        
        //Faz inserção do fechado no BD
        $resul = $this->insert();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->fechado->setRenovacaoId($this->data['id']);
            $this->fechado->setStatus('R');
            $this->em->persist($this->fechado);
            $this->em->flush();
            $this->registraLogFechado();
        }
        
        return $resul;
    }

    /**
     * Calcula nova vigencia da renovacao periodo mensal ou anual
     * @return boolean | array
     */
    public function recalculaVigencia(){
        if(!isset($this->data['validade'])){
            return ['Campo validade não existe!!'];
        }
        $this->data['codano'] = $this->data['criadoEm']->format('Y');
        
        $interval_spec = ''; 
        if($this->data['validade'] == 'mensal'){
            $interval_spec = 'P1M'; 
        } 
        if($this->data['validade'] == 'anual'){
            $interval_spec = 'P1Y'; 
        } 
        if(empty($interval_spec)){
            $this->data['validade'] = 'anual';
            $interval_spec = 'P1Y'; 
            //return ['Campo validade com valor que não existe na lista!!'];
        }
        
        $this->data['inicio']->add(new \DateInterval($interval_spec)); 
        
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval($interval_spec)); 
        return TRUE;
    }
    
    public function registraLogFechado(){
        //Criar serviço logorcamento
        $log = new LogFechados($this->em);
        $dataLog['fechados']    = $this->fechado;
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'gerarRenovacao';
        $fechado   = $this->fechado->getId() . '/' . $this->fechado->getCodano();
        $renovacao = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Renovar Seguro(' . $fechado . ') e gerou a renovacao de numero ' . $renovacao ;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }

        /**
     * Faz referencia para new ou edit dos registros a serem inclusos
     * Converte id de entity em referencia
     * Converte string date em objeto date
     */
    public function setReferences(){
        $this->idToReference('locador', 'Livraria\Entity\Locador');
        $this->idToReference('locatario', 'Livraria\Entity\Locatario');
        $this->idToReference('atividade', 'Livraria\Entity\Atividade');
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('imovel', 'Livraria\Entity\Imovel');
        $this->idToReference('taxa', 'Livraria\Entity\Taxa');
        $this->idToReference('user', 'Livraria\Entity\User');
        $this->idToReference('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');
        $this->idToReference('comissaoEnt', 'Livraria\Entity\Comissao');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');
    }

    /**
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade
     */
    public function insert(array $data=[]) {
        if(!empty($data))
            $this->data = $data;

        $this->setReferences();

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
        $log = new LogRenovacao($this->em);
        $dataLog['renovacao']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_renovacao';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'gerarRenovacao';
        $fechado = $this->fechado->getId() . '/' . $this->fechado->getCodano();
        $renovacao   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Nova renovação de seguro n ' . $renovacao . ' do seguro fechado n ' . $fechado;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
       
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
        parent::logForEdit('renovacao');
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
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Renovacao $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
    }
    
}
