<?php

namespace Livraria\Entity;

/**
 * FechadosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class FechadosRepository extends AbstractRepository {

    public function findFechados($data){
        
        if (empty($data['inicio']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim';
        $this->parameters['inicio']  = $this->dateToObject($data['inicio']);
        if (!empty($data['fim'])){
            $this->parameters['fim'] = $this->dateToObject($data['fim']);
        }else{
            $this->parameters['fim'] = clone $this->parameters['inicio'];
            $this->parameters['fim']->add(new \DateInterval('P1M')); 
            $this->parameters['fim']->sub(new \DateInterval('P1D')); 
        }
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
            
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('o.administradora'); 
        
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
            $query->orderBy($orderBy);
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
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQueryMapaRenovacao();
    }

    /**
     * Faz a consulta no BD procurando registro com base no mes de aniversario e do tipo mensal
     * @param array $data
     * @return array
     */
    public function getMapaRenovacaoMensal($data) {
        $this->parameters = [];
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.validade = :valido AND o.mesNiver = :niver AND o.status = :status';
        $this->parameters['valido']  = 'mensal';
        $this->parameters['niver']   = $data['mes'];
        $this->parameters['status']  = 'F';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1('o.administradora','','Orcamento');
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
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
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
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status AND o.comissao = :comissao AND o.seguradora = :seguradora';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'A';
        $this->parameters['comissao']   = $data['comissao'];
        $this->parameters['seguradora'] = $data['seguradora'];
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
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status AND o.gerado = :gerado';
        $this->parameters['inicio'] = $data['inicio'];
        $this->parameters['fim']    = $data['fim'];
        $this->parameters['status'] = 'A';
        $this->parameters['gerado'] = 'N';
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        if(!empty($data['seguradora'])){
            $this->where .= ' AND o.seguradora = :seguradora';
            $this->parameters['seguradora']    = $data['seguradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery3('o.administradora');         
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
    
    public function setGerado($id=false){
        if(!$id)
            return FALSE;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->update('Livraria\Entity\Fechados', 'o')
                ->set('o.gerado', ':S')
                ->where('o.id = :id')
                ->setParameter('S', 'S')
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
            $query->orderBy('l.criadoEm', 'DESC');
        }
        
        return $query;
    }
}