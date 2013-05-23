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
    public function executaQuery1(){
        // Monta a dql para fazer consulta no BD
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('o,ad,at,im')
                ->from('Livraria\Entity\Fechados', 'o')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.imovel', 'im')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('o.administradora')
                ->groupBy('o.imovel'); 
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
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.fim >= :inicio AND o.fim <= :fim AND o.validade = :valido';
        $this->parameters['inicio']  = $data['inicio'];
        $this->parameters['fim']     = $data['fim'];
        $this->parameters['valido']  = 'anual';
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
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.fim >= :inicio AND o.fim <= :fim AND o.validade = :valido AND o.mesNiver = :niver';
        $this->parameters['inicio']  = $data['inicioMensal'];
        $this->parameters['fim']     = $data['fimMensal'];
        $this->parameters['valido']  = 'mensal';
        $this->parameters['niver']   = $data['mes'];
        if(!empty($data['administradora'])){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        // Retorna um array com todo os registros encontrados        
        return $this->executaQueryMapaRenovacao();
    }
    
    /**
     * Executa a query que é a mesma para Mensal, Anual do mapa de renovação.
     * @return array
     */
    public function executaQueryMapaRenovacao(){
        // Retorna um array com todo os registros encontrados        
        return $this->executaQuery1();
    }
    
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
        return $this->executaQuery1();
    }
}