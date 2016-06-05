<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;

/**
 * Exporta
 * Gerar arquivo de texto e lista de dados a serem exportados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Exporta extends AbstractService{
    
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
    
    protected $fp;
    protected $zip;
    protected $saida;
    protected $baseWork;
    protected $baseWorkTemp;
    protected $item;
    protected $tipoLocatario;
    protected $tipoLocador;
    protected $ativid;
    protected $qtdExportado;
    protected $fechadoRepository;
    protected $locatarioRepository;
    protected $locatarioService;
    protected $locatarioAcertoLog;

    /**
     * Repository de Administradora
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @since 05-06-2016  
     * @var \Livraria\Entity\AdministradoraRepository
     */
    protected $rpAdministradora;

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
     * Retorna Instancia do repository do locatario
     * @return \Livraria\Entity\LocatarioRepository 
     */
    public function getLtr(){
        if ($this->locatarioRepository) {
            return $this->locatarioRepository;
        }
        $this->locatarioRepository = $this->em->getRepository("Livraria\Entity\Locatario");
        return $this->locatarioRepository;
    }
    
    /**
     * Retorna Instancia do serviço do locatario
     * @return \Livraria\Service\Locatario 
     */
    public function getLts(){
        if ($this->locatarioService) {
            return $this->locatarioService;
        }
        $this->locatarioService = new Locatario($this->em);
        return $this->locatarioService;
    }
     
    /**
     * Recebe e trata os dados do form para fazer a consulta
     * @param array $data com campos do form
     * @return array com todos os registros
     */
    public function listaExpt($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        $this->data['administradora'] = $data['administradora'];
        $this->data['seguradora']     = $data['seguradora'];
        //Guardar dados do resultado 
        $this->getSc()->lista = $this->em->getRepository("Livraria\Entity\Fechados")->getListaExporta($this->data); 
        $this->getSc()->data     = $data;
        $obs = 'Paramentros da pesquisa:<br>';
        $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['seguradora']) ? '' : 'Seguradora : ' . $data['seguradora'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $this->logForSis('fechados', '', 'Exportar', 'Listar Registros', $obs);
        return $this->getSc()->lista;
    }
     
    /**
     * Recebe e trata os dados do form para fazer a lista para exibir para usuario confirmar a geração do arquivo emissão cartão.
     * @param array $data com campos do form
     * @return array com todos os registros
     */
    public function listaCartao($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        $this->data['administradora'] = $data['administradora'];
        //Guardar dados do resultado 
        $this->getSc()->lista = $this->em->getRepository("Livraria\Entity\Fechados")->getListaCartao($this->data); 
        $this->getSc()->data     = $data;
        $obs = 'Paramentros da pesquisa:<br>';
        $obs .= 'Mes = '. $data['mesFiltro'] . ' Ano = '. $data['anoFiltro'] .'<br>';
        $obs .= empty($data['administradora']) ? '' : 'Administradora : ' . $data['administradoraDesc'] .'<br>';
        $this->logForSis('fechados', '', 'Exportar', 'Emissão de Cartão', $obs);
        return $this->getSc()->lista;
    }
    
    /**
     * Gerar o(s) arquivo(s) para exportação para emissão de cartao 
     * Retorna arquivos de texto para usuario fazer download
     * @param string $admFiltro
     * @return string Caminho absoluto para o arquivo texto
     */
    public function geraArqsForCartao($admFiltro){
        $data = $this->getSc()->data; 
        $this->baseWork = '/var/www/zf2vv/data/work/' ;
        if(!is_dir($this->baseWork)){
            mkdir($this->baseWork , 0777);
        }
        $File = $this->baseWork . 'Cartoes_' . $data['mesFiltro'] . $data['anoFiltro'] . '.txt';
        $this->qtdExportado = 0;
        $this->openFile($File);
        
        $this->prepArqsForCartao($admFiltro);
        
        $this->closeFile();
        if($this->qtdExportado != 0){
            $obs = 'Gravou os dados do arquivo texto para emissão de cartão com sucesso.';
            $this->logForSis('fechados', '', 'Exportar', 'Gera Arquivo Cartão', $obs);
            return $File;
        }
        return FALSE;
    }
    
    /**
     * Varre o dados do cache e monta arquivo texto para exportação 
     * Retorna caminho absoluto para o arquivo gerado
     * @param type $admFiltro
     */
    public function prepArqsForCartao($admFiltro) {        
        $this->item = 0;
        $this->saida = '';
        foreach ($this->getSc()->lista as $value) {
            // Filtrar a Administradora
            if($admFiltro == $value['administradora']['id']){
                continue;
            }
            //locatario nome
            $this->addSaida($value['locatario']['nome'], 100);
            //locatario documento
            if($value['locatario']['tipo'] == 'fisica'){
                $this->saida .= $this->cleanDocFomatacao($value['locatario']['cpf'], 15);                
            }else{
                $this->saida .= $this->cleanDocFomatacao($value['locatario']['cnpj'], 15);                
            }
            //endereço
            $end = $value['imovel']['rua'] . ', ' . $value['imovel']['numero'];
            if(!empty($value['imovel']['apto'])){
                $end .= ' - AP ' . $value['imovel']['apto'];                
            }
            if(!empty($value['imovel']['bloco'])){
                $end .= ' - BL ' . $value['imovel']['bloco'];                
            }
            if(!empty($value['imovel']['endereco']['compl'])){
                $end .= ' - ' . $value['imovel']['endereco']['compl'];                
            }
            $this->addSaida($end, 66);
            // Bairro
            $this->addSaida($value['imovel']['endereco']['bairro']['nome'], 100);
            // Cidade
            $this->addSaida($value['imovel']['endereco']['cidade']['nome'], 50);
            // Estado
            $this->addSaida($value['imovel']['endereco']['estado']['sigla'], 2);
            // CEP
            $this->addSaida($value['imovel']['cep'], 8, '0', 'STR_PAD_LEFT');
            // Inicio Vigencia
            $this->saida .= $value['inicio']->format('dmY');
            // Fim Vigencia
            $this->saida .= $value['fim']->format('dmY');
            // Locador nome
            $this->addSaida($value['locador']['nome'], 100);
            // Locador documento
            if($value['locador']['tipo'] == 'fisica'){
                $this->saida .= $this->cleanDocFomatacao($value['locador']['cpf'], 15);
            }else{
                $this->saida .= $this->cleanDocFomatacao($value['locador']['cnpj'], 15);
            }
            // Administradora
            $this->addSaida($value['administradora']['nome'], 40);
            // Referencia do imovel
            $this->addSaida($value['imovel']['refImovel'], 40);
            // Fim da linha 
            $this->saida .= "\r\n";  
            $this->item ++;
            $this->qtdExportado ++;
        }     
        $this->writeFile();
    }
    
    public function addSaida($conteudo,$tam,$compl='',$opt=''){
        if(empty($opt)){
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam);
        }else{
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam, $compl, STR_PAD_LEFT);            
        }
    }

        /**
     * Gerar o(s) arquivo(s) para exportação maritima 
     * Coloca o(s) arquivo(s) dentro do zip 
     * Retorna caminha absoluto do arquivo zip
     * @param string $admFiltro
     * @return string Caminho absoluto para o arquivo zip
     */
    public function geraArqsForMaritima($admFiltro){
        $data = $this->getSc()->data; 
        //$this->baseWork = '\\s-1482\Imagem\Incendio_locacao\\' . $data['mesFiltro'] . $data['anoFiltro'] . '\\';
        $this->baseWorkTemp = '/var/www/zf2vv/data/work/' . $data['mesFiltro'] . $data['anoFiltro'] . '/';
        //$this->baseWork = '/mnt/share/locacaoincendio/' . $data['mesFiltro'] . $data['anoFiltro'] . '/';
        $this->baseWork = '/mnt/share/locacaoincendio/';
        $this->baseWork = '/var/www/zf2vv/data/work/maritima/';
        if(!is_dir($this->baseWork)){
            mkdir($this->baseWork , 0777);
        }
        if(!is_dir($this->baseWorkTemp)){
            mkdir($this->baseWorkTemp , 0777);
        }
        $zipFile = $this->baseWorkTemp . "Exporta_Maritima.zip";
        $this->qtdExportado = 0;
        $this->fechadoRepository = $this->em->getRepository("Livraria\Entity\Fechados");
        $this->openZipFile($zipFile);
        if(!empty($admFiltro)){
            /* @var $entityAdm \Livraria\Entity\Administradora */
            $entityAdm = $this->getRpAdm()->find($admFiltro);
            if($entityAdm->getGeraExpSep()){
                $this->prepArquivoSeparados($admFiltro);
            }else{
                $this->prepArqsForMaritima($admFiltro);
            }
        }else{
            $admArray = $this->getAdmCods();
            foreach ($admArray as $admCod) {
                $this->prepArqsForMaritima($admCod);
            }    
        }    
        $this->zip->close();
        if($this->qtdExportado != 0){
            $obs = 'Gravou os dados da exportação com sucesso.<br>' . json_encode($this->locatarioAcertoLog);
            $this->logForSis('fechados', '', 'Exportar', 'Exportar Registros', $obs);
        }
        return $zipFile;
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
    
    /**
     * Usa os dados da consulta armazenado em cache
     * Gera os arquivos separando em empresarial e residencial
     * Cada ocupação separando pela forma de pagamento
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.2  
     * @since 05-06-2016 
     * @return string com caminho do arquivo zip
     */
    public function prepArqsForMaritima($admCod, $returnArray=false){
        // Separar Adm em arquivos por tipo de pagamento e tipo de ocupacao
        $file['e0130.00'] = $this->baseWork . $admCod . '_empresarial_ato_30.KM2';
        $file['e0230.00'] = $this->baseWork . $admCod . '_empresarial_1x1_30.KM2';
        $file['e0330.00'] = $this->baseWork . $admCod . '_empresarial_1x2_30.KM2';
        $file['e0430.00'] = $this->baseWork . $admCod . '_empresarial_mensal_30.KM2';
        $file['e0150.00'] = $this->baseWork . $admCod . '_empresarial_ato_50.KM2';
        $file['e0250.00'] = $this->baseWork . $admCod . '_empresarial_1x1_50.KM2';
        $file['e0350.00'] = $this->baseWork . $admCod . '_empresarial_1x2_50.KM2';
        $file['e0450.00'] = $this->baseWork . $admCod . '_empresarial_mensal_50.KM2';
        $file['e0169.99'] = $this->baseWork . $admCod . '_empresarial_ato_69.KM2';
        $file['e0269.99'] = $this->baseWork . $admCod . '_empresarial_1x1_69.KM2';
        $file['e0369.99'] = $this->baseWork . $admCod . '_empresarial_1x2_69.KM2';
        $file['e0469.99'] = $this->baseWork . $admCod . '_empresarial_mensal_69.KM2';        
        $file['r0130.00'] = $this->baseWork . $admCod . '_residencial_ato_30.KM2';
        $file['r0230.00'] = $this->baseWork . $admCod . '_residencial_1x1_30.KM2';
        $file['r0330.00'] = $this->baseWork . $admCod . '_residencial_1x2_30.KM2';
        $file['r0430.00'] = $this->baseWork . $admCod . '_residencial_mensal_30.KM2';
        $file['r0150.00'] = $this->baseWork . $admCod . '_residencial_ato_50.KM2';
        $file['r0250.00'] = $this->baseWork . $admCod . '_residencial_1x1_50.KM2';
        $file['r0350.00'] = $this->baseWork . $admCod . '_residencial_1x2_50.KM2';
        $file['r0450.00'] = $this->baseWork . $admCod . '_residencial_mensal_50.KM2';
        $file['r0169.99'] = $this->baseWork . $admCod . '_residencial_ato_69.KM2';
        $file['r0269.99'] = $this->baseWork . $admCod . '_residencial_1x1_69.KM2';
        $file['r0369.99'] = $this->baseWork . $admCod . '_residencial_1x2_69.KM2';
        $file['r0469.99'] = $this->baseWork . $admCod . '_residencial_mensal_69.KM2';
        if($returnArray){
            return $file;
        }
        foreach ($file as $key => $arq) {
            if(!$this->setConteudo($key, $arq, $admCod)){
                $this->writeFile();
                $this->closeFile();  
                $this->addFileToZip($arq,$this->baseWork);
            }
        }  
    }
    
    /**
     * 
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.0  
     * @since 05-06-2016 
     * @param type $admCod
     */
    public function prepArquivoSeparados($admCod) {
        foreach ($this->getSc()->lista as $value) {
            $file = $this->prepArqsForMaritima($admCod, TRUE);
            $file = str_replace('.KM2', '_' . $value['id'] . '.KM2', $file);
            foreach ($file as $key => $arq) {
                if($this->setConteudoOne($key, $arq, $admCod, $value)){
                    $this->writeFile();
                    $this->closeFile();  
                    $this->addFileToZip($arq,$this->baseWork);
                }
            }
        }
    }
    
    /**
     * 
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.0  
     * @since 05-06-2016 
     * @param type $filtro
     * @param type $arq
     * @param type $admCod
     * @param type $value
     * @return boolean
     */
    public function setConteudoOne($filtro, $arq, $admCod, $value) {        
        $head = TRUE;
        $ocupacao = substr($filtro, 0, 1);
        $formaPgto = substr($filtro, 1, 2);
        $comissao = substr($filtro, 3, 5);
        $this->item = 0;
        $this->saida = '';
        // Filtrar a Administradora
        if($admCod != $value['administradora']['id']){
            return false;
        }
        // Verificar se nome do locatario esta correto provisoriamente.
        if ($value['locatarioNome'] != $value['locatario']['nome']){
            $this->acertaNomeLocatario($value);
        }
        $this->ativid = $value['atividade']['codSeguradora'];
        $this->tipoLocatario = strtoupper(substr($value['locatario']['tipo'], 0, 1));
        $this->tipoLocador   = strtoupper(substr($value['locador']['tipo'], 0, 1));
        // Filtrar apenas empresarial
        if($ocupacao == 'e'){ 
            if($this->ativid == 911 OR $this->ativid == 919){
                return false; // é residencial entao filtra
            }
        }else{ // Filtrar apenas residencial
            if($this->ativid != 911 AND $this->ativid != 919){
                return false; // não é residencial entao filtra
            }                
        }
        // Filtra forma de pagamento
        if($formaPgto != $value['formaPagto']){
            return false;
        }
        // Filtra comissão os 2 primeiros digitos
//echo '<pre>';            var_dump($value['comissao']); die;
        if($comissao != substr($value['comissao'], 0, 5)){
            return false;
        }
        
        $this->openFile($arq);
        $this->montaHead($value);
        
        $this->item ++;
        $this->qtdExportado ++;
        $this->setLine03($value);
        $this->setLine05($value);
        $this->setLine10($value);
        return $head;
    }
    
    /**
     * Repository de Administradora
     * 
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.0  
     * @since 05-06-2016  
     * @return \Livraria\Entity\AdministradoraRepository
     */
    public function getRpAdm() {
        if(is_null($this->rpAdministradora)){
            $this->rpAdministradora = $this->em->getRepository('\Livraria\Entity\Administradora');
        }
        return $this->rpAdministradora;
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
     * Gera o todo conteudo a gravar no arquivo 
     * Filtra pelo tipo ocupação comercial ou residencial e tipo de pagamento
     * @param string $filtro composto de ocupação e forma de pagamento
     * @param string $arq nome do arquivo a ser gerado
     * @return boolean retorna falso caso gere um arquivo
     */
    public function setConteudo($filtro, $arq, $admCod){
        $head = TRUE;
        $ocupacao = substr($filtro, 0, 1);
        $formaPgto = substr($filtro, 1, 2);
        $comissao = substr($filtro, 3, 5);
        $this->item = 0;
        $this->saida = '';
        foreach ($this->getSc()->lista as $value) {
            // Filtrar a Administradora
            if($admCod != $value['administradora']['id']){
                continue;
            }
            // Verificar se nome do locatario esta correto provisoriamente.
            if ($value['locatarioNome'] != $value['locatario']['nome']){
                $this->acertaNomeLocatario($value);
            }
            $this->ativid = $value['atividade']['codSeguradora'];
            $this->tipoLocatario = strtoupper(substr($value['locatario']['tipo'], 0, 1));
            $this->tipoLocador   = strtoupper(substr($value['locador']['tipo'], 0, 1));
            // Filtrar apenas empresarial
            if($ocupacao == 'e'){ 
                if($this->ativid == 911 OR $this->ativid == 919){
                    continue; // é residencial entao filtra
                }
            }else{ // Filtrar apenas residencial
                if($this->ativid != 911 AND $this->ativid != 919){
                    continue; // não é residencial entao filtra
                }                
            }
            // Filtra forma de pagamento
            if($formaPgto != $value['formaPagto']){
                continue;
            }
            // Filtra comissão os 2 primeiros digitos
//echo '<pre>';            var_dump($value['comissao']); die;
            if($comissao != substr($value['comissao'], 0, 5)){
                continue;
            }
            if($head){
                $this->openFile($arq);
                $this->montaHead($value);
                $head = FALSE;
            }
            $this->item ++;
            $this->qtdExportado ++;
            $this->setLine03($value);
            $this->setLine05($value);
            $this->setLine10($value);
            //não marcar como gerado ainda
//            if($this->fechadoRepository->setGerado($value['id'])){
//                $this->logForGerado($value['id'], $value['codano']);
//            }
        }        
        return $head;
    }

    /**
     * Registro o log de quando foi exportado e por quem
     * @param string $id
     * @param string $ano
     */
    public function logForGerado($id,$ano) {
        //serviço LogFechamento
        $log = new LogFechados($this->em);
        $dataLog['fechados']   = $id; 
        $dataLog['tabela']     = 'log_fechados';
        $dataLog['controller'] = 'Exporta' ;
        $dataLog['action']     = 'Exportar Maritima';
        $dataLog['mensagem']   = 'Foi exportado para arquivo de texto para Maritima numero ' . $id . '/' . $ano;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);        
    }

    /**
     * Monta as 3 linhas do cabeçalho com dados da corretora
     */
    public function montaHead(&$value){
        $this->setLine00($value);
        $this->setLine01();
        $this->setLine02();
    }

    public function setLine00(&$value){
        //========= Linha 00 com Dados do Orçamento Obrigatorio =======================
        $this->saida .= '00';
        // Ocupação residencial ou comercial
        if($value['atividade']['codSeguradora'] >= 910 AND $value['atividade']['codSeguradora'] <= 921){
            $this->saida .= '11401';
        }else{
            $this->saida .= '11801';
        }
        // Numero do orçamento tam 6
        $this->addSaida2($value['id'], 6, '0', 'STR_PAD_LEFT'); 
        // Data do calculo
        $this->saida .= $value['criadoEm']->format('d/m/Y');
        // Inicio Vigencia
        $this->saida .= $value['inicio']->format('d/m/Y');
        // Fim Vigencia
        $this->saida .= $value['fim']->format('d/m/Y');
        // Proponente tam 60
        $this->addSaida2($value['administradora']['nome'], 60);
        // Tipo Proponente
        $this->saida .= 'J';
        // Fator de calculo tam 5
        $this->saida .= '08000';
        // Percentual agravação tam 5
        $this->saida .= '00000';
        // Print certificado
        $this->saida .= 'S';
        // Print premio no certificado
        $this->saida .= 'S';
        // Tipo de seguro
        $this->saida .= 'N';
        // Nº Apolice tam 10
        $this->saida .= '0000000000';
        // Fim da linha 00
        $this->saida .= "\r\n";        
    }

    public function setLine01() {
        //========= Linha 01 com Dados do Corretor Obrigatorio =======================
        $this->saida .= '01';
        // Cod da Sucursal 2
        $this->saida .= '01';
        // Cod do Corretor 5
        $this->saida .= '00225';
        // Cod do Colaborador 5
        $this->saida .= '00000';
        // Cod Susep 14
        $this->saida .= '05952610195766';
        // tipo de pessoa do corretor 1
        $this->saida .= 'J';
        // nome do corretor 40
        $this->saida .= str_pad('Vila Velha Corret. Seguros S/C Ltda', 40);
        // cod da inspetoria 6
        $this->saida .= '000194';
        // cod do inspetor 6
        $this->saida .= '000058';
        // email corretor 60
        $this->saida .= str_pad('vilavelha@vilavelha.com.br', 60);
        // DD tel 2
        $this->saida .= '11';
        // tel corretor 8
        $this->saida .= '32269600';
        // DD tel 2
        $this->saida .= '11';
        // fax corretor 8
        $this->saida .= '32269622';
        // Incluir automenticamente co-corretagem 1
        $this->saida .= 'N';
        // Fim da linha 01
        $this->saida .= "\r\n";        
    }

    public function setLine02() {
        //========= Linha 02 com DADOS DO CLIENTE Obrigatorio =======================
        $this->saida .= '02';
        // Cod cliente 6
        $this->saida .= '000225';
        // cpf ou cnpj 14
        $this->saida .= '47186283000171';
        // data nasc
        $this->saida .= str_pad(' ', 10);
        // tipo de documento
        $this->saida .= str_pad(' ', 1);
        // RG 15
        $this->saida .= str_pad(' ', 15);
        // orgao expedidor 20
        $this->saida .= str_pad(' ', 20);
        // data expedição 10
        $this->saida .= str_pad(' ', 10);
        // end. de correspondencia 40
        $this->saida .= str_pad('AV. IPIRANGA', 40);
        // num de corresondencia 6
        $this->saida .= '000313';
        // compl. de corresondencia 15
        $this->saida .= str_pad(' ', 15);
        // bairro de corresondencia 20
        $this->saida .= str_pad('CENTRO', 20);
        // cidade de corresondencia 40
        $this->addSaida2('SÃO PAULO', 40);
        // uf de corresondencia 2
        $this->saida .= 'SP';
        // cep de corresondencia 8
        $this->saida .= '01045001';
        // DDD de corresondencia 2
        $this->saida .= '11';
        // Tel de corresondencia 8
        $this->saida .= '32269600';
        // DDD Fax de corresondencia 2
        $this->saida .= '11';
        // Fax de corresondencia 8
        $this->saida .= '32269622';
        // End. de cobrança 40
        $this->saida .= str_pad('AV. IPIRANGA', 40);
        // num. de cobrança 6
        $this->saida .= '000313';
        // Compl. de cobrança 15
        $this->saida .= str_pad(' ', 15);
        // bairro de cobrança 20
        $this->saida .= str_pad('CENTRO', 20);
        // cidade de cobrança 40
        $this->addSaida2('SÃO PAULO', 40);
        // uf de cobrança 2
        $this->saida .= 'SP';
        // cep de cobrança 8
        $this->saida .= '01045001';
        // DDD de cobrança 2
        $this->saida .= '11';
        // Telde cobrança 8
        $this->saida .= '32269600';
        // DDD Fax de cobrança 2
        $this->saida .= '11';
        // Fax de cobrança 8
        $this->saida .= '32269622';
        // nome cliente 40
        $this->saida .= str_pad('Vila Velha Corret. Seguros S/C Ltda', 40);
        // tipo de pessoa do cliente
        $this->saida .= 'J';
        // Fim da linha 02
        $this->saida .= "\r\n";
    }
    
    public function acertaNomeLocatario(&$v){
        $this->locatarioAcertoLog[] = '<p>Locatario diferente ' . $v['locatarioNome'] . ' Versus ' . $v['locatario']['nome'] . '</p>';
        /* @var $entity \Livraria\Entity\Locatario */
        $entity = $this->getLtr()->findOneBy(['nome' => $v['locatarioNome']]);
        if($entity){
            /* @var $seguroF \Livraria\Entity\Fechados */
            $seguroF = $this->em->find("Livraria\Entity\Fechados", $v['id']);
            if($seguroF){
                if($seguroF->getLocatarioNome() != $entity->getNome()){
                    $this->locatarioAcertoLog[] = '<p>Não encontrou o mesmo nome ' . $seguroF->getLocatarioNome() . ' com seu id correto ' . $entity->getNome() . '</p>';                    
                    return;
                }
                $idOrigem = FALSE;
                if($seguroF->getOrcamentoId() != 0 AND $seguroF->getOrcamentoId() != null){
                    $idOrigem = $seguroF->getOrcamentoId();                    
                }
                if($seguroF->getRenovacaoId() != 0 AND $seguroF->getRenovacaoId() != null){
                    $idOrigem = $seguroF->getRenovacaoId();                    
                }
                if(!$idOrigem){
                    $this->locatarioAcertoLog[] = '<p>Não encontrou a origem desse seguro fechado renovaçao id '. $seguroF->getRenovacaoId() . ' orcamento id ' . $seguroF->getOrcamentoId() . '</p>';                    
                    return;                    
                }
                /* @var $seguroO \Livraria\Entity\Orcamento */
                $seguroO = $this->em->find("Livraria\Entity\Orcamento", $idOrigem);
                if($seguroO){
                    if($seguroF->getLocatario()->getId() != $seguroO->getLocatario()->getId()){
                        $this->locatarioAcertoLog[] = '<p>Locatario do fechado diferente do orçamento  ' . $seguroF->getLocatario()->getId() . ' id locatario do orçamento ' . $seguroO->getLocatario()->getId() . '</p>';                    
                        return;                        
                    }
                    $this->locatarioAcertoLog[] = '<p>Concertou ' . $v['locatarioNome'] . ' com seu id correto ' . $entity->getId() . '</p>';
                    $seguroF->setLocatario($entity);
                    $seguroO->setLocatario($entity);
                    $this->em->persist($seguroF);
                    $this->em->persist($seguroO);
                    $this->em->flush();
                    $v['locatario']['nome'] = $entity->getNome();
                    $v['locatario']['tipo'] = $entity->getTipo();
                    $v['locatario']['cpf'] = $entity->getCpf();
                    $v['locatario']['cnpj'] = $entity->getCnpj();
                }
            }else{
                $this->locatarioAcertoLog[] = '<p>Fechado não encontrado com esse id  ' . $v['id'] . '</p>';                    
            }
        }else{
            $this->locatarioAcertoLog[] = '<p>Locatario não encontrado com esse nome  ' . $v['locatarioNome'] . '</p>';                    
        }
    }
    
    public function validaCNPJ($cnpj = null,$adm=0) {
        if(empty($cnpj)){
            if($adm == 196){
                return '05117179897';                
            }else{
                return $cnpj;
            }            
        }
        if($cnpj == 0){
            if($adm == 196){
                return '05117179897';                
            }else{
                return $cnpj;
            }                
        }
        return $cnpj;        
    }
    
    public function validaCPF($cpf = null,$adm=0) {
        
        // Verifica se um número foi informado
        if(empty($cpf)) {
            if($adm == 196){
                return '05117179897';                
            }else{
                return $cpf;
            }
        }
        
        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);        
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        if ($cpf == '00000000000' || 
            $cpf == '11111111111' || 
            $cpf == '22222222222' || 
            $cpf == '33333333333' || 
            $cpf == '44444444444' || 
            $cpf == '55555555555' || 
            $cpf == '66666666666' || 
            $cpf == '77777777777' || 
            $cpf == '88888888888' || 
            $cpf == '99999999999') {
            if($adm == 196){
                return '05117179897';                
            }else{
                return $cpf;
            }
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
        }   

        for ($t = 9; $t < 11; $t++) {

            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            
            $d = ((10 * $d) % 11) % 10;
            
            if ($cpf{$c} != $d) {
                if($adm == 196){
                    return '05117179897';                
                }else{
                    return $cpf;
                }
            }
        }

        return $cpf;
        
    }
    
    public function setLine03(&$value) {
        //========= Linha 03 com DADOS DO LOCAL DE RISCO Obrigatorio =======================
        $item = $this->item;
        $this->saida .= '03';
        //Número do Item	6
        $this->saida .= str_pad($item, 6, '0', STR_PAD_LEFT);
        //Nome do Inquilino	60
        $this->addSaida2($value['locatario']['nome'], 60);
        //CPF / CNPJ Inquilino	14
        if ($this->tipoLocatario == 'F'){
            //Tipo de Pessoa do Inquilino	1
            $this->saida .= $this->tipoLocatario;
            $this->saida .= $this->cleanDocFomatacao($this->validaCPF($value['locatario']['cpf'], $value['administradora']['id']));
        }else{
            if($this->validaCNPJ($value['locatario']['cnpj'],$value['administradora']['id']) == '05117179897'){
                //Tipo de Pessoa do Inquilino	1
                $this->saida .= 'F';                
                $this->saida .= $this->cleanDocFomatacao('05117179897');
            }else{
                //Tipo de Pessoa do Inquilino	1
                $this->saida .= $this->tipoLocatario;
                $this->saida .= $this->cleanDocFomatacao($value['locatario']['cnpj']);                
            }
        }
        //Nome do Proprietário	60
        $this->addSaida2($value['locador']['nome'], 60);
        //Tipo de Pessoa do Proprietário	1
        $this->saida .= $this->tipoLocador;
        //CPF / CNPJ Proprietário	14
        if($this->tipoLocador == 'F'){
            $this->saida .= $this->cleanDocFomatacao($value['locador']['cpf']);
        }else{
            $this->saida .= $this->cleanDocFomatacao($value['locador']['cnpj']);
        }
        //Endereco	40
        $this->addSaida2($value['imovel']['rua'], 40);
        //Número do Endereço	6
        $this->addSaida2($value['imovel']['numero'], 6, '0', 'STR_PAD_LEFT'); 
        //Compl. do Endereço	15
        $compl = '';
        if(!empty($value['imovel']['apto'])){
            $compl .= 'AP ' . $value['imovel']['apto'] . ' ';
        }
        if(!empty($value['imovel']['bloco'])){
            $compl .= 'BL ' . $value['imovel']['bloco'] . ' ';
        }
        $compl .= $value['imovel']['endereco']['compl'];
        $this->addSaida2($compl, 15);
        //Bairro	30
        $this->addSaida2($value['imovel']['endereco']['bairro']['nome'], 30);
        //Cidade	30
        $this->addSaida2($value['imovel']['endereco']['cidade']['nome'], 30);
        //UF	2
        $this->saida .= str_pad($value['imovel']['endereco']['estado']['sigla'], 2);
        //CEP	8
        $this->addSaida2($value['imovel']['cep'], 8);
        //Código da Atividade	4
        $ativid = $value['atividade']['codSeguradora'];
        $this->addSaida2($ativid, 4, '0', 'STR_PAD_LEFT'); 
        //Cobertura	1
        $this->saida .= substr($value['tipoCobertura'], 1, 1);
        //Tipo de Residência	1
        switch ($this->ativid) {
            case 918:
            case 919:
            case 920:
            case 921:
                    $this->saida .= '2';
                break;
            default:
                    $this->saida .= '1';
                break;
        }
        //Tipo de Moradia	4
        $this->saida .= '1101';
        //Tipo de Construção	1
        switch ($this->ativid) {
            case 910:
            case 918:
                    $this->saida .= '1';
                break;
            default:
                    $this->saida .= '2';
                break;
        }
        //Indicador de Assistência	1
        $this->saida .= '0';
        //Número do Orçamento	6
        $this->addSaida2($value['id'], 6, '0', 'STR_PAD_LEFT'); 
        //Data de Nascimento Inquilino	10
        $this->saida .= str_pad(' ', 10);
        //Observação	254
        $this->addSaida2($value['observacao'], 254);
        //Indicador de Verbas Separadas	1
        $this->saida .= ($value['tipoCobertura'] == '04') ? '1' : '0';
        //Período Indenitário Perda/Pgto. Aluguel	2
        $this->saida .= str_pad('0', 2);
        if($value['taxaAjuste'] == 1 OR $value['taxaAjuste'] == 0){ // taxaAjuste vazio ou neutra
            $tax = '';
        }else{ // menor ou maior conveter em agravo ou desconto dependendo do sinal
            $tax =  number_format(($value['taxaAjuste'] - 1) * 100, 2, ',', '.') ;
        }
        // Agravo tam 6
        $this->addSaida2($tax, 6, ' ', 'STR_PAD_LEFT'); 
        // Fim da linha 03
        $this->saida .= "\r\n";
    }

    public function setLine05(&$value) {
        //========= Linha 05 com DADOS DA COBERTURA ====================================
        // Se escolha entre Incendio ou Incendio + Conteudo
        if($value['tipoCobertura'] == '01' ){
            $incendio = 'incendio';   
        }else{
            $incendio = 'conteudo';   
        }
        $cobArray = [
            '011101' => $incendio,
            '011103' => 'eletrico',
            '011117' => 'aluguel',
            '011131' => 'vendaval',
        ];
        foreach ($cobArray as $key => $cob) {
            if($value[$cob] == 0){
                continue; // não coloca coberturas zeradas
            }
            $this->saida .= '05';
            // Número do Item	6
            $this->saida .= str_pad($this->item, 6, '0', STR_PAD_LEFT);
            // Número do Orçamento	6
            $this->addSaida2($value['id'], 6, '0', 'STR_PAD_LEFT');
            // Código da Cobertura	6
            $this->saida .= $key;
            // Importância Segurada	17
            $this->saida .= str_pad(number_format($value[$cob], 2, '', ''), 17, '0', STR_PAD_LEFT);;
            // Taxa	6
            $this->saida .= '000000';
            // Taxa Informada	1
            $this->saida .= '0';
            // Fim da linha 05
            $this->saida .= "\r\n";        
        }        
    }

    public function setLine10(&$value) {
        //========= Linha 10 CLAUSULA BENEFICIARIA  ====================================
        $this->saida .= '10';    
        // NUMERO DO ITEM DEVER SER BRANCOS	3
        $this->saida .= '   ';    
        // Número de beneficiário	3
        $this->saida .= '001';    
        // Nome	60
        $this->addSaida2($value['locador']['nome'], 60);
        // Tipo de pessoa	1
        $this->saida .= ($this->tipoLocador == 'F')? '1' : '2';
        // CPF/CNPJ	14
        if($this->tipoLocador == 'F'){
            $this->saida .= $this->cleanDocFomatacao($value['locador']['cpf']);    
        }else{
            $this->saida .= $this->cleanDocFomatacao($value['locador']['cnpj']);    
        }
        // Cobertura	1
        $this->saida .= '0';    
        // Participação (%)	5
        $this->saida .= '10000';    
        // Número do item	6
        $this->saida .= str_pad($this->item, 6, '0', STR_PAD_LEFT);
        // Fim da linha 10
        $this->saida .= "\r\n";         
    }
    
    /**
     * Incrementa variavel e formata para saida para gravação
     * @param string $conteudo Dados a ser incrementado
     * @param integer $tam      Quantidade de caracteres
     * @param string $compl    Completar com esse caractere(padrão spaços)
     * @param string $opt      Para completar no lado esquerdo ou direito(Padrão)
     */
    public function addSaida2($conteudo,$tam,$compl='',$opt=''){
        if(empty($opt)){
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam);             
        }else{
            $this->saida .= str_pad(substr(utf8_decode($conteudo),0,$tam), $tam, $compl, STR_PAD_LEFT);            
        }
    } 
    
    /**
     * Limpar do documento a formatação e outros caracteres diferentes de numeros
     * @param string $doc cpf ou cpnf a ser limpo
     * @param int $tam tamanhos da string a ser retornada
     * @param string $rep depois de limpar o documento preencher os espaços ou com padrão '0'.
     * @return string string com o tamanho configurado ou padrão 14 posições
     */
    public function cleanDocFomatacao($doc , $tam = 14, $rep = '0') {
        $clean = preg_replace("/[^0-9]/", "", $doc);
        if(strlen($clean) > $tam){
            $clean = substr(utf8_decode($clean),0,$tam);
        }
        return str_pad($clean, $tam, $rep, STR_PAD_LEFT);              
    }
    
}
    