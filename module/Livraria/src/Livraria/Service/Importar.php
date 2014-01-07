<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;

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
    
    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
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
    
    public function importar(){
        $file = $this->getSc()->file;
        if(!$file){
            return FALSE;
        }
        $arrayFile = file($file);
        $service = new Orcamento($this->em);
        // ferramentas para locador
        $this->repLocd = $this->em->getRepository('Livraria\Entity\Locador');
        $this->serLocd = new Locador($this->em);
        $this->serLocd->notValidateNew();
        
        // ferramentas para locatario
        $this->repLoct = $this->em->getRepository('Livraria\Entity\Locatario');
        $this->serLoct = new Locatario($this->em);
        $this->serLoct->notValidateNew();
        
        // ferramentas para imovel
        $this->repImovel = $this->em->getRepository('Livraria\Entity\Imovel');
        $this->estados  = $this->em->getRepository('Livraria\Entity\Estado')->fetchPairs();
        
        // ferramentas para Atividade
        $this->repAtivid = $this->em->getRepository('Livraria\Entity\Atividade');
        // ferramentas para Atividade
        $this->repOrcamento = $this->em->getRepository('Livraria\Entity\Orcamento');
        $this->repFechado = $this->em->getRepository('Livraria\Entity\Fechados');
        $dataResul = [];
        $reg = 0;
        foreach ($arrayFile as $key => $value) {
            if($key == 0){
                $dataResul[] = $this->csvToArray(utf8_encode($value)); 
                $this->montaIndice($this->csvToArray(utf8_encode($value)));
                continue;
            }
            $registro = $this->csvToArray(utf8_encode($value)); 
            if(empty($registro[0])){
                continue;
            }            
            $dataResul[] = $registro; 
            $this->montaData($registro);
            if(!empty($this->erro)){
                var_dump($this->erro);
                continue;
            }
            $premio      = number_format($registro[19] / 100, 2, '.', '') ;
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
            $this->em->clear();
            $reg++;
        }
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
        $this->data['id'] = '' ;
        $this->data['codano'] = '' ;
        $this->data['administradora'] = '3234' ;
        
        $this->buscaLocador($array[5], $array[6]);        
        $this->buscaLocatario($array[7], $array[8]); 
        $this->buscaImovel($array[9], $array[10], $array[4]);
        
        $this->data['inicio'] = substr($array[14], 6, 2) . '/' . substr($array[14], 4, 2) . '/' . substr($array[14], 0, 4);
        $this->data['fim'] = substr($array[15], 6, 2) . '/' . substr($array[15], 4, 2) . '/' . substr($array[15], 0, 4);
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
        if(trim($array[2]) == 'R'){
            $this->data['status'] = 'R' ;              
            $this->data['fechadoOrigemId'] = $this->buscaFechadoAnterior() ;
        }else{
            $this->data['status'] = 'A' ;                    
        }
        
        if($array[11] == '1'){
            $this->data['ocupacao'] = '02';
            $this->data['atividade'] = '487' ;
            $this->data['atividadeDesc'] = 'RESIDENCIAL' ;
            $this->data['comissao'] = '80,00' ;
            $this->data['tipoCobertura'] = '02' ;
            $this->data['conteudo'] = number_format($array[16] / 100, 2, ',', '.') ;
            $this->data['incendio'] = '' ;
        }else{
            $this->data['ocupacao'] = '01';
            $this->data['atividade'] = '488' ;
            $this->data['atividadeDesc'] = 'COMERCIAL' ;            
            $this->data['comissao'] = '57,00' ;
            $this->data['tipoCobertura'] = '01' ;
            $this->data['conteudo'] = '' ;
            $this->data['incendio'] = number_format($array[16] / 100, 2, ',', '.') ; ;
        }
        $this->data['observacao'] = 'Ref Imovel ' . trim($array[4]) ;        
        $this->data['observacao'] .=  '| Ocupação ' . trim($array[12]) ;        
        $this->data['observacao'] .=  '| ' . trim($array[13]) ;        
        $this->data['observacao'] .=  '| Seq. ' . trim($array[20]) ;        
        
        $this->data['aluguel']  = number_format($array[17] / 100, 2, ',', '.') ;
        $this->data['eletrico'] = number_format($array[18] / 100, 2, ',', '.') ;
        $this->data['valorAluguel'] = number_format($array[17] /100 / 6, 2, ',', '.') ;
        $this->data['vendaval'] = '' ;
        $this->data['premioTotal'] = '' ;
        
        
        $this->data['seguradora'] = '3' ;
        $this->data['seguroEmNome'] = '02' ;
        $this->data['validade'] = 'anual' ;
        $this->data['formaPagto'] = '01' ;
        $this->data['criadoEm'] = new \DateTime() ;
        
        $this->data['comissaoEnt'] = '13' ;
        $this->data['taxa'] = '' ;
        $this->data['canceladoEm'] = '' ;
        $this->data['numeroParcela'] = '' ;
        $this->data['premio'] = '' ;
        $this->data['premioLiquido'] = '' ;
        $this->data['fechadoId'] = '' ;
        $this->data['taxaIof'] = '' ;
        $this->data['user'] = '' ;
        $this->data['multiplosMinimos'] = '' ;
        $this->data['proposta'] = '' ;
        $this->data['mesNiver'] = '' ;
        $this->data['codigoGerente'] = '' ;
        
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

    public function buscaImovel($cep, $rua, $ref){
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
        //procurar imovel pelo referencia
        if(empty($ref)){
            $this->erro[] = 'Falha referencia do imovel obrigatorio verifique os dados por favor!';
            return;
        }
        $this->data['refImovel'] = $ref ;
        $entity = $this->repImovel->findOneByRefImovel($ref);
        if($entity){
            $this->data['imovel'] = $entity;
            return;
        }
        $separado = $this->desmontaEnd($rua);
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
                
        $retorno = @file_get_contents('http://cep.republicavirtual.com.br/web_cep.php?cep='.urlencode($this->data['cep']).'&formato=json'); 
        if($retorno){ 
            $resultado = json_decode($retorno, true);
        }else{
            $this->erro[] = 'Falha na busaca ao CEP (' . $this->data['cep'] . ')verifique os dados por favor!';
            return;
        }
        $this->data['bairro'] = '';
        $this->data['bairroDesc'] = isset($resultado['bairro']) ? $resultado['bairro'] : '';
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
    
    public function desmontaEnd($end){
        $r = trim($end);
        $array = explode(' ', $r);
        $res['compl'] = '';
        $res['rua'] = substr($r, 0, strpos($r, ','));
        for ($i = 0; $i < count($array); $i++) {
            switch ($array[$i]) {
                case 'AP.':
                case 'SL.':
                    $i++;
                    if($array[$i] == 'AP'){
                        $i++;
                    }
                    $res['apto'] = $array[$i];
                    break;
                case 'BL.':
                    $i++;
                    $res['bloco'] = $array[$i];
                    break;
                case 'CS.':
                case 'LJ.':
                case 'BOX':
                    $res['compl'] .= $array[$i];
                    $i++;
                    $res['compl'] .= ' ' . $array[$i];
                    break;
                default:
                    if(is_numeric($array[$i]) OR strpos($array[$i], '/')){
                        $res['numero'] = $array[$i];                        
                    }                    
                    break;
            }
        }
        return $res;
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
        $tamanho = strlen($doc);
        $this->data['tipoLoc'] = ($tamanho <= 11)? 'fisica' : 'juridica';
        //Arrumar tamanho da numeração do cpf
        if($this->data['tipoLoc'] == 'fisica' AND $tamanho <> 11){
            $doc = str_pad($doc, 11, '0', STR_PAD_LEFT);  
        }      
        //Arrumar tamanho da numeração do cnpj
        if($this->data['tipoLoc'] == 'juridica' AND $tamanho <> 14){
            $doc = str_pad($doc, 14, '0', STR_PAD_LEFT);  
        }      
        $this->data['cpfLoc'] = ($this->data['tipoLoc'] == 'fisica') ? $doc : '' ;
        $this->data['cnpjLoc'] = ($this->data['tipoLoc'] == 'fisica') ? ''   : $doc ;
        //procurar locador pelo nome
        $lod = $this->repLocd->findOneBy(['nome' => $nome, 'administradora' => $this->data['administradora']]);
        if ($lod) {
            $this->data['locador']      = $lod;
            if($this->data['tipoLoc'] == 'fisica'){
                if($this->data['cpfLoc'] != $lod->formatarCPF_CNPJ($lod->getCpf(), false)){
                    $lod->setCpf($this->data['cpfLoc']);
                    $lod->setTipo($this->data['tipoLoc']);
                }
            }else{
                if($this->data['cnpjLoc'] != $lod->formatarCPF_CNPJ($lod->getCnpj(), false)){
                    $lod->setCnpj($this->data['cnpjLoc']);
                    $lod->setTipo($this->data['tipoLoc']);
                }                
            }
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
        $tamanho = strlen($doc);
        $this->data['tipo'] = ($tamanho <= 11)? 'fisica' : 'juridica';
        //Arrumar tamanho da numeração do cpf
        if($this->data['tipo'] == 'fisica' AND $tamanho <> 11){
            $doc = str_pad($doc, 11, '0', STR_PAD_LEFT);  
        }      
        //Arrumar tamanho da numeração do cnpj
        if($this->data['tipo'] == 'juridica' AND $tamanho <> 14){
            $doc = str_pad($doc, 14, '0', STR_PAD_LEFT);  
        }     
        $this->data['cpf'] =  ($this->data['tipo'] == 'fisica') ? $doc : '' ;
        $this->data['cnpj'] = ($this->data['tipo'] == 'fisica') ? '' : $doc ;
        $loc = $this->repLoct->findOneByNome($nome);
        if ($loc) {
            $this->data['locatario']      = $loc;
            if($this->data['tipo'] == 'fisica'){
                if($this->data['cpf'] != $loc->formatarCPF_CNPJ($loc->getCpf(), false)){
                    $loc->setCpf($this->data['cpf']);
                    $loc->setTipo($this->data['tipo']);
                }
            }else{
                if($this->data['cnpj'] != $loc->formatarCPF_CNPJ($loc->getCnpj(), false)){
                    $loc->setCnpj($this->data['cnpj']);
                    $loc->setTipo($this->data['tipo']);
                }                
            }
        }
    }  
  
}