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
        //cria variaveis cpf e cnpj
        $filtros['cpf'] = '';
        $filtros['cnpj'] = '';
        //Monta parametros se conteudo for vazio ele coloca um espaco e depois acresente o coringa '%'
        $paramentros['locador'] =$filtros['nome'] == '' ? ' ' : $filtros['nome'] . '%';
        if (!empty($filtros['cpfOuCnpj'])) {
            $filtros[$filtros['cpfOuCnpj']] = $filtros['documento'];
            //Monta clasula where e seus paramentros
            if ($filtros['cpfOuCnpj'] == 'cpf') {
                $where = "(u.nome LIKE :locador OR u.cpf LIKE :cpf) AND u.tipo = :tipo";
                $paramentros['cpf'] = $filtros['cpf'] == '' ? ' ' : $filtros['cpf'] . '%';
                $paramentros['tipo'] = 'fisica';
            } else {
                $where = "(u.nome LIKE :locador OR u.cnpj LIKE :cnpj) AND u.tipo = :tipo";
                $paramentros['cnpj'] = $filtros['cnpj'] == '' ? ' ' : $filtros['cnpj'] . '%';
                $paramentros['tipo'] = 'juridica';
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locador', 'u')
                ->where($where)
                ->orderBy('u.nome')
                ->setParameters($paramentros);
        
        return $query;
    }
    
}

