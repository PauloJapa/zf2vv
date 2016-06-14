<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * Comissao
 * Faz o CRUD da tabela Comissao no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Comissao extends AbstractService {
    
    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Comissao";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
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

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;       
    }
    
    public function logForNew(){
        parent::logForNew('Comissao', 'comissaos');
    }

    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        $this->verificaFimDeVigencia();
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    public function logForEdit(){
        parent::logForEdit('Comissao', 'comissaos');
    }

    public function verificaFimDeVigencia() {
        // Pega registro do banco
        $ent = $this->em->find($this->entity, $this->data['id']);
        // Se houver alteração para menor ou igual na data inicio retorna
        if($ent->getInicio('obj') >= $this->data['inicio'])
            return FALSE;
        
        // Se não for vigente retorna
        if($ent->getFim() != 'vigente')
            return FALSE;
        
        //Reconfirma dados para inserção do novo registro
        $data = $this->data;
        $data['id']     = '' ;
        $data['status'] = 'A';
        $data['fim']    = 'vigente';
        
        $auxService = new Comissao($this->em);
        $auxService->notValidateNew();
        $resul = $auxService->insert($data);
        if($resul !== TRUE)
            var_dump ($data);
        
        //Refazer dados
        $this->data = $ent->toArray();
        
        //Finalizar vigência e refazer dados do registro atual
        $this->data['fim'] = clone $data['inicio'];
        $this->data['fim']->sub(new \DateInterval('P1D')); 
        $this->data['status'] = 'C';
        unset($this->data['criadoEm']);
        
        //Refazer referencia dos registro
        $this->setReferences();
        return TRUE;
        
    }
    
    public function getDiff(\Livraria\Entity\Comissao $ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Data fim', $ent->getFim(), $ent->trataData($this->data['fim']));
        $this->dePara .= $this->diffAfterBefore('Comissão', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        $this->dePara .= $this->diffAfterBefore('Comissão Res.', $ent->floatToStr('comissaoRes'), $this->strToFloat($this->data['comissaoRes']));
        
        $this->dePara .= $this->diffAfterBefore('M. Incendio', $ent->floatToStr('multIncendio'), $this->strToFloat($this->data['multIncendio']));
        $this->dePara .= $this->diffAfterBefore('M. Conteudo', $ent->floatToStr('multConteudo'), $this->strToFloat($this->data['multConteudo']));
        $this->dePara .= $this->diffAfterBefore('M. Aluguel', $ent->floatToStr('multAluguel'), $this->strToFloat($this->data['multAluguel']));
        $this->dePara .= $this->diffAfterBefore('M. Eletrico', $ent->floatToStr('multEletrico'), $this->strToFloat($this->data['multEletrico']));
        $this->dePara .= $this->diffAfterBefore('M. Vendaval', $ent->floatToStr('multVendaval'), $this->strToFloat($this->data['multVendaval']));
        
        $this->dePara .= $this->diffAfterBefore('M. Incendio Res.', $ent->floatToStr('multIncendioRes'), $this->strToFloat($this->data['multIncendioRes']));
        $this->dePara .= $this->diffAfterBefore('M. Conteudo Res.', $ent->floatToStr('multConteudoRes'), $this->strToFloat($this->data['multConteudoRes']));
        $this->dePara .= $this->diffAfterBefore('M. Aluguel Res.' , $ent->floatToStr('multAluguelRes') , $this->strToFloat($this->data['multAluguelRes']));
        $this->dePara .= $this->diffAfterBefore('M. Eletrico Res.', $ent->floatToStr('multEletricoRes'), $this->strToFloat($this->data['multEletricoRes']));
        $this->dePara .= $this->diffAfterBefore('M. Vendaval Res.', $ent->floatToStr('multVendavalRes'), $this->strToFloat($this->data['multVendavalRes']));
        
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        // Casos especias as validações estão dispensadas
        if(!$this->isValid)
            return TRUE;
        
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        $erro   = array();
            
        $filtro['administradora'] = $this->data['administradora']->getId();
        $filtro['status'] = 'A';
        
        $entitys = $repository->findBy($filtro);
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($entity->getFim() == 'vigente') and ($entity->trataData($this->data['fim']) == 'vigente')){
                    $erro[] = "Alerta! Já existe um registro para esta Administradora com data vigente! ID = " . $entity->getId() ;
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
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
