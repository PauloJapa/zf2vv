<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Locador
 * Faz o CRUD da tabela Locador no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Locador extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do locador
     * @var string 
     */
    protected $deParaEnd;
    
    /**
     * Conexão com o direta com o BD
     * @var Mysql
     */
    protected $con;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Locador";
    }
    
    /**
     * Cria ou pega um instancia do PDO do Mysql
     * @return Mysql
     */
    public function getCon() {
        if($this->con){
            return $this->con;
        }
        $this->con = new Mysql();
        return $this->con;
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
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
        
        $this->setReferences();
        
        //Pegando o servico endereco e inserindo novo endereco do locador se houver
        if(isset($this->data['rua']) AND !empty($this->data['rua'])){
            $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);            
        }else{
        // Setar endereço vazio para 1 pois não tem endereço
            $this->data['endereco'] = '1';
            $this->idToReference('endereco', 'Livraria\Entity\Endereco');            
        }

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em locadors
     */
    public function logForNew($tabela='locador'){
        parent::logForNew($tabela);
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
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEnd = new Endereco($this->em);
        /* @var $ent  \Livraria\Entity\Locador  */
        $ent = $this->getEntity(); 
        $this->data['idEnde'] = $ent->getEndereco()->getId();
        $this->data['endereco'] = $serviceEnd->update($this->data);
        $this->deParaEnd = $serviceEnd->getDePara();
        //Verificar se mudou o nome para alterar nos Orcamentos e Fechados.
        $this->getCon()->bg();
        if($ent->getNome() != $this->data['nome']){
            $this->changeNameAtSeguros($ent->getId(), $this->data['nome']);
        }
        
        if (parent::update()) {
            $this->logForEdit();
            $this->getCon()->co();
        }

        return TRUE;
    }

    /**
     * Altera o nome em todos os Orçamento e Fechados desse Locador
     * @param int $id
     * @param string $nome
     * @return void
     */
    public function changeNameAtSeguros($id, $nome) {
        if(empty($nome)){
            return;
        }
        $q1 = 'UPDATE `orcamento` SET `locador_nome` = ? WHERE `locador_id` = ? ;';
        $this->getCon()->p($q1);
        $this->getCon()->e([$nome, $id]);
        $q2 = 'UPDATE `fechados` SET `locador_nome` = ? WHERE `locador_id` = ? ;';
        $this->getCon()->p($q2);
        $this->getCon()->e([$nome, $id]);        
    }
    
    /**
     * Grava no logs dados da alteção feita em locadors De/Para
     */
    public function logForEdit($tabela='locador'){
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Locador $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Tipo', $ent->getTipo(), $this->data['tipo']);
        $this->dePara .= $this->diffAfterBefore('CPF', $ent->getCpf(), $ent->formatarCPF_CNPJ($this->data['cpf']));
        $this->dePara .= $this->diffAfterBefore('CNPJ', $ent->getCnpj(), $ent->formatarCPF_CNPJ($this->data['cnpj']));
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
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
        if(!$this->isValid)   
            return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if(!empty($this->data['cpf']))
            $filtro['cpf'] = $this->data['cpf'];
        
        if(!empty($this->data['cnpj']))
            $filtro['cnpj'] = $this->data['cnpj'];
        
        if(!empty($this->data['administradora']))
            $filtro['administradora'] = $this->data['administradora'];
        
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if($entity->getTipo() == 'fisica'){
                    if(($entity->getCpf() == $this->data['cpf'])
                    and ($entity->getAdministradora() == $this->data['administradora'])){
                        $erro[] = 'Já existe esse cpf de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
                        $erro[] = $entity->getId();
                    }
                }else{
                    if(($entity->getCnpj() == $this->data['cnpj'])
                    and ($entity->getAdministradora() == $this->data['administradora'])){
                        $erro[] = 'Já existe esse cnpj de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
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
