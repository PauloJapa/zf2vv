<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * TaxaAjusteRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaAjusteRepository extends EntityRepository {
    
    /**
     *
     * @var \Livraria\Entity\TaxaAjuste 
     */
    protected $entity;
    
    protected $ocupacao;

    /**
     * Busca uma taxaAjuste para atividade e seguradora na data $date.
     * 
     * @param type $seguradora
     * @param type $administradora
     * @param type $date
     * @param string $validade      mensal|anual
     * @param type $classe
     * @param type $ocupacao
     * @return boolean|Entity \Livraria\Entity\TaxaAjuste
     */
    public function getTaxaAjusteFor($seguradora, $administradora,  \DateTime $inicio, $validade, $atividade, $ocupacao){
        /* @var $atividade     \Livraria\Entity\Atividade */
        /* @var $classe        \Livraria\Entity\Classe */
        /* @var $taxaAjuste    \Livraria\Entity\TaxaAjuste */
        // trata os filtros
        $idSeg = is_object($seguradora)     ? $seguradora->getId()     : $seguradora;
        $idAdm = is_object($administradora) ? $administradora->getId() : $administradora;
        
        $classe = $atividade->findClasseFor($inicio);
        if(!$classe){
            throw new \Exception('Classe não encontrada para atividade ', $atividade->getDescricao(), ' periodo ' , $inicio->format('d/m/Y'));
        }
        if($ocupacao == '02'){
            if(strpos($classe->getDescricao(), 'APTO') !== FALSE){
                $ocupacao = '04';
            }
        }
        $this->ocupacao = $ocupacao;
        $this->entity   = false;
        // Procura pelo taxa especifica da administradora.
        $filters = ['administradora' => $idAdm, 'seguradora' => $idSeg, 'validade' => $validade, 'ocupacao' => $ocupacao];
        if('01' == $ocupacao OR '03' == $ocupacao){
            $filters['classe'] = $classe->getId();
        }
        
        $taxaAjustes = $this->findBy($filters);
        //procura em ativos
        foreach ($taxaAjustes as $taxaAjuste) {
            if($taxaAjuste->getInicio('obj') <= $inicio AND $taxaAjuste->getStatus() == 'A'){
                $this->entity = $taxaAjuste;
                return $taxaAjuste;
            }
        }
        //procura em inativos
        foreach ($taxaAjustes as $taxaAjuste) {
            if($taxaAjuste->getStatus() == 'A'){
                continue;
            }
            if($taxaAjuste->getInicio('obj') <= $inicio AND $taxaAjuste->getFim('obj') >= $inicio){
                $this->entity = $taxaAjuste;
                return $taxaAjuste;
            }
        }
        // Procura pelo taxa especifica para todas administradoras.
        $filters['administradora'] = 1;
        $taxaAjustes = $this->findBy($filters);
        $taxaAjustes = $this->findBy(['administradora' => '1', 'seguradora' => $idSeg, 'validade' => $validade, 'ocupacao' => $ocupacao]);
        //procura em ativos
        foreach ($taxaAjustes as $taxaAjuste) {
            if($taxaAjuste->getInicio('obj') <= $inicio AND $taxaAjuste->getStatus() == 'A'){
                $this->entity = $taxaAjuste;
                return $taxaAjuste;
            }
        }
        //procura em inativos
        foreach ($taxaAjustes as $taxaAjuste) {
            if($taxaAjuste->getStatus() == 'A'){
                continue;
            }
            if($taxaAjuste->getInicio('obj') <= $inicio AND $taxaAjuste->getFim('obj') >= $inicio){
                $this->entity = $taxaAjuste;
                return $taxaAjuste;
            }
        } 

        throw new \Exception(
            '<pre>'.
            'Not found' . '<br>'.
            'inicio '. var_dump($inicio->format('d-m-Y')). '<br>'.
            'validade '. var_dump($validade). '<br>'.
            'ativ '. var_dump($atividade->toArray()). '<br>'.
            'ocup '. var_dump($ocupacao). '<br>'.
            'SEG '. var_dump($idSeg). '<br>'.
            'ADM '. var_dump($idAdm). '<br>'.
            '</pre>'
        );
    }
    
    /**
     * Extrai do registro taxaAjuste a taxa necessaria baseada nos parametros conteudo e eletrico
     * 
     * @author PauloWatakabe <watakabe05@gmailcom>
     * @param float $txConteudo Procura taxa nos item que tem ou não conteudo diferente de zero
     * @param float $txEletrico Procura taxa nos item que tem ou não eletrico diferente de zero
     * @param \Livraria\Entity\TaxaAjuste | false $entTaxaAjuste
     * @return float | integer
     */
    public function changeEntityForTaxaFloat($txConteudo = 0, $txEletrico = 0, $entTaxaAjuste = FALSE) {
        if($entTaxaAjuste){
            $this->entity = $entTaxaAjuste;
        }
        $taxaAjuste = $this->entity->getUnica();
        
        if($taxaAjuste != 0){
            return $taxaAjuste;
        }     
        switch($this->ocupacao){
            case '04': //apto
                switch (TRUE) {
                    case $txEletrico != 0:
                        $taxaAjuste = $this->entity->getComEletrico();
                        break;
                    case $txEletrico == 0:
                        $taxaAjuste = $this->entity->getSemEletrico();
                        break;
                }
                break;
            case '02': // casa
                $taxaAjuste = $this->entity->getUnica();
                break;
            case '01': // comercio
            case '03': // industria
                switch (TRUE) {
                    case $txConteudo != 0 and $txEletrico != 0:
                        $taxaAjuste = $this->entity->getContEle();
                        break;
                    case $txConteudo != 0 and $txEletrico == 0:
                        $taxaAjuste = $this->entity->getConteudo();
                        break;
                    case $txConteudo == 0 and $txEletrico != 0:
                        $taxaAjuste = $this->entity->getEletrico();
                        break;
                    case $txConteudo == 0 and $txEletrico == 0:
                        $taxaAjuste = $this->entity->getSemContEle();
                        break;
                }
                break;
        }         
        return $taxaAjuste;        
    }
    
    public function getData($id='') {
        if(empty($id)){
            return [];
        }
        $entity = $this->find($id);
        
        switch($entity->getOcupacao()){
            case '04': //apto
            case '02': // casa
                return $entity->toArray();
            case '01': // comercio
            case '03': // industria
                return $this->allExistClassesOf($entity);
            default:    
                return [];
        } 
    }

    public function allExistClassesOf($entity) {
        $data = $entity->toArray();
        
        $filters = ['inicio' => $entity->getInicio('obj'), 'ocupacao' => $entity->getOcupacao(),'validade' => $entity->getValidade()];
        if(!is_null($entity->getAdministradora())){
            $filters['administradora'] = $entity->getAdministradora()->getId(); 
        }
        $entitys = $this->findBy($filters);
        $inputs = ['contEle'             
                   ,'conteudo'                
                   ,'eletrico'               
                   ,'semContEle'                
                   ,'unica'      ]; 
        foreach ($inputs as $input) {
            $data[$input] = '';
        }
        foreach ($entitys as $ent) {
            $data['idArray[' . $ent->getClasse()->getId() . ']'] = $ent->getId();
            foreach ($inputs as $input) {
                $data[$input . 'Array[' . $ent->getClasse()->getId() . ']'] = $ent->floatToStr($input);
            }
        }
        return $data;
    }

}

