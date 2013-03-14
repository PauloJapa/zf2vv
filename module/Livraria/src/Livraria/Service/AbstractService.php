<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

/**
 * AbstractService
 * Tem os metodos basicos para o Crud no BD
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractService {

    /**
     * Objeto para efetuar operações no banco
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * Caminho para "Tabela" é nome da tabela em que está sendo tratada.
     * Livraria\Entity\"Tabela" 
     * @var string 
     */
    protected $entity;
    
    /**
     * Caminho para "Tabela" é nome da tabela em que está sendo tratada.
     * Livraria\Entity\"Tabela" 
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
    protected $dePara;

    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
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
        if ($user = $this->getIdentidade())
            $this->data['userIdCriado'] = $user->getId();
        
        $this->entityReal = new $this->entity($this->data);
        
        $this->em->persist($this->entityReal);
        $this->em->flush();
        
        $this->data['id'] = $this->entityReal->getId();
        
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
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller ;
        $dataLog['action']     = 'new';
        $dataLog['dePara']     = $obs;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
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
        if ($user = $this->getIdentidade())
            $this->data['userIdAlterado'] = $user->getId();
        
        if(method_exists($this,'getDiff')){
            $this->entityReal = $this->em->find($this->entity, $this->data['id']);
            $this->getDiff($this->entityReal);            
            if(empty($this->dePara)) 
                return TRUE;
        }else{
            $this->entityReal = $this->em->getReference($this->entity, $this->data['id']);
        }
        
        $this->entityReal = Configurator::configure($this->entityReal, $this->data);
        
        $this->em->persist($this->entityReal);
        $this->em->flush();
        
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
        $dataLog['idDoReg']    = $this->data['id'];
        $dataLog['tabela']     = $tabela;
        $dataLog['controller'] = $controller;
        $dataLog['action']     = 'edit';
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $dataLog['ip']         = $_SERVER['REMOTE_ADDR'];
        $log->insert($dataLog);
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
            $this->em->flush();
            return TRUE ;
        }
        return FALSE ;
    }
 
    /** 
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return Livraria\Entity\User 
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
                && ($this->data[$index] != "00/00/0000")){
            $date = explode("/", $this->data[$index]);
            $this->data[$index]    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
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
            echo "erro no indice e nao pode ser carregar entity";
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
            echo "erro no indice e nao pode ser carregar entity";
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
        if(empty($this->entityReal))
            return FALSE;
        
        return $this->entityReal;
    }
    
    /**
     * Busca um paramentro especifico cadastrado com um key definida
     * @param string $key
     * @return boolean | entity
     */
    public function getParametroSis($key){
        $entity = $this->em->getRepository('Livraria\Entity\ParametroSis')->findByKey($key);
        if($entity){
            return $entity[0]->getConteudo();
        }else
            return FALSE;        
    }

    
    public function CalculaPremio($data=[]){
        if(!empty($data)){
            $this->data = $data ;
        }
        
        //Base de todo calculo        
        $vlrAluguel = $this->strToFloat($this->data['valorAluguel'],'float');
        
        //Coberturas 
        $incendio = $this->strToFloat($this->data['incendio'], 'float');
        $conteudo = $this->strToFloat($this->data['conteudo'], 'float');            
        $aluguel  = $this->strToFloat($this->data['aluguel'],  'float');
        $eletrico = $this->strToFloat($this->data['eletrico'], 'float');
        $vendaval = $this->strToFloat($this->data['vendaval'], 'float');
        
        //Calcula de coberturas caso estejam zeradas do form
        if($incendio == 0.0)
            $incendio = $vlrAluguel * $this->data['multiplosMinimos']->getMultIncendio();
        
        if($conteudo == 0.0)
            $conteudo = $vlrAluguel * $this->data['multiplosMinimos']->getMultConteudo();
        
        if($aluguel == 0.0)
            $aluguel  = $vlrAluguel * $this->data['multiplosMinimos']->getMultAluguel();
        
        if($eletrico == 0.0)
            $eletrico = $vlrAluguel * $this->data['multiplosMinimos']->getMultEletrico();
        
        if($vendaval == 0.0)
            $vendaval = $vlrAluguel * $this->data['multiplosMinimos']->getMultVendaval();

        // Calcula cobertura premio = cobertura * (taxa / 100)       
        $total = 0.0 ;
        $txIncendio = $this->calcTaxaMultMinMax($incendio,'Incendio') ;
        $total += $txIncendio;
        
        $txConteudo = $this->calcTaxaMultMinMax($conteudo,'IncendioConteudo','Conteudo') ;
        $total += $txConteudo;
        
        $txAluguel = $this->calcTaxaMultMinMax($aluguel,'Aluguel') ;
        $total += $txAluguel;
        
        $txEletrico = $this->calcTaxaMultMinMax($eletrico,'Eletrico') ;
        $total += $txEletrico;
        
        $txVendaval = $this->calcTaxaMultMinMax($vendaval,'Desastres','Vendaval') ;
        $total += $txVendaval;
        
        //Verificar Se administradora tem total de cobertura minima e compara
        $coberturaMinAdm = $this->getParametroSis($this->data['administradora']->getId() . '_cob_min');
        $totalAntes = 0.0;
        if($coberturaMinAdm !== FALSE);{
            if($total < $coberturaMinAdm){
                $totalAntes = $total;
                $total = $coberturaMinAdm;
            }
        }
        
        $iof = floatval($this->getParametroSis('taxaIof')); 
        
        $this->data['taxaIof'] = $this->strToFloat($iof,'',4);
        
        $totalBruto = $total * (1 + $iof) ;
        
        if($totalAntes != 0.0){
            $this->data['premio']        = $this->strToFloat($totalAntes);
        }else{
            $this->data['premio']        = $this->strToFloat($total);
        }
        $this->data['premioLiquido'] = $this->strToFloat($total);
        $this->data['premioTotal']   = $this->strToFloat($totalBruto);
        $this->data['incendio']      = $this->strToFloat($incendio);
        $this->data['cobIncendio']   = $this->strToFloat($txIncendio);
        $this->data['conteudo']      = $this->strToFloat($conteudo);
        $this->data['cobConteudo']   = $this->strToFloat($txConteudo);
        $this->data['aluguel']       = $this->strToFloat($aluguel);
        $this->data['cobAluguel']    = $this->strToFloat($txAluguel);
        $this->data['eletrico']      = $this->strToFloat($eletrico);
        $this->data['cobEletrico']   = $this->strToFloat($txEletrico);
        $this->data['vendaval']      = $this->strToFloat($vendaval);
        $this->data['cobVendaval']   = $this->strToFloat($txVendaval);
        
        return array($total,$totalBruto,$incendio,$conteudo,$aluguel,$eletrico,$vendaval);
    }

    /**
     * Pega os inputs com dados calculados e trabalhados
     * @return array com inputs atualizados para colocar no form
     */
    public function getNewInputs() {
        return array(
            'premioTotal'=>$this->data['premioTotal'],
            'premioLiquido'=>$this->data['premioLiquido'],
            'premio'=>$this->data['premio'],
            'incendio'=>$this->data['incendio'],
            'conteudo'=>$this->data['conteudo'],
            'aluguel'=>$this->data['aluguel'],
            'eletrico'=>$this->data['eletrico'],
            'vendaval'=>$this->data['vendaval'],
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
    public function calcTaxaMultMinMax($vlr, $fTaxa, $fMin='') {
        if($vlr == 0.0)    
            return 0.0;

        if (empty($fMin))
            $fMin = $fTaxa;

        //Gera o nome da função a ser chamada na entity taxa
        if($this->data['validade'] == 'anual')
            $fTaxa = 'get' . $fTaxa ;
        else
            $fTaxa = 'get' . $fTaxa . 'Men';
        //Gera o nome da função a ser chamada na entity multiplosMinimos
        $fMax = 'getMax' . $fMin ;
        $fMin = 'getMin' . $fMin ;
        
        $calc = $vlr * ($this->data['taxa']->$fTaxa() / 100);
        
        // Se calculado for menor que o minimo retorna o min
        $vlrMin = floatval($this->data['multiplosMinimos']->$fMin());
        if($calc < $vlrMin)
            return $vlrMin;
        
        // Se calculado for maior que o maximo retorna o max
        $vlrMax = floatval($this->data['multiplosMinimos']->$fMax());
        if(($vlrMax != 0.0)AND($calc > $vlrMax))
            return $vlrMax;
        // Valor calculado
        return $calc;
    }
}
