<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;
use Livraria\Service\Mysql;

/**
 * Importar
 * Salvar arquivo, Ler arquivo array, 
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Importar extends AbstractService{
    
    /**
     * Objeto com SessionContainer
     * @var object 
     */
    protected $sc;
    /**
     *
     * @var \Livraria\Service\Mysql
     */
    protected $sel;
    protected $data;
    protected $erro;
    protected $serLoct;
    protected $repLoct;
    protected $serLocd;
    protected $repLocd;
    protected $repFechado;
    protected $repOrcamento;    
    protected $entitys;    
    protected $estados;
    protected $date;
    protected $assit24;
    
    /**
     *
     * ferramentas para Atividade
     * @var \Livraria\Entity\AtividadeRepository 
     */
    protected $repAtivid;
    
    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->date = new \DateTime() ;
    }
    
    /**
     * Retorna Instancia do Session Container
     * @return object 
     */
    public function getSc(){
        if($this->sc)
            return $this->sc;
        $this->sc = new SessionContainer("LivrariaAdmin");
        return $this->sc;
    }
    
    /**
     * Salvar o arquivo na pasta work do sistema
     * Grava na sessão o caminho para o arquivo salvo ou false caso de erro
     * @param array $file
     * @return nothing
     */
    public function upload($file){
        //Verificando a existencia do arquivo
        if(!file($file['content']['tmp_name'])){
            echo 'arquivo não encontrado!!';
            $this->getSc()->file = FALSE;            
            return;
        }
        $fileTemp = $file['content']['tmp_name'];
        $fileName = $file['content']['name'];
        $baseDir = '/var/www/zf2vv/data/work/';
        if(move_uploaded_file($fileTemp, $baseDir . $fileName)){
            $this->getSc()->file = $baseDir . $fileName;
            $obs = 'Fez upload do arquivo:<br>';
            $obs .= 'Nome do arquivo = '. $fileName .'<br>';
            $this->logForSis('data/work', '', 'Importar', 'Upload', $obs);
        }else{
            $this->getSc()->file = FALSE;            
        }
    }
    
    /**
     * Recebe o caminho do arquivo pela sessao
     * Lê o arquivo csv convertendo em array de dados
     * @return boolean | array
     */    
    public function fileToArray(){
        $file = $this->getSc()->file;
        if(!$file){
            return FALSE;
        }
        $arrayFile = file($file);
        foreach ($arrayFile as $value) {
            $csvToArray[] = $this->csvToArray(utf8_encode($value));            
        }
        return $csvToArray;
    }
    
    /**
     * Recebe uma string e converte em array pelo separador ponto e virgula
     * @param string $str
     * @return array
     */
    public function csvToArray($str){
        $linha = str_replace("\r\n","",trim($str));
        return explode(';',  $linha);
    }
    
    /**
     * ferramentas para Atividade
     * @return \Livraria\Entity\AtividadeRepository
     */
    public function rpAti() {
        if (is_null($var)) {
            $this->repAtivid = $this->em->getRepository('Livraria\Entity\Atividade');
        }
        return $this->repAtivid;
    }
    
    public function importar(){
        $file = $this->getSc()->file;
        if(!$file){
            return FALSE;
        }
        echo 'Inicio !!' , date('d/m/Y - h:i'), ' <br>';
        $lello = $this->em->find('Livraria\Entity\Administradora',3234);
        $this->assit24 = $lello->getAssist24();
        $arrayFile = file($file);
        $service = new Orcamento($this->em);
        // ferramentas para locador
        /* @var $this->repLocd \Livraria\Entity\LocadorRepository */
        $this->repLocd = $this->em->getRepository('Livraria\Entity\Locador');
        $this->serLocd = new Locador($this->em);
        $this->serLocd->notValidateNew();
           
        // montar array de com locadores
        $loc = $this->repLocd->findBy(['administradora' => '3234']);
        /* @var $ent \Livraria\Entity\Locador */
        foreach ($loc as $ent) {
            $this->locador[$ent->getId()][0] = $ent->getNome();
            if ($ent->getTipo() == 'fisica') {
                $this->locador[$ent->getId()][1] = $this->cleanDocFomatacao($ent->getCpf());
            }else{
                $this->locador[$ent->getId()][1] = $this->cleanDocFomatacao($ent->getCnpj());
            }
//            $this->locador[$ent->getId()][2] = $ent->getAdministradora()->getId();
        } 
        unset($loc);
        
        // ferramentas para locatario
        /* @var $this->repLoct \Livraria\Entity\LocatarioRepository */
        $this->repLoct = $this->em->getRepository('Livraria\Entity\Locatario');
        $this->serLoct = new Locatario($this->em);
        $this->serLoct->notValidateNew();
        $this->listLocat = $this->repLoct->getListaLocatario();
        
        // ferramentas para imovel
        $this->repImovel = $this->em->getRepository('Livraria\Entity\Imovel');
        $this->estados  = $this->em->getRepository('Livraria\Entity\Estado')->fetchPairs();
        
        // ferramentas para Atividade
        $this->repOrcamento = $this->em->getRepository('Livraria\Entity\Orcamento');
        $this->repFechado = $this->em->getRepository('Livraria\Entity\Fechados');
        $dataResul = [];
        $reg = 0;
        $clean = $prox = 50;
        foreach ($arrayFile as $key => $value) {
            if($key == 0){
                $dataResul[] = $this->csvToArray(utf8_encode($value)); 
                $this->montaIndice($this->csvToArray(utf8_encode($value)));
                continue;
            }
            $registro = $this->csvToArray(utf8_encode($value)); 
            if($registro[0] != "01"){
                continue;
            }            
            $dataResul[] = $registro; 
            $this->montaData($registro);
            if(!empty($this->erro)){
                var_dump($this->erro);
                continue;
            }
            $premio      = number_format($registro[23] / 100, 2, '.', '') ;
            $this->preValida();
            $insertResul = $service->insert($this->data);
            // apos inserir com sucesso verificar se o valor do premio é igual ao calculado
            if($insertResul[0] === true){
                $premioCalc = $service->getEntity()->getPremioTotal();
                $diferenca = (float)$premio - $premioCalc ;
                if($diferenca < -0.10 OR $diferenca > 0.10){
                    $insertResul[0] = "Alerta existe uma diferença no valor do premio por favor verificar no orçamento ". $insertResul[1];
                    $insertResul[1] = "Valor da Importação ". number_format($premio, 2, ',', '.') . ' Valor calculado '. number_format($premioCalc, 2, ',', '.') . ' Diferença '. number_format($diferenca, 2, ',', '.');
                }
                $dataResul[]['result'] = $insertResul;
            }else{
                $dataResul[]['result'] = $insertResul;
            }
            $reg++;
            if($reg > $clean){
                $this->em->clear();
                echo 'importando ', $prox, ' Aguarde !!' , date('d/m/Y - h:i'), ' <br>';
                @flush();
                $clean += $prox;
            }
        }
        echo 'Fim !!' , date('d/m/Y - h:i'), ' <br>';
        $this->getSc()->importacaoResul = $dataResul;
        $obs = 'Gerou os Orçamentos:<br>';
        $obs .= 'Quantidade de registro lidos = '. $reg .'.<br>';
        $this->logForSis('orcamentos', '', 'importar', 'importar', $obs);
        return $dataResul;
    }
    
    public function preValida(){
        if(empty($this->data['refImovel'])){
            return;
        }
        // Lello validar pela referencia do imovel.
        $filtro['refImovel'] = $this->data['refImovel'];            
        $filtro['administradora'] = $this->data['administradora'];            
        $this->entitys = $this->repOrcamento->findBy($filtro);
        $this->dateToObject('inicio');
        $inicio = $this->data['inicio'];
        $flush = false;
        foreach ($this->entitys as $key => $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($inicio < $entity->getFim('obj'))){ // Data de inicio em conflito com algum perido existente
                    if($entity->getLocatarioNome() != $this->data['locatarioNome']){ // locatarios diferentes
                        switch ($entity->getStatus()) {
                            case "R":
                            case "A":
                                    $entity->setStatus('C');
                                    $this->em->persist($this->entitys[$key]);
                                    $data['motivoNaoFechou'] = 'Trocou de locatario do Sr(a) ' . $entity->getLocatarioNome() . ' para Sr(a) ' . $this->data['locatarioNome'];
                                    $this->logForCancelOrcamento($entity->getId(),$data,'orca');
                                    $flush = true;
                                break;
                            case "F":
                                    $fechado = $this->repFechado->find($entity->getFechadoId());
                                    if($fechado){
                                        $fechado->setStatus('C');
                                        $this->em->persist($fechado);
                                    }                                
                                    $entity->setStatus('C');
                                    $this->em->persist($this->entitys[$key]);
                                    $data['motivoNaoFechou']  = 'Excluido porque seu registro de fechado foi excluido fechado numero= ' . $entity->getId();
                                    $data['motivoNaoFechou'] .= 'Trocou de locatario do Sr(a) ' . $entity->getLocatarioNome() . ' para Sr(a) ' . $this->data['locatarioNome'];
                                    $this->logForCancelOrcamento($entity->getId(),$data,'orca');
                                    $this->logForCancelFechado($entity->getFechadoId(),$data);
                                    $flush = true;
                                break;
                        }
                    }
                }
            }
        }
        if($flush){
            $this->em->flush();
        }
    }
    
    /**
     * Registra a exclusão do registro com seu motivo.
     * @param type $id
     * @param type $data
     */
    public function logForCancelFechado($id,$data) {
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
     * Registra a exclusão do registro com seu motivo.
     * @param type $id
     * @param type $data
     */
    public function logForCancelOrcamento($id,$data, $orcaReno) {
        //serviço logorcamento
        if($orcaReno == 'reno'){
            $log = new LogRenovacao($this->em);
            $dataLog['renovacao'] = $id;
        }else{
            $log = new LogOrcamento($this->em);
            $dataLog['orcamento'] = $id;
        }
        $dataLog['tabela'] = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Orçamento excluido com numero ' . $id;
        if(!empty($data['motivoNaoFechou'])){
            $dataLog['dePara'] = $data['motivoNaoFechou'] ;
        }
        if(!empty($data['motivoNaoFechou2'])){
            $dataLog['dePara'] = $data['motivoNaoFechou2'] ;
        }
        $log->insert($dataLog);
    }
    
    public function getImpResul(){
        return $this->getSc()->importacaoResul;
    }

    public function montaIndice($array) {
        return;
    }

    public function montaData($array) {
        $this->erro = [];
        $this->data = [];
        if(sizeof($array) < 20){
            $this->erro[] = 'Quantidade de campos menor que 20 campos!';
            return;
        }
        if($array[0] != "01"){
            $this->erro[] = 'tipo de registro esperado é 01 e veio o ' . $array[0];
            return;            
        }
        $this->data['id'] = '' ;
        $this->data['codano'] = '' ;
        $this->data['administradora'] = '3234' ;
        
        $this->buscaLocador($array[5], $array[6]);        
        $this->buscaLocatario($array[7], $array[8]); 
        $this->buscaImovel($array[9], $array[10], $array[4], $array[11], $array[12], $array[13], $array[14]);
        
        $this->data['inicio'] = substr($array[18], 0, 2) . '/' . substr($array[18], 2, 2) . '/' . substr($array[18], 4, 4);
        $this->data['fim']    = substr($array[19], 0, 2) . '/' . substr($array[19], 2, 2) . '/' . substr($array[19], 4, 4);
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
        if(trim($array[2]) == 'R'){
            $this->data['status'] = 'R' ;              
            $this->data['orcaReno'] = 'reno' ;              
            $this->data['fechadoOrigemId'] = $this->buscaFechadoAnterior() ;
        }else{
            $this->data['status'] = 'A' ;                    
            $this->data['orcaReno'] = 'orca' ;              
        }
        
        $this->data['seguradora'] = '2' ;
        
        if($this->data['seguradora'] == '3'){
            if($array[15] == '1'){
                $this->data['ocupacao'] = '02';
                $this->data['atividade'] = '487' ;
                $this->data['atividadeDesc'] = 'RESIDENCIAL' ;
                $this->data['comissao'] = '80,00' ;
                $this->data['tipoCobertura'] = '02' ;
                $this->data['conteudo'] = number_format($array[20] / 100, 2, ',', '.') ;
                $this->data['incendio'] = '' ;
            }else{
                $this->data['ocupacao'] = '01';
                $this->data['atividade'] = '488' ;
                $this->data['atividadeDesc'] = 'COMERCIAL' ;            
                $this->data['comissao'] = '57,00' ;
                $this->data['tipoCobertura'] = '01' ;
                $this->data['conteudo'] = '' ;
                $this->data['incendio'] = number_format($array[20] / 100, 2, ',', '.') ; ;
            }
        }else{
            /* @var $entAtiv \Livraria\Entity\Atividade */
            $entAtiv = $this->rpAti()->findDescricao(trim($array[16]));
            if($entAtiv){
                $this->data['atividade'] = $entAtiv->getId() ;
                $this->data['atividadeDesc'] = $entAtiv->getDescricao() ;
                $this->data['comissao'] = '69,99' ;
                if($array[15] == '1'){
                    $this->data['ocupacao'] = '02';
                    $this->data['tipoCobertura'] = '02' ;
                    $this->data['conteudo'] = number_format($array[20] / 100, 2, ',', '.') ;
                    $this->data['incendio'] = '' ;
                }else{
                    $this->data['ocupacao'] = '01';
                    $this->data['tipoCobertura'] = '01' ;
                    $this->data['conteudo'] = '' ;
                    $this->data['incendio'] = number_format($array[20] / 100, 2, ',', '.') ; ;
                }           
                
            }else{
                throw new \Exception("Atividade não encontrada " . $array[16]);
            }
        }
        $this->data['observacao'] = 'Ref Imovel ' . trim($array[4]) ;        
        $this->data['observacao'] .=  '| Ocupação ' . trim($array[16]) ;        
        $this->data['observacao'] .=  '| ' . trim($array[17]) ;        
        $this->data['observacao'] .=  '| Seq. ' . trim($array[24]) ;        
        
        $this->data['aluguel']  = number_format($array[21] / 100, 2, ',', '.') ;
        $this->data['eletrico'] = number_format($array[22] / 100, 2, ',', '.') ;
        $this->data['valorAluguel'] = number_format($array[21] /100 / 6, 2, ',', '.') ;
        if($array[22] == 0){
            $this->data['eletrico'] =  'Não Calcular';
        }
        $this->data['vendaval'] = 'Não Calcular' ;
        $this->data['premioTotal'] = '' ;
        
        
        $this->data['seguroEmNome'] = '02' ;
        $this->data['validade'] = 'anual' ;
        $this->data['formaPagto'] = '01' ;
        $this->data['criadoEm'] = $this->date ;
        
        $this->data['comissaoEnt'] = '13' ;
        $this->data['taxa'] = '' ;
        $this->data['canceladoEm'] = '' ;
        $this->data['numeroParcela'] = '01' ;
        $this->data['premio'] = '' ;
        $this->data['premioLiquido'] = '' ;
        $this->data['fechadoId'] = '' ;
        $this->data['taxaIof'] = '' ;
        $this->data['user'] = '' ;
        $this->data['multiplosMinimos'] = '' ;
        $this->data['proposta'] = '' ;
        $this->data['mesNiver'] = '' ;
        $this->data['codigoGerente'] = '' ;
        $this->data['mensalSeq'] = '0' ;
        $this->data['assist24'] = $this->assit24 ;
        
    }
    
    public function buscaFechadoAnterior(){
        if(empty($this->data['refImovel'])){
            return 1;
        }
        $entity = $this->repFechado->findBy(['refImovel' => $this->data['refImovel'], 'fim' => $this->data['inicio']]);
        if($entity){
            return $entity[0]->getId();
        }else{
            return 1;
        }
    }
    
    /**
     * Script para remover acentos e caracteres especiais:
     * @param string $s
     * @return string Sem Acentos
     */
    public function rmAcentos($s){
        //$palavra = ereg_replace("[^a-zA-Z0-9]", "", strtr($s, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC"));
        $palavra = strtr(strtoupper($s), "ÁÀÃÂÉÊÍÓÔÕÚÜÇ", "AAAAEEIOOOUUC");
        return $palavra;
    }

//    public function buscaImovel($cep, $rua, $ref){
    public function buscaImovel($cep, $bairro, $ref, $tipo, $logradouro, $num, $compl){
        /*
        $this->data['imovel'] = '2553' ;
        $this->data['imovelTel'] = '' ;
        $this->data['imovelStatus'] = 'A' ;
        $this->data['idEnde'] = '21672' ;
        $this->data['cep'] = '4602000' ;
        $this->data['rua'] = 'R BARAO DO TRIUNFO' ;
        $this->data['numero'] = '277' ;
        $this->data['apto'] = '' ;
        $this->data['bloco'] = '' ;
        $this->data['compl'] = '' ;
        $this->data['bairro'] = '259' ;
        $this->data['bairroDesc'] = 'Brooklin Paulista' ;
        $this->data['cidade'] = '99' ;
        $this->data['cidadeDesc'] = 'São Paulo' ;
        $this->data['estado'] = '27' ;
        $this->data['pais'] = '1' ;
         */        
        // Colocar CEP para validação do seguro
        $this->data['cep'] = str_pad($cep, 8, '0', STR_PAD_LEFT);
        //procurar imovel pelo referencia
        if(empty($ref)){
            $this->erro[] = 'Falha referencia do imovel obrigatorio verifique os dados por favor!';
            return;
        }
        $ref = trim($ref);
        $this->data['refImovel'] = $ref ;       
        // Procura um igual na base importa
        $entity = false;
        if(!empty($this->data['locador'])){
            $this->gSel()->p('Select id, ref_imovel from imovel where locador_id=?');
            $this->gSel()->e([$this->data['locador']]);        
            $r = $this->gSel()->fAll();
            $cleanRef = $this->cleanRefFormatacao($ref);
            foreach ($r as $reg) {
                if($cleanRef == $this->cleanRefFormatacao($reg['ref_imovel'])){
                    $this->data['imovel'] = $reg['id'];
                    $entity = $this->repImovel->find($reg['id']);
                    break;
                }
            }           
        } 
        
        $separado = $this->desmontaEnd($bairro, $tipo, $logradouro, $num, $compl);

        if($entity){
            // Verificar se imovel encontrado é de fato igual ao do arquivo caso contrario atualizar
            //echo "<p>", $this->rmAcentos($separado['rua']) , '<br>', $this->rmAcentos($entity->getRua()) , '</p>'; 
            if(
            $this->rmAcentos($separado['rua']) != $this->rmAcentos($entity->getRua()) 
            OR $separado['numero'] != $entity->getNumero()
            OR (isset($separado['apto']) AND $separado['apto'] != $entity->getApto())
            OR (isset($separado['bloco']) AND $separado['bloco'] != $entity->getBloco())
            OR $separado['compl'] != $entity->getCompl()
            ){
                $entity->setRua($separado['rua']);
                $entity->getEndereco()->setRua($separado['rua']);
                $entity->setNumero($separado['numero']);
                $entity->getEndereco()->setNumero($separado['numero']);
                $entity->setCep(str_pad($cep, 8, '0', STR_PAD_LEFT));
                $entity->setRefImovel($ref);
                $entity->getEndereco()->setCep(str_pad($cep, 8, '0', STR_PAD_LEFT));
                $entity->setApto(isset($separado['apto']) ? $separado['apto'] : '');
                $entity->setBloco(isset($separado['bloco']) ? $separado['bloco'] : '');
                $entity->setCompl(isset($separado['compl']) ? $separado['compl'] : '');
                $entity->getEndereco()->setCompl(isset($separado['compl']) ? $separado['compl'] : '');
                //echo "<p>Atualizando", $this->rmAcentos($separado['rua']) , '<br>', $this->rmAcentos($entity->getRua()) , '</p>'; 
                $this->em->persist($entity);
                $this->em->flush();
            }
            $this->data['imovel'] = $entity;
            return;
        }
        $this->data['imovel'] = '';
        $this->data['refImovel'] = $ref ;
        $this->data['cep'] = str_pad($cep, 8, '0', STR_PAD_LEFT);
        $this->data['rua'] = $separado['rua'];
        $this->data['numero'] = $separado['numero'];
        if(isset($separado['apto'])){
            $this->data['apto'] = $separado['apto'];
        }else{
            $this->data['apto'] = '';
        }
        if(isset($separado['bloco'])){
            $this->data['bloco'] = $separado['bloco'];
        }else{
            $this->data['bloco'] = '';
        }
        $this->data['compl'] = isset($separado['compl']) ? $separado['compl'] : '';
        $resultado['uf'] = 'SP';       
        //$retorno = @file_get_contents('http://cep.republicavirtual.com.br/web_cep.php?cep='.urlencode($this->data['cep']).'&formato=json'); 
        //if($retorno){ 
        //    $resultado = json_decode($retorno, true);
        //}else{
        //    $this->erro[] = 'Falha na busaca ao CEP (' . $this->data['cep'] . ')verifique os dados por favor!';
        //    return;
        //}
        $this->data['bairro'] = '';
        $this->data['bairroDesc'] = isset($resultado['bairro']) ? $resultado['bairro'] : $bairro;
        $this->data['cidade'] = '';
        $this->data['cidadeDesc'] = isset($resultado['cidade']) ? $resultado['cidade'] : '';
        if (isset($resultado['uf']) AND $resultado['uf'] == 'SP')
            $this->data['estado'] = '27';
        else {
            foreach ($this->estados as $key => $value) {
                if($resultado['uf'] == $value){
                    $this->data['estado'] = $key;
                    break;                    
                }
            }
        }
        $this->data['pais'] = '1';
    }
    
    public function desmontaEnd($bairro, $tipo, $logradouro, $num, $compl){
        $array         = explode(' ', trim($compl)) ;
        $res['compl']  = '';
        $res['rua']    = trim($tipo) . ' ' . trim($logradouro);
        $res['numero'] = trim($num);
        for ($i = 0; $i < count($array); $i++) {
            switch ($array[$i]) {
                case 'AP':
                case 'AP.':
                    $i++;
                    if($array[$i] == 'AP' OR $array[$i] == 'AP.'){
                        $i++;
                    }
                    $res['apto'] = $array[$i];
                    break;
                case 'BL':
                case 'BL.':
                    $i++;
                    if($array[$i] == 'BL' OR $array[$i] == 'BL.'){
                        $i++;
                    }
                    $res['bloco'] = $array[$i];
                    break;
                case 'SL.':
                case 'CS.':
                case 'LJ.':
                case 'BOX':
                case '/':
                    $res['compl'] .= $array[$i];
                    $i++;
                    $res['compl'] .= ' ' . $array[$i];
                    break;
                case '-':
                    // do nothing
                    break;
                default:
                    $res['compl'] .= " " . $array[$i];
                    break;
            }
        }
//var_dump($array); echo '<br>';       
        return $res;
    }
    
    public function cleanRefFormatacao($ref) {
        $clean = preg_replace("/[^0-9]/", "", $ref);
        return (int) $clean ;
    }
    
    public function cleanDocFomatacao($doc, &$tipo='') {
        if(empty($doc)){
            return '';
        }
        $clean = preg_replace("/[^0-9]/", "", $doc);
        $tamanho = strlen($clean);
        if($tamanho <= 11){
            return str_pad($clean, 11, '0', STR_PAD_LEFT);  
        }else{
            $tipo = 'juridica';
            return str_pad($clean, 14, '0', STR_PAD_LEFT);              
        }        
    }

    public function buscaLocador($nome, $doc) {
        /*
        $this->data['locador'] = '3483' ;
        $this->data['locadorNome'] = 'ROBERTO DARIENZO FILHO' ;
        $this->data['tipoLoc'] = 'fisica' ;
        $this->data['cpfLoc'] = '3014131857' ;
        $this->data['cnpjLoc'] = '' ;
         */
        $nome = trim($nome);
        $this->data['locador'] = '' ;
        $this->data['locadorNome'] = $nome ;
        $this->data['tipoLoc'] = 'fisica';  // padrao fisica na limpeza do documento muda para juridica
        $doc = $this->cleanDocFomatacao($doc, $this->data['tipoLoc']);
        $this->data['cpfLoc'] = ($this->data['tipoLoc'] == 'fisica') ? $doc : '' ;
        $this->data['cnpjLoc'] = ($this->data['tipoLoc'] == 'fisica') ? ''   : $doc ;
        
        // Procura um igual na base importa
        if(!empty($doc)){            
            $this->gSel()->p('Select id, nome from locador where (cpf LIKE ? OR cnpj LIKE ?) AND administradoras_id=?');
            $this->gSel()->e([$doc, $doc, $this->data['administradora']]);        
            $r = $this->gSel()->fAll();
            foreach ($r as $reg) {
                if($nome == $reg['nome']){
    //                echo '<p>locador encontrado na pesquisa ' , $nome, ' ',  $reg['nome'], '</p>';   
                    $this->data['locador']      = $reg['id'];
                    return ;                  
                }
                $cur_dist = levenshtein($nome, $reg['nome']);
                if ($cur_dist <= 5) {
    //                echo '<p>locador encontrado na pesquisa ' , $nome, ' ',  $reg['nome'], ' distancia ', $cur_dist, '</p>';   
                    $this->data['locador']      = $reg['id'];
                    return ;                  
                }
            }
        }
        // Compara por aproximação
        $target   = 5;
        $min_dist = 1000;
        $sugestao = "";
        foreach ($this->locador as $key => $loc){
//echo '<p>deb 2 ', $nome, ' ', $doc, ' ', $loc[1], '</p>';die;
            if($doc != $loc[1]){
                continue;
            }
            $cur_dist = levenshtein($nome, $loc[0]);
            if ($cur_dist < $min_dist) {
                $min_dist = $cur_dist;
                $sugestao = $key;
                $nomeTar = $loc[0];
            }
            if($cur_dist <= 2){
                $this->data['locador']      = $sugestao ;
                return;
            }
        }        
//        echo '<pre>';        var_dump($r);         die;
        if($min_dist <= $target){           
//            echo "distancia edicao:" . $nome . " -> " . $nomeTar . " = " . $min_dist . "<br/>", PHP_EOL;
            $this->data['locador']      = $sugestao ;
        }       
        
    }
    
    public function buscaLocatario($nome, $doc) {       
        /*
        $this->data['locatario'] = '6046' ;
        $this->data['locatarioNome'] = 'FREDERICO REBELLO NEHME' ;
        $this->data['tipo'] = 'fisica' ;
        $this->data['cpf'] = '29881473888' ;
        $this->data['cnpj'] = '' ;
         */
        //procurar locatrio pelo nome
        $nome = trim($nome);
        $this->data['locatario'] = '' ;
        $this->data['locatarioNome'] = $nome ;
        $this->data['tipo'] = 'fisica';  // padrao fisica na limpeza do documento muda para juridica
        $doc = $this->cleanDocFomatacao($doc, $this->data['tipo']);
        $this->data['cpf'] =  ($this->data['tipo'] == 'fisica') ? $doc : '' ;
        $this->data['cnpj'] = ($this->data['tipo'] == 'fisica') ? '' : $doc ;
        if(!empty($doc)){            
            $this->gSel()->p('Select id, nome from locatario where (cpf LIKE ? OR cnpj LIKE ?) ;');
            $this->gSel()->e([$doc, $doc]);        
            $r = $this->gSel()->fAll();
            foreach ($r as $reg) {
                if($nome == $reg['nome']){
    //                echo '<p>locatario encontrado na pesquisa ' , $nome, ' ',  $reg['nome'], '</p>';   
                    $this->data['locatario']      = $reg['id'];
                    return ;                  
                }
                $cur_dist = levenshtein($nome, $reg['nome']);
                if ($cur_dist <= 5) {
    //                echo '<p>locatario encontrado na pesquisa ' , $nome, ' ',  $reg['nome'], ' distancia ', $cur_dist, '</p>';   
                    $this->data['locatario']      = $reg['id'];
                    return ;                  
                }
            }
        }
        $target   = 5;
        $min_dist = 1000;
        $sugestao = "";
        foreach ($this->listLocat as $key => $loc){
//echo '<p>deb 2 ', $nome, ' ', $doc, ' ', $loc[1], '</p>';die;
            if($doc != $loc[1]){
                continue;
            }
            $cur_dist = levenshtein($nome, $loc[0]);
            if ($cur_dist < $min_dist) {
                $min_dist = $cur_dist;
                $sugestao = $key;
                $nomeTar = $loc[0];
            }
            if ($cur_dist <= 2) {
                $this->data['locatario']  = $key ;
                return;
            }
        }   
//        echo '<pre>';        var_dump($r);         die;
        if($min_dist <= $target){           
//            echo "distancia edicao:" . $nome . " -> " . $nomeTar . " = " . $min_dist . "<br/>", PHP_EOL;
            $this->data['locatario']  = $sugestao ;
        } 
    }  
    
    public function gSel() {
        if($this->sel){
            return $this->sel;
        }
        $this->sel = new Mysql();
//        $this->sel->ex('SET FOREIGN_KEY_CHECKS=0');
        return $this->sel;
    }
  
}