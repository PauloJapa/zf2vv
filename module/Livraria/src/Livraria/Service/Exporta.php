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
    protected $item;
    protected $tipoLocatario;
    protected $tipoLocador;
    protected $ativid;
    
    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function getSc(){
        if($this->sc)
            return $this->sc;
        $this->sc = new SessionContainer("LivrariaAdmin");
        return $this->sc;
    }
     
    public function listaExptMar($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        $this->data['administradora'] = $data['administradora'];
        $this->data['seguradora']     = $data['seguradora'];
        //Guardar dados do resultado 
        $this->getSc()->listaMar = $this->em->getRepository("Livraria\Entity\Fechados")->getListaExporta($this->data); 
        $this->getSc()->data     = $data;
        return$this->getSc()->listaMar;
    }
    
    public function geraArqsForMaritima(){
        // Separar Adm em arquivos por tipo de pagamento e tipo de ocupacao
        $data   = $this->getSc()->data;
        $admCod = $data['administradora'];
        //$baseWork = '\\s-1482\Imagem\Incendio_locacao\\';
        $baseWork = '/var/www/zf2vv/data/work/';
        $zipFile = $baseWork . $admCod . "_Maritima.zip";
        $this->openZipFile($zipFile);
        $file['e01'] = $baseWork . $admCod . '_empresarial_ato.KM2';
        $file['e02'] = $baseWork . $admCod . '_empresarial_1x1.KM2';
        $file['e03'] = $baseWork . $admCod . '_empresarial_1x2.KM2';
        $file['e04'] = $baseWork . $admCod . '_empresarial_mensal.KM2';
        $file['r01'] = $baseWork . $admCod . '_residencial_ato.KM2';
        $file['r02'] = $baseWork . $admCod . '_residencial_1x1.KM2';
        $file['r03'] = $baseWork . $admCod . '_residencial_1x2.KM2';
        $file['r04'] = $baseWork . $admCod . '_residencial_mensal.KM2';
        foreach ($file as $key => $arq) {
            if(!$this->setConteudo($key, $arq)){
                $this->writeFile();
                $this->closeFile();  
                $this->addFileToZip($arq,$baseWork);
            }
        }  
        $this->zip->close();
        return $zipFile;
    }
    
    public function openZipFile($zipFile){
        $this->zip = new \ZipArchive;
        if($this->zip->open($zipFile, \ZipArchive::OVERWRITE)  !== true){
            echo 'erro';
            return FALSE;
        }
    }

    public function addFileToZip($file,$baseWork){
        $name = substr($file, strlen($baseWork));
        $this->zip->addFile($file,$name);
    }

    public function openFile($arq) {
        $this->fp = fopen($arq, "w");
    }

    public function writeFile(){
        fwrite($this->fp, $this->saida);
    }

    public function closeFile(){
        fclose($this->fp);
    }

    public function setConteudo($filtro, $arq){
        $head = TRUE;
        $ocupacao = substr($filtro, 0, 1);
        $formaPgto = substr($filtro, 1, 2);
        $this->item = 0;
        $this->saida = '';
        foreach ($this->getSc()->listaMar as $value) {
            $this->ativid = $value['atividade']['codSeguradora'];
            $this->tipoLocatario = strtoupper(substr($value['locatario']['tipo'], 0, 1));
            $this->tipoLocador   = strtoupper(substr($value['locador']['tipo'], 0, 1));
            if($ocupacao == 'e'){ // apenas empresarial
                if($this->ativid == 911 OR $this->ativid == 919){
                    continue; // é residencial entao filtra
                }
            }else{ // apenas residencial
                if($this->ativid != 911 AND $this->ativid != 919){
                    continue; // não é residencial entao filtra
                }                
            }
            // Filtra forma de pagamento
            if($formaPgto != $value['formaPagto']){
                continue;
            }
            if($head){
                $this->openFile($arq);
                $this->montaHead($value);
                $head = FALSE;
            }
            $this->item ++;
            $this->setLine03($value);
            $this->setLine05($value);
            $this->setLine10($value);
        }        
        return $head;
    }

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
        $this->saida .= str_pad($value['id'], 6, '0', STR_PAD_LEFT);
        // Data do calculo
        $this->saida .= $value['criadoEm']->format('d/m/Y');
        // Inicio Vigencia
        $this->saida .= $value['inicio']->format('d/m/Y');
        // Fim Vigencia
        $this->saida .= $value['fim']->format('d/m/Y');
        // Proponente tam 60
        $this->saida .= str_pad($value['administradora']['nome'], 60);
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
        $this->saida .= PHP_EOL;        
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
        // Incluir automenticamente co-corretagem 1
        $this->saida .= 'N';
        // Fim da linha 01
        $this->saida .= PHP_EOL;        
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
        $this->saida .= str_pad('SÃO PAULO', 40);
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
        $this->saida .= str_pad('SÃO PAULO', 40);
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
        $this->saida .= PHP_EOL;
    }

    public function setLine03(&$value) {
        //========= Linha 03 com DADOS DO LOCAL DE RISCO Obrigatorio =======================
        $item = $this->item;
        $this->saida .= '03';
        //Número do Item	6
        $this->saida .= str_pad($item, 6, '0', STR_PAD_LEFT);
        //Nome do Inquilino	60
        $this->saida .= str_pad($value['locatario']['nome'], 60);
        //Tipo de Pessoa do Inquilino	1
        $this->saida .= $this->tipoLocatario;
        //CPF / CNPJ Inquilino	14
        $this->saida .= ($this->tipoLocatario == 'F')?str_pad($value['locatario']['cpf'], 14):str_pad($value['locatario']['cnpj'], 14);
        //Nome do Proprietário	60
        $this->saida .= str_pad($value['locador']['nome'], 60);
        //Tipo de Pessoa do Proprietário	1
        $this->saida .= $this->tipoLocador;
        //CPF / CNPJ Proprietário	14
        $this->saida .= ($this->tipoLocador == 'F')?str_pad($value['locador']['cpf'], 14):str_pad($value['locador']['cnpj'], 14);
        //Endereco	40
        $this->saida .= str_pad($value['imovel']['rua'], 40);
        //Número do Endereço	6
        $this->saida .= str_pad($value['imovel']['numero'], 6, '0', STR_PAD_LEFT);
        //Compl. do Endereço	15
        $this->saida .= str_pad($value['imovel']['rua'], 15);
        //Bairro	30
        $this->saida .= str_pad($value['imovel']['endereco']['bairro']['nome'], 30);
        //Cidade	30
        $this->saida .= str_pad($value['imovel']['endereco']['cidade']['nome'], 30);
        //UF	2
        $this->saida .= str_pad($value['imovel']['endereco']['estado']['sigla'], 2);
        //CEP	8
        $this->saida .= str_pad($value['imovel']['cep'], 15);
        //Código da Atividade	4
        $ativid = $value['atividade']['codSeguradora'];
        $this->saida .= str_pad($ativid, 4, '0', STR_PAD_LEFT);
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
        $this->saida .= str_pad($value['id'], 6, '0', STR_PAD_LEFT);
        //Data de Nascimento Inquilino	10
        $this->saida .= str_pad(' ', 10);
        //Observação	254
        $this->saida .= str_pad($value['observacao'], 254);
        //Indicador de Verbas Separadas	1
        $this->saida .= ($value['tipoCobertura'] == '04') ? '1' : '0';
        //Período Indenitário Perda/Pgto. Aluguel	2
        $this->saida .= str_pad(' ', 2);
        // Fim da linha 03
        $this->saida .= PHP_EOL;
    }

    public function setLine05(&$value) {
        //========= Linha 05 com DADOS DA COBERTURA ====================================
        $cobArray = [
            '011101' => 'incendio',
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
            $this->saida .= str_pad($value['id'], 6, '0', STR_PAD_LEFT);
            // Código da Cobertura	6
            $this->saida .= $key;
            // Importância Segurada	17
            $this->saida .= str_pad(number_format($value[$cob], 2, '', ''), 17, '0', STR_PAD_LEFT);;
            // Taxa	6
            $this->saida .= '000000';
            // Taxa Informada	1
            $this->saida .= '0';
            // Fim da linha 05
            $this->saida .= PHP_EOL;        
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
        $this->saida .= str_pad($value['locador']['nome'], 60);    
        // Tipo de pessoa	1
        $this->saida .= ($this->tipoLocador == 'F')? '1' : '2';
        // CPF/CNPJ	14
        $this->saida .= ($this->tipoLocador == 'F')?str_pad($value['locador']['cpf'], 14, '0', STR_PAD_LEFT):str_pad($value['locador']['cnpj'], 14, '0', STR_PAD_LEFT);
        // Cobertura	1
        $this->saida .= '0';    
        // Participação (%)	5
        $this->saida .= '100000';    
        // Número do item	6
        $this->saida .= str_pad($this->item, 6, '0', STR_PAD_LEFT);
        // Fim da linha 10
        $this->saida .= PHP_EOL;         
    }
    
}