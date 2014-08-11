<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * Locatario
 * Faz o CRUD da tabela Locatario no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Locatario extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do locatario
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
        $this->entity = "Livraria\Entity\Locatario";
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
        //$this->idToReference('user', 'Livraria\Entity\User');
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
        
        //Pegando o servico endereco e inserindo novo endereco do locatario se houver
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
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em locatarios
     */
    public function logForNew($tabela='locatario'){
        parent::logForNew($tabela);
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        /* @var $ent  \Livraria\Entity\Locatario  */
        $ent = $this->getEntity(); 
        $this->data['idEnde'] = (string)$ent->getEndereco()->getId(); 
        //Pegando o servico endereco e atualizando endereco do locatario
        $serviceEnd = new Endereco($this->em);
        $this->data['endereco'] = $serviceEnd->update($this->data);
        $this->deParaEnd = $serviceEnd->getDePara(); 
        //Verificar se mudou o nome para alterar nos Orcamentos e Fechados.
        $this->getCon()->bg();
        if($ent->getNome() != $this->data['nome']){
            $this->changeNameAtSeguros($ent->getId(), $this->data['nome']);
        }
            
        $this->setReferences();
        
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
        $q1 = 'UPDATE `orcamento` SET `locatario_nome` = ? WHERE `locatario_id` = ? ;';
        $this->getCon()->p($q1);
        $this->getCon()->e([$nome, $id]);
        $q2 = 'UPDATE `fechados` SET `locatario_nome` = ? WHERE `locatario_id` = ? ;';
        $this->getCon()->p($q2);
        $this->getCon()->e([$nome, $id]);        
    }
    
    /**
     * Grava no logs dados da alteção feita em locatarios De/Para
     */
    public function logForEdit($tabela='locatario'){
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Locatario $ent
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
        //Não validar algumas situações especiais
        if (!$this->isValid)
            return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if(!empty($this->data['cpf']))
            $filtro['cpf'] = $this->data['cpf'];
        
        if(!empty($this->data['cnpj']))
            $filtro['cnpj'] = $this->data['cnpj'];
        
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if($entity->getTipo() == 'fisica'){
                    if($entity->getCpf() == $this->data['cpf']){
                        $erro[] = 'Já existe um cpf cadastrado nome de ' . $entity->getNome();
                        $erro[] = $entity->getId();
                    }
                }else{
                    if($entity->getCnpj() == $this->data['cnpj']){
                        $erro[] = 'Já existe um cnpj cadastrado nome de ' . $entity->getNome();
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
