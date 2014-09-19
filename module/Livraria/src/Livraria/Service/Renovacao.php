<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use LivrariaAdmin\Fpdf\ImprimirSeguro;

/**
 * Renovacao
 * Faz o CRUD da tabela Renovacao no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Renovacao extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaImovel;
    
    /**
     * Para gerar o pdf com orçamento
     * @var objet 
     */
    protected $pdf;
    
    /**
     * Entity Orcamento
     * @var type
     */
    protected $fechado;
    
    /**
     * Data de agora da classe
     * @var \DateTime
     */
    protected $dataAgora;
    
    /**
     * Repository da Entity ComissaoEnt
     * @var Livraria\Entity\ComissaoRepository
     */
    protected $comissaoEntRep;
    
    /**
     * Repository da Entity Taxa
     * @var Livraria\Entity\TaxaRepository
     */
    protected $taxaEntRep;
    
    /**
     * Repository da Entity MultiplosMinimos
     * @var Livraria\Entity\MultiplosMinimosRepository
     */
    protected $multiplosMinimosRep;
    
    /**
     * Repository da Entity Fechados
     * @var Livraria\Entity\FechadosRepository
     */
    protected $fechadoRep;
    
    /**
     * Repository da Entity Orcamento
     * @var Livraria\Entity\OrcamentoRepository
     */
    protected $orcaRep;
    
    /**
     * Serviço da Entity LogRenovacao
     * @var LogRenovacao
     */
    protected $logRenovacaoServ;
    
    /**
     * Serviço da Entity LogFechados
     * @var LogFechados
     */
    protected $logFechadosServ;
    
    /**
     * Memoriza o primeiro ID de fechados 
     * @var int
     */
    protected $idInicial = 0;
    
    

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Orcamento";
        $this->fechado = "Livraria\Entity\Fechados";
        $this->dataAgora = new \DateTime('now');
    }
    
    public function getDataAgora() {
        return $this->dataAgora;
    }
    
    public function getComissaoEntRep(){
        if(is_null($this->comissaoEntRep)){
            $this->comissaoEntRep = $this->em->getRepository('Livraria\Entity\Comissao');            
        }
        return $this->comissaoEntRep;
    }
    
    public function getTaxaRep(){
        if(is_null($this->taxaEntRep)){
            $this->taxaEntRep = $this->em->getRepository('Livraria\Entity\Taxa');
        }
        return $this->taxaEntRep;
    }
    
    public function getMultiplosMinimosRep(){
        if(is_null($this->multiplosMinimosRep)){
            $this->multiplosMinimosRep = $this->em->getRepository('Livraria\Entity\MultiplosMinimos');
        }
        return $this->multiplosMinimosRep;
    }
    
    public function getFechadoRep() {
        if(is_null($this->fechadoRep)){
            $this->fechadoRep = $this->em->getRepository('Livraria\Entity\Fechados');
        }
        return $this->fechadoRep;        
    }
    
    public function getOrcamentoRep() {
        if(is_null($this->orcaRep)){
            $this->orcaRep = $this->em->getRepository('Livraria\Entity\Orcamento');
        }
        return $this->orcaRep;        
    }
    
    public function getlogRenovacaoServ() {
        if(is_null($this->logRenovacaoServ)){
            $this->logRenovacaoServ = new LogRenovacao($this->em);
            $this->logRenovacaoServ->setFlush($this->getFlush());
        }
        return $this->logRenovacaoServ;
    }
    
    public function getlogFechadosServ() {
        if(is_null($this->logFechadosServ)){
            $this->logFechadosServ = new LogFechados($this->em);
            $this->logFechadosServ->setFlush($this->getFlush());
        }
        return $this->logFechadosServ;
    }
    
    public function delete($id,$data) {
        if(parent::delete($id)){
            $this->logForDelete($id,$data);
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Registra a exclusão do registro com seu motivo.
     * @param type $id
     * @param type $data
     */
    public function logForDelete($id,$data) {
        $log = new LogRenovacao($this->em);
        $dataLog['renovacao'] = $id;
        $dataLog['tabela'] = 'log_renovacao';
        $dataLog['controller'] = 'renovacaos';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Renovação excluida com numero ' . $id;
        $dataLog['dePara'] = (isset($data['motivoNaoFechou'])) ? $data['motivoNaoFechou'] : '';
        $log->insert($dataLog);
    }
    
    public function renovar(\Livraria\Entity\Fechados $fechado){
        $this->fechado = $fechado;
        //Montar dados para tabela de renovacao
        $this->data = $this->fechado->toArray();
        $this->data['fechadoOrigemId'] = $this->data['id'];
        $this->data['id'] = '';
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['status'] = "R";
        if(is_null($this->data['mensalSeq'])){
            $this->data['mensalSeq'] = 0 ;
        }
        if(!is_numeric($this->data['mensalSeq'])){
            echo '<h1>Mensal seq não é numerico erro numero do fechado ', $this->data['fechadoOrigemId'] , '</h1>';
            $this->data['mensalSeq'] = 0 ;
        }
        $this->data['mensalSeq']++;
        $this->data['orcaReno'] = 'reno';
        $this->data['criadoEm'] = $this->getDataAgora();
        $this->data['inicio'] = $this->fechado->getInicio('obj');
        //Nova Vigência
        $resul = $this->recalculaVigencia();
        if ($resul !== TRUE) {
            return $resul;
        }

        //Pegando o locatario atual desse imovel porque o locatario pode ter sido trocado no meio da vigencia do fechado
        //Quando a troca de locatario é apenas atualizado no imovel.
        $this->data['locatario'] = $fechado->getImovel()->getLocatario()->getId();
        $this->data['refImovel'] = $fechado->getImovel()->getRefImovel();

/* Renovação mensal não precisa recalcular seguro mantem o preço do anterior
        //  Novo calculo do premio
        //  Comissão da Administradora padrão
        $this->data['comissaoEnt'] = $this->getComissaoEntRep()
            ->findComissaoVigente($this->data['administradora'],  $this->data['criadoEm']);
        $this->data['comissao'] = $this->data['comissaoEnt']->floatToStr('comissao');
        
        $this->data['taxa'] = $this->getTaxaRep()->findTaxaVigente(
                $this->data['seguradora'], 
                $this->data['atividade'], 
                $this->data['criadoEm'], 
                str_replace(',', '.', $this->data['comissao']), 
                $this->data['validade'], 
                $this->data['tipoCobertura']
        );

        if(!$this->data['taxa'])
            return ['Taxas para esta classe e atividade vigênte nao encontrada!!!'];
        
        $this->data['multiplosMinimos'] = $this->getMultiplosMinimosRep()
            ->findMultMinVigente($this->data['seguradora'],  $this->data['criadoEm']);
        
        //$this->data['administradora'] = $this->fechado->getAdministradora()->getObjeto();
        $resul = $this->CalculaPremio();
*/
        
        $this->data['fechadoId'] = '0';
        
        //Faz inserção do fechado no BD
        if($this->idInicial == 0){
            $this->setFlush(TRUE);
            $resul = $this->insert();
            $this->idInicial = $this->data['id'];
            $this->setFlush(FALSE);
        }else{
            $this->idInicial++;
            $this->data['id'] = $this->idInicial;
            $resul = $this->insert();
        }
        
        if($resul[0] === TRUE){
            //Registra o id do fechado de Orçamento
            $atualizaSeguro = $this->getFechadoRep()->setSeguroRenovado($this->fechado->getId(), $this->data['id'], 'R');
            if($atualizaSeguro){
                $this->registraLogFechado();
            }else{
                echo '<h1>erro ao atualiza seguro fechado com id da renovacao ', $this->fechado->getId();
            }
        }else{
            $this->idInicial--; //caso de erro na inserção decrementa o idinicial que foi incrementado e não usado
        }
        
        return $resul;
    }

    /**
     * Calcula nova vigencia da renovacao periodo mensal ou anual
     * @return boolean | array
     */
    public function recalculaVigencia(){
        if(!isset($this->data['validade'])){
            return ['Campo validade não existe!!'];
        }
        $this->data['codano'] = $this->data['criadoEm']->format('Y');
        
        $interval_spec = ''; 
        if($this->data['validade'] == 'mensal'){
            $interval_spec = 'P1M'; 
        } 
        if($this->data['validade'] == 'anual'){
            $interval_spec = 'P1Y'; 
        } 
        if(empty($interval_spec)){
            $this->data['validade'] = 'anual';
            $interval_spec = 'P1Y'; 
            //return ['Campo validade com valor que não existe na lista!!'];
        }
        
        $this->data['inicio']->add(new \DateInterval($interval_spec)); 
        
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval($interval_spec)); 
        return TRUE;
    }
    
    public function registraLogFechado(){
        //Criar serviço logorcamento
        $dataLog['fechados']   = $this->em->getReference('Livraria\Entity\Fechados', $this->fechado->getId());
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'gerarRenovacao';
        $fechado   = $this->fechado->getId() . '/' . $this->fechado->getCodano();
        $renovacao = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Renovar Seguro(' . $fechado . ') e gerou a renovacao de numero ' . $renovacao ;
        $dataLog['dePara']     = '';
        $this->getlogFechadosServ()->insert($dataLog);
    }
    
    public function saveLogRenovacao($data, $tot, $ok, $ng){
        // Gerar log dos seguros renovados
        $obs = 'Gerou Renovação dos seguros mensal:<br>';
        $obs .= 'Mes = '. $data['mesNiver'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $obs .= 'Total = '. $tot .'<br>';
        $obs .= 'Sucesso = '. $ok .'<br>';
        $obs .= 'Criticados = '. $ng .'<br>';
        $this->logForSis('orcamento', '', 'renovacaos', 'gerarRenovacao', $obs);
    }

        /**
     * Faz referencia para new ou edit dos registros a serem inclusos
     * Converte id de entity em referencia
     * Converte string date em objeto date
     */
    public function setReferences(){
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
        $this->dateToObject('criadoEm');
        $this->dateToObject('alteradoEm');
        return TRUE;
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
        $dataLog['renovacao']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_renovacao';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'gerarRenovacao';
        $fechado = $this->fechado->getId() . '/' . $this->fechado->getCodano();
        $renovacao   = $this->data['id'] . '/' . $this->data['codano'];
        $dataLog['mensagem']   = 'Nova renovação de seguro n ' . $renovacao . ' do seguro fechado n ' . $fechado;
        $dataLog['dePara']     = '';
        $this->getlogRenovacaoServ()->insert($dataLog);
    }

    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data,$param='') {
        $this->data = $data;
        
        if($data['status'] != 'A' AND $this->getIdentidade()->getTipo() != 'admin')
            return ['Esta renovação não pode ser editada!','Pois já esta finalizada!!'];
        
        if($data['status'] == 'C')
            return ['Esta renovacao está cancelada e não pode ser editado!!!'];
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
        
        $this->idToEntity('taxa', 'Livraria\Entity\Taxa');
        
        $this->idToEntity('comissaoEnt', 'Livraria\Entity\Comissao');
        
        $this->idToEntity('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');

        $resul = $this->CalculaPremio();
        
        if($param == 'OnlyCalc'){
            return []; 
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        if(parent::update()){
            $this->logForEdit();
            if($data['status'] == 'F' AND !empty($this->dePara)){
                $srvFechado = new Fechados($this->em);
                $srvFechado->update($this->entityReal,$this->dePara, 'renovacaos');
            }
        }
        
        return ['Salvo com Sucesso !!!']; 
    }
    
    /**
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        if(empty($this->dePara)) 
            return ;
        
        $log = new LogRenovacao($this->em);
        $dataLog['renovacao']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_renovacao';
        $dataLog['controller'] = 'renovacaos' ;
        $dataLog['action']     = 'edit';
        $dataLog['mensagem']   = 'Alterou renovação de numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
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
        $filtro = array();
        if(empty($this->data['imovel']))
            return array('Um imovel deve ser selecionado!');
        
        $inicio = $this->data['inicio'];
        $fim    = $this->data['fim'];
        if((empty($inicio)) or ($inicio < (new \DateTime('01/01/2000'))))
            return array('A data deve ser preenchida corretamente!');
            
        $filtro['imovel'] = $this->data['imovel']->getId();
        $entitys =$this->getOrcamentoRep()->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if(isset($this->data['id']) and ($this->data['id'] == $entity->getId())){
                continue;
            }
            if(($inicio >= $entity->getFim('obj'))){
                continue; // Inicio vigencia maior ou igual ao fim de vig. do seg. da base
            }
            if($fim <= $entity->getInicio('obj')){
                continue; // Fim vigencia menor ou igual ao inicio de vig. do seg. da base
            }
            if($entity->getStatus() == "A"){
                $erro[] = 'Alerta!Vigencia ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe uma novo Orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
            if($entity->getStatus() == "R"){
                $erro[] = 'Alerta!Vigencia ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe uma renovação com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
            if($entity->getStatus() == "F"){
                $erro[] = 'Alerta!Vigencia ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
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
     * @param \Livraria\Entity\Renovacao $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        // 10 referencia a outra entity
        $this->dePara .= $this->diffAfterBefore('Locador', $ent->getLocador(), $this->data['locador']);
        $this->dePara .= $this->diffAfterBefore('Locatario', $ent->getLocatario(), $this->data['locatario']);
        $this->dePara .= $this->diffAfterBefore('Imovel bloco', $ent->getImovel()->getBloco(), $this->data['imovel']->getBloco());
        $this->dePara .= $this->diffAfterBefore('Imovel apto', $ent->getImovel()->getApto(), $this->data['imovel']->getApto());
        $this->dePara .= $this->diffAfterBefore('Imovel tel', $ent->getImovel()->getTel(), $this->data['imovel']->getTel());
        $this->dePara .= $this->diffAfterBefore('Taxa', $ent->getTaxa()->getId(), $this->data['taxa']->getId());
        $this->dePara .= $this->diffAfterBefore('Atividade', $ent->getAtividade(), $this->data['atividade']);
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora(), $this->data['seguradora']);
        $this->dePara .= $this->diffAfterBefore('Administradora', $ent->getAdministradora(), $this->data['administradora']);
        // 9 de valores float
        $this->dePara .= $this->diffAfterBefore('Valor do Aluguel', $ent->floatToStr('valorAluguel'), $this->strToFloat($this->data['valorAluguel']));
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio', $ent->floatToStr('incendio',4), $this->strToFloat($this->data['incendio'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio + Conteudo', $ent->floatToStr('conteudo',4), $this->strToFloat($this->data['conteudo'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura aluguel', $ent->floatToStr('aluguel',4), $this->strToFloat($this->data['aluguel'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura eletrico', $ent->floatToStr('eletrico',4), $this->strToFloat($this->data['eletrico'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura vendaval', $ent->floatToStr('vendaval',4), $this->strToFloat($this->data['vendaval'],'',4));
        $this->dePara .= $this->diffAfterBefore('Premio Liquido', $ent->floatToStr('premioLiquido'), $this->strToFloat($this->data['premioLiquido']));
        $this->dePara .= $this->diffAfterBefore('Premio', $ent->floatToStr('premio'), $this->strToFloat($this->data['premio']));
        $this->dePara .= $this->diffAfterBefore('Premio Total', $ent->floatToStr('premioTotal'), $this->strToFloat($this->data['premioTotal']));
        $this->dePara .= $this->diffAfterBefore('Comissao', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        // 3 de datas
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Data Fim', $ent->getFim(), $this->data['fim']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Cancelado Em', $ent->getCanceladoEm(), $this->data['canceladoEm']->format('d/m/Y'));
        // 15 campos comuns
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Ano Referência', $ent->getCodano(), $this->data['codano']);
        $this->dePara .= $this->diffAfterBefore('locadorNome', $ent->getLocadorNome(), $this->data['locadorNome']);
        $this->dePara .= $this->diffAfterBefore('locatarioNome', $ent->getLocatarioNome(), $this->data['locatarioNome']);
        $this->dePara .= $this->diffAfterBefore('tipoCobertura', $ent->getTipoCobertura(), $this->data['tipoCobertura']);
        $this->dePara .= $this->diffAfterBefore('seguroEmNome', $ent->getSeguroEmNome(), $this->data['seguroEmNome']);
        $this->dePara .= $this->diffAfterBefore('codigoGerente', $ent->getCodigoGerente(), $this->data['codigoGerente']);
        $this->dePara .= $this->diffAfterBefore('refImovel', $ent->getRefImovel(), $this->data['refImovel']);
        $this->dePara .= $this->diffAfterBefore('formaPagto', $ent->getFormaPagto(), $this->data['formaPagto']);
        $this->dePara .= $this->diffAfterBefore('numeroParcela', $ent->getNumeroParcela(), $this->data['numeroParcela']);
        $this->dePara .= $this->diffAfterBefore('observacao', $ent->getObservacao(), $this->data['observacao']);
        if(isset($this->data['gerado']))
            $this->dePara .= $this->diffAfterBefore('gerado', $ent->getGerado(), $this->data['gerado']);
        $this->dePara .= $this->diffAfterBefore('comissao', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        $this->dePara .= $this->diffAfterBefore('Fechado nº', $ent->getFechadoId(), $this->data['fechadoId']);
        $this->dePara .= $this->diffAfterBefore('mesNiver', $ent->getMesNiver(), $this->data['mesNiver']);
        //Juntar as alterações no imovel se houver
        $this->dePara .= $this->deParaImovel;
    }
    
    /**
     * Gerar o pdf do Renovação 
     * @param strin $id
     * @return PDF file 
     */
    public function getPdfRenovacao($id){
        //Carregar Entity Fechados
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado uma renovação com esse numero!!!'];
        }
        
        $num = 'Renovação/' . $seg->getId() . '/' . $seg->getCodano();
        
        $this->pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());
        
        $this->conteudoDaPagina($seg);
        
        $this->sendPdf();
    }
    
    /**
     * Gerar vario pdfs do Orçamento 
     * @param strin $id
     * @param objet $objPdf
     */
    public function getPdfsRenovacao($id, $objPdf=null){
        //Carregar Entity Orçamento
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado uma renovação com esse numero!!!'];
        }
        
        if(!is_null($objPdf))
            $this->pdf = $objPdf;
        
        $num = 'Renovação/' . $seg->getId() . '/' . $seg->getCodano();
        
        if(!is_object($this->pdf))
            $this->pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());
        else
            $this->pdf->novaPagina($num, $seg->getSeguradora()->getId());
        
        $this->conteudoDaPagina($seg);
        
    }
    
    public function conteudoDaPagina($seg){
        $this->pdf->setL1($seg->getRefImovel(), $seg->getInicio());
        $this->pdf->setL2($seg->getAdministradora()->getNome());
        $this->pdf->setL3($seg->getLocatario(), $seg->getLocatario()->getCpf() . $seg->getLocatario()->getCnpj());
        $this->pdf->setL4($seg->getLocador(), $seg->getLocador()->getCpf() . $seg->getLocador()->getCnpj());
        //$this->pdf->setL5($seg->getImovel()->getEnderecoCompleto());
        $this->pdf->setL6($seg->getAtividade());
        $this->pdf->setL7($seg->getObservacao());
        $this->pdf->setL8($seg->floatToStr('valorAluguel'));
        $this->pdf->setL9($seg->getAdministradora()->getId(), '0');
        $this->pdf->setL10();
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
        $this->pdf->setL11($vlr, $label);
        $tot = [
            $seg->floatToStr('premio'),
            $seg->floatToStr('premioLiquido'),
            $this->strToFloat($seg->getPremioLiquido() * $seg->getTaxaIof()),
            $seg->floatToStr('premioTotal')
        ];
        $this->pdf->setL12($tot,  $this->strToFloat($seg->getTaxaIof() * 100), $seg->getValidade());
        $par = [
            $seg->floatToStr('premioTotal'),
            $this->strToFloat($seg->getPremioTotal() / 2),
            $this->strToFloat($seg->getPremioTotal() / 3),
            $this->strToFloat($seg->getPremioTotal() / 12)
        ];
        $this->pdf->setL13($par, ($seg->getValidade() =='mensal')?true:false, $seg->getFormaPagto(),$seg->getAdministradora()->getPropPag());
        $this->pdf->setL14();
        $this->pdf->setObsGeral();
        
    }
    
    public function sendPdf($opc=''){
        switch ($opc) {
            case '':
                $this->pdf->Output();
                break;
            case 'D':
                $this->pdf->Output($this->pdf->getNumSeguro(),'D');
                break;
            default:
                $this->pdf->Output();
                break;
        }
    }
    
    public function getObjectPdf(){
        return $this->pdf;
    }
    
}
