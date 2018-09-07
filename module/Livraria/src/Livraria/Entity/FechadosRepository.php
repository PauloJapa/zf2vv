<?php

namespace Livraria\Entity;

/**
 * FechadosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class FechadosRepository extends AbstractRepository {

    /**
     * Colunas selecionadas para o relatorio
     * @var string 
     */
    protected $colunas;
    
    /**
     * clasula where da pesquisa
     * @var string 
     */
    protected $where;
    /**
     * clasula where da pesquisa
     * @var array 
     */
    protected $parameters;
    
    public function findFechados($data){        
        $this->parameters = $data;            
        $this->where = 'o.inicio BETWEEN :inicio AND :fim AND o.status != :status';
        $this->parameters['status'] = 'C';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
        }  else {
            unset($this->parameters['administradora']);
        }
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('count(o.id) as qtd, sum(o.premioTotal) as total, ad.id, ad.nome')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->addGroupBy('o.administradora')
                ->orderBy('ad.nome'); 
        
        // Retorna um array com todo os registros encontrados  
        return $query->getQuery()->getArrayResult();
    }
    
    /**
     * Executa a query que é a mesma para Mensal, Anual e Imoveis Desocupados do mapa de renovação.
     * @return array
     */
    public function executaQuery1($orderBy='', $groupBy='', $tabela='Fechados'){
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,im')
                ->from('Livraria\Entity\\' . $tabela, 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'im')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        if(!empty($orderBy)){
            $query->orderBy($orderBy);
        }
        
        if(!empty($groupBy)){
            $query->groupBy($groupBy);
        }
        
        // Retorna um array com todo os registros encontrados        
        return $query->getQuery()->getArrayResult();
    }
    
    /**
     * Executa a query que é a mesma para Mensal, Anual e Imoveis Desocupados do mapa de renovação.
     * @return array
     */
    public function executaQuery2($orderBy='', $groupBy=''){
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,s')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.seguradora', 's')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        if(!empty($orderBy)){
            $query->orderBy($orderBy);
        }
        
        if(!empty($groupBy)){
            $query->groupBy($groupBy);
        }
        
        // Retorna um array com todo os registros encontrados        
        return $query->getQuery()->getArrayResult();
    }
    
    /**
     * Executa a query que é a mesma para Mensal, Anual e Imoveis Desocupados do mapa de renovação.
     * @return array
     */
    public function executaQuery3($orderBy='', $groupBy=''){
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,s,at,lct,imo,lcd,ende,ba,ci,es')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.seguradora', 's')
                ->join('o.atividade', 'at')
                ->join('o.locatario', 'lct')
                ->join('o.locador', 'lcd')
                ->join('o.imovel', 'imo')
                ->join('imo.endereco', 'ende')
                ->join('ende.bairro', 'ba')
                ->join('ende.cidade', 'ci')
                ->join('ende.estado', 'es')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        if(!empty($orderBy)){
            if(!is_array($orderBy)){
                $query->orderBy($orderBy);
            }else{
                foreach ($orderBy as $value) {
                    $query->addOrderBy($value);                    
                }
            }
        }
        
        if(!empty($groupBy)){
            $query->groupBy($groupBy);
        }
        
        // Retorna um array com todo os registros encontrados        
        return $query->getQuery()->getArrayResult();
    }
    
    /**
     * Faz consulta mensal ou anual ou ambas juntando em um unico array reordenado 
     * @param array $data
     * @return array
     */
    public function getMapaRenovacao($data){
         if(!$data['mensal'] OR !$data['anual']){
             if($data['mensal']){
                 return $this->getMapaRenovacaoMensal($data);
             }else{
                 return $this->getMapaRenovacaoAnual($data);
             }
         }
         //Junta os resultados de mensal e anual e um unico array
        $merge = array_merge($this->getMapaRenovacaoMensal($data), $this->getMapaRenovacaoAnual($data));
        $lista = [];
        foreach ($merge as $key => $value) {
            $lista[$key] = $value['administradora']['id'];
        }
        array_multisort($lista, SORT_ASC, SORT_NUMERIC, $merge);
        return $merge;
    }

    /**
     * Faz a consulta no BD procurando registro com base no fim da vigencia e anual
     * @param array $data
     * @return array
     */
    public function getMapaRenovacaoAnual($data) {
        $this->parameters = [];
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.fim >= :inicio AND o.fim <= :fim AND o.validade = :valido AND o.status = :status';
        $this->parameters['inicio']  = $data['inicio'];
        $this->parameters['fim']     = $data['fim'];
        $this->parameters['valido']  = 'anual';
        $this->parameters['status']  = 'A';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }else{
            //Filtrar Adm pelo parametro
            $admBlock = $this->getEntityManager()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('nao_gera_renovacao');
            foreach ($admBlock as $key => $value) {
                if(empty($key)){
                    continue;
                }
                $this->where .= '  AND o.administradora <> :admCod' . $key;  
                $this->parameters['admCod' . $key]  = $key;                
            }
        }
//        echo '<pre>';
//        var_dump($this->where);
//        var_dump($this->parameters);
//        die;
        // Retorna um array com todo os registros encontrados        
        return $this->executaQueryMapaRenovacao();
    }

    /**
     * Faz a consulta no BD procurando registro com base no mes de aniversario e do tipo mensal
     * 27/10/2014 ajuste para pegar o periodo pelo campo inicio e não pelo fim pois no fim do seguro é decrementado 1 dia
     * @param array $data
     * @return array
     */
    public function getMapaRenovacaoMensal($data) {
        $this->parameters = [];
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio BETWEEN :inicio AND :fim AND o.validade = :valido AND o.mesNiver = :niver AND (o.status = :status OR o.status = :status2) AND o.status != :status3';
        $this->where = 'o.inicio BETWEEN :inicio AND :fim AND o.validade = :valido AND o.mesNiver = :niver ';
        $this->parameters['valido']  = 'mensal';
        $this->parameters['niver']   = $data['niver'];
        $this->parameters['inicio']  = $data['inicioMensal'];
        $this->parameters['fim']     = $data['fimMensal'];
//        $this->parameters['status']  = 'A';     // SEGURO ATIVO
//        $this->parameters['status2']  = 'R';    // SEGURO RENOVADO
//        $this->parameters['status3']  = 'AR';   // DESPREZA SEGURO MENSAL ATUALIZAÇAO ANUAL DE VALOR
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }else{
            //Filtrar Adm pelo parametro
            $admBlock = $this->getEntityManager()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('nao_gera_renovacao');
            foreach ($admBlock as $key => $value) {
                if(empty($key)){
                    continue;
                }
                $this->where .= '  AND o.administradora <> :admCod' . $key;  
                $this->parameters['admCod' . $key]  = $key;                
            }
        }
        
        
        
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora');
    }
    
    /**
     * Executa a query que é a mesma para Mensal, Anual do mapa de renovação.
     * @return array
     */
    public function executaQueryMapaRenovacao(){
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora');
    }
    
    /**
     * Fazer pesquisa no BD com os Filtros passados.
     * @param array $data
     * @return array
     */
    public function getImoveisDesocupados($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.fim >= :inicio AND o.fim <= :fim AND (o.atividade = :des1 OR o.atividade = :des2 OR o.atividade = :des3)';
        $this->parameters['inicio']  = $data['inicio'];
        $this->parameters['fim']     = $data['fim'];
        $this->parameters['des1']    = 312;
        $this->parameters['des2']    =  86;
        $this->parameters['des3']    =  89;
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQueryImoveisDesocupados();
    }
    
    /**
     * Executa a query para pegar imoveis desocupados.
     * @return array
     */
    public function executaQueryImoveisDesocupados(){
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora','o.imovel');
    }

    /**
     * Gerar listagem de seguros fechados no periodo passado
     * @param array $data
     * @return array
     */
    public function getListaFechaSeguro($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status <> :status';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'C';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora');        
    }
    
    /**
     * Gera um lista de seguros fechados para confirmação na ADM
     * @param array $data
     * @return array
     */
    public function getListaEmail($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'A';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora');         
    }
    
    /**
     * Gera um lista de fechados para verificar comissão paga
     * @param array $data
     * @return array
     */
    public function getComissao($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status AND o.seguradora = :seguradora';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'A';
        $this->parameters['seguradora'] = $data['seguradora'];
        if(!empty($data['comissao'])){            
            $this->where = ' AND o.comissao = :comissao';
            $this->parameters['comissao']   = $data['comissao'];
        }
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        if(!empty($data['validade'])){
            $this->where .= ' AND o.validade = :validade';
            $this->parameters['validade']    = $data['validade'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery2('o.administradora');         
    }
    
    /**
     * Gera um lista de seguros fechados para exportação
     * @param array $data
     * @return array
     */
    public function getListaExporta($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status <> :status AND o.gerado = :gerado';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'C';
        $this->parameters['gerado'] = 'N';
        $order = ['o.administradora'];
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];  
            /* @var $adm \Livraria\Entity\Administradora */
            $adm = $this->getEntityManager()->find("Livraria\Entity\Administradora", $data['administradora']);
            if($adm->getExptRefOrder()){
                $order[] = 'o.locatarioNome';
            }
        }
        if(!empty($data['seguradora'])){
            $this->where .= ' AND o.seguradora = :seguradora';
            $this->parameters['seguradora']    = $data['seguradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery3($order);         
    }
    
    /**
     * Gera um lista de seguros fechados novos para geração de cartão
     * @param array $data
     * @return array
     */
    public function getListaCartao($data){
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status <> :status AND o.fechadoOrigemId IS NULL';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'C';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery3('o.administradora');         
    }
    
    public function setGerado($id=false, $campo='gerado', $value='S'){
        if(!$id)
            return FALSE;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->update('Livraria\Entity\Fechados', 'o')
                ->set('o.' . $campo, ':S')
                ->where('o.id = :id')
                ->setParameter('S',  $value)
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
        
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
        ->update('Livraria\Entity\Fechados', 'o')
        ->set('o.refImovel', ':ref')
        ->where('o.imovel = :id')
        ->setParameter('ref', $setRefImovel)
        ->setParameter('id', $id)
        ->getQuery()
        ->execute();
        
        return TRUE;
        
    }
    
    public function findListaFechados($filtros=[],$operadores=[]){
        $where = 'l.id IS NOT NULL';
        $parameters = [];
        
        if(empty($filtros)){
            $where .= ' AND l.status = :status';
            $parameters['status'] = 'A';
            $mes = date('m');
            $filtros['dataI'] = '01/' . $mes . '/' . date('Y');
            $filtros['dataF'] = '31/' . $mes . '/' . date('Y');
        }   
        
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
                    // Em fechados para diferenciar renovacao de orcamento verificar o campo orcamentoId e renovacaoId
                    if($filtro == 'A'){                         
                        $where .= ' AND l.orcamentoId != :orcamento AND l.status = :status';
                        $parameters['orcamento'] = '0';
                        $parameters['status'] = 'A';
                    }
                    if($filtro == 'R'){
                        $where .= ' AND l.renovacaoId != :renovacao AND l.status = :status';
                        $parameters['renovacao'] = '0';
                        $parameters['status'] = 'A';
                    }
                    if($filtro == 'T'){
                        $where .= ' AND l.status NOT LIKE :status';
                        $parameters['status'] = 'C';
                    }
                    if($filtro != 'A' AND $filtro != 'R' AND $filtro != 'T'){
                        $op = (isset($operadores[$key])) ? $operadores[$key] : 'LIKE';
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
//        echo '<pre>';        var_dump($where);var_dump($parameters); die;
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('l','u','i','ld','lc','ad','at')
                ->from('Livraria\Entity\Fechados', 'l')
                ->join('l.user', 'u')
                ->join('l.imovel', 'i')
                ->join('l.locador', 'ld')
                ->join('l.locatario', 'lc')
                ->join('l.administradora', 'ad')
                ->join('l.atividade', 'at')
                ->where($where)
                ->setParameters($parameters);
        
        if(isset($parameters['administradora'])){
            $query->orderBy('l.inicio', 'DESC');
        }
                
        return $query;
    }
    
    
    public function getFaturar($data){
        if (empty($data['inicio']))
            return [];
        // Busca todos a faturar a vista
        $this->where  = '';
        $this->getWhereFatura($data['inicio'], $data['fim'], '01', $data['administradora']);
        // Busca todos a faturar em 2 vezes
        if(isset($data['inicio2'])){
            $this->getWhereFatura($data['inicio2'], $data['fim2'], '02', $data['administradora']);
        }
        // Busca todos a faturar em 3 vezes
        if(isset($data['inicio3'])){
            $this->getWhereFatura($data['inicio3'], $data['fim3'], '03', $data['administradora']);
        }
        // Busca todos a faturar em 4 vezes
        if(isset($data['inicio4'])){
            $this->getWhereFatura($data['inicio4'], $data['fim4'], '04', $data['administradora']);
        }
        // Busca todos a faturar em 5 vezes
        if(isset($data['inicio5'])){
            $this->getWhereFatura($data['inicio5'], $data['fim5'], '05', $data['administradora']);
        }
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,i,ld,lc')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'i')
                ->join('o.locador', 'ld')
                ->join('o.locatario', 'lc')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->addOrderBy('o.administradora')
                ->addOrderBy('o.ocupacao', 'DESC')
                ->addOrderBy('o.validade')
                ->addOrderBy('o.formaPagto', 'ASC')
                ->addOrderBy('o.comissao')
                ->addOrderBy('o.inicio', 'DESC')
                ;
        return $query->getQuery()->getResult();
        
    }
    
    public function getWhereFatura($ini,$fim,$pag,$adm=''){
        if(empty($this->where)){
            $this->where  = '(o.inicio >= :inicio AND o.inicio <= :fim AND o.formaPagto <= :formaPagto';
            $this->colunas = ''; 
        }else{
            switch ($this->colunas) {
                case '':
                    $this->colunas = '2';
                    break;
                case '2':
                    $this->colunas = '3';
                    break;
                case '3':
                    $this->colunas = '4';
                    break;                
                default:
                    $this->colunas = '';
                    break;
            }
            $this->where  .= ' OR (o.inicio >= :inicio'.$this->colunas.' AND o.inicio <= :fim'.$this->colunas.' AND o.formaPagto = :formaPagto'.$this->colunas;
        }
        $this->where .= ' AND o.status <> :status'.$this->colunas ;
        $this->parameters['inicio'.$this->colunas]  = $this->dateToObject($ini);
        $this->parameters['fim'.$this->colunas]     = $this->dateToObject($fim);
        $this->parameters['formaPagto'.$this->colunas]  = $pag;
        $this->parameters['status'.$this->colunas]  = 'C';
        if(!empty($adm)){
            $this->where .= ' AND o.administradora = :administradora'.$this->colunas;
            $this->parameters['administradora'.$this->colunas]    = $adm;            
        }
        $this->where .= ')';
        
    }
    
    
    /**
     * Registra o id do fechado de Orçamento
     * @param type $id
     * @param type $idRenovacao
     * @param type $status
     * @return boolean
     */    
    public function setSeguroRenovado($id=false, $idRenovacao='', $status=''){
        if((!$id)OR($idRenovacao == '')OR($status == '')){
            return FALSE;
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->update('Livraria\Entity\Fechados', 'o')
                ->set('o.renovacaoId', ':idRenovacao')
                ->set('o.status', ':status')
                ->where('o.id = :id')
                ->setParameter('idRenovacao', $idRenovacao)
                ->setParameter('status', $status)
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
        
        return $query;
    }
}