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
        if(isset($data['limit']) AND $data['limit'] != '0'){
            $query->setMaxResults($data['limit']);
        }
        // Retorna um array com todo os registros encontrados
        return $query->getQuery()->getArrayResult();
    }
    
    public function orcareno($data){ 
        if (empty($data['inicio']) OR empty($data['fim']))
            return [];
        
        //Faz tratamento em campos que sejam data ou adm e  monta padrao
        $this->where = 'o.inicio >= :inicio AND o.inicio <= :fim AND ( o.status = :status OR o.status = :status2 )';
        $this->data['inicio'] = $data['inicio'];
        $this->data['fim']    = $data['fim'];
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->parameters['inicio'] = $this->data['inicio'];
        $this->parameters['fim']    = $this->data['fim'];
        $this->parameters['status'] = 'A';
        $this->parameters['status2'] = 'R';
        if(!empty($data['administradora'])){
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
        
        if(!empty($data['mesRefFiltro']) AND !empty($data['anoRefFiltro'])){
            $this->data['inicioMensal'] = '01/' . $data['mesRefFiltro'] . '/' . $data['anoRefFiltro'];
            $this->dateToObject('inicioMensal');
            $this->data['inicioMensal']->sub(new \DateInterval('P1M'));
            echo '<h1>', $this->data['inicioMensal']->format('d/m/Y'), '</h1>';
        }else{
            //Trata os filtro para data mensal
            $this->data['inicioMensal'] = clone $this->data['inicio'];
            // Pesquisar fechados mensal que terminam a vigencia no mes posterior
            $this->data['inicioMensal']->sub(new \DateInterval('P4M'));
        }
        
        $this->data['fimMensal'] = clone $this->data['inicioMensal'];
        $this->data['fimMensal']->add(new \DateInterval('P1MT20H'));
        $this->data['fimMensal']->sub(new \DateInterval('P1D'));
                
        $this->data['niver'] = (int)$data['mesFiltro'];

        //Filtro para ambos os casos
        $this->data['administradora'] = $data['administradora'];
        $this->data['anual'] = isset($data['anual']) ? TRUE : FALSE;
        $this->data['mensal'] = isset($data['mensal']) ? TRUE : FALSE;
        // Gerar log
        $obs = 'Simulação do Mapa Renovação:<br>';
        $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $obs .= empty($data['upAluguel']) ? '' : 'Reajustar em : ' . $data['upAluguel'] .'<br>';
        $obs .= isset($data['anual']) ? 'Gerar Anual' .'<br>' : '';
        $obs .= isset($data['mensal']) ? 'Gerar mensal' .'<br>' : '';
        $this->logForSis('fechados', '', 'relatorio', 'listarMapaRenovacao', $obs);
        return $this->em->getRepository("Livraria\Entity\Fechados")->getMapaRenovacao($this->data); 
    }
    
    public function gerarMapa($sc, $admFiltro){
        //Pegando o serviço de orçamento
        $servico = new Fechados($this->em);
        $servico->setFlush(FALSE);
        $array = $sc->mapaRenovacao;
        $data = $sc->data;
        $reajuste = $this->strToFloat($data['upAluguel'], 'float');
        $mes = (int)$data['mesFiltro'];
        $ano = (int)$data['anoFiltro'];
        $indClear = 100;
        $ind = $ok = $ng = 0;
        foreach ($array as $key => $value) {
            //Filtro Administradora
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            $array[$key]['resul'] = $servico->fechadoToOrcamento($value['id'], $mes, $ano, $reajuste);
            if($array[$key]['resul'][0] === TRUE){
                $ok++;
            }  else {
                $ng++;
            }
            $ind ++;
            if(($ind % $indClear) === 0){
                $this->em->flush();
                $this->em->clear();
                echo 'Gerou mapa ', $indClear, ' Orçamentos';
                @flush();
            }
        }
        $this->em->flush();
        $sc->mapaRenovacao = $array;
        // Gerar log
        $obs = 'Gerou Mapa Renovação:<br>';
        $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $obs .= empty($data['upAluguel']) ? '' : 'Reajustar em : ' . $data['upAluguel'] .'<br>';
        $obs .= isset($data['anual']) ? 'Gerar Anual' .'<br>' : '';
        $obs .= isset($data['mensal']) ? 'Gerar mensal' .'<br>' : '';
        $obs .= 'Total = '. ($ok + $ng) .'<br>';
        $obs .= 'Sucesso = '. $ok .'<br>';
        $obs .= 'Criticados = '. $ng .'<br>';
        $this->logForSis('orcamento', '', 'relatorio', 'gerarMapa', $obs);
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
        $total  = 0;
        foreach ($sc->mapaRenovacao as $value) {
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                $value['administradora']['email'] = $this->mailDefault; 
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
                    $this->sendEmailMapaRenovacaoAnual($servEmail, $admCod, $sc, $admEmai, $mesPrazo, $admNom, $data);
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $data    = [];              
                $i       = 0;
                $total++;
            }
            //Faz o acumulo dos dados.
            $data[$i][0] = ($value['validade'] == 'anual') ? $value['fim']->format('d/m') : $value['fim']->format('d/') . $mes ;
            $data[$i][0] .= ' - ' . $value['validade'] . (($value['validade'] == 'mensal') ? '(' . $value['mesNiver'] . ')' : '');
            $data[$i][1] = $value['fim']->format('d/m');
            $data[$i][2] = $value['refImovel'];
            $data[$i][3] = $value['validade'];
            $data[$i][4] = $value['imovel']['rua'] . ' n-' . $value['imovel']['numero']. ' ' . $value['imovel']['apto']. ' ' . $value['imovel']['bloco'];
            $data[$i][5] = $value['locatarioNome'];
            $data[$i][6] = number_format($value['incendio'], 2, ',', '.');
            $data[$i][7] = number_format($value['aluguel'], 2, ',', '.');
            $data[$i][8] = number_format($value['eletrico'], 2, ',', '.');
            $data[$i][9] = number_format($value['vendaval'], 2, ',', '.');
            if($value['validade'] == 'anual'){
                $formaP = isset($formaPagto[$value['formaPagto']]) ? $formaPagto[$value['formaPagto']] : 'Ñ encontrado' . $value['formaPagto'];;
            }else{
                $formaP = 'Mensal';
            }
            $data[$i][10] = $formaP;
            $data[$i][11] = $value['premioTotal'] / intval(($value['formaPagto'] == '04') ? '12' : $value['formaPagto']);;
            $data[$i][12] = number_format($value['premioTotal'], 2, ',', '.');
            $data[$i][13] = number_format($value['valorAluguel'], 2, ',', '.');
            $data[$i][14] = $value['atividade']['descricao'];
            $data[$i][15] = ($reajuste == 1)? '': number_format($value['valorAluguel'] * $reajuste, 2, ',', '.');
            $data[$i][16] = $value['locadorNome'];
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){
            $this->sendEmailMapaRenovacaoAnual($servEmail, $admCod, $sc, $admEmai, $mesPrazo, $admNom, $data);
            $total++;                    
        }
        
        if($total > 0){
            $obs = 'Enviou Email do Mapa Renovação:<br>';
            $obs .= 'Quantidade de emails enviados = '. $total .'.';
            $this->logForSis('', '', 'relatorios', 'sendEmailMapaRenovacao', $obs);
        }
        
        return true;
        
    }
    
    public function sendEmailMapaRenovacaoAnual(&$servEmail, &$admCod, &$sc, &$admEmai, &$mesPrazo, &$admNom, &$data) {
        $servEmail->enviaEmail(['nome' => $admNom,
                'emailNome' => $admNom,
                'cod' => $admCod,
                'date' => $sc->data,
                'email' => $admEmai,
                'mesPrazo' => $mesPrazo,
                'subject' => $admNom . ' - Renovação de Seguro - Mês ' . $sc->data['mesFiltro'],
                'data' => $data],'mapa-renovacao'); 
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
                $value['administradora']['email'] = $this->mailDefault; 
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $servEmail->enviaEmail(['nome' => $admNom,
                        'emailNome' => $admNom,
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
                'emailNome' => $admNom,
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
        //Retirar da forma de pag a descrição entre parenteses.
        foreach ($formaPagto as $key => $value) {
            $formaPagto[$key] = substr($value, 0, strpos($value, '('));
        }
        $admCod  = 0;
        $mes = '';
        foreach ($sc->fechaSeguro as $value) {
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                $value['administradora']['email'] = $this->mailDefault; 
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $this->sendEmailSegFechForAdm($servEmail, $admNom, $admEmai, $data, $mes);                   
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $mes     = $value['inicio']->format('m');
                $data    = [];              
                $i       = 0;
            }
            //Faz o acumulo dos dados.
            $data[$i][] = $value['refImovel'];
            $data[$i][] = $value['locadorNome'];
            $data[$i][] = $value['locatarioNome'];
            $data[$i][] = $value['inicio']->format('d/m/Y');
            $frmPagto = isset($formaPagto[$value['formaPagto']]) ? $formaPagto[$value['formaPagto']] : 'Ñ encontrado' . $value['formaPagto'];
            if($value['validade'] == 'mensal'){
                $frmPagto = 'Pag Mensal';
            }  
            $data[$i][] = $frmPagto;
            $data[$i][] = number_format($value['premioTotal'] / intval(($value['formaPagto'] == '04') ? '12' : $value['formaPagto']), 2, ',', '.');
            
            $rua = trim($value['imovel']['rua']);
            $num = trim($value['imovel']['numero']);
            $apt = trim($value['imovel']['apto']);
            $blc = trim($value['imovel']['bloco']);
            $cpt = trim($value['imovel']['compl']);
            if(empty($num) OR $num == 0){
                $end = $rua . ' ' . $blc . ' ' . $apt . ' ' . $cpt . ' CEP ' . $value['imovel']['cep'];
            }else{
                $end = $rua . ' n:' . $num . ' ' . $blc . ' ' . $apt . ' ' . $cpt . ' CEP ' . $value['imovel']['cep'];
            }
            $data[$i][] = $end;
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){   
            $this->sendEmailSegFechForAdm($servEmail, $admNom, $admEmai, $data, $mes);
        }
        
        return true;
        
    }
    
    /**
     * Faz o envio de email do seguros fechados faturamento.
     * @param type $servEmail
     * @param type $admNom
     * @param type $admEmai
     * @param type $data
     * @param type $mes
     */
    public function sendEmailSegFechForAdm(&$servEmail, &$admNom, &$admEmai, &$data, $mes) {
        $servEmail->enviaEmail(['nome' => $admNom,
            'emailNome' => $admNom,
            'email' => $admEmai,
            'subject' => $admNom . ' - Fatura - Mês ' . $mes,
            'data' => $data], 'seguro-fechado');
    }
    
    public function sendEmailOnlyRenovacao($sl,$admFiltro='') {
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if(empty($sc->dataOrcareno)){
            return;
        }

        $servEmail = $sl->get('Livraria\Service\Email');
        $formaPagto = $this->em->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        //Retirar da forma de pag a descrição entre parenteses.
        foreach ($formaPagto as $key => $value) {
            $formaPagto[$key] = substr($value, 0, strpos($value, '('));
        }
        
        $admCod  = 0;
        $mes = '';
        foreach ($sc->dataOrcareno as $value) {
            //Filtrar orcamentos
            if($value['orcaReno'] == 'orca'){
                continue;
            }
            //Filtro Administrador
            if(!empty($admFiltro) AND $admFiltro != $value['administradora']['id']){
                continue;
            }
            //caso venha configurado para nao enviar email ou vazio
            if(strtoupper($value['administradora']['email']) == 'NAO' OR empty($value['administradora']['email'])){
                $value['administradora']['email'] = $this->mailDefault; 
            }
            //se mudar adm faz envio e reseta os valores
            if($admCod != $value['administradora']['id']){
                if($admCod != 0){
                    $this->sendEmailOnlyRenovacaoMonta($servEmail, $admNom, $admEmai, $data, $mes);                   
                }
                $admCod  = $value['administradora']['id'];
                $admNom  = $value['administradora']['nome'];
                $admEmai = $value['administradora']['email'];
                $mes     = $value['inicio']->format('m');
                $data    = [];              
                $i       = 0;
            }
            //Faz o acumulo dos dados.
            $data[$i][] = $value['refImovel'];
            $data[$i][] = $value['locadorNome'];
            $data[$i][] = $value['locatarioNome'];
            $data[$i][] = $value['inicio']->format('d/m/Y');
            if($value['validade'] == 'anual'){
                $formaP = isset($formaPagto[$value['formaPagto']]) ? $formaPagto[$value['formaPagto']] : 'Ñ encontrado' . $value['formaPagto'];;
            }else{
                $formaP = 'Mensal';
            }
            $data[$i][] = $formaP;
            $data[$i][] = number_format($value['premioTotal'] / intval(($value['formaPagto'] == '04') ? '12' : $value['formaPagto']), 2, ',', '.');
            $i++;
        }
        
        //Envia ultima administradora se houver
        if($admCod != 0){   
            $this->sendEmailOnlyRenovacaoMonta($servEmail, $admNom, $admEmai, $data, $mes);
        }
        
        return true;
        
    }
    
    /**
     * Faz o envio de email do seguros em renovação ainda não fechados.
     * @param type $servEmail
     * @param type $admNom
     * @param type $admEmai
     * @param type $data
     * @param type $mes
     */
    public function sendEmailOnlyRenovacaoMonta(&$servEmail, &$admNom, &$admEmai, &$data, $mes) {
        $servEmail->enviaEmail(['nome' => $admNom,
            'emailNome' => $admNom,
            'email' => $admEmai,
            'subject' => $admNom . ' - Renovação Pendente - Mês ' . $mes,
            'data' => $data], 'renovacao-pendente');
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
        // Gerar log
        $obs = 'Paramentros da pesquisa:<br>';
        $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['seguradora']) ? '' : 'Seguradora : ' . $data['seguradora'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $obs .= empty($data['comissao']) ? '' : 'Comissão : ' . $data['comissao'] .'<br>';
        $obs .= isset($data['anual']) ? 'Gerar Anual' .'<br>' : '';
        $obs .= isset($data['mensal']) ? 'Gerar mensal' .'<br>' : '';
        $this->logForSis('fechados', '', 'relatorio', 'gerarComissao', $obs);
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
        /* @var $rpstr \Livraria\Entity\FechadosRepository */
        $rpstr = $this->em->getRepository("Livraria\Entity\Fechados");
        $lista = $rpstr->getFaturar($this->data);
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