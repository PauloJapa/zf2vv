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
        //cria variaveis cpf e cnpj
        $filtros['cpf'] = '';
        $filtros['cnpj'] = '';
        //Monta parametros se conteudo for vazio ele coloca um espaco e depois acresente o coringa '%'
        $paramentros['locatario'] =$filtros['nome'] == '' ? ' ' : $filtros['nome'] . '%';
        if (!empty($filtros['cpfOuCnpj'])) {
            $filtros[$filtros['cpfOuCnpj']] = $filtros['documento'];
            //Monta clasula where e seus paramentros
            if ($filtros['cpfOuCnpj'] == 'cpf') {
                $where = "(u.nome LIKE :locatario OR u.cpf LIKE :cpf) AND u.tipo = :tipo";
                $paramentros['cpf'] = $filtros['cpf'] == '' ? ' ' : $filtros['cpf'] . '%';
                $paramentros['tipo'] = 'fisica';
            } else {
                $where = "(u.nome LIKE :locatario OR u.cnpj LIKE :cnpj) AND u.tipo = :tipo";
                $paramentros['cnpj'] = $filtros['cnpj'] == '' ? ' ' : $filtros['cnpj'] . '%';
                $paramentros['tipo'] = 'juridica';
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locatario', 'u')
                ->where($where)
                ->orderBy('u.nome')
                ->setParameters($paramentros)
                ->getQuery();
        return $query->getResult();
    }
    
}

