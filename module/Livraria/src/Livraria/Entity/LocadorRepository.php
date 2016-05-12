<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LocadorRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocadorRepository extends EntityRepository {

    
    /**
     * Auto complete em ajax esta função retorna as entitys encontradas
     * com a ocorrencia passada por parametro
     * @param string $locador
     * @return \Livraria\Entity\Locador
     */
    public function autoComp($locador, $administradora='', $cpf='', $cnpj=''){
        
        $locador = trim($locador);
        
        if(empty($cpf))
            $cpf = $locador;
        
        if(empty($cnpj))
            $cnpj = $locador;
        
        if(empty($administradora)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('u')
                    ->from('Livraria\Entity\Locador', 'u')
                    ->where("u.nome LIKE :locador OR u.cpf LIKE :cpf OR u.cnpj LIKE :cnpj")
                    ->setParameter('locador', $locador)
                    ->setParameter('cpf', $cpf)
                    ->setParameter('cnpj', $cnpj)
                    ->setMaxResults(20)
                    ->getQuery();
        }else{
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('u')
                    ->from('Livraria\Entity\Locador', 'u')
                    ->where("(u.nome LIKE :locador OR u.cpf LIKE :cpf OR u.cnpj LIKE :cnpj) AND u.administradora = :administradora")
                    ->setParameter('locador', $locador)
                    ->setParameter('cpf', $cpf)
                    ->setParameter('cnpj', $cnpj)
                    ->setParameter('administradora', $administradora)
                    ->setMaxResults(20)
                    ->getQuery();
        }
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
                ->from('Livraria\Entity\Locador', 'u')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('u.nome');
        
    }
    
}

