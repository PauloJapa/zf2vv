<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

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
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
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
        $findInOrcamento = TRUE;
        $findInRenovacao = TRUE;
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
                        case '2':
                            $status = 'A';
                            $findInOrcamento = ($st == '1')? true : false;
                            $findInRenovacao = ($st == '2')? true : false;
                            break;
                        case '3':
                            $status = 'F';
                            $findInOrcamento = $findInRenovacao = true;
                            break;
                        case '4':
                        case '5':
                            $status = 'C';
                            $findInOrcamento = ($st == '4')? true : false;
                            $findInRenovacao = ($st == '5')? true : false;
                            break;
                    }
                    $this->where .= ' AND o.status = :status ';
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
        if($findInOrcamento AND !$findInRenovacao){
            return $this->getInOrcamento ($data);
        }        
        if($findInRenovacao AND !$findInOrcamento){
            return $this->getInRenovacao ($data);
        }        
        return array_merge ($this->getInOrcamento ($data), $this->getInRenovacao($data));
            
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
    
}


