<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use LivrariaAdmin\Fpdf\ImprimirSeguro;

/**
 * Orcamento
 * Faz o CRUD da tabela Orcamento no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Orcamento extends AbstractService {

    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string
     */
    protected $deParaImovel;

    /**
     * Para gerar o pdf com orçamento
     * @var objet
     */
    protected $pdf;

    /**
     * Locatario repository
     * @var  \Livraria\Entity\LocatarioRepository
     */
    protected $locatarioRepository;


    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Orcamento";
    }

    public function delete($id, $data, $force=false, $sl=null) {
        /* @var $enty \Livraria\Entity\Orcamento */
        $enty = $this->em->find($this->entity,$id);
        if(!$force AND $enty->getStatus() == 'F'){
            /* @var $srvFechado \Livraria\Service\Fechados */
            $srvFechado = $sl->get('\Livraria\Service\Fechados');
            return $srvFechado->delete($enty->getFechadoId(), $data, $sl);
        }
        if($enty->getStatus() == 'C'){
            return ['Erro este orçamento já foi cancelado anteriormente!!'];
        }
        if(!parent::delete($id)){
            return ['Erro ao tentar excluir registro!!'];
        }
        $this->logForDelete($id, $data, $enty->getOrcaReno());
        return TRUE;
    }

    /**
     * Registra a exclusão do registro com seu motivo.
     * @param string $id
     * @param array  $data (Motivo do cancelamento)
     */
    public function logForDelete($id,$data, $orcaReno) {
        //serviço logorcamento
        if($orcaReno == 'reno'){
            $log = new LogRenovacao($this->em);
            $dataLog['renovacao'] = $id;
        }else{
            $log = new LogOrcamento($this->em);
            $dataLog['orcamento'] = $id;
        }
        $dataLog['tabela'] = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'delete';
        $dataLog['mensagem'] = 'Orçamento excluido com numero ' . $id;
        if(!empty($data['motivoNaoFechou'])){
            $dataLog['dePara'] = $data['motivoNaoFechou'] ;
        }
        if(!empty($data['motivoNaoFechou2'])){
            $dataLog['dePara'] = $data['motivoNaoFechou2'] ;
        }
        $log->insert($dataLog);
    }

    public function logForReativa(\Livraria\Entity\Orcamento $ent, $motivo) {
        //serviço logorcamento
        if($ent->getOrcaReno() == 'reno'){
            $log = new LogRenovacao($this->em);
            $dataLog['renovacao'] = $ent->getId();
        }else{
            $log = new LogOrcamento($this->em);
            $dataLog['orcamento'] = $ent->getId();
        }
        $dataLog['tabela'] = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'reativa';
        $dataLog['mensagem'] = 'Registro reativado numero ' . $ent->getId();
        $dataLog['dePara'] = $motivo ;
        $log->insert($dataLog);

    }

    public function logForReativaFechado(\Livraria\Entity\Fechados $ent, $motivo) {
        $log = new LogFechados($this->em);
        $dataLog['fechados'] = $ent->getId();
        $dataLog['tabela'] = 'log_fechados';
        $dataLog['controller'] = 'orcamentos';
        $dataLog['action'] = 'reativa';
        $dataLog['mensagem'] = 'Registro reativado numero ' . $ent->getId();
        $dataLog['dePara'] = $motivo ;
        $log->insert($dataLog);
    }

    public function reativar($controller, $data) {
        if(empty($data['id']) OR empty($data['motivoReativa'])){
            $controller->flashMessenger()->addMessage('NÃO EXISTE PARAMETROS');
            return;
        }
        /* @var $entity \Livraria\Entity\Orcamento */
        $entity = $this->em->find($this->entity, $data['id']);
        if($entity->getStatus() != 'C'){
            $controller->flashMessenger()->addMessage('Este seguro não esta mais cancelado!!');
            return;
        }
        if($entity->getFechadoId() == '0'){
            if($entity->getOrcaReno() == 'reno'){
                $entity->setStatus('R');
            }else{
                $entity->setStatus('A');
            }
        }else{
            $entity->setStatus('F');
            $this->reativarFechado($controller,$entity->getFechadoId(), $data['motivoReativa']);
        }
        $this->logForReativa($entity, $data['motivoReativa']);
        $this->em->persist($entity);
        $this->em->flush();
        $controller->flashMessenger()->addMessage('Este seguro foi reativado com sucesso!!');
//        var_dump($entity->getAluguel());die;
    }

    public function reativarFechado($controller, $id='', $motivo) {
        if(empty($id) OR $id == 0){
            $controller->flashMessenger()->addMessage('Numero do seguro fechado em branco ou zerado!!');
            return;
        }
        /* @var $f \Livraria\Entity\Fechados */
        $f = $this->em->find('Livraria\Entity\Fechados', $id);
        if($f->getStatus() != 'C'){
            $controller->flashMessenger()->addMessage('Este seguro não esta mais cancelado!!');
            return;
        }
        $f->setStatus('A');
        $this->logForReativaFechado($f, $motivo);
        $this->em->persist($f);
    }

    /**
     * Altera os Registro selecionados para data(vigência inicio) ou validade(anual, mensal) comuns entre esses registro.
     * Recalcula taxa, valor premio
     * @param objeto $controller
     * @param array  $data
     */
    public function changeDateValidity($controller, $data) {
        switch (TRUE) {
            case empty($data['changeInicio']) AND empty($data['changeValidade']) AND empty($data['changeFormaPagto']):
                $controller->flashMessenger()->addMessage('NÃO EXISTE PARAMETROS');
                return;
        }
        foreach ($data['Checkeds'] as $value) {
            /* @var $entity \Livraria\Entity\Orcamento */
            $entity = $this->em->find($this->entity, $value);
            $dados = $entity->toArray();
            $flag = TRUE;
            if(!empty($data['changeInicio']) AND $dados['inicio'] != $data['changeInicio']){
                $flag = FALSE;
                $dados['inicio']   = $data['changeInicio'];

            }
            if(!empty($data['changeValidade']) AND $dados['validade'] != $data['changeValidade']){
                $flag = FALSE;
                $dados['validade'] = $data['changeValidade'];
            }
            if(!empty($data['changeFormaPagto']) AND $dados['formaPagto'] != $data['changeFormaPagto']){
                $flag = FALSE;
                $dados['formaPagto'] = $data['changeFormaPagto'];
            }
            if($flag){
                $controller->flashMessenger()->addMessage("Este registro $value por ja ter os parametros iguais");
                continue;
            }
//            echo '<pre>';            var_dump($dados['codano']); die;
            $result = $this->update($dados);
            $this->clean();
            if($result === TRUE){
                $controller->flashMessenger()->addMessage("Registro $value ".$dados['inicio']." atualizado com sucesso ".$dados['validade']);
                continue;
            }
            $controller->flashMessenger()->addMessage("Alerta Erros no registro $value  !!!");
            foreach ($result as $value) {
                $controller->flashMessenger()->addMessage($value);
            }
        }
    }

    /**
     * @ORM\OneToOne(targetEntity="Locador")
     * @ORM\OneToOne(targetEntity="Locatario")
     * @ORM\OneToOne(targetEntity="Imovel")
     * @ORM\OneToOne(targetEntity="Taxa")
     * @ORM\OneToOne(targetEntity="Atividade")
     * @ORM\OneToOne(targetEntity="Seguradora")
     * @ORM\OneToOne(targetEntity="Administradora")
     * @ORM\OneToOne(targetEntity="User")
     * @var \DateTime $inicio
     * @var \DateTime $fim
     * @var \DateTime $criadoEm
     * @var \DateTime $canceladoEm
     * @var \DateTime $alteradoEm

     */
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('administradora', 'Livraria\Entity\Administradora');
        $this->idToReference('user', 'Livraria\Entity\User');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        $this->dateToObject('criadoEm');
        $this->dateToObject('canceladoEm');
        $this->dateToObject('alteradoEm');

        $locadorResul = $this->setLocador();
        if($locadorResul !== TRUE){
            return $locadorResul;
        }
        $locatarioResul = $this->setLocatario();
        if($locatarioResul !== TRUE){
            return $locatarioResul;
        }
        $imovelResul = $this->setImovel();
        if($imovelResul !== TRUE){
            return $imovelResul;
        }
        $AtividadeResul = $this->setAtividade();
        if($AtividadeResul !== TRUE){
            return $AtividadeResul;
        }
        return TRUE;
    }

    /**
     * Valida se atividade escolhida esta ativa
     * Caso não retorna o erro
     * @return boolean | array
     */
    public function setAtividade(){
        $this->idToEntity('atividade', 'Livraria\Entity\Atividade');
        if($this->data['atividade']->getStatus() != "A"){
            return ['Atividade escolhida esta cancelada! Por Favor entre em contato com a Vila Velha.'];
        }
        if($this->getIdentidade()->getTipo() != 'admin'){
            $basica = $this->data['atividade']->getBasica();
            if($basica == "EX" OR $basica == "SC"){
                return ['Atividade escolhida esta bloqueada! Por Favor entre em contato com a Vila Velha.'];
            }
        }
        return TRUE;
    }

    /**
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade
     */
    public function insert(array $data, $onlyCalculo=false) {
        $this->data        = $data;
        if($onlyCalculo)
            $this->setFlush (FALSE);

        if (empty($this->data['user']))
            $this->data['user'] = $this->getIdentidade()->getId();

        $ret = $this->setReferences();
        if($ret !== TRUE)
            return $ret;

        $this->calculaVigencia();

        //Comissão da Administradora padrão
        $this->data['comissaoEnt'] = $this->em
            ->getRepository('Livraria\Entity\Comissao')
            ->findComissaoVigente($this->data['administradora']->getId(),  $this->data['inicio']);
        if(!isset($this->data['comissao']) OR empty($this->data['comissao'])){
            $this->data['comissao'] = $this->data['comissaoEnt']->floatToStr('comissao');            
        }
        
        /* @var $this->data['taxa'] \Livraria\Entity\Taxa */
        $this->data['taxa'] = $this->em
                ->getRepository('Livraria\Entity\Taxa')
                ->findTaxaVigente(
                    $this->data['seguradora']->getId(),
                        $this->data['atividade']->getId(),
                        $this->data['inicio'],
                        str_replace(',', '.', $this->data['comissao']),
                        $this->data['validade'],
                        $this->data['tipoCobertura'],
                        $this->data['administradora']->getId()
        );

        if(!$this->data['taxa']){
            echo 'Seguradora', $this->data['seguradora']->getId() , '<br />';
            echo 'Atividade', $this->data['atividade']->getId() , '<br />';
            echo 'Criado em ', $this->data['criadoEm']->format('d/m/Y') , '<br />';
            echo 'Comissão ', str_replace(',', '.', $this->data['comissao']) , '<br />';
            echo 'Validade ', $this->data['validade'] , '<br />';
            echo 'Tipo de cobertura ', $this->data['tipoCobertura'] , '<br />';
            echo '<br />';
            return ['Taxas para esta classe e atividade vigente nao encontrada!!!'];
        }

        $this->data['multiplosMinimos'] = $this->em
            ->getRepository('Livraria\Entity\MultiplosMinimos')
            ->findMultMinVigente($this->data['seguradora']->getId(), $this->data['criadoEm']);

        $resul = $this->CalculaPremio();

        $this->data['fechadoId'] = '0';

        if(!isset($this->data['status']) OR empty($this->data['status'])){
            $this->data['status'] = 'A';
        }

        if($onlyCalculo){
            return TRUE;
        }

        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }

        if ($this->getIdentidade()->getIsAdmin() == '0') {
            return ['Você não tem permissão para incluir registro'];
        }

        $this->trocaNaoCalcula();

        if(parent::insert())
            $this->logForNew();

        $this->trocaNaoCalcula(true);

        return array(TRUE,  $this->data['id']);
    }

    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     */
    public function logForNew(){
        //parent::logForNew('orcamento');
        //serviço logorcamento
        if(isset($this->data['orcaReno']) AND $this->data['orcaReno'] == 'reno'){
            return;
        }
        $log = new LogOrcamento($this->em);
        $log->setFlush($this->getFlush());
        $dataLog['orcamento']  = $this->data['id'];
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'new';
        $dataLog['mensagem']   = 'Novo orçamento com numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
        $dataLog['dePara']     = '';
        $log->insert($dataLog);
    }

    /**
     * Pegando o servico endereco e inserindo ou referenciando o imovel
     * @return boolean | array
     */
    public function setImovel(){
        if(is_object($this->data['imovel'])){
            if($this->data['imovel'] instanceof \Livraria\Entity\Imovel)
                return TRUE;
        }
        //Se tem id busca no banco
        if(!empty($this->data['imovel'])){
            //Verificar se esta cadastrando um novo apartamento
            $this->idToEntity('imovel', 'Livraria\Entity\Imovel');
            if(($this->data['imovel']->getBloco() == $this->data['bloco']) AND
               ($this->data['imovel']->getApto()   == $this->data['apto'])){
                return TRUE;
            }
            $this->data['imovel'] = '';
        }
        //Se id ta vazio ou apto ou bloco e diferente tentar cadastrar ou encontrar imovel ja cadastrado
        $serviceImovel = new Imovel($this->em);
        $resul = $serviceImovel->setFlush($this->getFlush())->insert(array_merge($this->data,['status'=>'A']));
        if(is_array($resul)){
            if(($resul[0] == "Já existe um imovel neste endereço  registro:") OR
               ($resul[0] == "Já existe um apto neste endereço  registro:")){
                $this->data['imovel'] = $resul[1];
                $this->idToReference('imovel', 'Livraria\Entity\Imovel');
            }else{
                return array_merge(['Erro ao tentar incluir imovel no BD.'],$resul);
            }
        }else{
            $this->data['imovel'] = $serviceImovel->getEntity();
        }
        return TRUE;
    }

    public function checkDocLod() {
        /* @var $locador  \Livraria\Entity\Locador             */
        if(!is_object($this->data['locador'])){
            $locador = $this->em->find('\Livraria\Entity\Locador', $this->data['locador']);
        }else{
            $locador = $this->data['locador'];
        }
        if($locador->getTipo() != $this->data['tipoLoc']){
            $locador->setTipo($this->data['tipoLoc']);
        }
        if($locador->getTipo() == "fisica"   AND $locador->getCpf()  != $this->data['cpfLoc']){
            $locador->setCpf($this->data['cpfLoc'])->setCnpj('');
            $this->em->persist($locador);
            $this->em->flush($locador);
        }
        if($locador->getTipo() == "juridica" AND $locador->getCnpj() != $this->data['cnpjLoc']){
            $locador->setCpf('')->setCnpj($this->data['cnpjLoc']);
            $this->em->persist($locador);
            $this->em->flush($locador);
        }
        $this->data['locador'] = $locador;
    }

    /**
     * Faz um referencia ou tenta incluir o locador no BD
     * Caso não consiga retorna os erros
     * @return boolean | array
     */
    public function setLocador(){
        if(is_object($this->data['locador'])){
            if ($this->data['locador'] instanceof \Livraria\Entity\Locador) {
                $this->checkDocLod();
                return TRUE;
            }
        }
        if(empty($this->data['locador'])){
            /* @var $serviceLocador \Livraria\Service\Locador */
            $serviceLocador = new Locador($this->em);
            $data['id'] = '';
            $data['administradora'] = $this->data['administradora'];
            $data['nome'] = $this->data['locadorNome'];
            $data['tipo'] = $this->data['tipoLoc'];
            $data['cpf'] = $this->data['cpfLoc'];
            $data['cnpj'] = $this->data['cnpjLoc'];
            $data['status'] = 'A';
            $data['endereco'] = '1';
            $resul = $serviceLocador->setFlush($this->getFlush())->insert($data);
            if($resul === TRUE){
                $this->data['locador'] = $serviceLocador->getEntity();
            }else{
                if(substr($resul[0], 0, 15) == 'Já existe esse'){
                    $this->data['locador'] = $resul[1];
                    $this->checkDocLod();
                }else{
                    return array_merge(['Erro ao tentar incluir Locador no BD.'],$resul);
                }
            }
        }else{
            $this->checkDocLod();
        }
        return TRUE;
    }

    public function checkDocLoc() {
        /* @var $locatario  \Livraria\Entity\Locatario             */
        if(!is_object($this->data['locatario'])){
            $locatario = $this->em->find('\Livraria\Entity\Locatario', $this->data['locatario']);
        }else{
            $locatario = $this->data['locatario'];
        }
        if($locatario->getTipo() != $this->data['tipo']){
            $locatario->setTipo($this->data['tipo']);
        }
        if($locatario->getTipo() == "fisica"   AND $locatario->getCpf()  != $this->data['cpf']){
            $locatario->setCpf($this->data['cpf'])->setCnpj('');
            $this->em->persist($locatario);
            $this->em->flush($locatario);
        }
        if($locatario->getTipo() == "juridica" AND $locatario->getCnpj() != $this->data['cnpj']){
            $locatario->setCpf('')->setCnpj($this->data['cnpj']);
            $this->em->persist($locatario);
            $this->em->flush($locatario);
        }
        $this->data['locatario'] = $locatario;
    }

    /**
     * Faz um referencia ou tenta incluir o locatario no BD
     * Caso não consiga retorna os erros
     * @return boolean | array
     */
    public function setLocatario(){
        if(is_object($this->data['locatario'])){
            if ($this->data['locatario'] instanceof \Livraria\Entity\Locatario) {
                $this->checkDocLoc();
                return TRUE;
            }
        }
        if(!empty($this->data['locatario'])){
            $this->checkDocLoc();
            return TRUE;
        }
        $serviceLocatario = new Locatario($this->em);
        $data['id'] = '';
        $data['nome'] = $this->data['locatarioNome'];
        $data['tipo'] = $this->data['tipo'];
        $data['cpf'] = $this->data['cpf'];
        $data['cnpj'] = $this->data['cnpj'];
        $data['status'] = 'A';
        $resul = $serviceLocatario->setFlush($this->getFlush())->insert($data);
        if($resul === TRUE){
            $this->data['locatario'] = $serviceLocatario->getEntity();
        }else{
            if(substr($resul[0], 0, 13) == 'Já existe um'){
                $this->data['locatario'] = $resul[1];
                $this->checkDocLoc();
            }else{
                return array_merge(['Erro ao tentar incluir Locatario no BD.'],$resul);
            }
        }
        return TRUE;
    }

    /**
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array
     */
    public function  update(array $data, $onlyCalculo=false) {
        $this->data        = $data;
        if ($onlyCalculo) {
            $this->setFlush(FALSE);
        } else {
            $this->setFlush(TRUE);
        }

        if ($data['status'] != 'A' AND $data['status'] != 'R' AND $this->getIdentidade()->getTipo() != 'admin') {
            return ['Este orçamento não pode ser editado!', 'Pois já esta finalizado!!'];
        }

        if ($data['status'] === 'C') {
            return ['Este orçamento está cancelado e não pode ser editado!!!'];
        }

        $ret = $this->setReferences();
        if ($ret !== TRUE) {
            return $ret;
        }

        $this->calculaVigencia();

        $this->data['taxa'] = $this->em
                ->getRepository('Livraria\Entity\Taxa')
                ->findTaxaVigente(
                    $this->data['seguradora']->getId(),
                        $this->data['atividade']->getId(),
                        $this->data['inicio'],
                        str_replace(',', '.', $this->data['comissao']),
                        $this->data['validade'],
                        $this->data['tipoCobertura'],
                        $this->data['administradora']->getId()
        );

        if(!$this->data['taxa'])
            return ['Taxas para esta classe e atividade vigente nao encontrada!!!'];


        $this->idToEntity('comissaoEnt', 'Livraria\Entity\Comissao');

        $this->idToEntity('multiplosMinimos', 'Livraria\Entity\MultiplosMinimos');

        $this->CalculaPremio();

        $this->idToReference('user', 'Livraria\Entity\User');

        if($onlyCalculo){
            return TRUE;
        }

        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }

        if ($this->getIdentidade()->getIsAdmin() == '0') {
            return ['Você não tem permissão para alterar esse registro'];
        }

        $this->trocaNaoCalcula();

        if(parent::update()){
            $this->logForEdit();
            if($data['status'] == 'F' AND !empty($this->dePara)){
                $srvFechado = new Fechados($this->em);
                $srvFechado->update($this->entityReal,$this->dePara, 'orcamentos');
            }
        }

        $this->trocaNaoCalcula(true);

        return TRUE;
    }

    public function clean() {
        $this->data       = null;
        $this->dePara     = '';
        $this->entityReal = null;
        $this->em->clear();
    }

    /**
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        //parent::logForEdit('orcamento');
        //serviço logorcamento
        if(empty($this->dePara))
            return ;

        $log = new LogOrcamento($this->em);
        $dataLog['orcamento']  = $this->data['id'];
        $dataLog['tabela']     = 'log_orcamento';
        $dataLog['controller'] = 'orcamentos' ;
        $dataLog['action']     = 'edit';
        $dataLog['mensagem']   = 'Alterou orçamento de numero ' . $this->data['id'] . '/' . $this->data['codano'] ;
        $dataLog['dePara']     = 'Campo;Valor antes;Valor Depois;' . $this->dePara;
        $log->insert($dataLog);
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){
        if(!$this->isValid){
            return TRUE;
        }
        if ($this->getIdentidade()->getIsAdmin() == '0') {
            return ['Você não tem permissão para incluir ou alterar registro'];
        }
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $filtro = array();
        if (empty($this->data['imovel'])) {
            return array('Um imovel deve ser selecionado!');
        }
        if (empty($this->data['cep'])) {
            return array('O CEP não pode ficar em branco!');
        }
        $inicio = $this->data['inicio'];
        $fim = $this->data['fim'];
        if (!is_object($inicio)) {
            return array('A data deve ser preenchida corretamente!');
        }
        // Lello validar pela referencia do imovel.
        if(!empty($this->data['refImovel']) AND $this->data['administradora']->getId() == 3234){
            $filtro['refImovel'] = $this->data['refImovel'];
            $filtro['administradora'] = $this->data['administradora']->getId();
        }else{
            $filtro['imovel'] = $this->data['imovel']->getId();
            $filtro['administradora'] = $this->data['administradora']->getId();
        }
        $entitys = $repository->findBy($filtro);
        $erro = array();
        /* @var $entity \Livraria\Entity\Orcamento */
        foreach ($entitys as $entity) {
            //Caso de edição pular o proprio registro.
            if(isset($this->data['id']) AND $this->data['id'] == $entity->getId()){
                continue;
            }
            // Caso o fim desse orçamento for menor ou igual ao inicio do existente!!
            if(($fim->format('Ymd') <= $entity->getInicio('obj')->format('Ymd'))){
                continue;
            }
            // Validar data do inicio deste registro para que nao conflite com algum existente!!
            if(($inicio->format('Ymd') >= $entity->getFim('obj')->format('Ymd'))){
                continue;
            }
            // Lello fazer cancelamento direto
            if($filtro['administradora'] == 3234){
                if($entity->getStatus() == "A" OR $entity->getStatus() == "R") {
                    $this->delete($entity->getId(), ['motivoNaoFechou' => 'Cancelado por conflitar com registro da importação.']);
                    continue;
                }
            }
            //validação especial para mensal
            if($entity->getValidade() == "mensal"){                
                if(($fim <= $entity->getInicio('obj'))){
                    $interval = $inicio->diff($entity->getFim('obj'));
                    if($interval->format('%a') < 29){
                        $erro[] = "Alerta!" ;
                        $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                        $erro[] = "Já existe um orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
                    }
                }
                continue;
            }
            if($entity->getStatus() == "A"){
                $erro[] = "Alerta!" ;
                $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe um orçamento com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
            if($entity->getStatus() == "F"){            
                $interval = $inicio->diff($entity->getFim('obj'));
                if($interval->format('%a') <= 31){
                    continue;
                }
                $erro[] = "Alerta!" ;
                $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe um seguro fechado com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
            if($entity->getStatus() == "R"){
                $erro[] = "Alerta!" ;
                $erro[] = 'Vigencia inicio menor que vigencia final existente ' . $inicio->format('d/m/Y') . ' < ' . $entity->getFim();
                $erro[] = "Já existe um orçamento de renovação com periodo vigente conflitando ! N = " . $entity->getId() . '/' . $entity->getCodano();
            }
        }

        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }

    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Orcamento $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        // 10 referencia a outra entity
        $this->dePara .= $this->diffAfterBefore('Locador', $ent->getLocador(), $this->data['locador']);
        $this->dePara .= $this->diffAfterBefore('Locatario', $ent->getLocatario(), $this->data['locatario']);
        $this->dePara .= $this->diffAfterBefore('Imovel id', $ent->getImovel()->getId(), $this->data['imovel']->getId());
        $this->dePara .= $this->diffAfterBefore('Imovel Rua', $ent->getImovel()->getRua(), $this->data['imovel']->getRua());
        $this->dePara .= $this->diffAfterBefore('Imovel numero.', $ent->getImovel()->getNumero(), $this->data['imovel']->getNumero());
        $this->dePara .= $this->diffAfterBefore('Imovel apto', $ent->getImovel()->getApto(), $this->data['imovel']->getApto());
        $this->dePara .= $this->diffAfterBefore('Imovel bloco', $ent->getImovel()->getBloco(), $this->data['imovel']->getBloco());
        $this->dePara .= $this->diffAfterBefore('Imovel compl.', $ent->getImovel()->getCompl(), $this->data['imovel']->getCompl());
        $this->dePara .= $this->diffAfterBefore('Imovel tel', $ent->getImovel()->getTel(), $this->data['imovel']->getTel());
        $this->dePara .= $this->diffAfterBefore('Taxa', $ent->getTaxa()->getId(), $this->data['taxa']->getId());
        $this->dePara .= $this->diffAfterBefore('Atividade', $ent->getAtividade(), $this->data['atividade']);
        $this->dePara .= $this->diffAfterBefore('Seguradora', $ent->getSeguradora(), $this->data['seguradora']);
        $this->dePara .= $this->diffAfterBefore('Administradora', $ent->getAdministradora(), $this->data['administradora']);
        // 9 de valores float
        $this->dePara .= $this->diffAfterBefore('Valor do Aluguel', $ent->floatToStr('valorAluguel'), $this->strToFloat($this->data['valorAluguel']));
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio', $ent->floatToStr('incendio',4), $this->strToFloat($this->data['incendio'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura Incêndio + Conteudo', $ent->floatToStr('conteudo',4), $this->strToFloat($this->data['conteudo'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura aluguel', $ent->floatToStr('aluguel',4), $this->strToFloat($this->data['aluguel'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura eletrico', $ent->floatToStr('eletrico',4), $this->strToFloat($this->data['eletrico'],'',4));
        $this->dePara .= $this->diffAfterBefore('Cobertura vendaval', $ent->floatToStr('vendaval',4), $this->strToFloat($this->data['vendaval'],'',4));
        $this->dePara .= $this->diffAfterBefore('Premio Liquido', $ent->floatToStr('premioLiquido'), $this->strToFloat($this->data['premioLiquido']));
        $this->dePara .= $this->diffAfterBefore('Premio', $ent->floatToStr('premio'), $this->strToFloat($this->data['premio']));
        $this->dePara .= $this->diffAfterBefore('Premio Total', $ent->floatToStr('premioTotal'), $this->strToFloat($this->data['premioTotal']));
        $this->dePara .= $this->diffAfterBefore('Comissao', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        // 3 de datas
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Data Fim', $ent->getFim(), $this->data['fim']->format('d/m/Y'));
        $this->dePara .= $this->diffAfterBefore('Cancelado Em', $ent->getCanceladoEm(), $this->data['canceladoEm']->format('d/m/Y'));
        // 15 campos comuns
        $this->dePara .= $this->diffAfterBefore('Validade', $ent->getValidade(), $this->data['validade']);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Ano Referência', $ent->getCodano(), $this->data['codano']);
        $this->dePara .= $this->diffAfterBefore('locadorNome', $ent->getLocadorNome(), $this->data['locadorNome']);
        $this->dePara .= $this->diffAfterBefore('locatarioNome', $ent->getLocatarioNome(), $this->data['locatarioNome']);
        $this->dePara .= $this->diffAfterBefore('tipoCobertura', $ent->getTipoCobertura(), $this->data['tipoCobertura']);
        $this->dePara .= $this->diffAfterBefore('seguroEmNome', $ent->getSeguroEmNome(), $this->data['seguroEmNome']);
        $this->dePara .= $this->diffAfterBefore('codigoGerente', $ent->getCodigoGerente(), $this->data['codigoGerente']);
        $this->dePara .= $this->diffAfterBefore('refImovel', $ent->getRefImovel(), $this->data['refImovel']);
        $this->dePara .= $this->diffAfterBefore('formaPagto', $ent->getFormaPagto(), $this->data['formaPagto']);
        $this->dePara .= $this->diffAfterBefore('numeroParcela', $ent->getNumeroParcela(), $this->data['numeroParcela']);
        $this->dePara .= $this->diffAfterBefore('observacao', $ent->getObservacao(), $this->data['observacao']);
        if(isset($this->data['gerado']))
            $this->dePara .= $this->diffAfterBefore('gerado', $ent->getGerado(), $this->data['gerado']);
        $this->dePara .= $this->diffAfterBefore('comissao', $ent->floatToStr('comissao'), $this->strToFloat($this->data['comissao']));
        $this->dePara .= $this->diffAfterBefore('Fechado nº', $ent->getFechadoId(), $this->data['fechadoId']);
        $this->dePara .= $this->diffAfterBefore('mesNiver', $ent->getMesNiver(), $this->data['mesNiver']);
        //Juntar as alterações no imovel se houver
        $this->dePara .= $this->deParaImovel;
    }

    /**
     * Gerar o pdf do Orçamento inserido
     * @param strin $id
     * @return PDF file
     */
    public function getPdfOrcamento($id){
        //Carregar Entity Orcamento
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);

        if(!$seg){
            return ['Não foi encontrado um orçamento com esse numero!!!'];
        }
        $this->acertaNomeLocatarioEntity($seg);
        $num = 'Orçamento/' . $seg->getId() . '/' . $seg->getCodano();
        $this->pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());

        $this->conteudoDaPagina($seg);

        $this->sendPdf();
    }

    public function acertaNomeLocatarioEntity(&$ent){
        if($ent->getLocatarioNome() == $ent->getLocatario()->getNome()){
            return;
        }
        /* @var $locatario \Livraria\Entity\Locatario */
        $locatario = $this->em->getRepository("Livraria\Entity\Locatario")->findOneBy(['nome' => $ent->getLocatarioNome()]);
        if($locatario){
            if($ent->getLocatarioNome() != $locatario->getNome()){
                $this->locatarioAcertoLog[] = '<p>Não encontrou o mesmo nome ' . $ent->getLocatarioNome() . ' com seu id correto ' . $locatario->getNome() . '</p>';
                return;
            }
            if($ent->getFechadoId() != 0){
                $fechado = $this->em->find("Livraria\Entity\Fechados", $ent->getFechadoId());
                if($fechado){
                    $fechado->setLocatario($locatario);
                    $this->em->persist($fechado);
                }
            }
            $ent->setLocatario($locatario);
            $this->em->persist($ent);
            $this->em->flush();
        }else{
            $this->locatarioAcertoLog[] = '<p>Locatario não encontrado com esse nome  ' . $ent->getLocatarioNome() . '</p>';
        }
    }

    /**
     * Gerar vario pdfs do Orçamento
     * @param strin $id
     * @param objet $objPdf
     */
    public function getPdfsOrcamento($id, $objPdf=null){
        //Carregar Entity Orçamento
        $seg = $this->em
            ->getRepository($this->entity)
            ->find($id);

        if(!$seg){
            return ['Não foi encontrado um orçamento com esse numero!!!'];
        }

        if(!is_null($objPdf))
            $this->pdf = $objPdf;

        $num = 'Orçamento/' . $seg->getId() . '/' . $seg->getCodano();

        if(!is_object($this->pdf))
            $this->pdf = new ImprimirSeguro($num, $seg->getSeguradora()->getId());
        else
            $this->pdf->novaPagina($num, $seg->getSeguradora()->getId());

        $this->conteudoDaPagina($seg);

    }

    public function conteudoDaPagina($seg){
        /*  @var $seg \Livraria\Entity\Orcamento      */
        $this->pdf->setShowCusInd($seg->getAdministradora()->getShowCusInd());
        $this->pdf->setL1($seg->getRefImovel(), $seg->getInicio());
        $this->pdf->setL2($seg->getAdministradora()->getNome());
        $this->pdf->setL3($seg->getLocatario(), $seg->getLocatario()->getCpf() . $seg->getLocatario()->getCnpj());
        $this->pdf->setL4($seg->getLocador(), $seg->getLocador()->getCpf() . $seg->getLocador()->getCnpj());
        $this->pdf->setL5($seg->getImovel());
        $this->pdf->setL6($seg->getAtividade());
        $this->pdf->setL7($seg->getObservacao());
        $this->pdf->setL8($seg->floatToStr('valorAluguel'));
        $this->pdf->setL9($seg->getAdministradora()->getId(), '0');
        $this->pdf->setL10();
        switch ($seg->getTipoCobertura()) {
            case '01':
                $label = ' (Prédio)';
                $vlr[] = $seg->floatToStr('incendio');
                $vlr[] = $seg->floatToStr('cobIncendio');
                break;
            case '02':
                $label = ' (Conteúdo + prédio)';
                $vlr[] = $seg->floatToStr('conteudo');
                $vlr[] = $seg->floatToStr('cobConteudo');
                break;
            case '03':
                $label = ' (Conteúdo)';
                break;
            default:
                $label = '';
                break;
        }
        $vlr[] = $seg->floatToStr('eletrico');
        $vlr[] = $seg->floatToStr('cobEletrico');
        $vlr[] = $seg->floatToStr('aluguel');
        $vlr[] = $seg->floatToStr('cobAluguel');
        $vlr[] = $seg->floatToStr('vendaval');
        $vlr[] = $seg->floatToStr('cobVendaval');
        $vlr[] = $seg->floatToStr('respcivil');
        $vlr[] = $seg->floatToStr('cobRespcivil');
        $assist24 = null;
        if($seg->getAssist24() == 'S'){
            /* @var $parametro \Livraria\Entity\ParametroSis */
            $parametro = $this->getParametroSis('assist24_' . $seg->getOcupacao() . '_' . $seg->getValidade(), true)[0];
            if($parametro){
                $assist24 = [substr($parametro->getDescricao(), 26), number_format($parametro->getConteudo(), 2, ',', '.')];
            }            
        }
        $this->pdf->setL11($vlr, $label, $assist24);
        $tot = [
            $seg->floatToStr('premio'),
            $seg->floatToStr('premioLiquido'),
            $this->strToFloat($seg->getPremioLiquido() * $seg->getTaxaIof()),
            $seg->floatToStr('premioTotal')
        ];
        $this->pdf->setL12($tot,  $this->strToFloat($seg->getTaxaIof() * 100), $seg->getValidade());
        $par = [
            $seg->floatToStr('premioTotal')
            ,$this->strToFloat($seg->getPremioTotal() / 2)
            ,$this->strToFloat($seg->getPremioTotal() / 3)
            ,$this->strToFloat($seg->getPremioTotal() / 4)
            ,$this->strToFloat($seg->getPremioTotal() / 5)
            ,$this->strToFloat($seg->getPremioTotal() / 12)
        ];
        $this->pdf->setL13($par, ($seg->getValidade() =='mensal')?true:false, $seg->getFormaPagto(),$seg->getAdministradora()->getPropPag());
        $this->pdf->setL14($seg->getOcupacao());
        $this->pdf->setObsGeral('',($seg->getAssist24() == 'S')? TRUE : FALSE);

    }

    public function sendPdf($opc=''){
        switch ($opc) {
            case '':
                $this->pdf->Output();
                break;
            case 'D':
                $this->pdf->Output($this->pdf->getNumSeguro(),'D');
                break;
            default:
                $this->pdf->Output();
                break;
        }
    }

    public function getObjectPdf(){
        return $this->pdf;
    }

    public function verificaLocatario($param) {
        // tratar paramentros
        if(isset($param['adm'])){
            $this->data['administradora'] = $param['adm'];
        }  else {
            $this->data['administradora'] = '';
        }
        if(isset($param['ini'])){
            $this->data['inicio'] = $param['ini'];
        }
        if(isset($param['fim'])){
            $this->data['fim'] = $param['fim'];
        }
        // pegar lista de orçamento ou renovações
        /* @var $rep \Livraria\Entity\OrcamentoRepository */
        $rep = $this->em->getRepository("Livraria\Entity\Orcamento");
        $lista = $rep->getListaArray($this->data);
        $total = count($lista);
        $achou = 0;
        $ok = 0;
        $ng = 0;
        foreach ($lista as $value) {
            // Verificar se nome do locatario esta correto provisoriamente.
            if ($value['locatarioNome'] != $value['locatario']['nome']){
                echo 'Achou ', $value['locatarioNome'] , ' diferente de ', $value['locatario']['nome'], '<br>' ;
                $this->acertaNomeLocatario($value, $ng, $ok);
                $achou ++;
            }
        }
        echo '<h1>Total ', $total , '</h1>';
        echo '<h1>Achou ', $achou , '</h1>';
        echo '<h1>acertados ', $ok , '</h1>';
        echo '<h1>nao acertador ', $ng , '</h1>';
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

    public function acertaNomeLocatario(&$v, &$ng, &$ok){
        /* @var $entity \Livraria\Entity\Locatario */
        $entity = $this->getLtr()->findOneBy(['nome' => $v['locatarioNome']]);
        if($entity){
            /* @var $orcaReno \Livraria\Entity\Orcamento */
            $orcaReno = $this->em->find("Livraria\Entity\Orcamento", $v['id']);
            if($orcaReno){
                if($orcaReno->getLocatarioNome() != $entity->getNome()){
                    echo '<p>Não encontrou o mesmo nome ' . $orcaReno->getLocatarioNome() . ' com seu id correto ' . $entity->getNome() . ' id '.$orcaReno->getId().'</p>';
                    $ng ++;
                    return;
                }
                $orcaReno->setLocatario($entity);
                $this->em->persist($orcaReno);
                $this->em->flush();
                $ok++;
            }  else {
                echo '<p>Fechado não encontrado com esse id  ' . $v['id'] . '</p>';
                $ng ++;
            }
        }else {
            echo '<p>Locatario não encontrado com esse nome  ' . $v['locatarioNome'] . '</p>';
            $ng ++;
        }
    }
    
    public function acertaCondovel(\Livraria\Entity\OrcamentoRepository $rp) {

        $inicio = new \DateTime('2018-06-01');
        $fim    = new \DateTime('2018-06-31 23:59:00');
        $adm    = 2884;
        $seguros = $this->em
            ->createQueryBuilder()
            ->select('l,i,ld','lc')
            ->from('Livraria\Entity\Orcamento', 'l')
            ->join('l.imovel', 'i')
            ->join('l.locador', 'ld')
            ->join('l.locatario', 'lc')
            ->where('l.inicio BETWEEN :inicio AND :fim and l.administradora = :adm')
            ->setParameter('inicio', $inicio)
            ->setParameter('fim', $fim)
            ->setParameter('adm', $adm)
            ->getQuery()
            ->getResult()
        ;
        
        echo count($seguros), '<br>';
        
        
        /* @var $seguro \Livraria\Entity\Orcamento */
        foreach ($seguros as $seguro) {
            
            
            
            
            $this->data['ocupacao'] = $seguro->getOcupacao();
            $this->data['validade'] = $seguro->getValidade();
            
            echo '<pre> consertar' 
                    . ' id ' 
                    . $seguro->getId()
                    . ' st ' 
                    . $seguro->getStatus()
                    . ' locador ' 
                    . $seguro->getLocadorNome()
                    . ' locatario ' 
                    . $seguro->getLocatarioNome()
                    . ' total ' 
                    . $seguro->getPremio()
                    . ' bruto ' 
                    . $seguro->getPremioTotal()
                    . ' getOcupacao ' 
                    . $seguro->getOcupacao()
                    . ' getValidade ' 
                    . $seguro->getValidade()
                    .'</pre>';
            
            if($seguro->getAssist24() == 'S') {
                continue;
            }
            $seguro->setAssist24('S');
            
            $total = $seguro->getPremio();
            $total += $this->getAssist24Vlr();
            
            $totalBruto = round($total * (1 + $seguro->getTaxaIof()), 2) ;
            
            $seguro->setPremio($total)->setPremioLiquido($total)->setPremioTotal($totalBruto);
            
            $this->em->persist($seguro);
            
            echo '<pre> consertado ' 
                    . ' id ' 
                    . $seguro->getId()
                    . ' locador ' 
                    . $seguro->getLocadorNome()
                    . ' locatario ' 
                    . $seguro->getLocatarioNome()
                    . ' total ' 
                    . $seguro->getPremio()
                    . ' bruto ' 
                    . $seguro->getPremioTotal()
                    . ' 24 ' 
                    . $this->getAssist24Vlr()
                    .'</pre>';
            
        }
        
        $this->em->flush();
        
    }

}
