<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * RenovacaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class RenovacaoRepository extends EntityRepository {

    
    public function findRenovar($ini,$fim,$adm){
        if(!empty($ini)){
            $date = explode("/", $ini);
            $ini  = $date[2] . $date[1] . $date[0];
        }
        if(!empty($fim)){
            $date = explode("/", $fim);
            $fim  = $date[2] . $date[1] . $date[0];
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f')
            ->from('Livraria\Entity\Fechados', 'f');
        if(!empty($adm)){
            $qb->where(" f.status <> :status
                    AND   f.administradora = :administradora
                    AND   f.fim BETWEEN :inicio AND :fim
                ")
                ->setParameters([
                    'status' => 'R',
                    'administradora' => $adm,
                    'inicio' => $ini,
                    'fim' => $fim
                ]);
        }else{
            $qb->where(" f.status <> :status
                    AND   f.fim BETWEEN :inicio AND :fim
                ")
                ->setParameters([
                    'status' => 'R',
                    'inicio' => $ini,
                    'fim' => $fim
                ]);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    
    
    public function findRenovacao($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('l,u,i,ld','lc','ad','at')
                    ->from('Livraria\Entity\Renovacao', 'l')
                    ->join('l.user', 'u')
                    ->join('l.imovel', 'i')
                    ->join('l.locador', 'ld')
                    ->join('l.locatario', 'lc')
                    ->join('l.administradora', 'ad')
                    ->join('l.atividade', 'at')
                    ->where('l.status = :status')
                    ->setParameter('status', 'A')
                    ->orderBy('l.criadoEm', 'DESC')
                    ->getQuery();
            return $query->getResult();
        }
        $where = 'l.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND l.inicio >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND l.inicio <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
                    break;
                case 'status':
                    if($filtro != 'T'){
                        $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                        $where .= ' AND l.' . $key . ' ' . $op . ' :' . $key;
                        $parameters[$key] = $filtro;
                    }
                    break;
                default:
                    $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                    $where .= ' AND l.' . $key . ' ' . $op . ' :' . $key;
                    $parameters[$key] = $filtro;
                    break;
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('l,u,i,ld','lc','ad','at')
                ->from('Livraria\Entity\Renovacao', 'l')
                ->join('l.user', 'u')
                ->join('l.imovel', 'i')
                ->join('l.locador', 'ld')
                ->join('l.locatario', 'lc')
                ->join('l.administradora', 'ad')
                ->join('l.atividade', 'at')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('l.criadoEm', 'DESC')
                ->getQuery();
        
        return $query->getResult();
    }    
    
    public function getCustoRenovacao($data){
        
        if (empty($data['inicio']) OR empty($data['fim']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.fim <= :fim AND o.status = :status';
        $this->data['inicio'] = $data['inicio'];
        $this->data['fim']    = $data['fim'];
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->parameters['inicio'] = $this->data['inicio'];
        $this->parameters['fim']    = $this->data['fim'];
        $this->parameters['status'] = 'F';
        if($data['administradora']){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        
        // Monta a dql para fazer consulta no BD
        $query = $this->em
                ->createQueryBuilder()
                ->select('o,ad,i')
                ->from('Livraria\Entity\Renovacao', 'o')
                ->join('o.user', 'u')
                ->join('o.imovel', 'i')
                ->join('i.endereco', 'e')
                ->join('e.bairro', 'b')
                ->join('e.cidade', 'c')
                ->join('e.estado', 'uf')
                ->join('o.locador', 'ld')
                ->join('o.locatario', 'lc')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.seguradora', 's')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('o.administradora', 'ASC');
        
        // Retorna um array com todo os registros encontrados
        $principalResul = $query->getQuery()->getArrayResult();
        
    }
}

