<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

class User extends AbstractService {

    /**
     * String no formato para gravação de alterações feitas no registro endereço
     * Formato campo  nome; valor antes; valor depois;
     * @var string
     */
    protected $deParaEnd;
    
    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\User";
        $this->entityEnd = "Livraria\Entity\Endereco";
    }    
    
    public function setReferences(){
        //Pega uma referencia do registro no doctrine 
        if(empty($this->data['administradora']))
            $this->data['administradora'] = '1';
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
    }
    
    public function insert(array $data) {
        $this->data = $data;
        //Se passord esta em branco é emitido erro
        if (empty($data['password']))
            return ['A senha não pode estar em branco!!!'];
        //Pegando referencias padrao dessa entity
        $this->setReferences();
        //Valida dados comparando aos existente no banco
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        //Pegando o servico endereco e inserindo novo endereco na administradora
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;
    }
    
    public function logForNew($tabela = 'user') {
        parent::logForNew($tabela);
    }

    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        //Valida dados comparando aos existente no banco
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        //Se passord esta em branco é retirado do array para não alterar o BD
        if (empty($data['password']))
            unset($data['password']);
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    public function logForEdit($tabela = 'user') {
        parent::logForEdit($tabela);
    }
    
    public function delete($id) {
        if(parent::delete($id))
            parent::logForDelete ($id, 'user');
        else
            return FALSE;
    }

        /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\User $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
        $this->dePara .= $this->diffAfterBefore('Tipo', $ent->getTipo(), $this->data['tipo']);
        $this->dePara .= $this->diffAfterBefore('Root', $ent->getIsAdmin(), $this->data['isAdmin']);
        $this->dePara .= $this->diffAfterBefore('Administradora', $ent->getAdministradora()->getId(), $this->data['administradora']->getId());
        if(isset($this->data['password']))
            $this->dePara .= $this->diffAfterBefore('Senha ', 'foi', 'Alterada!!');
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid() {
        // Valida se o registro esta conflitando com algum registro existente
        if (empty($this->data['email']))
            return ['Email deve ser definido!!'];

        $filtro = [];
        $erro = [];
        $repository = $this->em->getRepository($this->entity);
        $filtro['email'] = $this->data['email'];

        $entitys = $repository->findBy($filtro);
        foreach ($entitys as $entity) {
            if ($this->data['id'] == $entity->getId()) {
                continue;  //Não valida o mesmo registro do BD
            }
            $erro[] = 'Já existe esse email cadastrado ' . $entity->getEmail() . "!!!";
        }

        return (!empty($erro)) ? $erro : TRUE;
    }
}
