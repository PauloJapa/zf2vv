<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LocatarioRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocatarioRepository extends EntityRepository {

    /**
     * Auto complete em ajax esta função retorna as entitys encontradas
     * com a ocorrencia passada por parametro
     * @param string $locatario
     * @param string $cpf
     * @param string $cnpj
     * @return array
     */
    public function autoComp($locatario, $cpf='', $cnpj=''){
        
        $locatario = trim($locatario);
        
        if(empty($cpf))
            $cpf = $locatario;
        
        if(empty($cnpj))
            $cnpj = $locatario;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locatario', 'u')
                ->where("u.nome LIKE :locatario OR u.cpf LIKE :cpf OR u.cnpj LIKE :cnpj")
                ->setParameter('locatario', $locatario)
                ->setParameter('cpf', $cpf)
                ->setParameter('cnpj', $cnpj)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
    /**
     * Pesquisa conforme paramentro passados:
     * Nome, cpfOuCnpj, documento
     * @param array $filtros
     * @return array
     */
    public function pesquisa(array $filtros){
        // Monta clasula where
        $this->where = 'u.id <> :id';
        $this->parameters['id']  = 'null';
        
        if(isset($filtros['nome'])){
            $this->where .= ' AND u.nome LIKE :nome';
            $this->parameters['nome']  = $filtros['nome'] . '%';            
        }
        
        if(isset($filtros['cpf'])){
            $this->where .= ' AND u.cpf LIKE :cpf';
            $this->parameters['cpf']  = preg_replace("/[' '-./ t]/",'',$filtros['cpf']) . '%'; 
        }
        
        if(isset($filtros['cnpj'])){
            $this->where .= ' AND u.cnpj LIKE :cnpj';
            $this->parameters['cnpj']  = preg_replace("/[' '-./ t]/",'',$filtros['cnpj']) . '%'; 
        }
        
        // Retorna uma qb para ser feita a paginação 
        return  $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locatario', 'u')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('u.nome');
    }
    
    public function getListaLocatario() {        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u.id, u.nome, u.cpf, u.cnpj')
                ->from('Livraria\Entity\Locatario', 'u')
                ->getQuery();        
        $reg = $query->getArrayResult();
        $ret = [];
        foreach ($reg as $value){
            $ret[$value['id']][0] = $value['nome'];
            if(!empty($value['cpf']) AND !is_null($value['cpf'])){
                $ret[$value['id']][1] = $value['cpf'];                
            }else{
                $ret[$value['id']][1] = $value['cnpj'];                                
            }
        }
        return $ret;
    }
    
}

