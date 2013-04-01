<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Locador
 * Faz o CRUD da tabela Locador no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Locador extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do locador
     * @var string 
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Locador";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->setReferences();
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e inserindo novo endereco do locador
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em locadors
     */
    public function logForNew($tabela='locador'){
        parent::logForEdit($tabela);
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
        
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em locadors De/Para
     */
    public function logForEdit($tabela='locador'){
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Locador $ent
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
        
        if(!empty($this->data['administradora']))
            $filtro['administradora'] = $this->data['administradora'];
        
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if($entity->getTipo() == 'fisica'){
                    if(($entity->getCpf() == $this->data['cpf'])
                    and ($entity->getAdministradora() == $this->data['administradora'])){
                        $erro[] = 'Já existe esse cpf de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
                        $erro[] = $entity->getId();
                    }
                }else{
                    if(($entity->getCnpj() == $this->data['cnpj'])
                    and ($entity->getAdministradora() == $this->data['administradora'])){
                        $erro[] = 'Já existe esse cnpj de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
                        $erro[] = $entity->getId();
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
}
