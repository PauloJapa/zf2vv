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
        $this->repFechado = $this->em->getRepository('Livraria\Entity\Fechados');
        $dataResul = [];
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
            $dataResul[]['result'] = $service->insert($this->data);
        }
        $this->getSc()->importacaoResul = $dataResul;
        return $dataResul;
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
        $this->buscaImovel($array[9], $array[10]);
        
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
        }else{
            $this->data['ocupacao'] = '01';
            $this->data['atividade'] = '488' ;
            $this->data['atividadeDesc'] = 'COMERCIAL' ;            
        }
        $this->data['observacao'] = trim($array[4]) ;        
        $this->data['observacao'] .=  '| ' . trim($array[12]) ;        
        $this->data['observacao'] .=  '| ' . trim($array[13]) ;        
        $this->data['observacao'] .=  '| ' . trim($array[20]) ;        
        
        $this->data['incendio'] = '' ;
        $this->data['conteudo'] = number_format($array[16] / 100, 2, ',', '.') ;
        $this->data['aluguel']  = number_format($array[17] / 100, 2, ',', '.') ;
        $this->data['eletrico'] = number_format($array[18] / 100, 2, ',', '.') ;
        $this->data['valorAluguel'] = number_format($array[17] /100 / 6, 2, ',', '.') ;
        $this->data['vendaval'] = '' ;
        $this->data['premioTotal'] = '' ;
        $this->data['refImovel'] = $array[19] ;
        
        $this->data['comissao'] = '69,99' ;
        $this->data['seguradora'] = '3' ;
        $this->data['seguroEmNome'] = '02' ;
        $this->data['validade'] = 'anual' ;
        $this->data['tipoCobertura'] = '02' ;
        $this->data['formaPagto'] = '01' ;
        $this->data['criadoEm'] = new \DateTime() ;
        
        $this->data['comissaoEnt'] = '67' ;
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
        if(!is_object($this->data['imovel'])){
            return 1;
        }
        $entity = $this->repFechado->findBy(['imovel' => $this->data['imovel']->getId(), 'fim' => $this->data['inicio']]);
        if($entity){
            return $entity[0]->getId();
        }else{
            return 1;
        }
    }

    public function buscaImovel($cep, $rua){
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
        //procurar imovel pelo rua, numero e locador
        $separado = $this->desmontaEnd($rua);
        $filtro['rua'] = $separado['rua'];
        $filtro['numero'] = isset($separado['numero']) ? $separado['numero'] : '';
        $filtro['locador'] = $this->data['locador']->getId();
        if(isset($separado['apto'])){
            $filtro['apto'] = $separado['apto'];
        }
        $entitys = $this->repImovel->findBy($filtro);
        if(!$entitys AND substr($filtro['rua'], 0, 3) == 'RUA'){
            $filtro['rua'] = str_replace('RUA', 'R', $filtro['rua']);
            $entitys = $this->repImovel->findBy($filtro);
        }
        if($entitys){
            foreach ($entitys as $entity) {
                $this->data['imovel'] = $entity;
                return;
            }
        }
        $this->data['imovel'] = '';
        $this->data['cep'] = str_pad($cep, 8, '0', STR_PAD_LEFT);
        $this->data['rua'] = $separado['rua'];
        $this->data['numero'] = $separado['numero'];
        $this->data['apto'] = '';
        $this->data['bloco'] = '';
        $this->data['compl'] = isset($separado['compl']) ? $separado['compl'] : '';
                
        $retorno = @file_get_contents('http://cep.republicavirtual.com.br/web_cep.php?cep='.urlencode($this->data['cep']).'&formato=json'); 
        if($retorno){ 
            $resultado = json_decode($retorno, true);
        }else{
            var_dump($filtro);
            $this->erro[] = 'Falha na busaca ao CEP verifique os dados por favor!';
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
        //procurar locatrio pelo nome
        $nome = rtrim($nome);
        $lod = $this->repLocd->findByNome($nome);
        foreach ($lod as $enty) {
            $this->data['locador']      = $enty;
            $this->data['locadorNome']  = $enty->getNome();
            return;
        }
        
        //Nao encontrou entao insere ele no BD
        $d['id'] = '';
        $d['nome'] = $nome;
        $tipo = (strlen($doc) <= 11)? 'fisica' : 'juridica';
        $d['tipo'] =  $tipo;
        $d['cpf']  = ($tipo == 'fisica') ? $doc : '' ;
        $d['cnpj'] = ($tipo == 'fisica') ? ''   : $doc ;
        $d['email'] = '';
        $d['tel'] = '';
        $d['status'] = 'A';
        $d['enderecos'] = '';
        $d['administradora'] = $this->data['administradora'];
        $rs = $this->serLocd->insert($d);
        if($rs === TRUE){
            $loc = $this->serLocd->getEntity();
            $this->data['locador']      = $loc;
            $this->data['locadorNome']  = $loc->getNome();
        }else{
            $this->erro[] = $rs;
            echo '<h2>Erro ao inserir locatario; </h2>';
            var_dump($rs);
            var_dump($d);
            $this->data['locador']      = '';
            $this->data['locadorNome']  = $nome;
            $this->data['tipo']  = $tipo;
            $this->data['cpf']   = $d['cpf'] ;
            $this->data['cnpj']  = $d['cnpj'] ;
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
        $nome = rtrim($nome);
        $loc = $this->repLoct->findByNome($nome);
        foreach ($loc as $enty) {
            $this->data['locatario']      = $enty;
            $this->data['locatarioNome']  = $enty->getNome();
            return;
        }
        
        //Nao encontrou entao insere ele no BD
        $d['id'] = '';
        $d['nome'] = $nome;
        $tipo = (strlen($doc) <= 11)? 'fisica' : 'juridica';
        $d['tipo'] = ($tipo == 'fisica') ? 'fisica' : 'juridica' ;
        $d['cpf'] = ($tipo == 'fisica') ? $doc : '' ;
        $d['cnpj'] = ($tipo == 'fisica') ? '' : $doc ;
        $d['email'] = '';
        $d['tel'] = '';
        $d['status'] = 'A';
        $d['enderecos'] = '';
        $rs = $this->serLoct->insert($d);
        if($rs === TRUE){
            $loc = $this->serLoct->getEntity();
            $this->data['locatario']      = $loc;
            $this->data['locatarioNome']  = $loc->getNome();
        }else{
            $this->erro[] = $rs;
            echo '<h2>Erro ao inserir locatario; </h2>';
            var_dump($rs);
            var_dump($d);
            $this->data['locatario']      = '';
            $this->data['locatarioNome']  = $nome;
            $this->data['tipo']  = $tipo;
            $this->data['cpf'] = $d['cpf'] ;
            $this->data['cnpj'] = $d['cnpj'] ;
        }
    }  
  
}