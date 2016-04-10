<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use \Livraria\Entity\Configurator;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

/**
 * AbstractService
 * Tem os metodos basicos para o Crud no BD
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractService {

    /**
     * Para Casos em que não se pode validar registro
     * @var boolean
     */
    protected $isValid = TRUE;

    /**
     * Objeto para efetuar operações no banco
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Define se vai comitar as alterações do BD
     * Para controle de alterações e melhorar desempenho
     * @var boolean
     */
    protected $flush;

    /**
     * Caminho para "Tabela" é nome da tabela em que está sendo tratada.
     * \Livraria\Entity\"Tabela"
     * @var string
     */
    protected $entity;

    /**
     * Caminho para "Tabela" é nome da tabela em que está sendo tratada.
     * \Livraria\Entity\"Tabela"
     * @var string
     */
    protected $entityReal;

    /**
     * Objeto que pega os dados do usuario armazenado
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authService;

    /**
     * Dados do form a serem validados
     * @var array
     */
    protected $data;

    /**
     * String no formato para gravação de alterações feitas no registro
     * Formato campo  nome; valor antes; valor depois;
     * @var string
     */
    protected $dePara = '';
    
    protected $debug = true;
    
    /**
     * String com endereço de email padrão
     * @var type String
     */
    protected $mailDefault = 'incendiolocacao@vilavelha.com.br';

    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function showdebug($msg, $var) {
        if(!$this->debug){
            return;
        }
        echo '<pre>', $msg, ($var)? var_dump($var) : '', '</pre>';
    }
    
    /**
     * Para situaçoes em que não se deve validar o regitro no BD
     */
    public function notValidateNew(){
        $this->isValid = FALSE;
    }

    /**
     * Se vai comitar as operações do BD.
     * @param boolen $flush
     * return this
     */
    public function setFlush($flush) {
        $this->flush = ( $flush ) ? TRUE : FALSE;
        return $this;
    }

    /**
     * Se vai comitar as operações do BD.
     * return boolean
     */
    public function getFlush() {
        if (is_null($this->flush)){
            $this->flush = TRUE ;
        }
        return ( $this->flush ) ? TRUE : FALSE ;
    }

    /**
     * Inserir no banco de dados o registro
     * @param array $data com os campos do registro
     * @return boolean
     */
    public function insert(array $data=[]) {
        if(!empty($data)){
            $this->data = $data;
        }
        if ($user = $this->getIdentidade()) {
            $this->data['userIdCriado'] = $user->getId();
        }

        $this->entityReal = new $this->entity($this->data);

        $this->em->persist($this->entityReal);
        if ($this->getFlush()) {
            $this->em->flush();
            $this->data['id'] = $this->entityReal->getId();
        }
        return TRUE;
    }

    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     * @param string $tabela
     * @param string $controller
     * @param string $obs
     * @return no return
     */
    public function logForNew($tabela='',$controller='', $obs='Inseriu um novo registro'){
        if(empty($tabela))$tabela = 'Tabela Não foi definida' ;

        if(empty($controller))$controller = $tabela . 's' ;

        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->entityReal->getId();
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller ;
        $dataLog['action']     = 'new';
        $dataLog['dePara']     = $obs;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->setFlush($this->getFlush())->insert($dataLog);
    }

    /**
     * Alterar no banco de dados o registro e
     * @param array $data com os campos do registro
     * @return boolean
     */
    public function update(array $data = null) {
        if($data){
            $this->data = $data;
        }
        if ($user = $this->getIdentidade()) {
            $this->data['userIdAlterado'] = $user->getId();
        }

        if(method_exists($this,'getDiff')){
            $this->getDiff($this->getEntity());
            if (empty($this->dePara)) {
                return TRUE;
            }
        }else{
            $this->entityReal = $this->em->getReference($this->entity, $this->data['id']);
        }

        $this->entityReal = Configurator::configure($this->entityReal, $this->data);


        $this->em->persist($this->entityReal);
        if ($this->getFlush()) {
            $this->em->flush();
        }

        return TRUE;
    }

    /**
     *
     * Grava no logs dados da alteção feita na Entity
     * @param string $tabela
     * @return no return
     */
    public function logForEdit($tabela='', $controller=''){
        if((empty($this->dePara)) OR (empty($tabela)))
            return ;

        if(empty($controller))$controller = $tabela . 's' ;

        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $data  = new \DateTime('now');
        $dataLog['data']       = $data->format('d/m/Y');
        $dataLog['idDoReg']    = $this->entityReal->getId();
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller;
        $dataLog['action']     = 'edit';
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->setFlush($this->getFlush())->insert($dataLog);
    }

    /**
     * Esclui o registro ou marca como cancelado se existir os campo status
     * @param $id do registro
     * @return boolean
     */
    public function delete($id) {
        $this->entityReal = $this->em->getReference($this->entity, $id);
        if($this->entityReal) {
            if(method_exists($this->entityReal,"setStatus")){
                $this->entityReal->setStatus('C'); //Cancelado
            }else{
                $this->em->remove($this->entityReal);
            }
            if ($this->getFlush())
                $this->em->flush();
            return TRUE ;
        }
        return FALSE ;
    }

    /**
     *
     * Grava no logs dados da exclusão do registro
     * @param string $id
     * @param string $tabela
     * @param string $controller
     * @return void
     */
    public function logForDelete($id, $tabela='', $controller='', $obs='Excluiu o Registro!!!'){
        if(empty($tabela))
            return ;

        if(empty($controller))
            $controller = $tabela . 's' ;

        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $dataLog['data']       = (new \DateTime('now'))->format('d/m/Y');
        $dataLog['idDoReg']    = $id;
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller;
        $dataLog['action']     = 'delete';
        $dataLog['dePara']     = $obs;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->setFlush($this->getFlush())->insert($dataLog);
    }

    /**
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return \Livraria\Entity\User
     * @return boolean
     */
    public function getIdentidade() {
        if (is_object($this->authService)) {
            return $this->authService->getIdentity();
        }else{
            $sessionStorage = new SessionStorage("LivrariaAdmin");
            $this->authService = new AuthenticationService;
            $this->authService->setStorage($sessionStorage);
            if ($this->authService->hasIdentity())
                return $this->authService->getIdentity();
        }
        return FALSE;
    }

    /**
     * Converte uma data string em data object no indice apontado.
     * @param string $index
     * @return boolean
     */
    public function dateToObject($index){
        //Trata as variveis data string para data objetos
        if(!isset($this->data[$index])){
            //echo '<h1>Indice do array data desconhecido ', $index , '.</h1>';die;
            $this->data[$index] = '';
        }

        if(is_object($this->data[$index])){
            if($this->data[$index] instanceof \DateTime)
                return TRUE;
            else
                return FALSE;
        }

        if((!empty($this->data[$index]))
                && ($this->data[$index] != "vigente")
                && ($this->data[$index] != "30/11/-0001")
                && ($this->data[$index] != "-")
                && ($this->data[$index] != "00/00/0000")){
            $date = explode("/", $this->data[$index]);
            try {
                $this->data[$index]    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
            }  catch (Exception $e){
                echo 'exceção: ', $e->getMessage(), "\n Erro no valor da data a ser convertida";
                var_dump($this->data[$index]);
            }
        }else{
            $this->data[$index]    = new \DateTime("01/01/1000");
        }

        if($this->data[$index]){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Converte o id de um registro dependente em object reference
     * @param string $index   Indice do array a ser feita a ligação
     * @param string $entity  Caminho para a Entity
     */
    public function idToReference($index, $entity){
        if((!isset($this->data[$index])) OR (empty($this->data[$index]))){
            echo "erro no indice e nao pode ser carregar entity ", $entity, ' id= ',$this->data[$index] , ' key=', $index;
            return FALSE;
        }

        if(is_object($this->data[$index])){
            if($this->data[$index] instanceof $entity)
                return TRUE;
            else
                return FALSE;
        }

        $this->data[$index] = $this->em->getReference($entity, $this->data[$index]);
    }

    /**
     * Converte o id de um registro dependente em um Entity
     * @param string $index   Indice do array a ser feita a ligação
     * @param string $entity  Caminho para a Entity
     */
    public function idToEntity($index, $entity){
        if((!isset($this->data[$index])) OR (empty($this->data[$index]))){
            echo "erro no indice e nao pode ser carregar entity ", $index;
            return FALSE;
        }

        if(is_object($this->data[$index])){
            if($this->data[$index] instanceof $entity)
                return TRUE;
            else
                return FALSE;
        }

        $this->data[$index] = $this->em->find($entity, $this->data[$index]);
    }

    /**
     * Faz a comparação de alteração e retorna uma string no formato para gravação.
     * @param string $input
     * @param string $after
     * @param string $before
     * @return string
     */
    public function diffAfterBefore($input,$after,$before){
        if($after != $before){
            return $input . ';' . $after . ';' . $before . ';';
        }
        return '';
    }

    /**
     * Faz tratamento na variavel string com conteudo float ou inverso
     * Retorna um float ou string com float para exibição
     * @param string $check (String a ser tratada com separador de decimal com virgula)
     * @param string $op    (Vazio retorna string ou 'f' retorna float)
     * @param integer $dec  (Casas decimais)
     * @return float|string (com conteudo float)
     */
    public function strToFloat($check,$op='',$dec=2){
        if(is_string($check)){
            $check = str_replace(",", ".", preg_replace("/[^0-9,]/", "", $check));
        }
        $float = floatval($check);
        if(empty($op)){
            return number_format($float, $dec, ',','.');
        }else{
            return $float;
        }
    }

    /**
     * Retorna um string com campos monitorados que foram afetados.
     * @return string
     */
    public function getDePara() {
        return $this->dePara;
    }

    /**
     * Dados da entity no formato array
     * @return array
     */
    public function getData() {
        return $this->entityReal->toArray();
    }

    /**
     * Retorna a entity que foi trabalhada no serviço
     * False se a Entity ainda não tiver sido inicializada
     * @return boolean | Entity
     */
    public function getEntity() {
        if ($this->entityReal) {
            return $this->entityReal;
        }
        $this->entityReal = $this->em->find($this->entity, $this->data['id']);
        return $this->entityReal;
    }

    /**
     * Busca um paramentro especifico cadastrado com um key definida
     * @param string $key
     * @return boolean | entity | array
     */
    public function getParametroSis($key, $array = false){
        $entity = $this->em->getRepository('Livraria\Entity\ParametroSis')->findByKey($key);
        if ($entity) {
            if ($array){
                return $entity;
            }else{
                return $entity[0]->getConteudo();
            }
        }
        return FALSE;
    }


    public function CalculaPremio($data=[]){
        if(!empty($data)){
            $this->data = $data ;
        }

        //Base de todo calculo
        $vlrAluguel = $this->strToFloat($this->data['valorAluguel'],'float');

        //Coberturas
        $incendio = ($this->data['incendio']=='Não Calcular')? 0.0001:$this->strToFloat($this->data['incendio'], 'float');
        $conteudo = ($this->data['conteudo']=='Não Calcular')? 0.0001:$this->strToFloat($this->data['conteudo'], 'float');
        $aluguel  = ($this->data['aluguel'] =='Não Calcular')? 0.0001:$this->strToFloat($this->data['aluguel'],  'float');
        $eletrico = ($this->data['eletrico']=='Não Calcular')? 0.0001:$this->strToFloat($this->data['eletrico'], 'float');
        $vendaval = ($this->data['vendaval']=='Não Calcular')? 0.0001:$this->strToFloat($this->data['vendaval'], 'float');

        //Calcula de coberturas caso estejam zeradas do form
        if($incendio == 0.0 and $this->data['tipoCobertura'] == '01')
            $incendio = $vlrAluguel * $this->data['comissaoEnt']->getMultIncendio();

        // Cobertura incendio + conteudo usa multiplo do incendio para calcular cobertura
        if($conteudo == 0.0 and $this->data['tipoCobertura'] == '02')
            $conteudo = $vlrAluguel * $this->data['comissaoEnt']->getMultIncendio();

        // Multiplo de conteudo usado somente quando existe valor nesse campo e a cobertura for incen. + cont.
        if($conteudo == 0.0 and $this->data['tipoCobertura'] == '02' AND $this->data['comissaoEnt']->getMultConteudo() != 0)
            $conteudo = $vlrAluguel * $this->data['comissaoEnt']->getMultConteudo();

        if($this->data['tipoCobertura'] == '01'){
            $base = $incendio ;
        }else{
            $base = $conteudo ;
        }

        if($aluguel == 0.0)
            $aluguel  = $vlrAluguel * $this->data['comissaoEnt']->getMultAluguel();

        if($eletrico == 0.0)
            $eletrico = $vlrAluguel * $this->data['comissaoEnt']->getMultEletrico();

        if($vendaval == 0.0)
            $vendaval = $vlrAluguel * $this->data['comissaoEnt']->getMultVendaval();

        // Validar coberturas conforme contrato da Maritima
        if((is_object($this->data['seguradora']) AND $this->data['seguradora']->getId() == '2') OR $this->data['seguradora'] == '2'){
            $this->validaMaritimaCoberturas($base, $aluguel, $eletrico, $vendaval);
        }
        
        //aplicacar regras de desconto ou acrescimo aqui(taxa de ajuste).
        
        /* @var $repTaxaAjuste    \Livraria\Entity\TaxaAjusteRepository */
        $repTaxaAjuste = $this->em->getRepository('Livraria\Entity\TaxaAjuste');
        $entTaxaAjuste = $repTaxaAjuste->getTaxaAjusteFor(
            $this->data['seguradora']
            , $this->data['administradora']
            , (is_object($this->data['inicio']) ? $this->data['inicio'] : $this->dateToObject($this->data['inicio']))
            , $this->data['validade']
            , $this->data['atividade']
            , $this->data['ocupacao']
        );
        $this->data['taxaAjuste'] = 1;
        // Se o tipo é Cobertura Incendo calcula com a taxa de incendio
        $txIncendio = 0.0;
$bkpDebug = $this->debug;
$this->debug = false;
        if ($this->data['tipoCobertura'] == '01' AND $incendio != 0.0001){
            $bkpVlr = $incendio;
            $txIncendio = round($this->calcTaxaMultMinMax($incendio, 'Incendio', 'Incendio'), 2) ;
            $incendio = $bkpVlr;
        }
        // Se o tipo é Cobertura Incendo + Conteudo(02) calcula com a taxa propria de incendio + conteudo
        $txConteudo = 0.0;
        if ($this->data['tipoCobertura'] == '02' AND $conteudo != 0.0001){
            $bkpVlr = $conteudo;
            $txConteudo = round($this->calcTaxaMultMinMax($conteudo,'Incendio','Conteudo'), 2) ;
            $conteudo = $bkpVlr;
        }
        
        $txAluguel = 0.0;
        if ($aluguel != 0.0001){
            $bkpVlr = $aluguel;
            $txAluguel = round($this->calcTaxaMultMinMax($aluguel,'Aluguel'), 2) ;
            $aluguel = $bkpVlr;
        }
        
        $txEletrico = 0.0;
        if ($eletrico != 0.0001){
            $bkpVlr = $eletrico;
            $txEletrico = round($this->calcTaxaMultMinMax($eletrico,'Eletrico'), 2) ;
            $eletrico = $bkpVlr;
        }
        
        $txVendaval = 0.0;
        if ($vendaval != 0.0001){
            $bkpVlr = $vendaval;
            $txVendaval = round($this->calcTaxaMultMinMax($vendaval,'Vendaval'), 2) ;
            $vendaval = $bkpVlr;
        }
        /* @var $entTaxaAjuste    \Livraria\Entity\TaxaAjuste */
$this->debug = $bkpDebug;
        $taxaAjuste = 1;
        if($entTaxaAjuste){
            $taxaAjuste = $repTaxaAjuste->changeEntityForTaxaFloat($txConteudo, $txEletrico, $entTaxaAjuste);
        }
        switch (TRUE) {
            case $taxaAjuste == 1:
                $taxaAjuste = 1;
                break;
            case !is_numeric($taxaAjuste) :
                $taxaAjuste = 1;
                break;
            default:
                $taxaAjuste = round(1 + ($taxaAjuste / 100), 4);
                break;
        }
        $this->data['taxaAjuste'] = $taxaAjuste;
        
        // Calcula cobertura premio = cobertura * (taxa / 100)       
        $total = 0.0 ;
        // Se o tipo é Cobertura Incendo calcula com a taxa de incendio
        $txIncendio = 0.0;
        if ($this->data['tipoCobertura'] == '01' AND $incendio != 0.0001){
            $txIncendio = round($this->calcTaxaMultMinMax($incendio, 'Incendio', 'Incendio'), 2) ;
        }
        // Se o tipo é Cobertura Incendo + Conteudo(02) calcula com a taxa propria de incendio + conteudo
        $txConteudo = 0.0;
        if ($this->data['tipoCobertura'] == '02' AND $conteudo != 0.0001){
            $txConteudo = round($this->calcTaxaMultMinMax($conteudo,'Incendio','Conteudo'), 2) ;
        }

        $txAluguel = 0.0;
        if ($aluguel != 0.0001){
            $txAluguel = round($this->calcTaxaMultMinMax($aluguel,'Aluguel'), 2) ;
        }

        $txEletrico = 0.0;
        if ($eletrico != 0.0001){
            $txEletrico = round($this->calcTaxaMultMinMax($eletrico,'Eletrico'), 2) ;
        }

        $txVendaval = 0.0;
        if ($vendaval != 0.0001){
            $txVendaval = round($this->calcTaxaMultMinMax($vendaval,'Vendaval'), 2) ;
        }

        $total += $txIncendio ;
        $total += $txConteudo;
        $total += $txAluguel;
        $total += $txEletrico;
        $total += $txVendaval;

        //Verificar Se administradora tem total de cobertura minima e compara
        if(is_object($this->data['administradora'])){
            $idAdm = $this->data['administradora']->getId();
        }else{
            $idAdm = $this->data['administradora'];
        }
        $coberturaMinAdm = $this->getParametroSis($idAdm . '_cob_min');
        $totalAntes = 0.0;
        if($coberturaMinAdm !== FALSE){
            if($total < $coberturaMinAdm){
                $totalAntes = $total;
                $total = $coberturaMinAdm;
            }
        }
        if($this->data['assist24'] == 'S'){
            $total += $this->getAssist24Vlr();
        }
     /*
        if ($this->data['validade'] == 'mensal'){
            $diff = $this->data['fim']->diff($this->data['inicio']);
            if($diff->days < 31){
                $recalculaPeriodo = $total / 31 ;
                $total = $recalculaPeriodo * $diff->days ;
            }
        }
     */
        $iof = floatval($this->getParametroSis('taxaIof'));

        $this->data['taxaIof'] = $this->strToFloat($iof,'',4);
                
        $totalBruto = round($total * (1 + $iof), 2) ;

        if($totalAntes != 0.0){
            $this->data['premio']        = $this->strToFloat($totalAntes);
        }else{
            $this->data['premio']        = $this->strToFloat($total);
        }

        $this->data['premioLiquido'] = $this->strToFloat($total);
        $this->data['premioTotal']   = $this->strToFloat($totalBruto);
        $this->data['incendio']      = ($incendio == 0.0001) ? 'Não Calcular' : $this->strToFloat($incendio);
        $this->data['cobIncendio']   = $this->strToFloat($txIncendio);
        $this->data['conteudo']      = ($conteudo == 0.0001) ? 'Não Calcular' : $this->strToFloat($conteudo);
        $this->data['cobConteudo']   = $this->strToFloat($txConteudo);
        $this->data['aluguel']       = ($aluguel == 0.0001) ? 'Não Calcular' : $this->strToFloat($aluguel);
        $this->data['cobAluguel']    = $this->strToFloat($txAluguel);
        $this->data['eletrico']      = ($eletrico == 0.0001) ? 'Não Calcular' : $this->strToFloat($eletrico);
        $this->data['cobEletrico']   = $this->strToFloat($txEletrico);
        $this->data['vendaval']      = ($vendaval == 0.0001) ? 'Não Calcular' : $this->strToFloat($vendaval);
        $this->data['cobVendaval']   = $this->strToFloat($txVendaval);

        return array($total,$totalBruto,$incendio,$conteudo,$aluguel,$eletrico,$vendaval);
    }

    /**
     * As coberturas de Perda Aluguel , Danos Eletricos, Vendaval não pode ser maior que
     * uma porcentagem pré definida da cobertura principal Incendio Conteudo
     * @param float $base
     * @param float $txAluguel
     * @param float $txEletrico
     * @param float $txVendaval
     */
    public function validaMaritimaCoberturas($base, &$txAluguel, &$txEletrico, &$txVendaval) {
        if( $this->data['ocupacao'] == '02' ) {
            // Residencial
            $perdAluguel = $base * 0.3 ;
            $eletrico    = $base * 0.2 ;
            $vendaval    = $base * 0.4 ;
        }else{
            // Comercial
            $perdAluguel = $base * 0.3 ;
            $eletrico    = $base * 0.4 ;
            $vendaval    = $base * 0.5 ;
        }
        if ($perdAluguel < $txAluguel){
            $txAluguel = $perdAluguel;
        }
        if ($eletrico < $txEletrico){
            $txEletrico = $eletrico;
        }
        if ($vendaval < $txVendaval){
            $txVendaval = $vendaval ;
        }
    }

    /**
     * Casos em que é suspenso ou ignorado o calculo do multiplo padrão recebem o valor de identificação 0.0001
     */
    public function trocaNaoCalcula($inverte=false){
        //Coberturas
        $txt=($inverte)? '0,0001'       : 'Não Calcular' ;
        $vlr=($inverte)? 'Não Calcular' : '0,0001'       ;
        if($this->data['incendio']==$txt) $this->data['incendio'] = $vlr;
        if($this->data['conteudo']==$txt) $this->data['conteudo'] = $vlr;
        if($this->data['aluguel'] ==$txt) $this->data['aluguel']  = $vlr;
        if($this->data['eletrico']==$txt) $this->data['eletrico'] = $vlr;
        if($this->data['vendaval']==$txt) $this->data['vendaval'] = $vlr;
    }

    /**
     * Pega os inputs com dados calculados e trabalhados
     * @return array com inputs atualizados para colocar no form
     */
    public function getNewInputs() {
        if(!is_object($this->data['taxa'])){
            echo 'Alerta taxa não encontrada para esta proposta.';
        }
        return array(
            'premioTotal'      => $this->data['premioTotal'],
            'premioLiquido'    => $this->data['premioLiquido'],
            'premio'           => $this->data['premio'],
            'incendio'         => $this->data['incendio'],
            'conteudo'         => $this->data['conteudo'],
            'aluguel'          => $this->data['aluguel'],
            'eletrico'         => $this->data['eletrico'],
            'vendaval'         => $this->data['vendaval'],
            'taxaIof'          => $this->data['taxaIof'],
            'taxa'             => (is_object($this->data['taxa'])) ?  $this->data['taxa']->getId() : NULL,
            'user'             => $this->data['user']->getId(),
            'multiplosMinimos' => $this->data['multiplosMinimos']->getId(),
            'rua'              => $this->data['imovel']->getRua(),
            'numero'           => $this->data['imovel']->getNumero(),
            'apto'             => $this->data['imovel']->getApto(),
            'bloco'            => $this->data['imovel']->getBloco(),
            'compl'            => $this->data['imovel']->getEndereco()->getCompl(),
            'fim'              => $this->data['fim']->format('d/m/Y'),
        );
    }

    /**
     * Calcula o premio(vlr) do seguro no item da cobertura passada pelo paramentro
     * premio = cobertura * (taxa / 100)
     * Tipo de Taxa pode ser anual ou mensal
     * se premio for menor que o vlr minimo prevalece o minimo o mesmo acontece para o vlr maximo
     * @param float $vlr     Valor da Cobertura
     * @param string $fTaxa  Parte do nome da funcao da entity taxa ex Incendio
     * @param string $fMin   Parte do nome da funcao da entity multiplosMinimos ex Incendio
     * @return real
     */
    public function calcTaxaMultMinMax(&$vlr, $fTaxa, $fMin='') {
        if($vlr == 0.0)
            return 0.0;

        if (empty($fMin))
            $fMin = $fTaxa;

        //Gera o nome da função a ser chamada na entity taxa
        $fTaxa = 'get' . $fTaxa ;
        //Gera o nome da função a ser chamada na entity multiplosMinimos
        $fMax = 'getMax' . $fMin ;
        $fMin = 'getMin' . $fMin ;
        // Seta as variavies de comparação dos paramentros da seguradora
        if($this->data['validade'] == 'anual'){
            $premioMin = floatval($this->data['multiplosMinimos']->getMinPremioAnual());
            $apoliceMin = floatval($this->data['multiplosMinimos']->getMinApoliceAnual());
            $parcelaMin = floatval($this->data['multiplosMinimos']->getMinParcelaAnual());
        }else{
            $premioMin = floatval($this->data['multiplosMinimos']->getMinPremioMensal());
            $apoliceMin = floatval($this->data['multiplosMinimos']->getMinApoliceMensal());
            $parcelaMin = floatval($this->data['multiplosMinimos']->getMinParcelaMensal());
        }

        // Se valor da cobertura for menor que o minimo calcula com o min
        $vlrMin = floatval($this->data['multiplosMinimos']->$fMin());
        if ($vlrMin != 0.0 AND $vlr < $vlrMin) {
            $vlr = $vlrMin;
        }

        // Se valor da cobertura for maior que o maximo calcula com o max
        $vlrMax = floatval($this->data['multiplosMinimos']->$fMax());
        if (($vlrMax != 0.0) AND ( $vlr > $vlrMax)) {
            $vlr = $vlrMax;
        }

        // Valor calculado
        $taxaCalculada = round($this->data['taxa']->$fTaxa() * $this->data['taxaAjuste'], 4) / 100;
        $calc = $vlr * $taxaCalculada;
        
        if($calc < $premioMin){
            $calc = $premioMin;
        } 
        
        $this->showdebug('$vlr ',$vlr); 
        $this->showdebug('$taxa ',($this->data['taxa']->$fTaxa())); 
        $this->showdebug('taxaAjuste ',$this->data['taxaAjuste']); 
        $this->showdebug('$taxaCalculada ',(round($taxaCalculada * 100,4))); 
        $this->showdebug(' $calc ' , ($calc));
        
        return $calc;
    }

    /**
     * Calcula a vigencia do seguro periodo mensal ou anual
     * @return boolean | array
     */
    public function calculaVigencia(){
        if(!isset($this->data['validade'])){
            return ['Campo validade não existe!!'];
        }
        if(!isset($this->data['codano']) OR empty($this->data['codano'])){
            $this->data['codano'] = $this->data['criadoEm']->format('Y');
        }
        $this->data['fim'] = clone $this->data['inicio'];
        if($this->data['validade'] == 'mensal'){
            $this->data['fim']->add(new \DateInterval('P1M'));
            $this->data['fim']->sub(new \DateInterval('P1D'));
//            $this->data['mesNiver'] = $this->data['inicio']->format('m'); já é calculado via js
            $this->data['formaPagto'] = '01' ;
            return TRUE;
        }
        if($this->data['validade'] == 'anual'){
            $this->data['fim']->add(new \DateInterval('P1Y'));
            $this->data['mesNiver'] = 0 ;
            //$this->data['fim']->sub(new \DateInterval('P1D'));
            return TRUE;
        }
        return ['Campo validade com valor que não existe na lista!!'];
    }

    /**
     * Retorna um campo do array do data do indice passar por parametro
     * @param indice $index
     * @return one item of array
     */
    public function getFiltroTratado($index){
        return isset($this->data[$index]) ? $this->data[$index] : false ;
    }

    /**
     * Gravação de log do sistema
     * @param string $tabela
     * @param string $id
     * @param string $controller
     * @param string $action
     * @param string $obs
     * @return no return
     */
    public function logForSis($tabela='', $id=0, $controller='', $action='', $obs=''){
        if(empty($tabela))$tabela = 'Tabela NDN' ;
        $log = new Log($this->em);
        $dataLog['user']       = $this->getIdentidade()->getId();
        $dataLog['data']       = (new \DateTime('now'))->format('d/m/Y');
        $dataLog['idDoReg']    = $id;
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller ;
        $dataLog['action']     = $action;
        $dataLog['dePara']     = $obs;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->setFlush($this->getFlush())->insert($dataLog);
    }

    /**
     * Retorna o filtro solicitado se não existir retorna falso
     * @param mixed $index
     * @return mixed
     */
    public function getFiltroData($index) {
        return (isset($this->data[$index])) ? $this->data[$index] : FALSE;
    }

    /**
     * Busca o valor a somar para seguros com assistencia 24 horas.
     * @return float
     */
    public function getAssist24Vlr() {
        return (floatval($this->getParametroSis('assist24_' . $this->data['ocupacao'] . '_' . $this->data['validade'])));
    }

}
