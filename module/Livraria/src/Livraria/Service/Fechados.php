<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use LivrariaAdmin\Fpdf\ImprimirSeguro;

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

    /**
     * Entity Renovacao
     * @var type
     */
    protected $Renovacao;

    /**
     * Orçamento ou Renovação !! para Registro no Log
     * @var string
     */
    protected $origem;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Fechados";
        $this->Orcamento = "Livraria\Entity\Orcamento";
        $this->Renovacao = "Livraria\Entity\Renovacao";
    }
    
    public function getPdfSeguro($id){
        //Carregar Entity Fechados
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado o seguro com esse numero!!!'];
        }
        
        $num = 'Fechado/' . $seg->getId() . '/' . $seg->getCodano();
        $pdf = new ImprimirSeguro($num);
        $pdf->setL1($seg->getRefImovel(), $seg->getInicio());
        $pdf->setL2($seg->getAdministradora()->getNome());
        $pdf->setL3($seg->getLocatario(), $seg->getLocatario()->getCpf() . $seg->getLocatario()->getCnpj());
        $pdf->setL4($seg->getLocador(), $seg->getLocador()->getCpf() . $seg->getLocador()->getCnpj());
        //$pdf->setL5($seg->getImovel()->getEnderecoCompleto());
        $pdf->setL6($seg->getAtividade());
        $pdf->setL7($seg->getObservacao());
        $pdf->setL8($seg->floatToStr('valorAluguel'));
        $pdf->setL9($seg->getAdministradora()->getId(), '0');
        $pdf->setL10();
        switch ($seg->getTipoCobertura()) {
            case '01':
                $label = ' (Prédio)';
                $vlr[] = $seg->floatToStr('incendio');
                $vlr[] = $seg->floatToStr('cobIncendio');
                break;
            case '02':
                $label = ' (Conteúdo + prédio)';
                $vlr[] = $seg->floatToStr('conteudo');
                $vlr[] = $seg->floatToStr('cobConteudo');
                break;
            case '03':
                $label = ' (Conteúdo)';
                break;
            default:
                $label = '';
                break;
        }
        $vlr[] = $seg->floatToStr('eletrico');
        $vlr[] = $seg->floatToStr('cobEletrico');
        $vlr[] = $seg->floatToStr('aluguel');
        $vlr[] = $seg->floatToStr('cobAluguel');
        $vlr[] = $seg->floatToStr('vendaval');
        $vlr[] = $seg->floatToStr('cobVendaval');
        $pdf->setL11($vlr, $label);
        $tot = [
            $seg->floatToStr('premio'),
            $seg->floatToStr('premioLiquido'),
            $this->strToFloat($seg->getPremioLiquido() * $seg->getTaxaIof()),
            $seg->floatToStr('premioTotal')
        ];
        $pdf->setL12($tot,  $this->strToFloat($seg->getTaxaIof() * 100));
        $par = [
            $seg->floatToStr('premioTotal'),
            $this->strToFloat($seg->getPremioTotal() / 2),
            $this->strToFloat($seg->getPremioTotal() / 3),
            $this->strToFloat($seg->getPremioTotal() / 12)
        ];
        $pdf->setL13($par, ($seg->getValidade() =='mensal')?true:false);
        $pdf->setL14();
        //$pdf->setObs($obs);
        $pdf->Output();
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
        if($this->Orcamento->getFechadoId() != 0){
            return [FALSE,'Este Orçamento já foi fechado uma vez!!!!'];
        }
        //Verificar se esta ativo
        if($this->Orcamento->getStatus() == 'C'){
            return [FALSE,'Este Orçamento foi cancelado!!!!'];
        }
        
        $this->origem = 'orcamentos';

        return TRUE;
    }


    public function fechaOrcamento($id,$pdf=true){
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
        $this->data['criadoEm'] = new \DateTime('now');

        //Faz inserção do fechado no BD
        $resul = $this->insert();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->Orcamento->setFechadoId($this->data['id']);
            $this->Orcamento->setStatus('F');
            $this->em->persist($this->Orcamento);
            $this->em->flush();
            $this->registraLogOrcamento();
            $this->atualizaImovel();
            if($pdf){
                $this->getPdfSeguro($this->data['id']);
            }
        }

        return $resul;
    }
    
    /**
     * 
     * @param Livraria\Entity\AbstractSeguro $obj
     */
    public function atualizaImovel(){
        $imovel = $this->em->find('Livraria\Entity\Imovel',  $this->data['imovel']);
        if($imovel){
            $dados = $imovel->toArray();
            $dados['fechadoId']  =  $this->data['id'];
            $dados['fechadoAno'] =  $this->data['codano'];
            $dados['vlrAluguel'] =  $this->data['valorAluguel'];
            $dados['fechadoFim'] =  $this->data['fim'];
            $dados['locatario']  =  $this->data['locatario'];
            $dados['locador']    =  $this->data['locador'];
            $servico = new Imovel($this->em);
            $rs = $servico->update($dados);
            if($rs === TRUE)
                return;
            if($this->getIdentidade()->getId() == 2)
                var_dump($rs);
        }
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

    
    public function fechaRenovacao($id,$pdf=true){
        $resul = $this->validaRenovacao($id);
        if($resul[0] === FALSE){
            return $resul;
        }

        //Montar dados para tabela de fechados
        $this->data = $this->Renovacao->toArray();
        $this->data['renovacaoId'] = $this->data['id'];
        unset($this->data['id']);
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "A";
        $this->data['criadoEm'] = new \DateTime('now');

        //Faz inserção do fechado no BD
        $resul = $this->insert();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->Renovacao->setFechadoId($this->data['id']);
            $this->Renovacao->setStatus('F');
            $this->em->persist($this->Renovacao);
            $this->em->flush();
            $this->registraLogRenovacao();
            $this->atualizaImovel();
            if($pdf){
                $this->getPdfSeguro($this->data['id']);
            }
        }

        return $resul;
    }

    public function validaRenovacao($id){
        //Carregar Entity Orcamento
        $this->Renovacao = $this->em
            ->getRepository($this->Renovacao)
            ->find($id);

        if(!$this->Renovacao){
            return [FALSE,'Registro de Renovação não encontrado!!!'];
        }
        //Outras Validações entra aqui
        if($this->Renovacao->getFechadoId() != 0){
            return [FALSE,'Esta Renovação já foi fechado uma vez!!!!'];
        }
        //Verificar se esta ativo
        if($this->Renovacao->getStatus() == 'C'){
            return [FALSE,'Esta Renovação foi cancelada!!!!'];
        }
        
        $this->origem = 'renovacaos';

        return TRUE;
    }

    public function registraLogRenovacao(){
        //Criar serviço logorcamento
        $log = new LogRenovacao($this->em);
        $dataLog['renovacao']    = $this->Renovacao;
        $dataLog['tabela']     = 'log_renovacao';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'fechaOrcamento';
        $renovacao = $this->Renovacao->getId() . '/' . $this->Renovacao->getCodano();
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Fechou a renovação(' . $renovacao . ') e gerou o fechado de numero ' . $fechado ;
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
        $this->idToReference('comissaoEnt', 'Livraria\Entity\Comissao');
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
        $dataLog['controller'] = $this->origem ;
        $dataLog['action']     = 'fechar';
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        switch ($this->origem) {
            case 'orcamentos':
                $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
                $dataLog['mensagem']   = 'Novo seguro fechado n ' . $fechado . ' do orçamento n ' . $orcamento;
                break;
            case 'renovacaos':
                $renovacao = $this->Renovacao->getId() . '/' . $this->Renovacao->getCodano();
                $dataLog['mensagem']   = 'Novo seguro fechado n ' . $fechado . ' da renovação n ' . $renovacao;
                break;
            default:
                $dataLog['mensagem']   = 'Erro Origem desconhecida!!!!!';
                break;
        }
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
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if(empty($this->data['imovel']))
            return array('Um imovel deve ser selecionado!');
        
        $inicio = $this->data['inicio'];
        if((empty($inicio)) or ($inicio < (new \DateTime('01/01/2000'))))
            return array('A data deve ser preenchida corretamente!');
            
        $filtro['imovel'] = $this->data['imovel']->getId();
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            $st = $entity->getStatus();
            if (($st != "A") and ($st != 'R')) {
                continue;  //so valida registros ativos ou renovados
            }
            $ini = $this->data['inicio'];
            if ($entity->getInicio('obj') >= $ini and $ini <= $entity->getFim('obj')) {
                $erro[] = "Alerta!";
                $erro[] = 'Vigencia ' . $entity->getInicio() . ' <= ' . $entity->getFim();
                $erro[] = "Já existe um seguro com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
        }
        
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }

    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Fechados $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
    }

}
