<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use LivrariaAdmin\Fpdf\ImprimirSeguro;
use Zend\Session\Container as SessionContainer;
use Livraria\Service\Mysql;

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
     * Vai conter o valor do is do fechado inicial
     * @var type
     */
    protected $idFechado;
    
    /**
     * Entity LogFechamentos
     * @var type
     */
    protected $logEnty;

    /**
     * Entity LogOrcamentos
     * @var type
     */
    protected $logOrcaEnty;

    /**
     * Entity LogRenovação
     * @var type
     */
    protected $logRenoEnty;

    /**
     * Entity Orcamento
     * @var \Livraria\Entity\Orcamento
     */
    protected $Orcamento;

    /**
     * Entity Renovacao
     * @var type
     */
    protected $Renovacao;

    /**
     * Repository da entidade Fechados
     * @var object
     */
    protected $repository;

    /**
     * Serviço da entidade Orçamento
     * @var object
     */
    protected $servicoOrcamento;

    /**
     * Serviço da entidade Logs Orçamento
     * @var object
     */
    protected $servicoLogOrcamento;

    /**
     * Serviço da entidade Logs Renovação
     * @var object
     */
    protected $servicoLogRenovacao;

    /**
     * Serviço da entidade Logs Fechados
     * @var object
     */
    protected $servicoLogFechado;

    /**
     * Orçamento ou Renovação !! para Registro no Log
     * @var string
     */
    protected $origem;

    /**
     * Service locator que vem do controller
     * @var object
     */
    protected $serviceLocator;

    /**
     * Data Agora o Momento em foi instanciado o Serviço
     * @var object
     */
    protected $dataAgora;
    
    /**
     * Conta os seguros fechados que tem critica
     * @var integer
     */
    private $fechadosNg = 0;
    
    /**
     * Conta os seguros fechados normalmente
     * @var integer
     */
    private $fechadosOk = 0;
    
    /**
     * Pega o primeiro Id inserido e depois incrementa para diminuir a leitura no banco
     * @var integer
     */
    protected $firstId = 0;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->logEnty = "Livraria\Entity\LogFechados";
        $this->logOrcaEnty = "Livraria\Entity\LogOrcamento";
        $this->logRenoEnty = "Livraria\Entity\LogRenovacao";
        $this->entity = "Livraria\Entity\Fechados";
        $this->Orcamento = "Livraria\Entity\Orcamento";
        $this->Renovacao = "Livraria\Entity\Renovacao";
        $this->dataAgora = new \DateTime('now');
    }
    /**
     * Recebe o service Locator do ZF2
     * @param objeto $serviceLocator
     */
    public function setServiceLocator($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Estorna um seguro fechado colocando novamente em Orçamento.
     * Gera o log com dados de quem e o motivo do estorno
     * @param string $id
     * @param string $motivo
     * @param objet  $controller
     * @return boolean
     */
    public function estornaFechado($id, $motivo, &$controller = null) {
        /* @var $entityF \Livraria\Entity\Fechados */
        /* @var $entityO \Livraria\Entity\Orcamento */
        $entityF = $this->em->find('\Livraria\Entity\Fechados', $id);
        if(!is_object($entityF)){
            if(!is_null($controller)){
                $controller->flashMessenger()->addMessage("Alerta!!! Seguro $id Não encontrado!!!!");
            }
            return FALSE;
        }
        /* @var $entityO \Livraria\Entity\Orcamento */
        if(is_null($entityF->getOrcamentoId()) OR $entityF->getOrcamentoId() == 0){
            $anterior = $entityF->getRenovacaoId();
        }else{
            $anterior = $entityF->getOrcamentoId();            
        }        
        $entityO = $this->em->find('\Livraria\Entity\Orcamento', $anterior);
        if(!is_object($entityO)){
            if(!is_null($controller)){
                $controller->flashMessenger()->addMessage('Alerta!!! Orçamento' . $anterior . 'Não encontrado!!!!');
            }
            return FALSE;
        }
        // Desfaz as alterações em Orçamento
        $entityO->setFechadoId(0);
        if($entityO->getOrcaReno() == 'orca'){
            $entityO->setStatus('A');
        }else{
            $entityO->setStatus('R');
        }
        $this->em->persist($entityO);
        // Remover Fechado 
        $this->em->remove($entityF); 
        $controller->flashMessenger()->addMessage("Seguro $id estornado com sucesso!!!!");
        //Gerar os log de estorno e salvar no BD
        $this->logEstornoDeFechado($entityO, $id, $motivo);
        $this->em->flush();
        return TRUE;
    }
    
    public function logEstornoDeFechado($entityO, $fechado, $motivo) {        
        //Criar serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']    = $entityO;
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'fechados' ;
        $dataLog['action']     = 'estornaVarios';
        $orcamento = $entityO->getId() . '/' . $entityO->getCodano();
        $dataLog['mensagem']   = 'Estornou Fechado(' . $fechado . ') para Orçamento(' . $orcamento . '). ';
        $dataLog['mensagem']  .= $motivo;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }
    /**
     * Exclui o seguro fechado e exclui tb o orçamento ou renovação referente
     * @param string $id
     * @param array $data
     * @param obj   $sl    service locator para enviar email
     * @return boolean
     */
    public function delete($id,$data, $sl=null) {
        $this->serviceLocator = $sl ;
        $enty = $this->em->find($this->entity,$id);
        if($enty->getStatus() == 'C'){
            return ['Erro este seguro já foi cancelado!!'];            
        }
        if(!parent::delete($id)){
            return ['Erro ao tentar excluir registro!!'];
        }
        if(empty($data['motivoNaoFechou']) AND isset($data['motivoNaoFechou2'])){
            $data['motivoNaoFechou'] = $data['motivoNaoFechou2'];
        }
        $this->logForDelete($id,$data);
        // Cancelar tb o orcamento ou renovação que gerou este seguro fechado.
        $data['motivoNaoFechou'] = 'Excluido porque seu registro de fechado foi excluido fechado numero= '. $id . '. Motivo ' . $data['motivoNaoFechou'];
        $serOrca = new Orcamento($this->em);
        $orca = $enty->getOrcamentoId();
        $reno = $enty->getRenovacaoId();
        $force = $resul = TRUE;
        if(!is_null($orca) AND $orca != 0){
            $resul = $serOrca->delete($orca, $data, $force) ;
        }
        if(!is_null($reno) AND $reno != 0){
            $resul = $serOrca->delete($reno, $data, $force);
        }
        // Verificar se é usuario da imobiliaria e enviar email.
//        if($this->getIdentidade()->getTipo() != 'admin'){
            $this->sendEmailCancelamento($enty, $data['motivoNaoFechou']);
//        }
        if($resul !== true){
            return $resul;
        }
        return TRUE;
    }
    
    /**
     * Registra a exclusão do registro com seu motivo.
     * @param type $id
     * @param type $data
     */
    public function logForDelete($id,$data) {
        //serviço logorcamento
        $log = new LogFechados($this->em);
        $dataLog['fechados'] = $id;
        $dataLog['tabela'] = 'log_fechado';
        $dataLog['controller'] = 'fechados';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Fechado excluido com numero ' . $id;
        if(!empty($data['motivoNaoFechou'])){
            $dataLog['dePara'] = $data['motivoNaoFechou'] ;
        }
        if(!empty($data['motivoNaoFechou2'])){
            $dataLog['dePara'] = $data['motivoNaoFechou2'] ;
        }
        $log->insert($dataLog);
    }
    
    /**
     * Retorna o Repositorio da entidade Fechados
     * @return object
     */
    public function getRep(){
        if($this->repository)
            return $this->repository;
        
        $this->repository = $this->em->getRepository($this->entity);
        return $this->repository;
    }
    
    /**
     * Retorna o Serviço da entidade Orçamento
     * @return \Livraria\Service\Orcamento
     */
    public function getSrvOrca(){
        if($this->servicoOrcamento)
            return $this->servicoOrcamento;
        
        $this->servicoOrcamento =  new Orcamento($this->em);    
        $this->servicoOrcamento->setFlush($this->getFlush());
        return $this->servicoOrcamento;        
    }
    
    /**
     * Retorna o Serviço da entidade Logs Fechados
     * @return object
     */
    public function getSrvLog(){
        if($this->servicoLogFechado)
            return $this->servicoLogFechado;
        
        $this->servicoLogFechado =  new LogFechados($this->em);  
        $this->servicoLogFechado->setFlush($this->getFlush());
        return $this->servicoLogFechado;        
    }
    
    /**
     * Retorna o Serviço da entidade Logs Orçamento
     * @return object
     */
    public function getSrvLogOrca(){
        if($this->servicoLogOrcamento)
            return $this->servicoLogOrcamento;
        
        $this->servicoLogOrcamento =  new LogOrcamento($this->em);  
        $this->servicoLogOrcamento->setFlush($this->getFlush());
        return $this->servicoLogOrcamento;        
    }
    
    /**
     * Retorna o Serviço da entidade Logs Renovação
     * @return object
     */
    public function getSrvLogReno(){
        if($this->servicoLogRenovacao)
            return $this->servicoLogRenovacao;
        
        $this->servicoLogRenovacao =  new LogRenovacao($this->em);   
        $this->servicoLogRenovacao->setFlush($this->getFlush());
        return $this->servicoLogRenovacao;        
    }

    public function getPdfSeguro($id){
        //Carregar Entity Fechados
        /*  @var $seg \Livraria\Entity\Fechados      */
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado o seguro com esse numero!!!'];
        }
        
        $num = 'Fechado/' . $seg->getId() . '/' . $seg->getCodano();
        $pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());
        $pdf->setShowCusInd($seg->getAdministradora()->getShowCusInd());
        $pdf->setL1($seg->getRefImovel(), $seg->getInicio());
        $pdf->setL2($seg->getAdministradora()->getNome());
        $pdf->setL3($seg->getLocatario(), $seg->getLocatario()->getCpf() . $seg->getLocatario()->getCnpj());
        $pdf->setL4($seg->getLocador(), $seg->getLocador()->getCpf() . $seg->getLocador()->getCnpj());
        $pdf->setL5($seg->getImovel());
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
        $assist24 = null;
        if($seg->getAssist24() == 'S'){
            /* @var $parametro \Livraria\Entity\ParametroSis */
            $parametro = $this->getParametroSis('assist24_' . $seg->getOcupacao() . '_' . $seg->getValidade(), true)[0];
            if($parametro){
                $assist24 = [substr($parametro->getDescricao(), 26), number_format($parametro->getConteudo(), 2, ',', '.')];
            }            
        }
        $pdf->setL11($vlr, $label, $assist24);
        $tot = [
            $seg->floatToStr('premio'),
            $seg->floatToStr('premioLiquido'),
            $this->strToFloat($seg->getPremioLiquido() * $seg->getTaxaIof()),
            $seg->floatToStr('premioTotal')
        ];
        $pdf->setL12($tot,  $this->strToFloat($seg->getTaxaIof() * 100), $seg->getValidade());
        $par = [
            $seg->floatToStr('premioTotal')
            ,$this->strToFloat($seg->getPremioTotal() / 2)
            ,$this->strToFloat($seg->getPremioTotal() / 3)
            ,$this->strToFloat($seg->getPremioTotal() / 4)
            ,$this->strToFloat($seg->getPremioTotal() / 5)
            ,$this->strToFloat($seg->getPremioTotal() / 12)
        ];
        $pdf->setL13($par, ($seg->getValidade() =='mensal')?true:false, $seg->getFormaPagto(),$seg->getAdministradora()->getPropPag());
        $pdf->setL14();
        $pdf->setObsGeral('',($seg->getAssist24() == 'S')? TRUE : FALSE);
        $pdf->Output();
    }

    public function validaOrcamento($id){
        if (!is_object($id)){
            //Carregar Entity Orcamento
            $this->Orcamento = $this->em
                ->getRepository('Livraria\Entity\Orcamento')
                ->find($id);
        }else{
            $this->Orcamento = $id;
        }
        
        
        if ($this->getIdentidade()->getIsAdmin() == '0') {
            return [FALSE,'Você não tem permissão para incluir ou alterar registro'];
        }
        if ($this->getIdentidade()->getMenu() == 'imob'){
            if($this->Orcamento->getAdministradora()->getBlockFechamento()){
                return [FALSE,'Esta Administradora não tem permissão para fechar propostas somente pode fazer orçamentos!!!'];
            }        
            $hoje = new \DateTime('now');            
            if($this->Orcamento->getInicio('obj')->format("Ymd") < $hoje->format("Ymd")){
                return [FALSE,'Você não tem permissão para fechar com vigência retroativa caso precise ligar para Vila velha.'];                
            }
        }

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


    public function fechaOrcamento($id,$pdf=true, $sl=null) {
        $this->serviceLocator = $sl ;
        $resul = $this->validaOrcamento($id);
        if($resul[0] === FALSE){
            return $resul;
        }

        //Montar dados para tabela de fechados
        $this->data = $this->Orcamento->toArray();
        if($this->data['orcaReno'] == 'orca'){
            $this->data['orcamentoId'] = $this->data['id'];
        }else{
            $this->data['renovacaoId'] = $this->data['id'];
        } 
        unset($this->data['id']);
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "A";
        $this->data['gerado'] = "N";
        $this->data['criadoEm'] = $this->getDataAgora();

        //Faz inserção do fechado no BD
        $resul = $this->insert();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->Orcamento->setFechadoId($this->data['id']);
            $this->Orcamento->setStatus('F');
            $this->em->persist($this->Orcamento);
            $this->em->flush();
            $this->registraLogOrcaReno();
            $this->atualizaImovel();
            $this->checkLimitVistoria();
            if($pdf){
                $this->getPdfSeguro($this->data['id']);
            }
        }

        return $resul;
    }
        
    /**
     * Gera o log com resumo dos parametros utilizados e qtd de seguros fechados.
     * @param array $data
     */
    public function logFechaRapido($data){   // Gerar log
        $obs = 'Fechou seguros pelo botão (Fechar Todas Paginas):<br>';
        $obs .= 'Total de seguros fechados nesta ação ' . ($this->fechadosOk + $this->fechadosNg) . ':<br>';
        $obs .= 'Total de seguros fechados normal ' . $this->fechadosOk . ':<br>';
        $obs .= 'Total de seguros fechados criticado ' . $this->fechadosNg . ':<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $obs .= empty($data['dataI']) ? '' : 'Periodo Inicio em : ' . $data['dataI'] .'<br>';
        $obs .= empty($data['dataF']) ? '' : 'Periodo Fim em : ' . $data['dataF'] .'<br>';
        $obs .= empty($data['status']) ? '' : 'Com Status de : ' . $data['status'] .'<br>';
        $obs .= isset($data['anual']) ? 'Gerou Somente Anual' .'<br>' : '';
        $obs .= isset($data['mensal']) ? 'Gerou Somente mensal' .'<br>' : '';
        $this->logForSis('fechados', '', 'fechados', 'fecharTodosSeguros', $obs);
    }
    
    public function fechaRapido($obj){
        $resul = $this->validaOrcamento($obj);
        if($resul[0] === FALSE){
            return $resul;
        }

        //Montar dados para tabela de fechados
        $this->data = $obj->toArrayWithObj();
        if($this->data['orcaReno'] == 'orca'){
            $this->origem = 'orcamentos';
            $this->data['orcamentoId'] = $this->data['id'];
        }else{
            $this->origem = 'renovacaos';
            $this->data['renovacaoId'] = $this->data['id'];
        } 
        unset($this->data['id']);

        $this->data['locador']          = $this->em->getReference('Livraria\Entity\Locador', $this->data['locador']);
        $this->data['locatario']        = $this->em->getReference('Livraria\Entity\Locatario', $this->data['locatario']);
        $this->data['imovel']           = $this->em->getReference('Livraria\Entity\Imovel', $this->data['imovel']);
        $this->data['taxa']             = $this->em->getReference('Livraria\Entity\Taxa', $this->data['taxa']);
        $this->data['atividade']        = $this->em->getReference('Livraria\Entity\Atividade', $this->data['atividade']);
        $this->data['seguradora']       = $this->em->getReference('Livraria\Entity\Seguradora', $this->data['seguradora']);
        $this->data['administradora']   = $this->em->getReference('Livraria\Entity\Administradora', $this->data['administradora']);
        $this->data['user']             = $this->em->getReference('Livraria\Entity\User', $this->getIdentidade()->getId());
        $this->data['multiplosMinimos'] = $this->em->getReference('Livraria\Entity\MultiplosMinimos', $this->data['multiplosMinimos']);
        
        $this->data['status'] = "A";
        $this->data['gerado'] = "N";
        $this->data['criadoEm'] = $this->getDataAgora();
      
        //Faz inserção do fechado no BD
        $resul = $this->insertRapido();

        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $this->Orcamento->setFechadoId($this->idFechado);
            $this->Orcamento->setStatus('F');
            $this->em->persist($this->Orcamento);
            $this->fechadosOk++;
        }else{
            $this->fechadosNg++;
        }
        
        //Criar log orcamento
        $fechado   = $this->idFechado . '/' . $this->entityReal->getCodano();
        $dataLog['dePara']       = '';
        $dataLog['data']         = $this->getDataAgora();
        $dataLog['user'] = $this->data['user'];
        $dataLog['ip']           = $_SERVER['REMOTE_ADDR'];
        if($this->Orcamento->getOrcaReno() == 'orca'){
            $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
            $dataLog['orcamento']    = $this->Orcamento;
            $dataLog['tabela']     = 'log_orcamento';
            $dataLog['controller'] = 'orcamentos' ;            
            $dataLog['action']     = 'fechaOrcamento';
            $dataLog['mensagem']   = 'Fechou o orçamento(' . $orcamento . ') e gerou o fechado de numero ' . $fechado ;
            $logOrca = new $this->logOrcaEnty($dataLog);
            $this->em->persist($logOrca);
        }
        //Criar log renovação
        if($this->Orcamento->getOrcaReno() == 'reno'){
            $renovacao = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
            $dataLog['renovacao']    = $this->Orcamento;
            $dataLog['tabela']     = 'log_renovacao';
            $dataLog['controller'] = 'renovacaos' ;            
            $dataLog['action']     = 'fecharSeguros';
            $dataLog['mensagem']   = 'Fechou o renovação(' . $renovacao . ') e gerou o fechado de numero ' . $fechado ;
            $logReno = new $this->logRenoEnty($dataLog);
            $this->em->persist($logReno);
        }
        //Atualiza dados do imovel        
        $imovel = $this->em->find('Livraria\Entity\Imovel',  $this->data['imovel']);
        if($imovel){
            $imovel->setFechadoId($this->idFechado);
            $imovel->setFechadoAno($this->data['codano']);
            $imovel->setVlrAluguel($this->data['valorAluguel']);
            $imovel->setFechadoFim($this->data['fim']);
            $imovel->setLocatario($this->data['locatario']);
            $imovel->setLocador($this->data['locador']);
            $this->em->persist($imovel);
        }
        
        if(!$this->checkMensal()){
            $this->checkLimitVistoria();            
        }
                
        return $resul;
    }

    /**
     * Verifica se seguro é mensal se não retorna falso
     * Verifica se mensal seq é zero significando renovação Anual do mensal 
     * @return boolean
     */
    public function checkMensal() {
        if($this->Orcamento->getValidade() != 'mensal'){
            return false;
        }
        if($this->Orcamento->getMensalSeq() == 0){
            return false;            
        }
        return TRUE;
    }
    
    public function insertRapido(){
        /*
         * Já busca as referencias no proprio array
        $this->setReferences();
         */
        /*
         * Não fazer validação presupondo que os orçamentos já estão validados
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
         */       
       
        $this->entityReal = new $this->entity($this->data);
        $this->em->persist($this->entityReal);   
        
        if (is_null($this->idFechado)){
            echo 'testando';
            $this->em->flush();
            $this->idFechado = $this->entityReal->getId();
        }else{
            $this->idFechado++;
        }

        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = $this->origem ;
        $dataLog['action']     = 'fechar';
        $fechado   = $this->idFechado . '/' . $this->entityReal->getCodano();
        switch ($this->origem) {
            case 'orcamentos':
                $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
                $dataLog['mensagem']   = 'Novo seguro fechado n ' . $fechado . ' do orçamento n ' . $orcamento;
                break;
            case 'renovacaos':
                $renovacao = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
                $dataLog['mensagem']   = 'Novo seguro fechado n ' . $fechado . ' da renovação n ' . $renovacao;
                break;
            default:
                $dataLog['mensagem']   = 'Erro Origem desconhecida!!!!!';
                break;
        }
        $dataLog['dePara']       = '';
        $dataLog['data']         = $this->getDataAgora();
        $dataLog['user']         = $this->data['user'];
        $dataLog['ip']           = $_SERVER['REMOTE_ADDR'];
        $dataLog['fechados']     = $this->entityReal;
       
        $log = new $this->logEnty($dataLog);
        $this->em->persist($log);       

        return array(TRUE,  'Inserido');
    }

    public function getDataAgora(){
        return $this->dataAgora;
    }
    
    /**
     * Verificar o valor limite para não ter vistoria.
     * Caso ultrapasse o valor um email é enviado alertando responveis.
     */
    public function checkLimitVistoria(){
        switch ($this->entityReal->getTipoCobertura()){
            case '01':  // predio
                $valor = $this->entityReal->getIncendio();
                break;
            case '02':  // /predio + conteudo
                $valor = $this->entityReal->getConteudo();
                break;
        }
        switch ($this->entityReal->getOcupacao()){
            case '01':  // Comercio
                $chave = 'vistoria_comercial';
                break;
            case '02':  // Residencial
                $chave = 'vistoria_residencial';
                break;
            case '03':  // Industrial
                $chave = 'vistoria_industrial';
                break;
        }
        if(is_null($chave) OR is_null($valor)){
            echo '<h1>Alerta tipo de cobertura = ', $this->entityReal->getTipoCobertura(), 
                                ' e Ocupação = ', $this->entityReal->getOcupacao() , '</h1>';
            return;
        }
        $enty = $this->em->getRepository('Livraria\Entity\ParametroSis')->findByKey($chave);
        if(empty($enty)){
            echo '<h1>Erro parametro não encontrado chave = ', $chave, '</h1>';
            return;
        }
        $limit = (float)$enty[0]->getConteudo();
        if($valor > $limit){
            $this->sendEmailVistoria();
        }        
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
    
    public function registraLogOrcaReno(){
        //Criar serviço logorcamento
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['dePara']     = '';
        if($this->Orcamento->getOrcaReno() == 'orca'){
            $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
            $dataLog['orcamento']    = $this->Orcamento;
            $dataLog['tabela']     = 'log_orcamento';
            $dataLog['controller'] = 'orcamentos' ;            
            $dataLog['action']     = 'fechaOrcamento';
            $dataLog['mensagem']   = 'Fechou o orçamento(' . $orcamento . ') e gerou o fechado de numero ' . $fechado ;
            $this->getSrvLogOrca()->insert($dataLog);
        }
        if($this->Orcamento->getOrcaReno() == 'reno'){
            $renovacao = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
            $dataLog['renovacao']    = $this->Orcamento;
            $dataLog['tabela']     = 'log_renovacao';
            $dataLog['controller'] = 'renovacaos' ;            
            $dataLog['action']     = 'fecharSeguros';
            $dataLog['mensagem']   = 'Fechou o renovação(' . $renovacao . ') e gerou o fechado de numero ' . $fechado ;
            $this->getSrvLogReno()->insert($dataLog);
        }
    }

    
    public function fechaRenovacao($id,$pdf=true, $sl=null) {
        $this->serviceLocator = $sl ;
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
        $this->data['gerado'] = "N";
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
            $this->checkLimitVistoria();
            if($pdf){
                $this->getPdfSeguro($this->data['id']);
            }
        }

        return $resul;
    }

    public function validaRenovacao($id){
        //Carregar Entity Orcamento
        $this->Renovacao = $this->em
            ->getRepository('Livraria\Entity\Orcamento')
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

        $this->idFechado = $this->data['id'];
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
    public function update($entity, $dePara, $origem) {
        $this->origem = $origem;
        $this->dePara = $dePara;
        $this->entityReal = $this->em->find($this->entity, $entity->getFechadoId());
        $this->data = $this->entityReal->toArray();
        
        // Lista de metodos para atualizar$metodos[] = 'Locador';        
        $metodos[] = 'Locador';            $param[] = '';
        $metodos[] = 'Locatario';          $param[] = '';
        $metodos[] = 'Imovel';             $param[] = '';
        $metodos[] = 'Taxa';               $param[] = '';
        $metodos[] = 'Atividade';          $param[] = '';
        $metodos[] = 'Seguradora';         $param[] = '';
        $metodos[] = 'Administradora';     $param[] = '';
        $metodos[] = 'MultiplosMinimos';   $param[] = '';
        $metodos[] = 'ComissaoEnt';        $param[] = '';
        $metodos[] = 'ValorAluguel';       $param[] = '';
        $metodos[] = 'Incendio';           $param[] = '';
        $metodos[] = 'Conteudo';           $param[] = '';
        $metodos[] = 'Aluguel';            $param[] = '';
        $metodos[] = 'Eletrico';           $param[] = '';
        $metodos[] = 'Vendaval';           $param[] = '';
        $metodos[] = 'CobIncendio';        $param[] = '';
        $metodos[] = 'CobConteudo';        $param[] = '';
        $metodos[] = 'CobAluguel';         $param[] = '';
        $metodos[] = 'CobEletrico';        $param[] = '';
        $metodos[] = 'CobVendaval';        $param[] = '';
        $metodos[] = 'PremioLiquido';      $param[] = '';
        $metodos[] = 'Premio';             $param[] = '';
        $metodos[] = 'PremioTotal';        $param[] = '';
        $metodos[] = 'Comissao';           $param[] = '';
        $metodos[] = 'Inicio';             $param[] = 'obj';
        $metodos[] = 'Fim';                $param[] = 'obj';
        $metodos[] = 'CanceladoEm';        $param[] = 'obj';
        $metodos[] = 'AlteradoEm';         $param[] = 'obj';
        $metodos[] = 'Codano';             $param[] = '';
        $metodos[] = 'LocadorNome';        $param[] = '';
        $metodos[] = 'LocatarioNome';      $param[] = '';
        $metodos[] = 'TipoCobertura';      $param[] = '';
        $metodos[] = 'SeguroEmNome';       $param[] = '';
        $metodos[] = 'CodigoGerente';      $param[] = '';
        $metodos[] = 'RefImovel';          $param[] = '';
        $metodos[] = 'FormaPagto';         $param[] = '';
        $metodos[] = 'Observacao';         $param[] = '';
        $metodos[] = 'MesNiver';           $param[] = '';
        $metodos[] = 'Assist24';           $param[] = '';
        $metodos[] = 'Validade';           $param[] = '';
        $metodos[] = 'Ocupacao';           $param[] = '';
        $metodos[] = 'taxaAjuste';         $param[] = '';
        // Atualizar campos modificados        
        foreach ($metodos as $key => $metodo) {
            if(empty($param[$key])){
                $value = call_user_func(array($entity, 'get' . $metodo));
            }else{
                $value = call_user_func(array($entity, 'get' . $metodo), $param[$key]);
            }
            call_user_func(array($this->entityReal, 'set' . $metodo), $value);
        }   
        // Pesiste, Salva e gera log em fechados.
        $this->em->persist($this->entityReal);
        $this->em->flush(); 
        $this->logForEdit($entity);

        return TRUE;
    }

    /**
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit($entity){
        //parent::logForEdit('fechados');
        //serviço LogFechamento
        $log = new LogFechados($this->em);
        $dataLog['fechados']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = $this->origem ;
        $dataLog['action']     = 'edit';
        $fechado   = $this->data['id'] . '/' . $this->data['codano'];
        switch ($this->origem) {
            case 'orcamentos':
                $orcamento = $entity->getId() . '/' . $entity->getCodano();
                $dataLog['mensagem']   = 'Alterado seguro fechado n ' . $fechado . ' a partir do orçamento n ' . $orcamento;
                break;
            case 'renovacaos':
                $renovacao = $entity->getId() . '/' . $entity->getCodano();
                $dataLog['mensagem']   = 'Alterado seguro fechado n ' . $fechado . ' a partir da renovação n ' . $renovacao;
                break;
            default:
                $dataLog['mensagem']   = 'Erro Origem desconhecida!!!!!';
                break;
        }
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $log->insert($dataLog);
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
        if (empty($this->data['imovel'])) {
            return array('Um imovel deve ser selecionado!');
        }
        $inicio = $this->data['inicio'];
        $fim = $this->data['fim'];
        if (!is_object($inicio)) {
            return array('A data deve ser preenchida corretamente!');
        }
        $filtro['imovel'] = $this->data['imovel']->getId();
        $filtro['administradora'] = $this->data['administradora']->getId();
        $filtro['locador'] = $this->data['locador']->getId();
        $filtro['locatario'] = $this->data['locatario']->getId();
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if(isset($this->data['id']) and ($this->data['id'] == $entity->getId())){
                continue;
            }
            if(($inicio >= $entity->getFim('obj'))){
                continue;
            }
            if(($fim <= $entity->getInicio('obj'))){
                continue;
            }
            switch ($entity->getStatus()) {
                case "A":
                    $erro[] = "Alerta!" ;
                    $erro[] = 'Vigencia ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                    $erro[] = "Já existe uma renovação com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    break;
                case "F":
                    $erro[] = "Alerta!" ;
                    $erro[] = 'Vigencia ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                    $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    break;
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
    
    /**
     * Recebe a chave do seguro a renovar gerar novo orçamento 
     * Parametro $mes e $ano são para atualizar data dos mensais
     * Caso receber o parametro de reajuste o Aluguel
     * Faz lançamento no log dos seguros fechados e tb no log de orçamentos
     * Complementa a obs do fechado dizendo que gerou um orçamento para renovação
     * @param int $key
     * @param string $mes
     * @param string $ano
     * @param int $reajuste
     * @return array
     */
    public function fechadoToOrcamento($key, $mes, $ano, $reajuste=0){
        //Pegando o serviço de fechados        
        $fechado = $this->getRep()->find($key);
        
        $this->data = $fechado->toArray();
        
        $this->data['fechadoOrigemId'] = $this->data['id'];
        $this->data['id'] = '';
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "R";
        $this->data['orcaReno'] = "reno";
        $this->data['mensalSeq'] = 0;
        $this->data['criadoEm'] = $this->getDataAgora();
        if($this->data['validade'] == 'anual'){
            $this->data['inicio'] = $fechado->getFim('obj');
        }else{// senão é considera mensal(Mensal é renovado 3 meses antes de chegar na ultima parcela)
            $this->data['inicio'] = $fechado->getInicio('obj')->format('d') . '/' . $mes . '/' . $ano;
        }
        //Pegando o locatario atual desse imovel porque o locatario pode ter sido trocado no meio da vigencia do fechado
        //Quando a troca de locatario é apenas atualizado no imovel.
        $this->data['locatario'] = $fechado->getImovel()->getLocatario()->getId();
        
        if($reajuste != 0){
            $this->data['valorAluguel'] = $this->data['valorAluguel'] * (1 + $reajuste / 100 );
        }
        
        //Faz inserção do fechado no BD
        if($this->firstId == 0){
            $this->getSrvOrca()->setFlush(TRUE);
            $resul = $this->getSrvOrca()->insert($this->data);
            $this->getSrvOrca()->setFlush(FALSE);
            if($resul[0] === TRUE){
                $this->firstId = $this->getSrvOrca()->getEntity()->getId();
            }
        }else{
            $this->firstId++;
            $this->data['id'] = $this->firstId;
            $resul = $this->getSrvOrca()->insert($this->data);
        }

        if($resul[0] === TRUE){
            $fechado->setObservacao($fechado->getObservacao() . 'Gerou Renovação para reajuste numero ' . $resul[1]);
            //Marcar como renovado somente os anuais visto que os mensais serão marcados pelo serviço de renovação.
            if($fechado->getValidade() == 'anual'){
                $fechado->setStatus('R');   // SEGURO RENOVADO
            }else{
                $fechado->setStatus('AR');  // SEGURO MENSAL QUE TEVE ATUALIZAÇAO ANUAL DE VALOR              
            }
            $this->em->persist($fechado);
            $this->registraLogFechadoToOrcamento($fechado);
        }  else {
            $this->firstId--;            
        }
        
        return $resul;
    }

    /**
     * Faz a inclusão no log de fechado e tb no log de orçamento.
     * @param entity $fechado
     */
    public function registraLogFechadoToOrcamento($fechado) {
        $this->Orcamento = $this->getSrvOrca()->getEntity();
        
        $dataLog['fechados']    = $fechado;
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = 'mapaRenovacao' ;
        $dataLog['action']     = 'gerarMapa';
        $orcamento = $this->Orcamento->getId() . '/' . $this->Orcamento->getCodano();
        $dataLog['mensagem']   = 'Fechado gerou orçamento(' . $orcamento . ') para renovação das taxas ';
        $dataLog['dePara']     = '';
        $this->getSrvLog()->insert($dataLog);
        
        $dataLog['renovacao']    = $this->Orcamento;
        $dataLog['tabela']     = 'log_renovacao';
        $fechadoNum   = $fechado->getId() . '/' . $fechado->getCodano();
        $dataLog['mensagem']   = 'Renovação(' . $orcamento . ') e gerado a partir do fechado de numero(' . $fechadoNum . ').';
        $dataLog['dePara']     = '';
        $this->getSrvLogReno()->insert($dataLog);
    }
    
    public function gerarListaEmail($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        //Filtro para Administradora
        $this->data['administradora'] = $data['administradora'];
        
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->faturados     = $this->em->getRepository("Livraria\Entity\Fechados")->getListaEmail($this->data); 
        $sc->data          = $this->data;
        
        return $sc->faturados;  
    }
    
    /**
     * Envia email avisando que o limite da vistoria foi ultrapassado
     * @return no
     */
    public function sendEmailVistoria(){
        if(is_null($this->serviceLocator)){
            return;
        }
        $servEmail = $this->serviceLocator->get('Livraria\Service\Email');  
        $dados = $this->entityReal->getAdministradora()->toArray();
        $dados['seguro'] = $this->idFechado . '/' . $this->entityReal->getCodano();
        $dados['imovel'] = $this->entityReal->getImovel()->__toString();
        $dados['locador'] = $this->entityReal->getLocadorNome();
        $dados['locatario'] = $this->entityReal->getLocatarioNome();
        $servEmail->enviaEmail(['nome' => $this->getIdentidade()->getNome(),'emailNome' => $this->getIdentidade()->getNome(),
            'subject' => 'Vistoria do Seguro Fechado do Incêndio Locação.(' . $dados['nome'] . ')' ,
            'data' => $dados],'seguro-vistoria');         
    }

    /**
     * Envia email avisando que foi cancelado o seguro fechado
     * @param \Livraria\Entity\Fechados $enty
     * @return no
     */
    public function sendEmailCancelamento($enty,$motivo=''){
        if(is_null($this->serviceLocator)){
            return;
        }
        $servEmail = $this->serviceLocator->get('Livraria\Service\Email');  
        $dados = $enty->getAdministradora()->toArray();
        $dados['seguro'] = $enty->getId() . '/' . $enty->getCodano();
        $dados['imovel'] = $enty->getImovel()->__toString();
        $dados['locador'] = $enty->getLocadorNome();
        $dados['locatario'] = $enty->getLocatarioNome();
        $dados['vigencia'] = $enty->getInicio();
        $dados['vigenciaF'] = $enty->getFim();
        $dados['motivo'] = $motivo;
        $nome = $this->getIdentidade()->getNome();
        
        // Envia para a administradora se houver email cadastrado
        $servEmail->enviaEmail(['emailNome' => $nome, 'email' => $dados['email'],
            'subject' => 'Cancelamento de Seguro Fechado do Incêndio Locação.(' . $dados['nome'] . ')' ,
            'data' => $dados],'seguro-canceladoForAdm');             
    }
    
    /**
     * Faz envio de email para imobiliaria com os seguros fechados no mes para confirmação
     * Recebe o service locator para poder pegar o servido e email com suas dependencias
     * Recebe Filtro para administradoras
     * @param object $sl
     * @param string $admCod
     * @return boolean
     */
    public function sendEmailFaturados($sl,$admFiltro=''){
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if (empty($sc->faturados))
            return FALSE;

        $servEmail = $sl->get('Livraria\Service\Email');

        $admCod  = 0;
        foreach ($sc->faturados as $value) {
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                $value['administradora']['email'] = $this->mailDefault; 
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $servEmail->enviaEmail(['nome' => $admNom,'emailNome' => $admNom,
                        'email' => $admEmai,
                        'subject' => $admNom . ' -Confirmação dos Seguro(s) Fechado(s) do Incêndio Locação',
                        'data' => $data],'seguro-faturado');                     
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $data    = [];              
                $i       = 0;
            }
            //Faz o acumulo dos dados.
            $data[$i][] = $value['id'];
            $data[$i][] = $value['locatarioNome'];
            $data[$i][] = $value['inicio']->format('d/m/Y');
            $data[$i][] = $value['fim']->format('d/m/Y');
            $data[$i][] = number_format($value['premioTotal'], 2, ',', '.');
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){
            $servEmail->enviaEmail(['nome' => $admNom,'emailNome' => $admNom,
                'email' => $admEmai,
                'subject' => $admNom . ' -Confirmação dos Seguro(s) Fechado(s) do Incêndio Locação',
                'data' => $data],'seguro-faturado');                     
        }
        
        return true;
        
    }
    
    /**
     * Trata os filtros 
     * Faz a consulta com periodo atual
     * Faz outra consulta retirando 1 ano do periodo
     * retorna array de resultados 
     * @param array $data
     * @return array mixed
     */
    public function montaListaAtualAnterior($data) {
        // Aborta caso filtro inicio vazio
        if (empty($data['inicio'])) {
            return [];
        }
        $this->data['inicio'] = $data['inicio'];
        $this->data['fim'] = $data['fim'];
        $this->data['administradora'] = $data['administradora'];
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        if(!$this->dateToObject('inicio')){
            return [];            
        }
        if (!empty($data['fim'])){
            if(!$this->dateToObject('fim')){
                return [];            
            }
        }else{
            $this->data['fim'] = clone $this->data['inicio'];
            $this->data['fim']->add(new \DateInterval('P1M')); 
            $this->data['fim']->sub(new \DateInterval('P1D')); 
        }
        $periodoAtual = $this->getRep()->findFechados($this->data);
        
        $this->data['inicio']->sub(new \DateInterval('P1Y')); 
        $this->data['fim']->sub(new \DateInterval('P1Y')); 
        $periodoAntes = $this->getRep()->findFechados($this->data);
        
        return $this->juntaAtualAntes($periodoAtual,$periodoAntes);
    }

    /**
     * Unifica os resultados para um unico array para exibição
     * @param array $periodoAtual
     * @param array $periodoAntes
     * @return array
     */
    public function juntaAtualAntes(&$periodoAtual, &$periodoAntes) {
        $uniao = [];
        foreach ($periodoAtual as $value) {
            $antes = $this->getInArray($periodoAntes,$value['id']);
            $uniao[] = ['id' => $value['id'], 
                        'nome' => $value['nome'], 
                        'atual' => ['qtd' => $value['qtd'], 'total' => $value['total']], 
                        'antes' => ['qtd' => $antes['qtd'], 'total' => $antes['total']]];
        }
        foreach ($periodoAntes as $value) {
            $uniao[] = ['id' => $value['id'], 
                        'nome' => $value['nome'], 
                        'atual' => ['qtd' => 0, 'total' => 0], 
                        'antes' => ['qtd' => $value['qtd'], 'total' => $value['total']]];
        }
        return $uniao;
    }

    /**
     * Busca no array o registro com a chave passada
     * Caso exista ele monta valores para retornar e apaga registro do array
     * Caso não exista retorna um array com os valores zerados.
     * @param type $periodoAntes
     * @param type $value
     * @return array
     */
    public function getInArray(&$periodoAntes, $value) {
        $antes = ['qtd' => 0, 'total' => 0];
        foreach ($periodoAntes as $key => $reg) {
            if($reg['id'] == $value){
                $antes['qtd'] = $reg['qtd'];
                $antes['total'] = $reg['total'];
                unset($periodoAntes[$key]);
            }
        }
        return $antes;
    }
    
    public function checkFechados() {
        $sql = new Mysql();
        $q  = "SELECT `fechados`.`id`";
        $q .= ",`fechados`.`orcamento_id`";
        $q .= ",`fechados`.`renovacao_id`";
        $q .= ",`orcamento`.`fechado_id`";
        $q .= ",`orcamento`.`id`";
        $q .= ",`orcamento`.`status`";
        $q .= ",`fechados`.`status`";
        $q .= ",`orcamento`.`locador_id`";
        $q .= ",`fechados`.`locador_id`";
        $q .= ",`orcamento`.`administradoras_id`";
        $q .= ",`fechados`.`administradoras_id`";
        $q .= ",`orcamento`.`inicio`";
        $q .= ",`fechados`.`inicio`";
        $q .= ",`orcamento`.`validade`";
        $q .= ",`fechados`.`validade`";
        $q .= " FROM `fechados`, `orcamento` ";
        $q .= " WHERE `orcamento`.`inicio` >= '2014-10-01 00:00:00'";
        $q .= " AND `orcamento`.`status` <> 'C'";
        $q .= " AND `orcamento`.`fechado_id` = ''";
        $q .= " AND `fechados`.`status` <> 'C'";
//        $q .= " AND `orcamento`.`inicio` = `fechados`.`inicio`";
        $q .= " AND `orcamento`.`locador_id` = `fechados`.`locador_id`";
        $q .= " AND (`orcamento`.`id` = `fechados`.`orcamento_id` OR `orcamento`.`id` = `fechados`.`renovacao_id`)";
//        $q .= " AND `orcamento`.`locador_id` <> `fechados`.`locador_id`";
//        $q .= " AND `orcamento_id` IS NULL AND `renovacao_id` IS NULL";
        $sql->p($q);
        $sql->e();
        $data = $sql->fAll('FETCH_NUM');
        $seq = 0;
        echo '<pre>';
        echo $q;
        echo '<pre>';
        echo '<table>';
        echo 
            '<tr>'
                , '<td>', 'seq', '</td>'
                , '<td>', 'fechado', '</td>'
                , '<td>', 'fec orcamento', '</td>'
                , '<td>', 'fec renovocao', '</td>'
                , '<td>', 'orcamento fechado', '</td>'
                , '<td>', 'orcamento id', '</td>'
                , '<td>', 'orcamento status', '</td>'
                , '<td>', 'fechado status', '</td>'
                , '<td>', 'orcamento locador', '</td>'
                , '<td>', 'fechado locador', '</td>'
                , '<td>', 'orcamento adm', '</td>'
                , '<td>', 'fechado adm', '</td>'
                , '<td>', 'orcamento inicio', '</td>'
                , '<td>', 'fechado inicio', '</td>'
                , '<td>', 'orcamento validade', '</td>'
                , '<td>', 'fechado validade', '</td>'
            , '</tr>';
        foreach ($data as $d){
            $seq++;
            echo '<tr>'
                , '<td>', $seq, '</td>'
                , '<td>', $d[0], '</td>'
                , '<td>', $d[1], '</td>'
                , '<td>', $d[2], '</td>'
                , '<td>', $d[3], '</td>'
                , '<td>', $d[4], '</td>'
                , '<td>', $d[5], '</td>'
                , '<td>', $d[6], '</td>'
                , '<td>', $d[7], '</td>'
                , '<td>', $d[8], '</td>'
                , '<td>', $d[9], '</td>'
                , '<td>', $d[10], '</td>'
                , '<td>', $d[11], '</td>'
                , '<td>', $d[12], '</td>'
                , '<td>', $d[13], '</td>'
                , '<td>', $d[14], '</td>'
            ,'</tr>';                
        }
        echo '</table>';
    }

}
