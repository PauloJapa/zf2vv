<?php


namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * ParametroSis
 *
 * Serviços Executa as ações na tabela
 */
class ParametroSis extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\ParametroSis";
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
        
        if(parent::insert())
            $this->logForNew();
        
        return TRUE;
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em taxas
     */
    public function logForNew(){
        parent::logForNew('parametro_sis', 'parametroSis');
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
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em taxas De/Para
     */
    public function logForEdit(){
        parent::logForEdit('parametro_sis', 'parametroSis');
    }
    
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Key', $ent->getKey(), $this->data['key']);
        $this->dePara .= $this->diffAfterBefore('Parametro', $ent->getConteudo(), $this->data['conteudo']);
        $this->dePara .= $this->diffAfterBefore('Descrição', $ent->getDescricao(), $this->data['descricao']);
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
        $entitys = $repository->findBy(array('key' => $this->data['key']));
        $erro = [] ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if($entity->getConteudo() == $this->data['conteudo']){
                    $erro[] = "Alerta!" ;
                    $erro[] = "Já existe um paremtro com esse valor!!";
                    $erro[] = $entity->getId() ;
                }
            }
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}