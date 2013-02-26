<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Fechados
 * Faz o CRUD da tabela Fechados no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Fechados extends AbstractService {

    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string
     */
    protected $deParaImovel;

    /**
     * Entity Orcamento
     * @var type
     */
    protected $Orcamento;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Fechados";
        $this->Orcamento = "Livraria\Entity\Orcamento";
    }

    public function validaOrcamento($id){
        //Carregar Entity Orcamento
        $this->Orcamento = $this->em
            ->getRepository($this->Orcamento)
            ->find($id);

        if(!$this->Orcamento){
            return [FALSE,'Registro Orçamento não encontrado'];
        }
        //Outras Validações entra aqui
        if($this->Orcamento->getCodFechado() != 0){
            return [FALSE,'Este Orçamento já foi fechado uma vez!!!!'];
        }


        return TRUE;
    }


    public function fechaOrcamento($id){
        $resul = $this->validaOrcamento($id);
        if($resul[0] === FALSE){
            return $resul;
        }

        //Montar dados para tabela de fechados
        $this->data = $this->Orcamento->toArray();
        $this->data['orcamentoId'] = $this->data['id'];
        unset($this->data['id']);
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "A";
        $this->data['criadoEm'] = new \DateTime('now');;

        //Faz inserção do fechado no BD
        $resul = $this->insert();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->Orcamento->setCodFechado($this->data['id']);
            $this->Orcamento->setStatus('F');
            $this->em->persist($this->Orcamento);
            $this->em->flush();
            $this->registraLogOrcamento();
        }

        return $resul;
    }

    public function registraLogOrcamento(){
        //Criar serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']    = $this->Orcamento;
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'fechaOrcamento';
        $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Fechou o orçamento(' . $orcamento . ') e gerou o fechado de numero ' . $fechado ;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }

    /**
     * Faz referencia para new ou edit dos registros a serem inclusos
     * Converte id de entity em referencia
     * Converte string date em objeto date
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('locador', 'Livraria\Entity\Locador');
        $this->idToReference('locatario', 'Livraria\Entity\Locatario');
        $this->idToReference('atividade', 'Livraria\Entity\Atividade');
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('imovel', 'Livraria\Entity\Imovel');
        $this->idToReference('taxa', 'Livraria\Entity\Taxa');
        $this->idToReference('user', 'Livraria\Entity\User');
        $this->idToReference('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');
    }

    /**
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade
     */
    public function insert(array $data=[]) {
        if(!empty($data))
            $this->data = $data;

        $this->setReferences();

        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }

        if(parent::insert())
            $this->logForNew();

        return array(TRUE,  $this->data['id']);
    }

    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     */
    public function logForNew(){
        //parent::logForNew('fechados');
        //serviço LogFechamento
        $log = new LogFechados($this->em);
        $dataLog['fechados']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'fechar';
        $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Novo seguro fechado n ' . $fechado . ' do orçamento n ' . $orcamento;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
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
        if(parent::update())
            $this->logForEdit();

        return TRUE;
    }

    /**
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        parent::logForEdit('fechados');
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
    }

    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Fechados $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
    }

}
