<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * TaxaAjuste
 * Faz o CRUD da tabela TaxaAjuste no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaAjuste extends AbstractService {
    
    

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\TaxaAjuste";
    }
    
    /**
     * Faz as conversões de id para entity para o doctrine valida
     * Abstração das actions new e edit
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        if(isset($this->data['classe']) AND !empty($this->data['classe'])){
            $this->idToReference('classe', 'Livraria\Entity\Classe');            
        }else{
            unset($this->data['classe']);
        }
        if(isset($this->data['administradora']) AND !empty($this->data['administradora'])){
            $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        }else{
            $this->data['administradora'] = '1';
            $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        }
        
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->setReferences();  
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }      
       
        $resul = false;
        switch($this->data['ocupacao']){
            case '04': //apto
            case '02': // casa
                $resul = parent::insert();
                break;
            case '01': // comercio
            case '03': // industria
                $resul = $this->saveLoteDeTaxas();
                break;
            default:    
                echo '<pre>Error ocupacao ' ,  var_dump($this->data['ocupacao']); 
                die;
        } 

        if($resul)
            $this->logForNew();
        
        return TRUE;
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em taxaAjustes
     */
    public function logForNew(){
        parent::logForNew('taxaAjuste');
    }


    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $resul = false;
        switch($this->data['ocupacao']){
            case '04': //apto
            case '02': // casa
                $resul = parent::update();
                break;
            case '01': // comercio
            case '03': // industria
                $resul = $this->saveLoteDeTaxas();
                break;
            default:    
                echo '<pre>Error ocupacao ' ,  var_dump($this->data['ocupacao']); 
                die;
        } 

        if($resul)
            $this->logForEdit();
        
        return TRUE;
    }
  
    /** 
     * Esclui o registro ou marca como cancelado se existir os campo status
     * @param $id do registro
     * @return boolean
     */   
    public function delete($id) {
        $this->entityReal = $this->em->getReference($this->entity, $id);
        if($this->entityReal) {
            $this->em->remove($this->entityReal);
            if ($this->getFlush())
                $this->em->flush();
            return TRUE ;
        }
        return FALSE ;
    }
    
    public function saveLoteDeTaxas() {
        $inputs = ['contEle'             
                   ,'conteudo'                
                   ,'eletrico'               
                   ,'semContEle'                
                   ,'unica'      ];
        foreach ($this->data['idArray'] as $key => $id){
            $this->data['id']     = $id;
            $this->data['classe'] = $key;
            $inserir              = FALSE;
            foreach ($inputs as $input){
                $this->data[$input] = $this->data[$input . 'Array'][$key];
                if(!empty($this->data[$input])){
                    $inserir = true;
                }
            }
            $this->idToReference('classe', 'Livraria\Entity\Classe');  
            if(empty($id)){
                if($inserir){
                    parent::insert();                               
                }
            }else{
                $this->entityReal = null;
                parent::update();                               
            }    
        }
        return true;
    }
    
    /**
     * Grava no logs dados da alteção feita em taxaAjustes De/Para
     */
    public function logForEdit(){
        parent::logForEdit('taxaAjuste');
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\TaxaAjuste $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Seguradora'            , $ent->getSeguradora()->getId()          , $this->data['seguradora']->getId()               ); 
        if(!is_null($ent->getClasse())){
            $this->dePara .= $this->diffAfterBefore('Classe'            , $ent->getClasse()->getId()              , $this->data['classe']->getId()                   );
        }
        if(!is_null($ent->getAdministradora())){
            $this->dePara .= $this->diffAfterBefore('Administradora'    , $ent->getAdministradora()->getId()      , $this->data['administradora']->getId()           );
        }
        $this->dePara .= $this->diffAfterBefore('Data inicio'           , $ent->getInicio()                       , $this->data['inicio']->format('d/m/Y')           );
        $fim = $this->data['fim']->format('d/m/Y');
        if ('01/01/0001' == $fim) {
            $fim = 'vigente';
        }
        $this->dePara .= $this->diffAfterBefore('Data Fim'              , $ent->getFim()                         , $fim                                              );
        $this->dePara .= $this->diffAfterBefore('Status'                , $ent->getStatus()                      , $this->data['status']                             );
        $this->dePara .= $this->diffAfterBefore('Validade'              , $ent->getValidade()                    , $this->data['validade']                           );
        $this->dePara .= $this->diffAfterBefore('Ocupação'              , $ent->getOcupacao()                    , $this->data['ocupacao']                           );
        
        $this->dePara .= $this->diffAfterBefore('contEle'               , $ent->floatToStr('contEle'    )        , $this->strToFloat($this->data['contEle'    ])     );
        $this->dePara .= $this->diffAfterBefore('conteudo'              , $ent->floatToStr('conteudo'   )        , $this->strToFloat($this->data['conteudo'   ])     );
        $this->dePara .= $this->diffAfterBefore('eletrico'              , $ent->floatToStr('eletrico'   )        , $this->strToFloat($this->data['eletrico'   ])     );
        $this->dePara .= $this->diffAfterBefore('semContEle'            , $ent->floatToStr('semContEle' )        , $this->strToFloat($this->data['semContEle' ])     );
        $this->dePara .= $this->diffAfterBefore('comEletrico'           , $ent->floatToStr('comEletrico')        , $this->strToFloat($this->data['comEletrico'])     );
        $this->dePara .= $this->diffAfterBefore('semEletrico'           , $ent->floatToStr('semEletrico')        , $this->strToFloat($this->data['semEletrico'])     );
        $this->dePara .= $this->diffAfterBefore('unica'                 , $ent->floatToStr('unica'      )        , $this->strToFloat($this->data['unica'      ])     );
        $this->dePara .= $this->diffAfterBefore('contEle'               , $ent->floatToStr('contEle'    )        , $this->strToFloat($this->data['contEle'    ])     );
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filters = array('seguradora'     => $this->data['seguradora'], 
                         'validade'       => $this->data['validade'],
                         'ocupacao'       => $this->data['ocupacao'],
                         'status'         => 'A'
        );
        if(!empty($this->data['administradora'])){
            $filters['administradora'] = $this->data['administradora'];
        }
        if(!empty($this->data['classe'])){
            $filters['classe'] = $this->data['classe'];
        }
        $entitys = $repository->findBy($filters);
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = null ;
        foreach ($entitys as $entity) {
            if($this->data['id'] == $entity->getId()){
                continue;
            }
            if(($entity->getFim() == 'vigente') and ($this->data['fim']->format('d/m/Y') == '01/01/0001')){
                $erro[] = "Alerta! Já existe um taxaAjuste para esta seguradora e classe vigente! ID = " . $entity->getId() ;
            }
            $fim = $entity->getFim('obj');
            if($fim >= $this->data['inicio']){
                $erro[] = "Alerta! Data de inicio conflita com data de registro existente! ID = " . $entity->getId() ;
                $erro[] = "Data de inicio não pode ser menor ou igual a data final de vigencia<br>";
            }
            $diff = $fim->diff($this->data['inicio']);
            if($diff->days < $diferenca){
                $diferenca = $diff->days ;
            }
        }
        if(($diferenca > 3) and ($this->data['fim']->format('d/m/Y') == '30/11/-0001') and ($diferenca != 3650)){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima taxaAjuste valida! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
