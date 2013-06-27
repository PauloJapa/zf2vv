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
    
    public function geraExpForCOL(){
        $data = $this->getSc()->data;
        $mes  = $data['mesFiltro'];
        $ano  = $data['anoFiltro'];
        
        foreach ($this->getSc()->lista as $reg) {
            $codigoCol = $reg['administradora']['codigoCol'];
            if ($codigoCol == '' OR $codigoCol == 0){
                echo '<p>Administradora sem codigo COL para exportação</p>';
                return false;
            }
            $verificaSeFez = $this->em
                             ->getRepository("Livraria\Entity\LogFaturaCol")
                             ->findBy(['administradoraId' => $reg['administradora']['id'], 'mes' => $mes, 'ano' => $ano ]);
            if($verificaSeFez){
                echo '<p>Já foi realizado a exportação dessa administradora nesse periodo!!!</p>';
                return false;
            }
            
        }
    }
    
    
    
    
}
