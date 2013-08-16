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
    
    /**
     * Para gerar o pdf com orçamento
     * @var objet 
     */
    protected $pdf;
    
    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Orcamento";
    }
    
    public function delete($id,$data) {
        if(!parent::delete($id)){
            return ['Erro ao tentar excluir registro!!'];
        }
        $this->logForDelete($id,$data);
        return TRUE;
    }
    
    /**
     * Registra a exclusão do registro com seu motivo.
     * @param type $id
     * @param type $data
     */
    public function logForDelete($id,$data) {
        //serviço logorcamento
        $log = new LogOrcamento($this->em);
        $dataLog['orcamento'] = $id;
        $dataLog['tabela'] = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Orçamento excluido com numero ' . $id;
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
        $locatarioResul = $this->setLocatario();
        if($locatarioResul !== TRUE){
            return $locatarioResul;
        }
        $imovelResul = $this->setImovel();
        if($imovelResul !== TRUE){
            return $imovelResul;
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
    public function insert(array $data, $onlyCalculo=false) { 
        $this->data        = $data;
        if($onlyCalculo)
            $this->setFlush (FALSE);
        
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
                ->findTaxaVigente(
                    $this->data['seguradora']->getId(), 
                        $this->data['atividade']->getId(), 
                        $this->data['criadoEm'], 
                        str_replace(',', '.', $this->data['comissao']),
                        $this->data['validade'],
                        $this->data['tipoCobertura']
        );
        
        if(!$this->data['taxa']){
            var_dump($this->data['seguradora']->getId());
            var_dump($this->data['atividade']->getId());
            var_dump($this->data['criadoEm']);
            var_dump(str_replace(',', '.', $this->data['comissao']));
            var_dump($this->data['validade']);
            var_dump($this->data['tipoCobertura']);
            return ['Taxas para esta classe e atividade vigente nao encontrada!!!'];
        }
        
        $this->data['multiplosMinimos'] = $this->em
            ->getRepository('Livraria\Entity\MultiplosMinimos')
            ->findMultMinVigente($this->data['seguradora']->getId(), $this->data['criadoEm']);
        
        $resul = $this->CalculaPremio();
        
        $this->data['fechadoId'] = '0';
        
        if(!isset($this->data['status']) OR empty($this->data['status'])){
            $this->data['status'] = 'A';
        }
        
        if($onlyCalculo){
            return TRUE; 
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->trocaNaoCalcula();

        if(parent::insert())
            $this->logForNew();
        
        $this->trocaNaoCalcula(true);
        
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
        if(is_object($this->data['imovel'])){
            if($this->data['imovel'] instanceof \Livraria\Entity\Imovel)
                return TRUE;
        }
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
        $resul = $serviceImovel->setFlush($this->getFlush())->insert(array_merge($this->data,['status'=>'A']));
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
        if(is_object($this->data['locador'])){
            if($this->data['locador'] instanceof \Livraria\Entity\Locador)
                return TRUE;
        }
        if(empty($this->data['locador'])){
            $serviceLocador = new Locador($this->em);
            $data['id'] = '';
            $data['administradora'] = $this->data['administradora'];
            $data['nome'] = $this->data['locadorNome'];
            $data['tipo'] = $this->data['tipoLoc'];
            $data['cpf'] = $this->data['cpfLoc'];
            $data['cnpj'] = $this->data['cnpjLoc'];
            $data['status'] = 'A';
            $resul = $serviceLocador->setFlush($this->getFlush())->insert($data);
            if($resul === TRUE){
                $this->data['locador'] = $serviceLocador->getEntity();
            }else{
                if(substr($resul[0], 0, 15) == 'Já existe esse'){
                    $this->data['locador'] = $resul[1];
                    $this->idToReference('locador', 'Livraria\Entity\Locador');
                }else{
                    return array_merge(['Erro ao tentar incluir Locador no BD.'],$resul);
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
        if(is_object($this->data['locatario'])){
            if($this->data['locatario'] instanceof \Livraria\Entity\Locatario)
                return TRUE;
        }
        if(!empty($this->data['locatario'])){
            $this->idToReference('locatario', 'Livraria\Entity\Locatario');
            return TRUE;
        }
        $serviceLocatario = new Locatario($this->em);
        $data['id'] = '';
        $data['nome'] = $this->data['locatarioNome'];
        $data['tipo'] = $this->data['tipo'];
        $data['cpf'] = $this->data['cpf'];
        $data['cnpj'] = $this->data['cnpj'];
        $data['status'] = 'A';
        $resul = $serviceLocatario->setFlush($this->getFlush())->insert($data);
        if($resul === TRUE){
            $this->data['locatario'] = $serviceLocatario->getEntity();
        }else{
            if(substr($resul[0], 0, 13) == 'Já existe um'){
                $this->data['locatario'] = $resul[1];
                $this->idToReference('locatario', 'Livraria\Entity\Locatario');
            }else{
                return array_merge(['Erro ao tentar incluir Locatario no BD.'],$resul);
            }
        }
        return TRUE;
    }

    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data, $onlyCalculo=false) { 
        $this->data        = $data;
        if($onlyCalculo)
            $this->setFlush (FALSE);
        else
            $this->setFlush (TRUE);
        
        if($data['status'] != 'A' AND $data['status'] != 'R')
            return ['Este orçamento não pode ser editado!','Pois já esta finalizado!!'];
        
        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;
        
        $this->calculaVigencia();
        
        $this->data['taxa'] = $this->em
                ->getRepository('Livraria\Entity\Taxa')
                ->findTaxaVigente(
                    $this->data['seguradora']->getId(), 
                        $this->data['atividade']->getId(), 
                        $this->data['criadoEm'], 
                        str_replace(',', '.', $this->data['comissao']),
                        $this->data['validade'],
                        $this->data['tipoCobertura']
        );
        
        if(!$this->data['taxa'])
            return ['Taxas para esta classe e atividade vigente nao encontrada!!!'];
        
        
        $this->idToEntity('comissaoEnt', 'Livraria\Entity\Comissao');
        
        $this->idToEntity('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');

        $this->CalculaPremio();
        
        $this->idToReference('user', 'Livraria\Entity\User');

        if($onlyCalculo){
            return TRUE; 
        }
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->trocaNaoCalcula();
        
        if(parent::update())
            $this->logForEdit();
        
        $this->trocaNaoCalcula(true);
        
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
        if(!$this->isValid){
            return TRUE;
        }
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
                if(($inicio < $entity->getFim('obj'))){
                    if($entity->getStatus() == "A"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                    if($entity->getStatus() == "F"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                    if($entity->getStatus() == "R"){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' <= ' . $entity->getFim();
                        $erro[] = "Já existe um orçamento de renovação com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
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
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio', $ent->floatToStr('incendio',4), $this->strToFloat($this->data['incendio'],4));
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio + Conteudo', $ent->floatToStr('conteudo',4), $this->strToFloat($this->data['conteudo'],4));
        $this->dePara .= $this->diffAfterBefore('Cobertura aluguel', $ent->floatToStr('aluguel',4), $this->strToFloat($this->data['aluguel'],4));
        $this->dePara .= $this->diffAfterBefore('Cobertura eletrico', $ent->floatToStr('eletrico',4), $this->strToFloat($this->data['eletrico'],4));
        $this->dePara .= $this->diffAfterBefore('Cobertura vendaval', $ent->floatToStr('vendaval',4), $this->strToFloat($this->data['vendaval'],4));
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
        $num = 'Orçamento/' . $seg->getId() . '/' . $seg->getCodano();
        $this->pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());
        
        $this->conteudoDaPagina($seg);
        
        $this->sendPdf();
    }
    
    /**
     * Gerar vario pdfs do Orçamento 
     * @param strin $id
     * @param objet $objPdf
     */
    public function getPdfsOrcamento($id, $objPdf=null){
        //Carregar Entity Orçamento
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);
        
        if(!$seg){
            return ['Não foi encontrado um orçamento com esse numero!!!'];
        }
        
        if(!is_null($objPdf))
            $this->pdf = $objPdf;
        
        $num = 'Orçamento/' . $seg->getId() . '/' . $seg->getCodano();
        
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
        $this->pdf->setL12($tot,  $this->strToFloat($seg->getTaxaIof() * 100));
        $par = [
            $seg->floatToStr('premioTotal'),
            $this->strToFloat($seg->getPremioTotal() / 2),
            $this->strToFloat($seg->getPremioTotal() / 3),
            $this->strToFloat($seg->getPremioTotal() / 12)
        ];
        $this->pdf->setL13($par, ($seg->getValidade() =='mensal')?true:false, $seg->getFormaPagto());
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
