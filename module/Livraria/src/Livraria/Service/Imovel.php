<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Imovel
 * Faz o CRUD da tabela Imovel no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Imovel extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Imovel";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('locador', 'Livraria\Entity\Locador');
        if(empty($this->data['atividade']))$this->data['atividade'] = '5';
        $this->idToReference('atividade', 'Livraria\Entity\Atividade');
        $this->idToReference('locatario', 'Livraria\Entity\Locatario');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e inserindo novo endereco do imovel
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);
        
        $this->setReferences();

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em imovels
     */
    public function logForNew(){
        parent::logForNew('imovel');
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        if(!empty($this->data['imovel'])){
            $this->data['id'] = $this->data['imovel'];
            $this->data['tel'] = $this->data['imovelTel'];
            $this->data['status'] = $this->data['imovelStatus'];
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e inserindo novo endereco do imovel
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        
        $this->setReferences();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em imovels De/Para
     */
    public function logForEdit(){
        parent::logForEdit('imovel');
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Imovel $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Locador', $ent->getLocador(), $this->data['locador']);
        $this->dePara .= $this->diffAfterBefore('Locatario', $ent->getLocatario(), $this->data['locatario']);
        $this->dePara .= $this->diffAfterBefore('Ref. Imovel', $ent->getRefImovel(), $this->data['refImovel']);
        $this->dePara .= $this->diffAfterBefore('Atividade', $ent->getAtividade(), $this->data['atividade']);
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Bloco', $ent->getBloco(), $this->data['bloco']);
        $this->dePara .= $this->diffAfterBefore('Apartamento', $ent->getApto(), $this->data['apto']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
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
        $filtro = array();
        if(empty($this->data['rua']))
            return array('Rua não pode estar vazia!!');
        if(empty($this->data['numero']))
            return array('Numero não pode estar vazio!!');
            
        $filtro['rua'] = $this->data['rua'];
        $filtro['numero'] = $this->data['numero'];
        
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($this->data['bloco'] == $entity->getBloco()) and ($this->data['apto'] == $entity->getApto())){
                    //Se bloco e apto vazio é um imovel se nao é apto
                    if((empty($this->data['bloco'])) and (empty($this->data['apto']))){
                        $erro[] = 'Já existe um imovel neste endereço  registro:' ;
                        $erro[] = $entity->getId();
                    }else{
                        $erro[] = 'Já existe um apto neste endereço  registro:' ;
                        $erro[] = $entity->getId();
                    }
                }
            }
        }
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
