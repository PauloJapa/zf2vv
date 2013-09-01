<?php

namespace Livraria\Entity;

/**
 * OrcamentoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class OrcamentoRepository extends AbstractRepository {

    public function findOrcamento($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('l,u,i,ld','lc','ad','at')
                    ->from('Livraria\Entity\Orcamento', 'l')
                    ->join('l.user', 'u')
                    ->join('l.imovel', 'i')
                    ->join('l.locador', 'ld')
                    ->join('l.locatario', 'lc')
                    ->join('l.administradora', 'ad')
                    ->join('l.atividade', 'at')
                    ->where('l.status = :status')
                    ->setParameter('status', 'A');
            return $query;
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
                ->from('Livraria\Entity\Orcamento', 'l')
                ->join('l.user', 'u')
                ->join('l.imovel', 'i')
                ->join('l.locador', 'ld')
                ->join('l.locatario', 'lc')
                ->join('l.administradora', 'ad')
                ->join('l.atividade', 'at')
                ->where($where)
                ->setParameters($parameters);
        
        if(isset($parameters['administradora'])){
            $query->orderBy('l.criadoEm', 'DESC');
        }
        
        return $query;
    }    
    
    /**
     * Faz a atualização de todos os seguros com a nova referencia
     * @param integer $id
     * @param string  $setRefImovel
     * @return boolean
     */
    public function cascateUpdateRefImovel($id='', $setRefImovel=''){
        if(empty($id) OR empty($setRefImovel)){
            return FALSE;
        }
        
        $this->getEntityManager()
        ->createQueryBuilder()
        ->update('Livraria\Entity\Orcamento', 'o')
        ->set('o.refImovel', ':ref')
        ->where('o.imovel = :id')
        ->setParameter('ref', $setRefImovel)
        ->setParameter('id', $id)
        ->getQuery()
        ->execute();
        
        return TRUE;
        
    }
    
    /**
     * Monta um DQL para pesquisar no banco e retornar um resultado em array 
     * Os seguros pendentes 
     * @param array $data
     * @return array
     */
    public function getPendentes($data){
        if (empty($data['inicio']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND (o.status = :status OR o.status = :status2)';
        $this->parameters['inicio']  = $this->dateToObject($data['inicio']);
        $this->parameters['fim']     = $this->dateToObject($data['fim']);
        $this->parameters['status']  = 'A';
        $this->parameters['status2'] = 'R';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,i,ld,lc')
                ->from('Livraria\Entity\Orcamento', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'i')
                ->join('o.locador', 'ld')
                ->join('o.locatario', 'lc')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        return $query->getQuery()->getArrayResult();
    }
    
    /**
     * Busca Fechados que estão vencendo para renovação
     * Filtra pela data de final da vigencia, administradora e validade(mensal ou anual)
     * Filtro automatico para administradoras inseridas no paramentro com a chave nao_gera_renovacao
     * @param string $ini
     * @param string $fim
     * @param string $adm
     * @param array $val
     * @return array de entities
     */
    public function findRenovar($mesFiltro,$ano,$adm){
        //Trata os filtro para data mensal
        $this->data['inicio'] = new \DateTime($mesFiltro . '/01/' . $ano);
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        
        $this->where =  ' f.status = :status';
        $this->where .= ' AND f.fim BETWEEN :inicio AND :fim';
        $this->where .= ' AND f.validade = :validade';
        $this->where .= ' AND f.validade = :validade';
        $this->where .= ' AND f.mensalSeq <= :mensalSeq';
        
        $this->parameters['status']   = 'A';
        $this->parameters['inicio']   = $this->data['inicio'];
        $this->parameters['fim']      = $this->data['fim'];
        $this->parameters['validade'] = 'mensal';
        $this->parameters['mensalSeq'] = 10;
        if(!empty($adm)){
            $this->where .= '  AND f.administradora = :administradora';
            $this->parameters['administradora']  = $adm;
        }else{
            //Filtrar Adm pelo parametro
            $admBlock = $this->getEntityManager()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('nao_gera_renovacao');
            foreach ($admBlock as $key => $value) {
                if(empty($key)){
                    continue;
                }
                $this->where .= '  AND f.administradora <> :admCod' . $key;  
                $this->parameters['admCod' . $key]  = $key;                
            }
        }
        
        // Monta a dql para fazer consulta no BD
        $qb = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('f,ad,at,im,ld,lc')
                ->from('Livraria\Entity\Fechados', 'f')
                ->join('f.administradora', 'ad')
                ->join('f.atividade', 'at')
                ->join('f.imovel', 'im')
                ->join('f.locador', 'ld')
                ->join('f.locatario', 'lc')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('f.administradora'); 
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Busca todos as renovaçoes mensais em aberto para fecha-los.
     * @param string $filtros
     * @param type $operadores
     * @param type $retQuery
     * @return type
     */
    public function findRenovacao($filtros=[],$operadores=[],$retQuery=false){
        if(empty($filtros)){
            $filtros['status'] = 'A';
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
                ->from('Livraria\Entity\Orcamento', 'l')
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
        
        if($retQuery){
            return $query;
        }
        return $query->getResult();
    }  
    
}

