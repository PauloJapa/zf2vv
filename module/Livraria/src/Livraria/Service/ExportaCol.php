<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;
use SisBase\Conexao\Mssql;

/**
 * Description of ExportaCol
 *
 * Gerar a partir dos seguros fechados a exportação para o COL fazendo conexão com
 * Banco de dados SQL Server e inserindo diretamente em suas tabelas.
 * 
 * @author Paulo Watakbe
 * 
 */
class ExportaCol extends AbstractService{
    
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
     * Conexão com Mssql
     * @var object 
     */
    protected $mssql;
    
    protected $fp;
    protected $zip;
    protected $saida;
    /**
     * Multi array com todos os registros da pesquisa
     * @var array 
     */
    protected $expts;
    /**
     * Guarda um registro do multi array $expts
     * @var array 
     */
    protected $expt;
    /**
     * Guarda todos os paramentros para inserção em varias tabelas do COL
     * @var array
     */
    protected $pr;
    protected $baseWork;
    protected $item;
    protected $tipoLocatario;
    protected $tipoLocador;
    protected $ativid;
    protected $qtdExportado;
    protected $fechadoRepository;


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
    
    public function getMssql(){
        if($this->mssql)
            return $this->mssql;
        $this->mssql = new Mssql();
        return $this->mssql;
    }
    
    public function geraTxtForCOL($adm=''){
        //echo '<h2>inicio</h2>';
        $data = $this->getSc()->data;
        $mes  = $data['mesFiltro'];
        $ano  = $data['anoFiltro'];
        $this->data['inicioMensal'] = '01/' . $mes . '/' . $ano;
        $this->dateToObject('inicioMensal');
        $this->data['fimMensal'] = clone $this->data['inicioMensal'];
        $this->data['fimMensal']->add(new \DateInterval('P1M'));
        $this->data['fimMensal']->sub(new \DateInterval('P1D'));
        $this->qtdExportado = 0 ;
        $verificaSeFez = $this->em
                         ->getRepository("Livraria\Entity\LogFaturaCol")
                         ->findBy(['administradoraId' => $adm, 'mes' => $mes, 'ano' => $ano ]);
        if($verificaSeFez){
            echo '<p>Já foi realizado a exportação dessa administradora nesse periodo!!!</p>';
            return false;
        }
        
        $this->baseWork = '/var/www/zf2vv/data/work/Col' . $data['anoFiltro'] . $data['mesFiltro'] . '/';
        if(!is_dir($this->baseWork)){
            mkdir($this->baseWork , 0777);
        }
        $zipFile = $this->baseWork . "Exporta_Col_Txt.zip";
        
        // Carregar as formas de pagamento
        $this->formaPagto = $this->em->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        $this->comissao = $this->em->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('comissaoParam00' . $data['seguradora'],false);
        foreach ($this->comissao as $key => $value) {
            $this->comissao[$key] = str_replace(',','.',$key);
        }
        
        $this->openZipFile($zipFile);
        if(!empty($adm)){
            $this->prepArqsForCol($adm);
        }else{
            $admArray = $this->getAdmCods();
            foreach ($admArray as $admCod) {
                $this->prepArqsForCol($admCod);
            }    
        }    
        $this->zip->close();    
        
        if($this->qtdExportado > 0){
            $obs = 'Gerou arquivo de texto para o Col:<br>';
            $obs .= 'Codigo da administradora = '. $adm .'.<br>';
            $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
            $obs .= 'Codigo da administradora = '. $adm .'.<br>';
            $obs .= 'Quantidade de registro gravados = '. $this->qtdExportado .'.<br>';
            $this->logForSis('fechados', '', 'exportar', 'geraTxtForCOL', $obs);
        }   
        return $zipFile;     
    }
    
    public function prepArqsForCol($adm){
        foreach ($this->comissao as $value) {
            $arq = $this->baseWork . $adm . '_' . $value . '_Col.txt';
//            echo '<pre>';
//        var_dump($arq);
            if($this->setConteudo($arq, $adm, $value)){
                $this->writeFile();
                $this->closeFile();  
                $this->addFileToZip($arq);
            }
        }
//        die;
    }
    
    /**
     * 
     * Gera o todo conteudo a gravar no arquivo a ser exportado para o COL
     * @param string $arq nome do arquivo na work
     * @param string $adm filtro codigo da administradora
     * @param float  $com filtro comissão para este arquivo
     * @return boolean
     */
    public function setConteudo($arq, $adm, $com){
        $head = TRUE;
        $this->item = 0;
        $this->saida = '';
        foreach ($this->getSc()->lista as $value) {
            // Filtrar a Administradora
            if($adm != $value['administradora']['id']){
                continue;
            }
            // Filtrar comissão
            if($com != number_format($value['comissao'], 2, '.', ',')){
//                var_dump(number_format($value['comissao'], 2, '.', ','));
                continue;
            }
            if($head){
                $this->openFile($arq);
                $head = FALSE;
            }            
            //locador nome 50
            $this->addSaida2($value['locadorNome'], 50);
            //echo $value['locadorNome'], '<br>',utf8_decode($value['locadorNome']) , "<br><br>";
            //locatario nome 50
            $this->addSaida2($value['locatarioNome'], 50);
            // imovel rua 50
            $this->addSaida2($value['imovel']['rua'], 50);
            // imovel numero 10
            $this->addSaida2($value['imovel']['numero'], 10, '0', 'STR_PAD_LEFT');     
            // imovel complemento 20
            $complemento = '';
            $sep         = '';
            if(!empty($value['imovel']['apto'])){
                $complemento = 'AP ' . $value['imovel']['apto'];
                $sep         = ', BL ';
            }
            if(!empty($value['imovel']['bloco'])){
                $complemento .= $sep . $value['imovel']['bloco'];
                $sep         = ', ';
            }
            if(!empty($value['imovel']['endereco']['compl'])){
                $complemento .= $sep . $value['imovel']['endereco']['compl'];
            }
            $this->addSaida2($complemento, 20);
            // Bairro
            $this->addSaida2($value['imovel']['endereco']['bairro']['nome'], 30);
            // Cidade
            $this->addSaida2($value['imovel']['endereco']['cidade']['nome'], 30);
            // Estado
            $this->addSaida2($value['imovel']['endereco']['estado']['sigla'], 3);
            // CEP
            $this->addSaida2($value['imovel']['cep'], 9);
            // Inicio Vigencia
            $this->saida .= $value['inicio']->format('dmYhis');
            // Fim Vigencia
            $this->saida .= $value['fim']->format('dmYhis');
            //Código da atividade(ocupação)	10
            $this->addSaida2($value['atividade']['codSeguradora'], 10, '0', 'STR_PAD_LEFT'); 
            // incendio
            // Se escolha entre Incendio ou Incendio + Conteudo
            if($value['tipoCobertura'] == '01' ){
                $incendio = $value['incendio'];   
            }else{
                $incendio = $value['conteudo'];   
            }
            $this->addSaida2(number_format($incendio, 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //alu
            $this->addSaida2(number_format($value['aluguel'], 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //ele
            $this->addSaida2(number_format($value['eletrico'], 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //ven
            $this->addSaida2(number_format($value['vendaval'], 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //n_parc numero_parcelas 10
            $this->addSaida2($value['formaPagto'], 10, '0', 'STR_PAD_LEFT'); 
            //premioliq
            $this->addSaida2(number_format($value['premioLiquido'], 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //totpremio
            $this->addSaida2(number_format($value['premioTotal'], 2, '', ''), 17, '0', 'STR_PAD_LEFT'); 
            //comissao
            $this->addSaida2(number_format($value['comissao'], 2, '', ''), 5, '0', 'STR_PAD_LEFT'); 
            //forma de pagamento(20)
            $this->addSaida2($this->formaPagto[$value['formaPagto']], 20);
            //Seguradora(20)
            $this->addSaida2($value['seguradora']['apelido'], 20);
            //Locador Doc(15)
            if($value['locador']['tipo'] == 'fisica'){
                $this->addSaida2(preg_replace('/[^0-9]/','',$value['locador']['cpf']), 15, '0', 'STR_PAD_LEFT'); 
            }else{
                $this->addSaida2(preg_replace('/[^0-9]/','',$value['locador']['cnpj']), 15, '0', 'STR_PAD_LEFT');                 
            }
            //Locatario Doc(15)
            if($value['locatario']['tipo'] == 'fisica'){
                $this->addSaida2(preg_replace('/[^0-9]/','',$value['locatario']['cpf']), 15, '0', 'STR_PAD_LEFT'); 
            }else{
                $this->addSaida2(preg_replace('/[^0-9]/','',$value['locatario']['cnpj']), 15, '0', 'STR_PAD_LEFT');                 
            }
            // Fim da linha 
            $this->saida .= PHP_EOL; 
            
            $this->qtdExportado ++;
            $this->item ++;
        }  
        if($this->item == 0){
            return false;
        }else{
            return true;
        }
    } 
    
    /**
     * Incrementa variavel e formata para saida para gravação
     * @param type $conteudo Dados a ser incrementado
     * @param type $tam      Quantidade de caracteres
     * @param type $compl    Completar com esse caractere(padrão spaços)
     * @param type $opt      Para completar no lado esquerdo ou direito(Padrão)
     */
    public function addSaida2($conteudo,$tam,$compl='',$opt=''){
        if(empty($opt)){
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam);             
        }else{
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam, $compl, STR_PAD_LEFT);            
        }
    }   
   
    
    /**
     * Instancia um Object Zip e abre o arquivo indicado na string
     * @param string $zipFile
     * @return boolean
     */
    public function openZipFile($zipFile){
        $this->zip = new \ZipArchive;
        if($this->zip->open($zipFile, \ZipArchive::OVERWRITE)  !== true){
            echo 'erro';
            return FALSE;
        }
    }

    /**
     * Adiciona um arquivo no objeto zip aberto 
     * Preciso do caminho absoluto e a da base do diretorio para separa o nome do arquivo
     * @param type $file
     * @param type $this->baseWork
     */
    public function addFileToZip($file){
        $name = substr($file, strlen($this->baseWork));
        $this->zip->addFile($file,$name);
    }

    /**
     * Abre arquivo para gravar caso já exista limpa seu conteudo
     * @param string $arq
     */
    public function openFile($arq) {
        $this->fp = fopen($arq, "w");
    }

    /**
     * Escreve o conteudo no arquio aberto
     */
    public function writeFile(){
        fwrite($this->fp, $this->saida);
    }

    /**
     * Fecha arquivo aberto
     */
    public function closeFile(){
        fclose($this->fp);
    }
    
    /**
     * Retorna somente os ids das Administradoras encontrados na consulta
     * @return array
     */
    public function getAdmCods(){
        $array = [];
        $auxCod = 0;
        foreach ($this->getSc()->lista as $value) {
            if ($auxCod == 0 OR $auxCod != $value['administradora']['id']){
                $array[] = $auxCod = $value['administradora']['id'];
            }
        }
        return $array;
    }
    
    public function geraExpForCOL($adm){
        echo '<h2>inicio</h2>';
        $this->pr['vue'] = $adm;
        $data = $this->getSc()->data;
        $mes  = $data['mesFiltro'];
        $ano  = $data['anoFiltro'];
        $this->data['inicioMensal'] = '01/' . $mes . '/' . date('Y');
        $this->dateToObject('inicioMensal');
        $this->data['fimMensal'] = clone $this->data['inicioMensal'];
        $this->data['fimMensal']->add(new \DateInterval('P1M'));
        $this->data['fimMensal']->sub(new \DateInterval('P1D'));
        $this->qtdExportado = 0 ;
        $verificaSeFez = $this->em
                         ->getRepository("Livraria\Entity\LogFaturaCol")
                         ->findBy(['administradoraId' => $adm, 'mes' => $mes, 'ano' => $ano ]);
        if($verificaSeFez){
            echo '<p>Já foi realizado a exportação dessa administradora nesse periodo!!!</p>';
            return false;
        }
        
        $this->expts = $this->getSc()->lista;
        
        if($adm == 3234){
            $this->exportaLello('C'); //empresarial
            $this->exportaLello('R'); //residencial
        }else{
            $this->exporta('C','1');
            $this->exporta('C','2');
            $this->exporta('C','3');
            $this->exporta('C','mensal');
            $this->exporta('R','1');
            $this->exporta('R','2');
            $this->exporta('R','3');
            $this->exporta('R','mensal');
        }
die;        
        if($this->qtdExportado != 0){
            $this->logExportadoCol($mes,$ano);
        }
    }  
    
    public function exportaLello($ocup){
        echo '<h2>inicio llelo</h2>';
        $this->pr['vCliente'] = false;
        $dia = 0;
        $this->resetaValores();
        foreach ($this->expts as $this->expt) {
            // lello somente faz seguros com Allianz
            if($this->expt['seguradora']['id'] != 3){
                echo '<p>Seguradora não é a Allianz fechado numero ' . $this->expt['id'] . '</p>';
                continue;
            }
            // Filtrar apenas Comercial
            if($ocup == 'C'){
                if($this->ativid == 911 OR $this->ativid == 919){
                    continue; // é residencial entao filtra
                }
                $this->pr['vTipo_ocupacao']          = 'COMERCIAL';
            }else{ // Filtrar apenas residencial
                if($this->ativid != 911 AND $this->ativid != 919){
                    continue; // não é residencial entao filtra
                }                
                $this->pr['vTipo_ocupacao']          = 'RESIDENCIAL';
            }
            
            // Monta header e footer conforme fluxo do loop de exportação lello tem um header e footer por dia
            $diaSeg = $this->expt['inicio']->format('d');
            if($dia != $diaSeg){
                // <=================== INICIO DO FOOTER (CALCULO DE COMISSÕES) 1 POR DIA
                // Somando IOF e configurando variaveis e Calculando Comissão
                // Inserindo Divisoes na fatura (1º Corretora) 
                // Inserindo Divisoes na fatura (2º UE)
                // Inserindo Divisoes na fatura (3º UI)
                // Zerando variaveis para o proximo loop
                if($dia != 0){
                    $this->insertColFooter();
                }    
                // ============== HEADER DA IMPORTAÇÃO  UM HEADER POR DIA !!!
                // Seleciona o Produto do COL
                // Seleciona os parametros do produto
                // Pega numero documento, alteracao, proposta e textos na tabela par_cont
                // Abrindo nova fatura (Cada linha tem 9 campo da tabela)
                //Pegando valor do Numero do item          
                $resul = $this->insertColHeader($ocup);
                if(!$resul){
                    echo '<p>Falha ao montar header iniciando por seg: ' . $this->expt['id'] . '</p>';
                    continue;                    
                }
                $dia = $diaSeg;
            }
            
            $this->qtdExportado++;
            // ============== RESTANTES DO REGISTROS DO MESMO DIA
            // Pega numero item na tabela par_cont
            // Pegando valor do Numero do item para Tabela DocsItensCobs
            // Inserir endereço dos itens
            // Inserindo dados na tabela DocsItensCobs (INCENDIO)
            // Inserindo dados na tabela DocsItensCobs (PERDA ALUGUEL)
            // Inserindo dados na tabela DocsItensCobs (DANOS ELÉTRICOS)
            // Inserindo dados na tabela DocsItensCobs (VENDAVAL)
            // Carregando variaveis dos premios e juros (Faz acumulo Geral)
            $this->insertColBody();
        }
        // Monta o ultimo FOOTERCOL de saida do loop caso dia <> 0
        if($dia != 0){
            $this->insertColFooter();
        }        
    }
    
    
    
    public function exporta($ocup,$formaPgto){
        echo '<h2>inicio Outras Adms ' . $ocup . ' ' . $formaPgto . '</h2>';
        $this->pr['vCliente'] = false;
        $qtd = 0;
        $this->resetaValores();
        foreach ($this->expts as $this->expt) {
            // Outras Adm somente faz seguros com Maritima
            if($this->expt['seguradora']['id'] != 2){
                echo '<p>Seguradora não é a Maritima fechado numero ' . $this->expt['id'] . '</p>';
                continue;
            }
            // Filtra forma de pagamento Mensal, avista, 2 ou 3 vezes
            if($formaPgto == 'mensal'){
                if($this->expt['validade'] != 'mensal'){
                    continue;                
                }
            }else{
                if($formaPgto != $this->expt['formaPagto']){
                    continue;
                }
            }
            // Filtrar apenas Comercial
            if($ocup == 'C'){
                if($this->ativid == 911 OR $this->ativid == 919){
                    continue; // é residencial entao filtra
                }
                $this->pr['vTipo_ocupacao']          = 'COMERCIAL';
            }else{ // Filtrar apenas residencial
                if($this->ativid != 911 AND $this->ativid != 919){
                    continue; // não é residencial entao filtra
                }                
                $this->pr['vTipo_ocupacao']          = 'RESIDENCIAL';
            }   
            // ============== HEADER DA IMPORTAÇÃO  UM HEADER POR FORMA DE PAGAMENTO !!!
            // Seleciona o Produto do COL
            // Seleciona os parametros do produto
            // Pega numero documento, alteracao, proposta e textos na tabela par_cont
            // Abrindo nova fatura (Cada linha tem 9 campo da tabela)
            //Pegando valor do Numero do item  
            if($qtd == 0){
                $resul = $this->insertColHeader($ocup);
                if(!$resul){
                    echo '<p>Falha ao montar header iniciando por seg: ' . $this->expt['id'] . '</p>';
                    continue;                    
                }
            }
            $qtd++;
            $this->insertColBody();
        }
        // Monta o ultimo FOOTERCOL de saida do loop caso dia <> 0
        if($qtd != 0){
            $this->insertColFooter();
        } 
    }
    
    public function insertColHeader($ocup){
        // ============== HEADER DA IMPORTAÇÃO  UM HEADER POR DIA !!!
        if(!$this->pr['vCliente']){
            $this->pr['vCliente'] = $this->expt['administradora']['codigoCol'];
            if ($this->pr['vCliente'] == '' OR $this->pr['vCliente'] == 0){
                echo '<p>Administradora sem codigo COL para exportação</p>';
                return false;
            }
        }            
        // Seleciona o Produto do COL
        $this->pr['vProduto'] = $this->getProduto($ocup);            
        // Seleciona os parametros do produto
        $this->getProdutoParams($this->pr['vProduto'], $this->expt['comissao']);            
        // Pega numero documento, alteracao, proposta e textos na tabela par_cont
        $this->getDocumentos();            
        // Abrindo nova fatura (Cada linha tem 9 campo da tabela)
        $this->insertToDocumentos();            
        //Pegando valor do Numero do item
        $this->pr['vNum_item'] = $this->getNumItem();            
        // ============== FIM HEADER DA IMPORTAÇÃO  UM HEADER POR DIA !!!
        return TRUE;
    }
    
    public function insertColBody(){
        // ============== Registros pertencentes ao corpo de exportação
        // Pega numero item na tabela par_cont
        $this->pr['vItem'] = $this->getVItem();              
        // Pegando valor do Numero do item para Tabela DocsItensCobs
        $this->pr['vItemCob'] = $this->pr['vItem'];              
        // Inserir endereço dos itens
        $this->insertToDocsItens();            
        // Inserindo dados na tabela DocsItensCobs (INCENDIO)
        $this->insertItensCobIncendio();            
        // Inserindo dados na tabela DocsItensCobs (PERDA ALUGUEL)
        $this->insertItensCobPerdaAluguel();            
        // Inserindo dados na tabela DocsItensCobs (DANOS ELÉTRICOS)
        $this->insertItensCobDanosEletricos();            
        // Inserindo dados na tabela DocsItensCobs (VENDAVAL)
        $this->insertItensCobVendaval();            
        // Carregando variaveis dos premios e juros (Faz acumulo Geral)
        $this->acumulaValores();          
    }
    
    public function insertColFooter(){
        // Somando IOF e configurando variaveis e Calculando Comissão
        $this->insertValores();            
        // Inserindo Divisoes na fatura (1º Corretora) 
        $this->insertDivisaoFatCorretora();            
        // Inserindo Divisoes na fatura (2º UE)
        $this->insertDivisaoFatAdm();            
        // Inserindo Divisoes na fatura (3º UI)
        $this->insertDivisaoFatUI();            
        // Zerando variaveis para o proximo loop
        $this->resetaValores();        
    }  
    
    public function insertDivisaoFatUI(){
        $q  = $this->insertDivisaoFatHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", 3, 11710, 0, 0, 0, 0, 0, '0', 0, '4', '2', 0, '" . $this->pr['vData_venc_primeira'] . "', ";
        $q .= "'C', '1', '2', 100, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "', 0 ";
        $q .= ")";
        print_r($q); echo '<br><br>'; return; 
        $this->getMssql()->q($q);  
        
    }
    
    public function insertDivisaoFatAdm(){
        $q  = $this->insertDivisaoFatHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", 2, " . $this->pr['vue'] . ", 1, 0, 0, 50, 0, '0', 1, '4', '2', 0, '" . $this->pr['vData_venc_primeira'] . "', ";
        $q .= "'P', '1', '2', 100, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "', 2140 ";
        $q .= ")";
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q); 
    }
    
    public function insertDivisaoFatCorretora(){
        $q  = $this->insertDivisaoFatHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", 1, 11601, 0, 0, 0, 0, 0, '0', 0, '4', '2', 0, '" . $this->pr['vData_venc_primeira'] . "', ";
        $q .= "'C', '1', '1', 100, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "', 0 ";
        $q .= ")";
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q);        
    }
    
    public function insertDivisaoFatHeader(){
        $q  = "INSERT INTO Tabela_DocsRepasses ( ";
        $q .= "Documento, Alteracao, Nivel, Divisao, Forma_recebimento, Forma_parcelamento, Moeda_repasses, Perc_repasse, ";
        $q .= "Perc_desconto, Incide_sobre_adic, Qtde_parcelas, Base_venc_prim, Base_venc_demais, Dias_venc_primeira, ";
        $q .= "Data_venc_primeira, Base_calculo, Repasse_quitado, Remuneracao, Perc_part, Valor_repasse, Valor_base, ";
        $q .= "Situacao, Data_inclusao, NivelDiv_com ";
        $q .= ") VALUES ( ";
        return $q;       
    }
    
    public function insertValores(){
        $this->pr['vIof']       = $this->pr['vPremio_total'] - $this->pr['vPremio_liquido'] ;
        $this->pr['vIofParc']   = $this->pr['vIof'] / $this->pr['vParcela'];  
        
        $this->pr['vPremio_totalParc']    = $this->pr['vPremio_total'] / $this->pr['vParcela'];  

        $this->pr['vPremio_liquidoParc']  = $this->pr['vPremio_liquido'] / $this->pr['vParcela'];
        $this->pr['vComissao']            = $this->pr['vPremio_liquido'] * 0.8;
    }
    
    public function acumulaValores(){
        $this->pr['vPremio_liquido'] += $this->expt['premioLiquido'];
        $this->pr['vPremio_total']   += $this->expt['premioTotal'];
        $this->pr['vAdicional']      = 0;
        $this->pr['vIof']            = 0;
        if($this->expt['validade'] == 'mensal'){
            $this->pr['vParcela']        = 1;
        }else{
            $this->pr['vParcela']        = $this->expt['formaPagto'];            
        }        
    }
    
    public function resetaValores(){
        $this->pr['vIof']                 = 0;
        $this->pr['vIofParc']             = 0;
        $this->pr['vPremio_totalParc']    = 0;
        $this->pr['vPremio_total']        = 0;
        $this->pr['vPremio_liquidoParc']  = 0;
        $this->pr['vComissao']            = 0;
        $this->pr['vPremio_liquido']      = 0;
        $this->pr['vPremio_liquidoParc']  = 0;
        $this->pr['vPremio_liquido']      = 0;
        $this->pr['vPremio_total']        = 0;
        $this->pr['vAdicional']           = 0;
        $this->pr['vIof']                 = 0;
        $this->pr['vParcela']             = 0;
    }

    public function insertItensCobVendaval(){
        $this->pr['vVen'] = $this->expt['vendaval'];            
        if($this->pr['vue'] == 3234){
            $this->pr['vVenPremio'] = $this->expt['cobVendaval'];            
        }else{
            $this->pr['vVenPremio'] = 0;
        }
        $q  = $this->insertItensCobHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", " . $this->pr['vItemCob'] . ", 26, 4, '4', " . $this->pr['vVen'] . ", 0, '0', 0, 0, 0, 'I', 0, 0, 0, '" . $this->pr['vVenPremio'] . "', 0, 'N', ";
        $q .= "'0', 0, 0, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "' ";
        $q .= ")";
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q);
    }
    
    public function insertItensCobDanosEletricos(){
        $this->pr['vEle'] = $this->expt['eletrico'];            
        if($this->pr['vue'] == 3234){
            $this->pr['vElePremio'] = $this->expt['cobEletrico'];            
        }else{
            $this->pr['vElePremio'] = 0;
        }
        $q  = $this->insertItensCobHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", " . $this->pr['vItemCob'] . ", 22, 3, '4', " . $this->pr['vEle'] . ", 0, '0', 0, 0, 0, 'I', 0, 0, 0, '" . $this->pr['vElePremio'] . "', 0, 'N', ";
        $q .= "'0', 0, 0, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "' ";
        $q .= ")";  
        print_r($q); echo '<br><br>'; return;      
        $this->getMssql()->q($q);
    }
    
    public function insertItensCobPerdaAluguel(){
        $this->pr['vAlu'] = $this->expt['aluguel'];            
        if($this->pr['vue'] == 3234){
            $this->pr['vAluPremio'] = $this->expt['cobAluguel'];            
        }else{
            $this->pr['vAluPremio'] = 0;
        }
        
        $q  = $this->insertItensCobHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", " . $this->pr['vItemCob'] . ", 92, 2, '4', " . $this->pr['vAlu'] . ", 0, '0', 0, 0, 0, 'I', 0, 0, 0, '" . $this->pr['vAluPremio'] . "', 0, 'N', ";
        $q .= "'0', 0, 0, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "' ";
        $q .= ")";    
        print_r($q); echo '<br><br>'; return;    
        $this->getMssql()->q($q);
        
    }
    
    public function insertItensCobIncendio(){
        // Se escolha entre Incendio ou Incendio + Conteudo
        if($this->expt['tipoCobertura'] == '01' ){
            $incendio = $this->expt['incendio'];   
            $cobIncen = $this->expt['cobIncendio']; 
        }else{
            $incendio = $this->expt['conteudo'];   
            $cobIncen = $this->expt['cobConteudo'];             
        }
        $this->pr['vInc'] = $incendio;            
        if($this->pr['vue'] == 3234){
            $this->pr['vIncPremio'] = $cobIncen;            
        }else{
            $this->pr['vIncPremio'] = 0;
        }
        $q  = $this->insertItensCobHeader();
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", " . $this->pr['vItemCob'] . ", 266, 1, '4', " . $this->pr['vInc'] . ", 0, '0', 0, 0, 0, 'I', 0, 0, 0, '" . $this->pr['vIncPremio'] . "', 0, 'N', ";
        $q .= "'0', 0, 0, 0, 0, 'A', '" . $this->pr['vData_inclusao'] . "' ";
        $q .= ")";
        
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q);
    }
    
    public function insertItensCobHeader(){
        $q  = "INSERT INTO Tabela_DocsItensCobs (";
        $q .= "Documento, Alteracao, Item, Cobertura, Ordem, Tem_impseg, Imp_segurada, Moeda, Tem_franquia, ";
        $q .= "Tipo_franquia, Valor_franquia, Perc_franquia, Aplica_sobre, Franquia_minima, Franquia_maxima, ";
        $q .= "Classe_bonus, Premio_base, Perc_comissao, Tem_relacao_bens, Bem_obrig, Forma_recebimento,  ";
        $q .= "Qtde_parcelas, Parcela_inicial, Perc_ajuste, Situacao, Data_inclusao ";
        $q .= ") VALUES ( "; 
        return $q;       
    }
    
    public function insertToDocsItens(){
        
        $this->pr['vProprietario'] = $this->addSaida($this->expt['locador']['nome'], 50);
        $this->pr['vCep']          = $this->addSaida($this->expt['imovel']['cep'], 8, '0', 'STR_PAD_LEFT');
        $this->pr['vEndereco']     = $this->addSaida($this->expt['imovel']['rua'], 50);
        $this->pr['vNumero']       = $this->expt['imovel']['numero'];
        $this->pr['vComplemento']  = $this->addSaida($this->expt['imovel']['endereco']['compl'], 20);
        $this->pr['vBairro']       = $this->addSaida($this->expt['imovel']['endereco']['bairro']['nome'], 30);
        $this->pr['vCidade']       = $this->addSaida($this->expt['imovel']['endereco']['cidade']['nome'], 30);
        $this->pr['vEstado']       = $this->expt['imovel']['endereco']['estado']['sigla'];
        $this->pr['vSegurado']     = $this->addSaida($this->expt['locatario']['nome'], 50);
        $this->pr['vObs']          = '';        
        
        $q  = "INSERT INTO Tabela_DocsItens (";
        $q .= "Documento, Alteracao, Item, Num_item, Item_cia, Estudo, Segurado, Proprietario, Salario, Moeda_salario, ";
        $q .= "Fabricante, Modelo, Ano_fabricacao, Ano_modelo, Renavam, Qtde_passag, Categ_AT, Bonus, Classe_bonus, ";    
        $q .= "Cep, Endereco, Numero, Complemento, Bairro, Cidade, Estado, Tipo_ocupacao, Valor_em_risco, Moeda_valor, ";
        $q .= "Situacao_item, Renovavel, Ultima_posicao, Item_alterou, Cliente, Cod_plano, Qtde_portas, Sorteio, Idade, ";
        $q .= "Premio_plano, Nivel, Divisao, Cod_prserv, Cod_fipe, Situacao, Data_inclusao, observacoes ";
        $q .= ") VALUES (";	
        $q .= "" . $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", " . $this->pr['vItem'] . ", " . $this->pr['vNum_item'] . ", 0, 0, '" . $this->pr['vSegurado'] . "', '" . $this->pr['vProprietario'] . "', 0, 0, 0, 0, 0, 0, ";
        $q .= "0, 0, 0, 0, 0, '" . $this->pr['vCep'] . "', '" . $this->pr['vEndereco'] . "', '" . $this->pr['vNumero'] . "', '" . $this->pr['vComplemento'] . "', '" . $this->pr['vBairro'] . "', ";
        $q .= "'" . $this->pr['vCidade'] . "', '" . $this->pr['vEstado'] . "', '" . $this->pr['vTipo_ocupacao'] . "', 0, 0, 1, '1', '1', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ";
        $q .= "0, 'A', '" . $this->pr['vData_inclusao'] . "', '" . $this->pr['vObs'] . "' ";
        $q .= ")";	
        
        // executar query sql sem verificar o retorno
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q);
    }
    
    public function getVItem(){
            // Pega numero item na tabela par_cont
        $q = "EXEC ss_Par_Cont_Int tabela_docsitens";
        $r = $this->execColQuery($q);
        return $r['contador'];        
    }
    
    public function getNumItem(){
            //Pegando valor do Numero do item
        $q = "SELECT max(Num_item) max_item FROM tabela_docsitens WHERE documento = " . $this->pr['vDocumento'];
        $r = $this->execColQuery($q);
        if(!$r){
            return 1;
        }
        $n = $r['max_item'] + 1 ;
        if(!is_numeric($n)){
            return 1;
        }
        return $n;
    }
    
    public function insertToDocumentos(){
        // Adionando outra variaveis para inserção
        $this->pr['vApolice']           = null;
        $this->pr['vData_proposta']     = date('Y/m/d');
        $this->pr['vData_protocolo']    = date('Y/m/d');
        $this->pr['vProposta']          = str_pad(($this->pr['vComanda']), 6, '0', STR_PAD_LEFT) .  str_pad(($this->pr['vEndosso']), 2, '0', STR_PAD_LEFT) . str_pad(($this->pr['vAno']), 4, '0', STR_PAD_LEFT);
        $this->pr['vGrupo_hierarquico'] = 47;
        $this->pr['vInicio_vigencia']   = $this->expt['inicio']->format('Y/m/d');
        $this->pr['vTermino_vigencia']  = $this->expt['fim']->format('Y/m/d');
        $this->pr['vInicvig_prop']      = $this->expt['inicio']->format('Y/m/d');
        $this->pr['vTermvig_prop']      = $this->expt['fim']->format('Y/m/d');
        $this->pr['vData_inclusao']     = date('Y/m/d');
        if(is_null($this->pr['vSeguradora']) OR empty($this->pr['vSeguradora'])){
            $this->pr['vSeguradora']        = 0;
        }else{
            $this->pr['vSeguradora']    = str_replace('.', '', $this->pr['vSeguradora']);
            $this->pr['vSeguradora']    = str_replace(',', '.', $this->pr['vSeguradora']);
        }
        if(is_null($this->pr['vPerc_com_base']) OR empty($this->pr['vPerc_com_base'])){
            $this->pr['vPerc_com_base'] = 0;            
        }else{
            $this->pr['vPerc_com_base'] = str_replace('.', '', $this->pr['vPerc_com_base']);
            $this->pr['vPerc_com_base'] = str_replace(',', '.', $this->pr['vPerc_com_base']);            
        }
        if(is_null($this->pr['vPerc_iof']) OR empty($this->pr['vPerc_iof'])){
            $this->pr['vPerc_iof']      = 0;            
        }else{
            $this->pr['vPerc_iof']      = str_replace('.', '', $this->pr['vPerc_iof']);
            $this->pr['vPerc_iof']      = str_replace(',', '.', $this->pr['vPerc_iof']);            
        }
        // Data venc primeira joga para o mes seguinte
        $inicio = clone $this->expt['inicio'];
        $inicio->add(new \DateInterval('P1M'));        
        $this->pr['vData_venc_primeira'] = $inicio->format('Y/m') . '/10';
        
        // Abrindo nova fatura (Cada linha tem 9 campo da tabela)
        $q  = "INSERT INTO Tabela_documentos ( ";
        $q .= "Documento, Alteracao, Data_proposta, Proposta, Tipo_documento, Sub_tipo, Tipo_negocio, Tipo_endosso, Cliente, ";
        $q .= "Grupo_hierarquico, Inicio_vigencia, Termino_vigencia, Seguradora, Produto, Apol_coletiva, Parcela_inicial, Valor_em_risco, Premio_liquido, ";
        $q .= "Premio_liqdesc, Adicional, Custo, Perc_iof, Iof, Premio_total, Forma_parcelamento, Forma_recebimento, Moeda_premios, ";
        $q .= "Moeda_comissoes, Cod_descricao, Perc_com_base, Perc_comissao, Perc_desconto, Perc_cocorret, Incide_sobre_adic, Comissao, Qtde_parc_premio, ";
        $q .= "Qtde_parc_comis, Base_venc_prim, Base_venc_demais, Dias_venc_primeira, Data_venc_primeira, Documento_quitado, Premios_quitados, Comissao_quitada, Repasses_quitados, ";
        $q .= "Premio_liq_cobranca, Adicional_cobranca, Custo_cobranca, Iof_cobranca, Premio_tot_cobranca, Moeda_cobranca, Forma_parc_cobranca, Qtde_parc_cobranca, Dias_vencpr_cobranca, ";
        $q .= "Cobranca_quitada, Cocorretor_quitado, Doc_alterou, Tem_depend_benef, Tem_clausulas, Tem_cosseguro, Impseg_geral, Tem_estipulante, Gera_parcelas, ";
        $q .= "Tem_cobranca, Cobra_igual_cia, Tem_cocorretor, Tipo_controle, Com_varia_cob, Bonus_varia_cob, Qtde_vidas, Taxa, Qtde_salarios, ";
        $q .= "Prop_impressa, Cod_texto1, Cod_texto2, Forma_pagamento, Premio_liqprop, Adicional_prop, Custo_prop, Iof_prop, Comissao_prop, ";
        $q .= "Inicvig_prop, Termvig_prop, Campanha, Sucursal, Usuario, Tipo_prop, Tipo_emis, Cod_plano, Perc_adic, ";
        $q .= "Perc_enc1, Perc_enc2, Encargos1, Encargos2, Total_enc1, Total_enc2, Situacao, Data_inclusao, Banco, ";
        $q .= "Premio_liqserv, Motivo_orc, Gera_comanda, Juros, Data_protocolo, Apolice ";
        $q .= ") VALUES ( ";
        $q .= $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", '" . $this->pr['vData_proposta'] . "', " . $this->pr['vProposta'] . ", 1, 1, '1', '1', " . $this->pr['vCliente'] . ", ";
        $q .= $this->pr['vGrupo_hierarquico'] . ", '" . $this->pr['vInicio_vigencia'] . "', '" . $this->pr['vTermino_vigencia'] . "', " . $this->pr['vSeguradora'] . ", " . $this->pr['vProduto'] . ", 0, 1, 0, 0, ";
        $q .= "0, 0, 0, " . $this->pr['vPerc_iof'] . ", 0, 0, 1, 0, 1, ";
        $q .= "0, '1', " . $this->pr['vPerc_com_base'] . ", 80, 0, 0, '1', 0, 1, ";
        $q .= "0, '3', '3', 10, '" . $this->pr['vData_venc_primeira'] . "', '1', '1', '1', '1', ";
        $q .= "0, 0, 0, 0, 0, 0, 0, 0, 0, ";
        $q .= "'1', '1', 0, '" . $this->pr['vTem_depend_benef'] . "', '" . $this->pr['vTem_clausulas'] . "', '" . $this->pr['vTem_cosseguro'] . "', '" . $this->pr['vImpSeg_geral'] . "', '" . $this->pr['vTem_estipulante'] . "', '" . $this->pr['vGera_parcelas'] . "', ";
        $q .= "'" . $this->pr['vTem_cobranca'] . "', '" . $this->pr['vCobra_igual_cia'] . "', '" . $this->pr['vTem_cocorretor'] . "', '" . $this->pr['vTipo_controle'] . "', '" . $this->pr['vCom_varia_cob'] . "', '" . $this->pr['vBonus_varia_cob'] . "', 0, 0, 0, ";
        $q .= "'0', " . $this->pr['vCod_texto1'] . ", " . $this->pr['vCod_texto2'] . ", '1', 0, 0, 0, 0, 0, ";
        $q .= "'" . $this->pr['vInicvig_prop'] . "', '" . $this->pr['vTermvig_prop'] . "', 0, 0, 34, 'P', '0', 0, 0, ";
        $q .= "0, 0, 0, 0, 0, 0, '1', '" . $this->pr['vData_inclusao'] . "', 0, ";
        $q .= "0, 0, 0, 0, '" . $this->pr['vData_protocolo'] . "', '" . $this->pr['vApolice'] . "'";		
        $q .= ")";
        // executar query sql sem verificar o retorno
        print_r($q); echo '<br><br>'; return;
        $this->getMssql()->q($q);
    }
    
    
    public function getProduto($o){
        switch ($o) {
            case 'M':
                return '53';   break;
            case 'C':
                return '327';  break;
            case 'R':
                return '328';  break;
            default:
                return false;  break;
        }
        
    }
    
    public function getProdutoParams($p,$c){
        $qCol = "SELECT * FROM Tabela_produtos WHERE produto = " . $p . " ";
        $resul = $this->execColQuery($qCol);
        $this->pr['vProduto']          = $p;
        $this->pr['vSeguradora']       = $resul['Seguradora'];
        $this->pr['vTipo_controle']    = $resul['Tipo_controle'];
        $this->pr['vTem_depend_benef'] = $resul['Tem_depend_benef'];
        $this->pr['vTem_cosseguro']    = $resul['Tem_cosseguro'];
        $this->pr['vTem_clausulas']    = $resul['Tem_clausulas'];
        $this->pr['vImpSeg_geral']     = $resul['ImpSeg_geral'];
        $this->pr['vTem_estipulante']  = $resul['Tem_estipulante'];
        $this->pr['vGera_parcelas']    = $resul['Gera_parcelas'];
        $this->pr['vTem_cobranca']     = $resul['Tem_cobranca'];
        $this->pr['vCobra_igual_cia']  = $resul['Cobra_igual_cia'];
        $this->pr['vTem_cocorretor']   = $resul['Tem_cocorretor'];
        $this->pr['vCom_varia_cob']    = $resul['Com_varia_cob'];
        $this->pr['vBonus_varia_cob']  = $resul['Bonus_varia_cob'];
        $this->pr['vPerc_iof']         = $resul['iof'];
        if($c == 50 AND $this->pr['vue'] == '3234'){
            $this->pr['vPerc_com_base']         = 50;
        }else{
            $this->pr['vPerc_com_base']         = $resul['Perc_com_base'];
        }
    }
    
    public function getDocumentos(){
        $resul = $this->execColQuery("EXEC ss_Par_Cont_Int tabela_documentos");
        $this->pr['vDocumento'] = $resul['contador'];
        $this->pr['vAlteracao'] = '0';
        
        $resul = $this->execColQuery("EXEC ss_Par_Cont_Int tabela_textos");
        $this->pr['vCod_texto1'] = $resul['contador'];
        
        $resul = $this->execColQuery("EXEC ss_Par_Cont_Int tabela_textos");
        $this->pr['vCod_texto2'] = $resul['contador'];
        
        $resul = $this->execColQuery("EXEC ss_Par_Cont_Int VVTabela_Comandas");
        $this->pr['vComanda'] = $resul['contador'];
        $this->pr['vEndosso'] = $resul['0'];
        $this->pr['vAno'] = date('Y');          
    }
    
    public function execColQuery($q){
        print_r($q); echo '<br><br>'; return;
        $resul = $this->getMssql()->q($q);
        return $resul->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * retorna string formatada
     * @param type $conteudo Dados a ser incrementado
     * @param type $tam      Quantidade de caracteres
     * @param type $compl    Completar com esse caractere(padrão spaços)
     * @param type $opt      Para completar no lado esquerdo ou direito(Padrão)
     */ 
    public function addSaida($conteudo,$tam,$compl='',$opt=''){
        if(empty($opt)){
            return str_pad(substr(utf8_decode($conteudo),0,$tam), $tam);
        }else{
            return str_pad(substr(utf8_decode($conteudo),0,$tam), $tam, $compl, STR_PAD_LEFT);            
        }
    }
    
    public function logExportadoCol($mes, $ano){
        $this->data['administradoraId'] = $this->expt['administradora']['id'];
        $this->data['mes'] = $mes;
        $this->data['ano'] = $ano;
        $this->data['criadoEm'] = new \DateTime('now');
        
        $this->entity = 'Livraria\Entity\LogFaturaCol';
        
        parent::insert();
    }
    
    
    
    
}
