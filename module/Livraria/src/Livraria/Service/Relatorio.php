<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\Container as SessionContainer;

/**
 * AbstractService
 * Tem os metodos basicos para o Crud no BD
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Relatorio extends AbstractService{
    
    /**
     * Colunas selecionadas para o relatorio
     * @var string 
     */
    protected $colunas;
    
    /**
     * clasula where da pesquisa
     * @var string 
     */
    protected $where;
    /**
     * clasula where da pesquisa
     * @var array 
     */
    protected $parameters;
    
    /**
     * Objeto com SessionContainer
     * @var object 
     */
    protected $sc;
    
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
     
    public function montaQuery($data){
        if(empty($data)){
            return [];
        }
        //Montar a primeira parte do Where Obrigatorio
        if(empty($data['mesNiver'])){
            $this->where = 'o.id IS NOT NULL';
            $this->parameters = [];
        }else{
            $this->where = 'o.mesNiver = :omesNiver';
            $this->parameters = ['omesNiver' => $data['mesNiver']];
        }
        $this->colunas = implode(',', $data['campo']);
        //Faz leitura em todos os filtro da tela e vai montando restante da clausula where
        $this->data = $data['valor'];
        foreach ($data['filtro'] as $key => $filtro) {
            if(empty($data['comando'][$key]) OR empty($data['valor'][$key]) OR empty($filtro)){
                continue;
            }
            //Faz tratamento em campos que sejam data ou pesquisa de documento cpf e cnpj e status se nao monta padrao
            switch ($filtro) {
                case 'o.inicio':
                    $param = isset($this->parameters['dataI']) ? 'dataI2' : 'dataI';
                    $this->where .= ' AND o.inicio ' . $data['comando'][$key] . ' :' . $param;
                    $this->dateToObject($key);
                    $this->parameters[$param] = $this->data[$key];
                    break;
                case 'o.fim':
                    $param = isset($this->parameters['dataF']) ? 'dataF2' : 'dataF';
                    $this->where .= ' AND o.fim ' . $data['comando'][$key] . ' :' . $param;
                    $this->dateToObject($key);
                    $this->parameters[$param] = $this->data[$key];
                    break;
                case 'ld.Doc':
                    $this->where .= ' AND (ld.cpf ' . $data['comando'][$key] . ' :ldcpf OR ld.cnpj ' . $data['comando'][$key] . ' :ldcnpj)';
                    $this->parameters['ldcpf'] = $data['valor'][$key];
                    $this->parameters['ldcnpj'] = $data['valor'][$key];
                    break;
                case 'lc.Doc':
                    $this->where .= ' AND (lc.cpf ' . $data['comando'][$key] . ' :lccpf OR lc.cnpj ' . $data['comando'][$key] . ' :lccnpj)';
                    $this->parameters['lccpf'] = $data['valor'][$key];
                    $this->parameters['lccnpj'] = $data['valor'][$key];
                    break;
                case 'o.status':  // Status decide em qual tabela vai fazer a busca entre orcamento e renovacao
                    $st = $data['valor'][$key];
                    switch ($st) {
                        case '1':
                            $status = 'A';
                            $orcaReno = 'orca';
                            break;
                        case '2':
                            $status = 'R';
                            $orcaReno = 'reno';
                            break;
                        case '3':
                            $status = 'F';
                            $orcaReno = FALSE;
                            break;
                        case '4':
                            $status = 'C';
                            $orcaReno = 'orca';
                            break;
                        case '5':
                            $status = 'C';
                            $orcaReno = 'reno';
                            break;
                    }
                    if($orcaReno){
                        $this->where .= ' AND o.status = :status AND o.orcaReno = :orcaReno';
                        $this->parameters['orcaReno'] = $orcaReno;                        
                    }else{
                        $this->where .= ' AND o.status = :status';                        
                    }
                    $this->parameters['status'] = $status;
                    break;
                default:
                    $op = $data['comando'][$key];
                    $param = str_replace('.', '', $filtro);
                    $this->where .= ' AND ' . $filtro . ' ' . $op . ' :' . $param;
                    if($op == 'LIKE'){
                        $this->parameters[$param] = $data['valor'][$key] . "%";
                    }else{
                        $this->parameters[$param] = $data['valor'][$key];
                    }
                    break;
            }
        }
        return $this->getInOrcamento ($data);
    }

    public function getInOrcamento($data){        
        return $this->getIn($data, 'Orcamento');
    }

    public function getInRenovacao($data){        
        return $this->getIn($data, 'Renovacao');
    }

    public function getIn($data, $tabela='Orcamento'){
        
        // Monta a dql para fazer consulta no BD
        $query = $this->em
                ->createQueryBuilder()
                ->select($this->colunas)
                ->from('Livraria\Entity\\' . $tabela, 'o')
                ->join('o.user', 'u')
                ->join('o.imovel', 'i')
                ->join('i.endereco', 'e')
                ->join('e.bairro', 'b')
                ->join('e.cidade', 'c')
                ->join('e.estado', 'uf')
                ->join('o.locador', 'ld')
                ->join('o.locatario', 'lc')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.seguradora', 's')
                ->where($this->where)
                ->setParameters($this->parameters);
        
        // Ordena pelo escolhido se houver
        if(isset($data['orderBy']) AND !empty($data['orderBy'])){
            $query->orderBy($data['orderBy'], 'ASC');
        }// Ordena pelo escolhido se houver
        if(isset($data['limit']) AND $data['orderBy'] != '0'){
            $query->setMaxResults($data['limit']);
        }
        // Retorna um array com todo os registros encontrados
        return $query->getQuery()->getArrayResult();
    }
    
    public function orcareno($data){ 
        if (empty($data['inicio']) OR empty($data['fim']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND o.status = :status';
        $this->data['inicio'] = $data['inicio'];
        $this->data['fim']    = $data['fim'];
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->parameters['inicio'] = $this->data['inicio'];
        $this->parameters['fim']    = $this->data['fim'];
        $this->parameters['status'] = 'F';
        if($data['administradora']){
            $this->where .= ' AND o.administradora = :administradora';
            $this->parameters['administradora']    = $data['administradora'];            
        }
        
        return $this->getOrcareno('Orcamento');
        
        /*
        $merge = array_merge($this->getOrcareno('Orcamento'), $this->getOrcareno('Renovacao'));
        $lista=[];
        foreach ($merge as $key => $value) {
            $lista[$key] = $value['administradora']['id'];
        }
        array_multisort($lista, SORT_ASC, SORT_NUMERIC, $merge);
        return $merge;
         */
    }
    
    public function getOrcareno($tabela){ 
        // Monta a dql para fazer consulta no BD
        $query = $this->em
                ->createQueryBuilder()
                ->select('o,ad,i')
                ->from('Livraria\Entity\\' . $tabela, 'o')
                ->join('o.user', 'u')
                ->join('o.imovel', 'i')
                ->join('i.endereco', 'e')
                ->join('e.bairro', 'b')
                ->join('e.cidade', 'c')
                ->join('e.estado', 'uf')
                ->join('o.locador', 'ld')
                ->join('o.locatario', 'lc')
                ->join('o.administradora', 'ad')
                ->join('o.atividade', 'at')
                ->join('o.seguradora', 's')
                ->where($this->where)
                ->setParameters($this->parameters)
                ->orderBy('o.administradora', 'ASC');
        
        // Retorna um array com todo os registros encontrados
        return $query->getQuery()->getArrayResult();
    }
    
    public function mapaRenovacao($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        
        //Trata os filtro para data mensal
        $this->data['mes'] = intval($data['mesFiltro']);
        $mes = date('m');
        $this->data['inicioMensal'] = '01/' . $mes . '/' . date('Y');
        $this->dateToObject('inicioMensal');
        // Pesquisar fechados de 2 ou 1 meses atras  
        $this->data['inicioMensal']->sub(new \DateInterval('P1M'));
        $this->data['fimMensal'] = clone $this->data['inicioMensal'];
        $this->data['fimMensal']->add(new \DateInterval('P1M'));
        $this->data['fimMensal']->sub(new \DateInterval('P1D'));

        //Filtro para ambos os casos
        $this->data['administradora'] = $data['administradora'];
        $this->data['anual'] = isset($data['anual']) ? TRUE : FALSE;
        $this->data['mensal'] = isset($data['mensal']) ? TRUE : FALSE;
        
        return $this->em->getRepository("Livraria\Entity\Fechados")->getMapaRenovacao($this->data); 
    }
    
    public function gerarMapa($sc, $admFiltro){
        //Pegando o serviço de orçamento
        $servico = new Fechados($this->em);
        $array = $sc->mapaRenovacao;
        $data = $sc->data;
        $reajuste = $this->strToFloat($data['upAluguel'], 'float');
        foreach ($array as $key => $value) {
            //Filtro Administradora
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            $array[$key]['resul'] = $servico->fechadoToOrcamento($value['id'], $data['mesFiltro'], $data['anoFiltro'], $reajuste);
        }
        $sc->mapaRenovacao = $array;
    }
    
    /**
     * Faz envio de email para imobiliaria com as renovações a serem feitas
     * Recebe o service locator para poder pegar o servido e email com suas dependencias
     * Recebe Filtro para administradoras
     * @param object $sl
     * @param string $admCod
     * @return boolean
     */
    public function sendEmailMapaRenovacao($sl,$admFiltro=''){
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if (empty($sc->mapaRenovacao))
            return FALSE;

        $servEmail = $sl->get('Livraria\Service\Email');
        $formaPagto = $sc->formaPagto;
        $mesPrazo = intval($sc->data['mesFiltro']);
        $mes = $sc->data['mesFiltro'];
        $ano = $sc->data['anoFiltro'];
        if($mesPrazo == 1){
            $mesPrazo = '12';
        }else{
            $mesPrazo--;
            $mesPrazo = ($mesPrazo < 10) ? '0'. $mesPrazo : $mesPrazo;            
        }
        $reajuste = empty($sc->data['upAluguel']) ? 1 : 1 + ($this->strToFloat($sc->data['upAluguel'], 'float') / 100);
        $admCod  = 0;
        foreach ($sc->mapaRenovacao as $value) {
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                continue; 
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            if($value['resul'][0] !== TRUE){
                continue;
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $servEmail->enviaEmail(['nome' => $admNom,
                        'cod' => $admCod,
                        'date' => $sc->data,
                        'email' => $admEmai,
                        'mesPrazo' => $mesPrazo,
                        'subject' => $admNom . ' -Seguro(s) para Renovação Anual do Incêndio Locação',
                        'data' => $data],'mapa-renovacao');                     
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $data    = [];              
                $i       = 0;
            }
            //Faz o acumulo dos dados.
            $data[$i][0] = ($value['validade'] == 'anual') ? $value['fim']->format('d/m/Y') : $value['fim']->format('d/') . $mes . '/' . $ano;
            $data[$i][0] .= ' - ' . $value['validade'] . (($value['validade'] == 'mensal') ? '(' . $value['mesNiver'] . ')' : '');
            $data[$i][1] = $value['fim']->format('d/m/Y');
            $data[$i][2] = $value['refImovel'];
            $data[$i][3] = $value['validade'];
            $data[$i][4] = $value['imovel']['rua'] . ' n-' . $value['imovel']['numero']. ' ' . $value['imovel']['apto']. ' ' . $value['imovel']['bloco'];
            $data[$i][5] = $value['locatarioNome'];
            $data[$i][6] = number_format($value['incendio'], 2, ',', '.');
            $data[$i][7] = number_format($value['aluguel'], 2, ',', '.');
            $data[$i][8] = number_format($value['eletrico'], 2, ',', '.');
            $data[$i][9] = number_format($value['vendaval'], 2, ',', '.');
            $data[$i][10] = isset($formaPagto[$value['formaPagto']]) ? $formaPagto[$value['formaPagto']] : 'Ñ encontrado' . $value['formaPagto'];;
            $data[$i][11] = $value['premioTotal'] / intval(($value['formaPagto'] == '04') ? '12' : $value['formaPagto']);;
            $data[$i][12] = number_format($value['premioTotal'], 2, ',', '.');
            $data[$i][13] = number_format($value['valorAluguel'], 2, ',', '.');
            $data[$i][14] = $value['atividade']['descricao'];
            $data[$i][15] = ($reajuste == 1)? '': number_format($value['valorAluguel'] * $reajuste, 2, ',', '.');
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){
            $servEmail->enviaEmail(['nome' => $admNom,
                'cod' => $admCod,
                'date' => $sc->data,
                'email' => $admEmai,
                'mesPrazo' => $mesPrazo,
                'subject' => $admNom . ' -Seguro(s) para Renovação Anual do Incêndio Locação',
                'data' => $data],'mapa-renovacao');                     
        }
        
        return true;
        
    }

    /**
     * Trata os filtros e carrega os dados do repository
     * @param array $data
     * @return array
     */
    public function listaImoDesoc($data){
        $this->data = $data;
        
        if(empty($this->data['inicio'])){
            return array();
        }
        
        $this->dateToObject('inicio');
        
        if(empty($this->data['fim'])){
            $this->data['fim'] = clone $this->data['inicio'];
            $this->data['fim']->add(new \DateInterval('P1M'));
            $this->data['fim']->sub(new \DateInterval('P1D'));
        }else{
            $this->dateToObject('fim');
        }
        
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->ImoveisDesocu = $this->em->getRepository("Livraria\Entity\Fechados")->getImoveisDesocupados($this->data); 
        $sc->data          = $data;
        
        return $sc->ImoveisDesocu;       
    }
    
    /**
     * Faz envio de email para imobiliaria com os imoveis desocupaos
     * Recebe o service locator para poder pegar o servido e email com suas dependencias
     * Recebe Filtro para administradoras
     * @param object $sl
     * @param string $admCod
     * @return boolean
     */
    public function sendEmailImoveisDesocupados($sl,$admFiltro='') {
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if (empty($sc->ImoveisDesocu))
            return FALSE;

        $servEmail = $sl->get('Livraria\Service\Email');

        $admCod  = 0;
        foreach ($sc->ImoveisDesocu as $value) {
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                continue; 
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $servEmail->enviaEmail(['nome' => $admNom,
                        'email' => $admEmai,
                        'subject' => $admNom . ' -Imóveis Desocupados do Incêndio Locação',
                        'data' => $data],'imovel-desocupado');                     
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $data    = [];              
                $i       = 0;
            }
            //Faz o acumulo dos dados.
            $data[$i][] = $value['inicio']->format('d/m/Y');
            $data[$i][] = $value['fim']->format('d/m/Y');
            $data[$i][] = $value['locatarioNome'];
            $data[$i][] = $value['locadorNome'];
            $data[$i][] = $value['imovel']['rua'] . ', n ' . $value['imovel']['numero'] . $value['imovel']['bloco'] . $value['imovel']['apto'];
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){
            $servEmail->enviaEmail(['nome' => $admNom,
                'email' => $admEmai,
                'subject' => $admNom . ' -Imóveis Desocupados do Incêndio Locação',
                'data' => $data],'imovel-desocupado');                     
        }
        
        return true;
    }
    
    /**
     * Trata os filtros e faz consulta no BD e cachea o resultado
     * @param array $data
     * @return array
     */
    public function listaFechaSeguro($data){
        $this->data = $data;
        
        if(empty($this->data['inicio'])){
            return array();
        }        
        $this->dateToObject('inicio');
        
        if(empty($this->data['fim'])){
            $this->data['fim'] = clone $this->data['inicio'];
            $this->data['fim']->add(new \DateInterval('P1M'));
            $this->data['fim']->sub(new \DateInterval('P1D'));
        }else{
            $this->dateToObject('fim');
        }        
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->fechaSeguro = $this->em->getRepository("Livraria\Entity\Fechados")->getListaFechaSeguro($this->data); 
        $sc->data        = $data;
        
        return $sc->fechaSeguro;          
    }
    
    /**
     * Faz envio de email para imobiliaria com os seguros fechados
     * Recebe o service locator para poder pegar o servido e email com suas dependencias
     * Recebe Filtro para administradoras
     * @param object $sl
     * @param string $admCod
     * @return boolean
     */
    public function sendEmailSegurosFechado($sl,$admFiltro=''){
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if (empty($sc->fechaSeguro))
            return FALSE;

        $servEmail = $sl->get('Livraria\Service\Email');
        $formaPagto = $this->em->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');

        $admCod  = 0;
        foreach ($sc->fechaSeguro as $value) {
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                continue; 
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $servEmail->enviaEmail(['nome' => $admNom,
                        'email' => $admEmai,
                        'subject' => $admNom . ' -Seguro(s) Fechado(s) do Incêndio Locação',
                        'data' => $data],'seguro-fechado');                     
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
            $data[$i][] = isset($formaPagto[$value['formaPagto']]) ? $formaPagto[$value['formaPagto']] : 'Ñ encontrado' . $value['formaPagto'];
            $data[$i][] = number_format($value['premioTotal'], 2, ',', '.');
            $data[$i][] = number_format($value['premioTotal'] / intval(($value['formaPagto'] == '04') ? '12' : $value['formaPagto']), 2, ',', '.');
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){
            $servEmail->enviaEmail(['nome' => $admNom,
                'email' => $admEmai,
                'subject' => $admNom . ' -Seguro(s) Fechado(s) do Incêndio Locação',
                'data' => $data],'seguro-fechado');                     
        }
        
        return true;
        
    }
        
    public function gerarComissao($data){
        //Trata os filtros para consulta
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        $this->data['administradora'] = $data['administradora'];
        $this->data['seguradora'] = $data['seguradora'];
        $this->data['comissao'] = $this->strToFloat($data['comissao'],'f');
        if(isset($data['anual']) AND isset($data['mensal'])){
            //Ambos selecionados nao usa filtro
            $this->data['validade'] = '';
        }else{
            //Se um tiver selecionado decide o filtro caso nenhum selecionado filtro anual
            $this->data['validade'] = isset($data['mensal']) ? 'mensal' : 'anual';
        }
        
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->comissao      = $this->em->getRepository("Livraria\Entity\Fechados")->getComissao($this->data); 
        $sc->data          = $this->data;
        
        return $sc->comissao; 
    }
    
    public function getRelatorio($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        // A vista
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        // Em 2 vezes as 1 parcelas
        $this->data['inicio2'] = $this->data['inicio'];
        $this->data['fim2']    = $this->data['fim'];
        // Em 3 vezes as 1 parcelas
        $this->data['inicio3'] = $this->data['inicio2'];
        $this->data['fim3']    = $this->data['fim'];
        // Em 2 vezes as 2 parcelas
//        $this->data['inicio2'] = clone $this->data['inicio'];
//        $this->data['inicio2']->sub(new \DateInterval('P1M'));
//        $this->data['fim2']    = clone $this->data['fim'];
        // Em 3 vezes as 3 parcelas
//        $this->data['inicio3'] = clone $this->data['inicio2'];
//        $this->data['inicio3']->sub(new \DateInterval('P1M'));
//        $this->data['fim3']    = clone $this->data['fim'];
        
        $this->data['administradora'] = $data['administradora'];
        
        $lista = $this->em->getRepository("Livraria\Entity\Fechados")->getFaturar($this->data);
        //Armazena lista no cache para gerar outra saidas
        $this->getSc()->lista = $lista;
        
        return $lista;
    }
    
    /**
     * 
     */
    public function getCustoRenovacao($data){
        if (empty($data['inicio']))
            return []; 
        
        $this->data = $data;
        
        $this->dateToObject('inicio');
        if(empty($this->data['fim'])){            
            $this->data['fim'] = clone $this->data['inicio'];
            $this->data['fim']->add(new \DateInterval('P1M'));
            $this->data['fim']->sub(new \DateInterval('P1D'));
        }else{
            $this->dateToObject('fim');            
        }
        
        return $this->em->getRepository("Livraria\Entity\Orcamento")->CustoRenovacao($this->data);
    }
    
}