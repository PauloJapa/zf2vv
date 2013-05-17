<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

class Administradora extends AbstractService {
    
    /**
     * String no formato para gravação de alterações feitas no registro endereço
     * Formato campo  nome; valor antes; valor depois;
     * @var string
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Administradora";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro no doctrine 
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        if(!isset($this->data['validade']))$this->data['validade'] = '';
    }
    
    public function insert(array $data) {
        //Verifica esse codigo já existe.
        $exist = $this->em->find($this->entity, $data['id']);
        if($exist)
            return ['Erro já existe uma Administradora com esse codigo!!!'];
        
        $this->data = $data;
        //Pegando referencias padrao dessa entity
        $this->setReferences();
        //Pegando o servico endereco e inserindo novo endereco na administradora
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;
    }
    
    public function logForNew($tabela = 'administradora') {
        parent::logForNew($tabela);
    }

    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    public function logForEdit($tabela = 'administradora') {
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Administradora $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Apelido', $ent->getApelido(), $this->data['apelido']);
        $this->dePara .= $this->diffAfterBefore('CNPJ', $ent->getCnpj(), $ent->formatarCPF_CNPJ($this->data['cnpj']));
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Forma de Pag', $ent->getFormaPagto(), $this->data['formaPagto']);
        $this->dePara .= $this->diffAfterBefore('Validade', $ent->getValidade(), $this->data['validade']);
        $this->dePara .= $this->diffAfterBefore('Tipo de Cobertura', $ent->getTipoCobertura(), $this->data['tipoCobertura']);
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora()->getId(), $this->data['seguradora']->getId());
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
    }
}
