<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

/**
 * AbstractService
 * Tem os metodos basicos para o Crud no BD
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractService {

    /**
     * Objeto para efetuar operações no banco
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    /**
     * Onde "Tabela" é nome da tabela em que está sendo tratada.
     * @var Livraria\Entity\"Tabela" 
     */
    protected $entity;
    /**
     * Objeto que pega os dados do usuario armazenado
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authService;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
 
    /** 
     * Inserir no banco de dados o registro
     * Retorna a entity da tabela que foi criada.
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function insert(array $data) {
        if ($user = $this->getIdentidade())
            $data['userIdCriado'] = $user->getId();
        $entity = new $this->entity($data);
        
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }

    /** 
     * Alterar no banco de dados o registro e
     * Retorna a entity da tabela que foi alterada.
     * @param Array $data com os campos do registro
     * @return entidade
     */      
    public function update(array $data) {
        if ($user = $this->getIdentidade())
            $data['userIdAlterado'] = $user->getId();
        $entity = $this->em->getReference($this->entity, $data['id']);
        $entity = Configurator::configure($entity, $data);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
  
    /** 
     * Esclui o registro ou marca como cancelado se existir os campo status
     * @param $id do registro
     * @return Boolean
     */   
    public function delete($id) {
        $entity = $this->em->getReference($this->entity, $id);
        if($entity) {
            if(method_exists($entity,"setStatus")){
                $entity->setStatus('C'); //Cancelado
            }else{
                $this->em->remove($entity);
            }
            $this->em->flush();
            return true ;
        }
    }
 
    /** 
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function getIdentidade() { 
        if (is_object($this->authService)) {
            return $this->authService->getIdentity();
        }else{
            $sessionStorage = new SessionStorage("LivrariaAdmin");
            $this->authService = new AuthenticationService;
            $this->authService->setStorage($sessionStorage);
            if ($this->authService->hasIdentity()) 
                return $this->authService->getIdentity();
        }
        return false;
    }
    
    
    
}
