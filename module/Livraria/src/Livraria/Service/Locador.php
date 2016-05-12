<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Locador
 * Faz o CRUD da tabela Locador no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Locador extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do locador
     * @var string 
     */
    protected $deParaEnd;
    
    /**
     * Conexão com o direta com o BD
     * @var Mysql
     */
    protected $con;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Locador";
    }
    
    /**
     * Cria ou pega um instancia do PDO do Mysql
     * @return Mysql
     */
    public function getCon() {
        if($this->con){
            return $this->con;
        }
        $this->con = new Mysql();
        return $this->con;
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
                
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        if(!isset($this->data['endereco'])){
            $this->data['endereco'] = '1';
        }
        if(!isset($this->data['compl'])){
            $this->data['compl'] = '';
        }
        if(!isset($this->data['tel'])){
            $this->data['tel'] = '';
        }
        if(!isset($this->data['email'])){
            $this->data['email'] = '';
        }
        if(!isset($this->data['status'])){
            $this->data['status'] = 'A';
        }       
        
        //Pegando o servico endereco e inserindo novo endereco do locador se houver
        if(isset($this->data['rua']) AND !empty($this->data['rua'])){
            $this->data['endereco'] = (new Endereco($this->em))->insert($this->data);            
        }else{
        // Setar endereço vazio para 1 pois não tem endereço
            $this->data['endereco'] = '1';
            $this->idToReference('endereco', 'Livraria\Entity\Endereco');            
        }

        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em locadors
     */
    public function logForNew($tabela='locador'){
        parent::logForNew($tabela);
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        //Pegando o servico endereco e atualizando endereco do locador
        $serviceEnd = new Endereco($this->em);
        /* @var $ent  \Livraria\Entity\Locador  */
        $ent = $this->getEntity(); 
        
        if(!isset($this->data['endereco'])){
            $this->data['endereco'] = $ent->getEndereco()->getId();
        }
        if(!isset($this->data['compl'])){
            $this->data['compl'] = $ent->getEndereco()->getCompl();
        }
        if(!isset($this->data['tel'])){
            $this->data['tel'] = $ent->getTel();
        }
        if(!isset($this->data['email'])){
            $this->data['email'] = $ent->getEmail();
        }
        if(!isset($this->data['status'])){
            $this->data['status'] = $ent->getStatus();
        }        
        
        $this->data['idEnde'] = $ent->getEndereco()->getId();
        $this->data['endereco'] = $serviceEnd->update($this->data);
        $this->deParaEnd = $serviceEnd->getDePara();
        //Verificar se mudou o nome para alterar nos Orcamentos e Fechados.
        $this->getCon()->bg();
        if($ent->getNome() != $this->data['nome']){
            $this->changeNameAtSeguros($ent->getId(), $this->data['nome']);
        }
        
        if (parent::update()) {
            $this->logForEdit();
            $this->getCon()->co();
        }

        return TRUE;
    }

    /**
     * Altera o nome em todos os Orçamento e Fechados desse Locador
     * @param int $id
     * @param string $nome
     * @return void
     */
    public function changeNameAtSeguros($id, $nome) {
        if(empty($nome)){
            return;
        }
        $q1 = 'UPDATE `orcamento` SET `locador_nome` = ? WHERE `locador_id` = ? ;';
        $this->getCon()->p($q1);
        $this->getCon()->e([$nome, $id]);
        $q2 = 'UPDATE `fechados` SET `locador_nome` = ? WHERE `locador_id` = ? ;';
        $this->getCon()->p($q2);
        $this->getCon()->e([$nome, $id]);        
    }
    
    /**
     * Grava no logs dados da alteção feita em locadors De/Para
     */
    public function logForEdit($tabela='locador'){
        parent::logForEdit($tabela);
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Locador $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getNome(), $this->data['nome']);
        $this->dePara .= $this->diffAfterBefore('Tipo', $ent->getTipo(), $this->data['tipo']);
        $this->dePara .= $this->diffAfterBefore('CPF', $ent->getCpf(), $ent->formatarCPF_CNPJ($this->data['cpf']));
        $this->dePara .= $this->diffAfterBefore('CNPJ', $ent->getCnpj(), $ent->formatarCPF_CNPJ($this->data['cnpj']));
        $this->dePara .= $this->diffAfterBefore('Telefone', $ent->getTel(), $this->data['tel']);
        $this->dePara .= $this->diffAfterBefore('Email', $ent->getEmail(), $this->data['email']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        //Juntar as alterações no endereço se houver
        $this->dePara .= $this->deParaEnd;
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        if(!$this->isValid)   
            return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        if(empty($this->data['cpf']) AND empty($this->data['cnpj'])){
            return ['Cadastro digitado sem documento'];
        }
        $filtro = array('status' => 'A');
        if (!empty($this->data['cpf'])) {
            $filtro['cpf'] = $this->validaDoc($this->data['cpf']);
            if($filtro['cpf'] === FALSE){
                return ['CPF Invalido por favor confira o numero digitado !!!!'];                             
            }
        }

        if (!empty($this->data['cnpj'])) {
            $filtro['cnpj'] = $this->validaDoc($this->data['cnpj'],'juridica');
            if($filtro['cnpj'] === FALSE){
                return ['CNPJ Invalido por favor confira o numero digitado !!!!'];                             
            }
        }
        if (!empty($this->data['administradora'])) {
            $filtro['administradora'] = $this->data['administradora'];
        }
        /* @var $entitys \Livraria\Entity\Locador */
        $entitys = $repository->findBy($filtro);
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] == $entity->getId()){
                continue;
            }
            if($entity->getTipo() == 'fisica'){
                if(($entity->getCpf(FALSE) == $filtro['cpf'])){
                    $erro[] = 'Já existe esse cpf de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
                    $erro[] = $entity->getId();
                }
            }else{
                if(($entity->getCnpj(FALSE) == $filtro['cnpj'])){
                    $erro[] = 'Já existe esse cnpj de ' . $entity->getNome() . " nesta administradora " . $entity->getAdministradora();
                    $erro[] = $entity->getId();
                }
            }
        }
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
        
    
    public function validaDoc($doc, $tipo = 'fisica') {
        if($tipo == 'fisica'){
            return $this->validaCPF($doc);
        }
        return $this->validaCnpj($doc);
    }    
    
    
    public function validaCnpj($cnpj = null) {
    	// Deixa o CNPJ com apenas números
        $cnpj = str_pad(preg_replace( '/[^0-9]/', '', $cnpj ), 14, '0', STR_PAD_LEFT);
        // O valor original
        $cnpj_original = $cnpj;
        // Captura os primeiros 12 números do CNPJ
        $primeiros_numeros_cnpj = substr( $cnpj, 0, 12 );    
        // Faz o primeiro cálculo
        $primeiro_calculo = $this->multiplica_cnpj( $primeiros_numeros_cnpj );
        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 :  11 - ( $primeiro_calculo % 11 );
        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = $this->multiplica_cnpj( $primeiros_numeros_cnpj, 6 );
        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 :  11 - ( $segundo_calculo % 11 );
        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;
        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            return $cnpj_original;
        }
        return FALSE;
    }
    
    	
    /**
     * Multiplicação do CNPJ
     *
     * @param string $cnpj Os digitos do CNPJ
     * @param int $posicoes A posição que vai iniciar a regressão
     * @return int O
     *
     */
    public function multiplica_cnpj($cnpj, $posicao = 5) {
        // Variável para o cálculo
        $calculo = 0;
        // Laço para percorrer os item do cnpj
        for ($i = 0; $i < strlen($cnpj); $i++) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ( $cnpj[$i] * $posicao );
            // Decrementa a posição a cada volta do laço
            $posicao--;
            // Se a posição for menor que 2, ela se torna 9
            if ($posicao < 2) {
                $posicao = 9;
            }
        }
        // Retorna o cálculo
        return $calculo;
    }

    public function validaCPF($cpf = null) {
        // Verifica se um número foi informado
        if(empty($cpf)) {
            return false;
        }
        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11
        if (strlen($cpf) != 11) {
            return false;
        }
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
            return false;
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
         } else {  
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return $cpf;
        }
    }
}
