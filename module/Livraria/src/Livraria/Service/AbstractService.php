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
    
    /**
     * Dados do form a serem validados
     * @var array
     */
    protected $data;

    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
 
    /** 
     * Inserir no banco de dados o registro
     * @param array $data com os campos do registro
     * @return boolean 
     */  
    public function insert(array $data = null) {
        if($data){
            $this->data = $data;
        }
        if ($user = $this->getIdentidade())
            $this->data['userIdCriado'] = $user->getId();
        
        $entity = new $this->entity($this->data);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return TRUE;
    }

    /** 
     * Alterar no banco de dados o registro e
     * @param array $data com os campos do registro
     * @return boolean
     */      
    public function update(array $data = null) {
        if($data){
            $this->data = $data;
        }
        if ($user = $this->getIdentidade())
            $this->data['userIdAlterado'] = $user->getId();
        $entity = $this->em->getReference($this->entity, $this->data['id']);
        $entity = Configurator::configure($entity, $this->data);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return TRUE;
    }
  
    /** 
     * Esclui o registro ou marca como cancelado se existir os campo status
     * @param $id do registro
     * @return boolean
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
            return TRUE ;
        }
        return FALSE ;
    }
 
    /** 
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return Livraria\Entity\User 
     * @return boolean
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
        return FALSE;
    }
    
    /**
     * Converte uma data string em data object no indice apontado.
     * @param string $index
     * @return boolean
     */
    public function dateToObject($index){
        //Trata as variveis data string para data objetos
        if(!isset($this->data[$index]))
            return FALSE;
        
        if((!empty($this->data[$index])) && ($this->data[$index] != "vigente")){
            $date = explode("/", $this->data[$index]);
            $this->data[$index]    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }else{
            $this->data[$index]    = new \DateTime("00/00/0000");
        }
        
        if($this->data[$index]){
            return TRUE;
        }
        return FALSE;
    }   
    
    /**
     * Converte o id de um registro dependente em object reference
     * @param string $index   Indice do array a ser feita a ligação
     * @param string $entity  Caminho para a Entity 
     */
    public function idToReference($index, $entity){
        if(!isset($this->data[$index]))
            return FALSE;
        $this->data[$index] = $this->em->getReference($entity, $this->data[$index]);
    }
    
    
}
