<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Locatario
 * Faz o CRUD da tabela Locatario no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Locatario extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do locatario
     * @var string 
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Locatario";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        //$this->idToReference('user', 'Livraria\Entity\User');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e inserindo novo endereco do locatario
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);
        
        $this->setReferences();

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em locatarios
     */
    public function logForNew(){
        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = 'locatario';
        $dataLog['controller'] = 'locatarios';
        $dataLog['action']     = 'new';
        $dataLog['dePara']     = 'Inseriu um novo registro';
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e inserindo novo endereco do locatario
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        
        $this->setReferences();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em locatarios De/Para
     */
    public function logForEdit(){
        if(empty($this->dePara)) 
            return ;
        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = 'locatario';
        $dataLog['controller'] = 'locatarios';
        $dataLog['action']     = 'edit';
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Locatario $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Tipo', $ent->getTipo(), $this->data['tipo']);
        $this->dePara .= $this->diffAfterBefore('CPF', $ent->getCpf(), $ent->formatarCPF_CNPJ($this->data['cpf']));
        $this->dePara .= $this->diffAfterBefore('CNPJ', $ent->getCnpj(), $ent->formatarCPF_CNPJ($this->data['cnpj']));
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
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
        if(!empty($this->data['cpf']))
            $filtro['cpf'] = $this->data['cpf'];
        
        if(!empty($this->data['cnpj']))
            $filtro['cnpj'] = $this->data['cnpj'];
        
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if($entity->getCpf() == $this->data['cpf'])
                    $erro[] = 'Já existe um cpf cadastrado nome de ' . $entity->getNome();
                if($entity->getCnpj() == $this->data['cnpj'])
                    $erro[] = 'Já existe um cnpj cadastrado nome de ' . $entity->getNome();
            }
        }
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
}