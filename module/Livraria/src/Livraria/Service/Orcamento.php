<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use LivrariaAdmin\Fpdf\ImprimirSeguro;

/**
 * Orcamento
 * Faz o CRUD da tabela Orcamento no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Orcamento extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaImovel;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Orcamento";
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
        //parent::logForDelete($id);
        //serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento'] = $id;
        $dataLog['tabela'] = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Orçamento excluido com numero ' . $this->data['id'];
        $dataLog['dePara'] = (isset($data['motivoNaoFechou'])) ? $data['motivoNaoFechou'] : '';
        $log->insert($dataLog);
    }

    /**
     * @ORM\OneToOne(targetEntity="Locador")
     * @ORM\OneToOne(targetEntity="Locatario")
     * @ORM\OneToOne(targetEntity="Imovel")
     * @ORM\OneToOne(targetEntity="Taxa")
     * @ORM\OneToOne(targetEntity="Atividade")
     * @ORM\OneToOne(targetEntity="Seguradora")
     * @ORM\OneToOne(targetEntity="Administradora")
     * @ORM\OneToOne(targetEntity="User")
     * @var \DateTime $inicio
     * @var \DateTime $fim
     * @var \DateTime $criadoEm
     * @var \DateTime $canceladoEm
     * @var \DateTime $alteradoEm
     
     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('user', 'Livraria\Entity\User');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('criadoEm');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');
        
        $locadorResul = $this->setLocador();
        if($locadorResul !== TRUE){
            return $locadorResul;
        }
        $imovelResul = $this->setImovel();
        if($imovelResul !== TRUE){
            return $imovelResul;
        }
        $locatarioResul = $this->setLocatario();
        if($locatarioResul !== TRUE){
            return $locatarioResul;
        }
        $AtividadeResul = $this->setAtividade();
        if($AtividadeResul !== TRUE){
            return $AtividadeResul;
        }
        return TRUE;
    }
    
    /**
     * Valida se atividade escolhida esta ativa
     * Caso não retorna o erro 
     * @return boolean | array
     */
    public function setAtividade(){
        $this->idToEntity('atividade', 'Livraria\Entity\Atividade');
        if($this->data['atividade']->getStatus() != "A"){
            return ['Atividade escolhida esta cancelada! Por Favor entre em contato com a Vila Velha.'];
        }
        return TRUE;
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */   
    public function insert(array $data, $param='') { 
        $this->data = $data;
        
        if (empty($this->data['user']))
            $this->data['user'] = $this->getIdentidade()->getId();
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
       
        //Comissão da Administradora padrão
        if(empty($this->data['comissao'])){
            $this->data['comissaoEnt'] = $this->em
                ->getRepository('Livraria\Entity\Comissao')
                ->findComissaoVigente($this->data['administradora']->getId(),  $this->data['criadoEm']);
            $this->data['comissao'] = $this->data['comissaoEnt']->floatToStr('comissao');
        }else{
            $this->idToEntity('comissaoEnt', 'Livraria\Entity\Comissao');
        }
        
        $this->data['taxa'] = $this->em
            ->getRepository('Livraria\Entity\Taxa')
            ->findTaxaVigente($this->data['seguradora']->getId(), $this->data['atividade']->getId(), $this->data['criadoEm']);

        if(!$this->data['taxa'])
            return ['Taxas para esta classe e atividade vigente nao encontrada!!!'];
        
        $this->data['multiplosMinimos'] = $this->em
            ->getRepository('Livraria\Entity\MultiplosMinimos')
            ->findMultMinVigente($this->data['seguradora']->getId(), $this->data['criadoEm']);
        
        $resul = $this->CalculaPremio();
        
        $this->data['codFechado'] = '0';
        $this->data['status'] = 'A';
        
        
        if($param == 'OnlyCalc'){
            return ['Calculado com Sucesso !!!']; 
        }
        
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
        //parent::logForNew('orcamento');
        //serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'new';
        $dataLog['mensagem']   = 'Novo orçamento com numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }
    
    /**
     * Pegando o servico endereco e inserindo ou referenciando o imovel
     * @return boolean | array
     */
    public function setImovel(){
        //Se tem id busca no banco
        if(!empty($this->data['imovel'])){
            //Verificar se esta cadastrando um novo apartamento
            $this->idToEntity('imovel', 'Livraria\Entity\Imovel');
            if(($this->data['imovel']->getBloco() == $this->data['bloco']) AND
               ($this->data['imovel']->getApto()   == $this->data['apto'])){
                return TRUE;
            }
            $this->data['imovel'] = '';
        }
        //Se id ta vazio ou apto ou bloco e diferente tentar cadastrar ou encontrar imovel ja cadastrado
        $serviceImovel = new Imovel($this->em);
        $resul = $serviceImovel->insert(array_merge($this->data,['status'=>'A']));
        if(is_array($resul)){
            if(($resul[0] == "Já existe um imovel neste endereço  registro:") OR
               ($resul[0] == "Já existe um apto neste endereço  registro:")){
                $this->data['imovel'] = $resul[1];
                $this->idToReference('imovel', 'Livraria\Entity\Imovel');
            }else{
                return array_merge(['Erro ao tentar incluir imovel no BD.'],$resul);
            }
        }else{
            $this->data['imovel'] = $serviceImovel->getEntity();
        }
        return TRUE;
    }
    
    /**
     * Faz um referencia ou tenta incluir o locador no BD
     * Caso não consiga retorna os erros 
     * @return boolean | array
     */
    public function setLocador(){
        if(empty($this->data['locador'])){
            $serviceLocador = new Locador($this->em);
            $data['id'] = '';
            $data['administradora'] = $this->data['administradora'];
            $data['nome'] = $this->data['locadorNome'];
            $data['tipo'] = $this->data['tipoLoc'];
            $data['cpf'] = $this->data['cpfLoc'];
            $data['cnpj'] = $this->data['cnpjLoc'];
            $data['status'] = 'A';
            $resul = $serviceLocador->insert($data);
            if($resul === TRUE){
                $this->data['locador'] = $serviceLocador->getEntity();
            }else{
                if(substr($resul[0], 0, 15) == 'Já existe esse'){
                    $this->data['locador'] = $resul[1];
                    $this->idToReference('locador', 'Livraria\Entity\Locador');
                }else{
                    return $resul;
                }
            }
        }else{
            $this->idToReference('locador', 'Livraria\Entity\Locador');
        }
        return TRUE;
    }
    
    /**
     * Faz um referencia ou tenta incluir o locatario no BD
     * Caso não consiga retorna os erros 
     * @return boolean | array
     */
    public function setLocatario(){
        if(empty($this->data['locatario'])){
            $serviceLocatario = new Locatario($this->em);
            $data['id'] = '';
            $data['nome'] = $this->data['locatarioNome'];
            $data['tipo'] = $this->data['tipo'];
            $data['cpf'] = $this->data['cpf'];
            $data['cnpj'] = $this->data['cnpj'];
            $data['status'] = 'A';
            $resul = $serviceLocatario->insert($data);
            if($resul === TRUE){
                $this->data['locatario'] = $serviceLocatario->getEntity();
            }else{
                if(substr($resul[0], 0, 13) == 'Já existe um'){
                    $this->data['locatario'] = $resul[1];
                    $this->idToReference('locatario', 'Livraria\Entity\Locatario');
                }else{
                    var_dump(substr($resul[0], 0, 13));
                    return $resul;
                }
            }
        }else{
            $this->idToReference('locatario', 'Livraria\Entity\Locatario');
        }
        return TRUE;
    }

    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data,$param='') {
        $this->data = $data;
        
        if($data['status'] != 'A')
            return ['Este orçamento não pode ser editado!','Pois já esta finalizado!!'];
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
        
        $this->idToEntity('taxa', 'Livraria\Entity\Taxa');
        
        $this->idToEntity('comissaoEnt', 'Livraria\Entity\Comissao');
        
        $this->idToEntity('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');

        $resul = $this->CalculaPremio();
        
        $this->idToReference('user', 'Livraria\Entity\User');
        
        if($param == 'OnlyCalc'){
            return ['Calculado com Sucesso !!!']; 
        }
        
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
        //parent::logForEdit('orcamento');
        //serviço logorcamento
        if(empty($this->dePara)) 
            return ;
        
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']  = $this->data['id']; 
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'edit';
        $dataLog['mensagem']   = 'Alterou orçamento de numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
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
        if(empty($this->data['imovel']))
            return array('Um imovel deve ser selecionado!');
        
        $inicio = $this->data['inicio'];
        if((empty($inicio)) or ($inicio < (new \DateTime('01/01/2000'))))
            return array('A data deve ser preenchida corretamente!');
            
        $filtro['imovel'] = $this->data['imovel']->getId();
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($inicio <= $entity->getFim('obj'))){
                    if($entity->getStatus() == "A"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia ' . $entity->getInicio() . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                    if($entity->getStatus() == "F"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia ' . $entity->getInicio() . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
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
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Orcamento $ent
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
        $this->dePara .= $this->diffAfterBefore('Incêndio', $ent->floatToStr('incendio'), $this->strToFloat($this->data['incendio']));
        $this->dePara .= $this->diffAfterBefore('Cobertura aluguel', $ent->floatToStr('aluguel'), $this->strToFloat($this->data['aluguel']));
        $this->dePara .= $this->diffAfterBefore('Cobertura eletrico', $ent->floatToStr('eletrico'), $this->strToFloat($this->data['eletrico']));
        $this->dePara .= $this->diffAfterBefore('Cobertura vendaval', $ent->floatToStr('vendaval'), $this->strToFloat($this->data['vendaval']));
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
        $this->dePara .= $this->diffAfterBefore('codFechado', $ent->getCodFechado(), $this->data['codFechado']);
        $this->dePara .= $this->diffAfterBefore('mesNiver', $ent->getMesNiver(), $this->data['mesNiver']);
        //Juntar as alterações no imovel se houver
        $this->dePara .= $this->deParaImovel;
    }
    
    /**
     * Gerar o pdf do Orçamento inserido
     * @param strin $id
     * @return PDF file 
     */
    public function getPdfOrcamento($id){
        //Carregar Entity Fechados
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado um orçamento com esse numero!!!'];
        }
        
        $pdf = new ImprimirSeguro();
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
        $vlr = [
            $seg->floatToStr('incendio'),
            $seg->floatToStr('cobIncendio'),
            $seg->floatToStr('eletrico'),
            $seg->floatToStr('cobEletrico'),
            $seg->floatToStr('aluguel'),
            $seg->floatToStr('cobAluguel'),
            $seg->floatToStr('vendaval'),
            $seg->floatToStr('cobVendaval'),
        ];
        switch ($seg->getTipoCobertura()) {
            case '01':
                $label = ' (Prédio)';
                break;
            case '02':
                $label = ' (Conteúdo + prédio)';
                break;
            case '03':
                $label = ' (Conteúdo)';
                break;
            default:
                $label = '';
                break;
        }
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
    
}
