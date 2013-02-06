<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Taxa
 * Faz o CRUD da tabela Taxa no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Taxa extends AbstractService {
    
    

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Taxa";
    }
    
    /**
     * Faz as conversões de id para entity para o doctrine valida
     * Abstração das actions new e edit
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('classe', 'Livraria\Entity\Classe');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();        

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em taxas
     */
    public function logForNew(){
        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = 'taxa';
        $dataLog['controller'] = 'taxas';
        $dataLog['action']     = 'new';
        $dataLog['dePara']     = 'Inseriu um novo registro';
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
    }


    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em taxas De/Para
     */
    public function logForEdit(){
        if(empty($this->dePara)) return ;
        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = 'taxa';
        $dataLog['controller'] = 'taxas';
        $dataLog['action']     = 'edit';
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
    }
    
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora()->getId(), $this->data['seguradora']->getId()); 
        $this->dePara .= $this->diffAfterBefore('Classe', $ent->getClasse()->getId(), $this->data['classe']->getId());
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $fim = $this->data['fim']->format('d/m/Y');
        if('30/11/-0001' == $fim)
            $fim = 'vigente';
        $this->dePara .= $this->diffAfterBefore('Data Fim', $ent->getFim(), $fim);
        $this->dePara .= $this->diffAfterBefore('Incêndio', $ent->floatToStr('incendio'), $this->strToFloat($this->data['incendio']));
        $this->dePara .= $this->diffAfterBefore('Incêndio Conteúdo', $ent->floatToStr('incendioConteudo'), $this->strToFloat($this->data['incendioConteudo']));
        $this->dePara .= $this->diffAfterBefore('Aluguel', $ent->floatToStr('aluguel'), $this->strToFloat($this->data['aluguel']));
        $this->dePara .= $this->diffAfterBefore('Eletrico', $ent->floatToStr('eletrico'), $this->strToFloat($this->data['eletrico']));
        $this->dePara .= $this->diffAfterBefore('Desastres', $ent->floatToStr('desastres'), $this->strToFloat($this->data['desastres']));
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $entitys = $repository->findBy(array('seguradora' => $this->data['seguradora'], 
                                             'classe' => $this->data['classe'],
                                             'status' => 'A'
                                            )
                                      );
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = null ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($entity->getFim() == 'vigente') and ($this->data['fim']->format('d/m/Y') == '30/11/-0001')){
                    $erro[] = "Alerta! Já existe um taxa para esta seguradora e classe vigente! ID = " . $entity->getId() ;
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
        }
        if(($diferenca > 3) and ($this->data['fim']->format('d/m/Y') == '30/11/-0001') and ($diferenca != 3650)){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima taxa valida! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
