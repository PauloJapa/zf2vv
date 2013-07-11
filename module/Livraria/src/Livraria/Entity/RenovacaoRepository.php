<?php

namespace Livraria\Entity;

/**
 * RenovacaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class RenovacaoRepository extends AbstractRepository {

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
    public function findRenovar($ini,$fim,$adm, $val=null){
        if(!empty($ini)){
            $date = explode("/", $ini);
            $ini  = $date[2] . $date[1] . $date[0];
        }
        if(!empty($fim)){
            $date = explode("/", $fim);
            $fim  = $date[2] . $date[1] . $date[0];
        }
           
        $this->where = ' f.status <> :status AND f.fim BETWEEN :inicio AND :fim';
        $this->parameters['status']  = 'R';
        $this->parameters['inicio']  = $ini;
        $this->parameters['fim']  = $fim;
        
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
        
        if(isset($val) AND count($val) == 1){
            $this->where .= '  AND f.validade = :validade';
            $this->parameters['validade']  = $val[0];            
        }
        
        // Monta a dql para fazer consulta no BD
        $qb = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('f')
                ->from('Livraria\Entity\Fechados', 'f')
                ->where($this->where)
                ->setParameters($this->parameters); 
        
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
         
        if (empty($data['inicio']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status';
        $this->parameters['inicio']  = $this->dateToObject($data['inicio']);
        if (!empty($data['fim'])){
            $this->parameters['fim'] = $this->dateToObject($data['fim']);
        }else{
            $this->parameters['fim'] = clone $this->parameters['inicio'];
            $this->data['fim']->add(new \DateInterval('P1M')); 
        }
        $this->parameters['status'] = 'F';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at')
                ->from('Livraria\Entity\Renovacao', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        // Retorna um array com todo os registros encontrados        
        $principalResul = $query->getQuery()->getArrayResult();
        
        // Segundo consulta é para pegar os os valores do seguro antes da renovação
        $this->where = 'o.id IN(';
        $this->parameters = [];
        foreach ($principalResul as $key => $renovacao) {
            if($renovacao['fechadoOrigemId'] == 0)
                echo "<h2>Sem registro do fechado anterior ", $renovacao['id'] , "</h2u>";
            $this->parameters[] = $renovacao['fechadoOrigemId'];
            $this->where .= '?' . $key . ',';
        }
        $this->where = substr($this->where, 0, -1) . ')';
        if(empty($this->parameters)){
            return [];
        }  
        
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o')
                ->from('Livraria\Entity\Renovacao', 'o')
                ->where($this->where)
                ->setParameters($this->parameters);
        $secundarioResul = $query->getQuery()->getArrayResult();
        
        // Montar Array com a descrição das formas de pagamento
        $formPagto = $this->getEntityManager()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        
        
        //Retonar 2 array com resultado para comparação
        return $this->formataDadosParaExibicao($principalResul,$secundarioResul,$formPagto,$data);
    }
    
    public function formataDadosParaExibicao($principal,$secundario,$formPagto,$data){
        $totF = $totI = 0;
        $seq = 1;
        $exibicao = [];
        $filtro = (empty($data['percent'])) ? FALSE : floatval(str_replace(',', '.', $data['percent'])); 
        foreach($principal as $lines){
            $coluns = [];
            $coluns[] = $seq;
            $coluns[] = $lines['id'] . "/" . $lines['codano'];
            $coluns[] = $lines['administradora']['id'];
            $coluns[] = $lines['refImovel'];
            $coluns[] = $lines['locadorNome'];
            $coluns[] = $lines['locatarioNome'];
            //Procurar fechado anterior a esse 
            $flag = false;
            foreach ($secundario as $value) {
                //Verificar se realmente o codigo anterior bate com esse ID
                if($value['id'] == $lines['fechadoOrigemId']){
                    $totI = $value['premioTotal'];
                    $coluns[] = $formPagto[$value['formaPagto']];
                    $coluns[] = number_format($value['premioLiquido'], 2, ',', '.');
                    $coluns[] = number_format($totI, 2, ',', '.');
                    $flag = true;
                    break;
                }        
            }
            if(!$flag){
                $coluns[] = '';$coluns[] = '';$coluns[] = '';$totI=0;
                echo "<p>Alert fechado anterior ñ encontrado com id " , $lines['fechadoOrigemId'], ".<p>";
            }
            $totF = $lines['premioTotal'];
            // Calcular porcentagem e filtra registro se necessario
            if($totI != 0){
                $percent = round($totF * 100 / $totI - 100, 2);
            }else{
                $percent = 0 ;
            }  
            // Filtrar os registros que a porcentagem diferença é menor que o filtro
            if ($filtro !== FALSE){
                $flag2 = FALSE;
                switch ($data['comando']) {
                    case '':
                    case '==':
                        if($filtro != $percent)
                            $flag2 = TRUE;
                        break;
                    case '>=':
                        if($filtro > $percent)
                            $flag2 = TRUE;
                        break;
                    case '<=':
                        if($filtro < $percent)
                            $flag2 = TRUE;
                        break;
                }
                if($flag2){
                    continue;
                }
            }
            $coluns[] = $formPagto[$lines['formaPagto']];
            $coluns[] = number_format($lines['premioLiquido'], 2, ',', '.');
            $coluns[] = number_format($totF, 2, ',', '.');
            $coluns[] = ($percent == 0) ? '' : number_format($percent, 2, ',', '.') . "%";
            $coluns[] = $lines['atividade']['descricao'];
            
            $exibicao[] = $coluns;
            $seq++;
        }   
        
        return $exibicao;
    }
    
    /**
     * Faz a atualização de todos os seguros com a nova referencia do imovel
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
        ->update('Livraria\Entity\Renovacao', 'o')
        ->set('o.refImovel', ':ref')
        ->where('o.imovel = :id')
        ->setParameter('ref', $setRefImovel)
        ->setParameter('id', $id)
        ->getQuery()
        ->execute();
        
        return TRUE;
        
    }
    
}

