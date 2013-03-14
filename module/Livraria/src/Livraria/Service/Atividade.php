<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * Atividade
 * Faz o CRUD da tabela Atividade no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Atividade extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Atividade";
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
        parent::logForNew('Atividade', 'atividades');
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
            'descricao' => $this->data['descricao']
        ));
        $erro = [] ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                $erro[] = "Alerta! Já existe esta Atividade cadastrada!!";
            }
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
