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
    
    public function geraExpForCOL($adm){
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
            $this->exporta();
        }
        
        if($this->qtdExportado != 0){
            $this->logExportadoCol($mes,$ano);
        }
    }
    
    public function exportaLello($ocup){
        $vCliente = false;
        foreach ($this->expts as $this->expt) {
            
            // ============== HEADER DA IMPORTAÇÃO  UM HEADER POR DIA !!!
            if(!$vCliente){
                $vCliente = $this->expt['administradora']['codigoCol'];
                if ($vCliente == '' OR $vCliente == 0){
                    echo '<p>Administradora sem codigo COL para exportação</p>';
                    return false;
                }
            }
            $this->pr['vCliente']                   = $vCliente;
            
            if($this->expt['seguradora']['id'] != 3){
                echo '<p>Seguradora não é a Allianz fechado numero ' . $this->expt['id'] . '</p>';
                continue;
            }
print_r($this->expt);die;
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
            $this->qtdExportado++;
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
            
            // ============== RESTANTES DO REGISTROS DO MESMO DIA
            // Pega numero item na tabela par_cont
            $this->pr['vItem'] = $this->getVItem();  
            
            // Pegando valor do Numero do item para Tabela DocsItensCobs
            $this->pr['vItemCob'] = $this->pr['vItem'];  
            
            // Inserir endereço dos itens
            $this->insertToDocsItens();
            
            // Inserindo dados na tabela DocsItensCobs (INCENDIO)
            
            
            
        }
        
    }
    
    public function insertToDocsItens($arg){
        
        $this->pr['vProprietario'] = $this->addSaida($this->expt['locador']['nome'], 50);
        $this->pr['vCep']          = $this->expt['imovel']['cep'];
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
        
        $this->execColQuery($q);
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
        $q .= $this->pr['vDocumento'] . ", " . $this->pr['vAlteracao'] . ", '" . $this->pr['vData_proposta'] . "', " . $this->pr['vProposta'] . ", 1, 1, '1', '1', " . $vCliente . ", ";
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
        if($c == 50){
            $this->pr['vPerc_com_base']         = 50;
        }else{
            $this->pr['vPerc_com_base']         = $resul['Perc_com_base'];
        }
    }
    
    public function getDocumentos(){
        $resul = $this->execColQuery("EXEC ss_Par_Cont_Int tabela_documentos");
        $this->pr['vDocumento'] = $resul['contador'];
        $this->pr['vAlteracao'] = $resul['0'];
        
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
        $resul = $this->getMssql()->q($q);
        return $resul->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function exporta(){
        
        foreach ($this->getSc()->lista as $this->expt) {
            $codigoCol = $this->expt['administradora']['codigoCol'];
            if ($codigoCol == '' OR $codigoCol == 0){
                echo '<p>Administradora sem codigo COL para exportação</p>';
                return false;
            }
        }
        
    }
    
    public function addSaida($conteudo,$tam,$compl='',$opt=''){
        if(empty($opt)){
            return str_pad(utf8_decode($conteudo), $tam);
        }else{
            return str_pad(utf8_decode($conteudo), $tam, $compl, STR_PAD_LEFT);            
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
