<?php

namespace Livraria\Entity;

/**
 * OrcamentoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class OrcamentoRepository extends AbstractRepository {

    public function findOrcamento($filtros=[],$operadores=[],$data=[]){
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
                    switch ($filtro) {
                        case 'T':
                            $where .= ' AND (l.status = :status1 OR  l.status = :status2)';
                            $parameters['status1'] = 'A';                         
                            $parameters['status2'] = 'R'; 
                            break;
                        case 'X':
                            continue;
                            break;

                        default:
                            $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                            $where .= ' AND l.' . $key . ' ' . $op . ' :' . $key;
                            $parameters[$key] = $filtro;
                            break;
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
        
        if(isset($data['ordenador']) AND !empty($data['ordenador'])){  
            $query->orderBy($data['ordenador'], $data['ascdesc']);
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
        if (empty($data['inicio'])) {
            return [];
        }
        
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
        
        $this->where =  ' (f.status = :status OR f.status = :status2)';
        $this->where .= ' AND f.inicio BETWEEN :inicio AND :fim';
        $this->where .= ' AND f.validade = :validade';
        $this->where .= ' AND f.mesNiver <> :mesFiltro';
//        $this->where .= ' AND f.mensalSeq <= :mensalSeq';
        
        $this->parameters['status']   = 'A';   // SEGURO que foi fechado
        $this->parameters['status2']   = 'AR'; // SEGURO MENSAL que foi fechado e renovado
        $this->parameters['inicio']   = $this->data['inicio'];
        $this->parameters['fim']      = $this->data['fim'];
        $mes = (int)$mesFiltro + 1;
        if($mes == 13){
            $mes = 1;
        }
        $this->parameters['mesFiltro'] = $mes;
        $this->parameters['validade'] = 'mensal';
//        $this->parameters['mensalSeq'] = 10;
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
    
    public function trySetFechadoOrigem(&$reno){
        $data = clone $reno['inicio'];
        $mesS = (int)$data->format('m');
        //Calcula periodo para seguro do tipo mensal
        if($reno['validade'] == 'mensal'){
            $mesN = (int)$reno['mesNiver'];
            if($mesN == 0){
                echo '<h1>Erro Seguro sem mes de niversario definido';
                return FALSE;
            }
            // Se mes maior que niver procura retira meses se nao tira ano inteiro
            if($mesN <= $mesS){
                $mesT = $mesS - $mesN + 1 ; // calcula quantos meses tem que tirar
                $interval = 'P' . $mesT . 'M';
            }else{                
                $mesT = $mesN - 1 ; // tira 1 mes antes do niver e tira 1 ano
                $interval = 'P1Y' . $mesT . 'M';
            }                
            $data->sub(new \DateInterval($interval));
        }else{ // Periodo Anual tira um ano e um mes.
            $data->sub(new \DateInterval('P1Y1M'));
        }
        // echo $data->format('d/m/Y'), '<br>';
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,i')
                ->from('Livraria\Entity\Orcamento', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'i')
                ->where("o.status = :status AND o.imovel = :imovel AND :data BETWEEN o.inicio AND o.fim")
                ->setParameters(['status' => 'F', 'imovel' => $reno['imovel']['id'], 'data' => $data]);
       
        $seguroAnterior = $query->getQuery()->getArrayResult(); 
        if(empty($seguroAnterior)){
            return FALSE;
        }
        // echo 'data ', $seguroAnterior[0]['inicio']->format('d/m/Y'), ' ate ', $seguroAnterior[0]['fim']->format('d/m/Y');
        
        return $seguroAnterior[0]['id'];
    }


    /**
     * Faz a pesquisa comparativa em os renovados fechados de um periodo com o fechados anteriormente
     * @param array $data Filtros para pesquisa.
     * @return array de entitys
     */
    public function CustoRenovacao($data){
        // Monta a dql para fazer consulta no BD
        $this->where =  ' o.status = :status';
        $this->where .= ' AND o.inicio BETWEEN :inicio AND :fim';
        $this->where .= ' AND o.orcaReno = :orcaReno';
        $this->parameters['status'] = 'F';
        $this->parameters['inicio']  = $data['inicio'];
        $this->parameters['fim']  = $data['fim'];
        $this->parameters['orcaReno'] = 'reno';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,i')
                ->from('Livraria\Entity\Orcamento', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'i')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        // Retorna um array com todo os registros encontrados        
        $principalResul = $query->getQuery()->getArrayResult();
        
        // Segundo consulta é para pegar os os valores do seguro antes da renovação
        $this->where = 'o.id IN(';
        $this->parameters = [];
        foreach ($principalResul as $key => $renovacao) {
            if(is_null($renovacao['fechadoOrigemId']) OR $renovacao['fechadoOrigemId'] == 0){
                if($principalResul[$key]['fechadoOrigemId'] = $this->trySetFechadoOrigem($renovacao)){
                //    echo "<p>registro do fechado anterior Atualizado id ", $renovacao['id'] , ' anterior ', $principalResul[$key]['fechadoOrigemId'], "</p>";
                    $continue = TRUE;
               // }else{
               //     echo "<h2>Sem registro do fechado anterior ", $renovacao['id'] , "</h2>";
                }
            }
            $this->parameters[] = $principalResul[$key]['fechadoOrigemId'];
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
                ->from('Livraria\Entity\Orcamento', 'o')
                ->where($this->where)
                ->setParameters($this->parameters);
        $secundarioResul = $query->getQuery()->getArrayResult();
        
        // Montar Array com a descrição das formas de pagamento
        $formPagto = $this->getEntityManager()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        
        
        //Retonar 2 array com resultado para comparação
        return $this->formataDadosParaExibicao($principalResul,$secundarioResul,$formPagto,$data);
    }
    
    /**
     * Mescla seguros atuais com seguros anterior para comparação e exibição.
     * @param array $principal    seguros do periodo
     * @param array $secundario   seguros anterior ao fechados
     * @param array $formPagto    Formas de pagamento usados atualmente
     * @param array $data         Filtros e dados para calculos
     * @return array com lista para exibição
     */
    public function formataDadosParaExibicao(&$principal,&$secundario,&$formPagto,&$data){
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
            if(is_null($lines['fechadoOrigemId']) OR $lines['fechadoOrigemId'] == 0){
                continue;
            }
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
                //echo "<p>Alert fechado anterior ñ encontrado com id " , $lines['fechadoOrigemId'], ".<p>";
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
    
}

