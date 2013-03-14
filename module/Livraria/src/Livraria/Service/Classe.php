<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * Classe
 * Faz o CRUD da tabela Classe no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Classe extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Classe";
    }
    
    /**
     * Faz as conversões de id para entity para o doctrine valida
     * Abstração das actions new e edit
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
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
        
        $this->setReferences();
        
        if(parent::insert())
            $this->logForNew();
        
        return TRUE;        
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em taxas
     */
    public function logForNew(){
        parent::logForNew('Classe', 'classes');
    }

        /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;      
    }
    
    /**
     * Grava no logs dados da alteção feita em taxas De/Para
     */
    public function logForEdit(){
        parent::logForEdit('Classe', 'classes');
    }
    
    public function getDiff(\Livraria\Entity\Classe $ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora()->getId(), $this->data['seguradora']->getId()); 
        $this->dePara .= $this->diffAfterBefore('Nome da Classe', $ent->getDescricao(), $this->data['descricao']);
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
        $entitys = $repository->findBy(array(
            'seguradora' => $this->data['seguradora'],
            'descricao' => $this->data['descricao']
        ));
        $erro = [] ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                $erro[] = "Alerta! Já existe esta classe cadastrada!!";
            }
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
