<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

class Administradora extends AbstractService {
    
    /**
     * String no formato para gravação de alterações feitas no registro endereço
     * Formato campo  nome; valor antes; valor depois;
     * @var string
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Administradora";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro no doctrine 
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        if(!isset($this->data['validade']))$this->data['validade'] = '';
    }
    
    public function insert(array $data) {
        //Verifica esse codigo já existe.
        $exist = $this->em->find($this->entity, $data['id']);
        if($exist)
            return ['Erro já existe uma Administradora com esse codigo!!!'];
        
        $this->data = $data;
        //Pegando referencias padrao dessa entity
        $this->setReferences();
        //Pegando o servico endereco e inserindo novo endereco na administradora
        $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;
    }
    
    public function logForNew($tabela = 'administradora') {
        parent::logForNew($tabela);
    }

    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEndereco = new Endereco($this->em);
        $this->data['endereco'] = $serviceEndereco->update($this->data);
        $this->deParaEnd = $serviceEndereco->getDePara();
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    public function logForEdit($tabela = 'administradora') {
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Administradora $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Codigo Col', $ent->getCodigoCol(), $this->data['codigoCol']);
        $this->dePara .= $this->diffAfterBefore('Apelido', $ent->getApelido(), $this->data['apelido']);
        $cnpj = $ent->formatarCPF_CNPJ($this->data['cnpj']);
        if($cnpj){
            $this->dePara .= $this->diffAfterBefore('CNPJ', $ent->getCnpj(), $cnpj);
        }
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Forma de Pag', $ent->getFormaPagto(), $this->data['formaPagto']);
        $this->dePara .= $this->diffAfterBefore('Validade', $ent->getValidade(), $this->data['validade']);
        $this->dePara .= $this->diffAfterBefore('Tipo de Cobertura', $ent->getTipoCobertura(), $this->data['tipoCobertura']);
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora()->getId(), $this->data['seguradora']->getId());
        $this->dePara .= $this->diffAfterBefore('Assistencia 24', $ent->getAssist24(), $this->data['assist24']);
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
    }
    
    /**
     * Atualiza manualmente as administradoras com seu codigo col de referencia
     */
/**
    public function setCodCol(){
        $list = [26 => 777,
            176 => 7585,
            1893 => 24303,
            56 => 1961,
            1308 => 9301,
            11715 => 0,
            1336 => 2079564,
            394 => 1793715,
            164 => 22695,
            1906 => 11430,
            486 => 1085555,
            17 => 1984403,
            3217 => 2181541,
            4572 => 2360661,
            1914 => 3636,
            2884 => 1708892,
            44 => 809,
            2711 => 2181607,
            3352 => 2199846,
            563 => 2181556,
            3028 => 0,
            3186 => 2265367,
            167 => 2219994,
            2087 => 9384235,
            13580 => 454796,
            11112 => 31574,
            2243 => 30488,
            290 => 9743,
            11049 => 9383492,
            3245 => 2250376,
            2921 => 1771449,
            2132 => 32354,
            278 => 9439,
            240 => 1990551,
            3275 => 2181645,
            1724 => 2102164,
            256 => 9647,
            3158 => 9388130,
            3234 => 27945,
            474 => 9709,
            199 => 9384409,
            3478 => 2178036,
            4760 => 0,
            2354 => 2075116,
            1452 => 9384237,
            4588 => 9384233,
            4208 => 2181575,
            94 => 8166,
            304 => 11270,
            104 => 25780,
            286 => 9384481,
            1980 => 30370,
            3344 => 2181522,
            1993 => 1824976,
            2677 => 2181581,
            1992 => 2128022,
            196 => 2038551,
            3371 => 28543,
            719 => 2181561,
            2721 => 2329884,
            1228 => 2181537,
            2901 => 9386411,
            4566 => 2200979,
            12 => 27133,
            2511 => 9386095,
            3209 => 9384241,
            618 => 9383559,
            2022 => 893,
            4533 => 2218972,
            3298 => 2247941,
            30 => 8228,
            284 => 11428,
            12001 => 9411547,
            3212 => 9386614,
            12739 => 9433778,
            8 => 835,
            12444 => 9421350,
            3237 => 9386625,
            12422 => 9421351,
            12569 => 9423918,
            3199 => 9386609,
            12014 => 9416048,
            1117 => 2246169,
            12524 => 9439997,
            3234 => 27945,
            12924 => 9436103,
            13556 => 9454406,
            13084 => 9443577,
            4986 => 9387896,
            13654 => 9457114,
            62 => 1990,
            13661 => 9457484,
            4857 => 9454724,
            13830 => 9463572];
        
        foreach ($list as $key => $value) {
            $adm = $this->em->find($this->entity, $key);            
            $adm->setCodigoCol($value);
            $this->em->persist($adm);
            var_dump($key);
            var_dump($value);
        }
        
        $this->em->flush();
        
    }
*/
}
