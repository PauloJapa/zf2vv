<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

use SisBase\Conexao\Mysql;

require '/var/www/zf2vv/module/Livraria/src/Livraria/Service/PHPExcel.php';

class RelatoriosController extends CrudController {
    
    /**
     * String com endereço de email padrão
     * @var type String
     */
    protected $mailDefault = 'incendiolocacao@vilavelha.com.br'; 
    
    public function __construct() {
        $this->entity = "Livraria\Entity\Orcamento";
        $this->form = "LivrariaAdmin\Form\Relatorio";
        $this->service = "Livraria\Service\Relatorio";
        $this->controller = "relatorios";
        $this->route = "livraria-admin";
    }
    
    public function queryAction() {
        /* @var $this->formData \LivrariaAdmin\Form\Relatorio */
        $this->formData = new $this->form($this->getEm());
        $this->formData->setQuery();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function gerarQueryAction(){
        $label['c1']  = "Vigência Inicio";       
        $label['c2']  = "Vigência Fim";        
        $label['c3']  = "Locador";             
        $label['c4']  = "Tipo";                
        $label['c5']  = "CPF";                 
        $label['c6']  = "CNPJ";                
        $label['c7']  = "Locatario";           
        $label['c8']  = "Tipo";                
        $label['c9']  = "CPF";                 
        $label['c10'] = "CNPJ";                
        $label['c11'] = "Endereço";            
        $label['c12'] = "Num";                 
        $label['c13'] = "Bairro";              
        $label['c14'] = "Cidade";              
        $label['c15'] = "Seg em Nome";         
        $label['c16'] = "Incêndio";            
        $label['c17'] = "Perda Aluguel";       
        $label['c18'] = "Eletrico";            
        $label['c19'] = "Vendaval";            
        $label['c20'] = "Valor Aluguel";       
        $label['c21'] = "Atividade";           
        $label['c22'] = "Premio liquido";       
        $label['c23'] = "Premio Total";        
        $label['c24'] = "Fechado";             
        $label['c25'] = "Segurado";            
        $label['c26'] = "Status";              
        $label['c27'] = "UE";                  
        $label['c28'] = "Ref. Imovel";         
        $label['c29'] = "Mês Niver"; 
        $label['c30'] = "Compl."; 
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->montaQuery($data);
        //Guardar dados dos registro para montar planilha excel
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->montaquery = $this->paginator;
        $sc->label      = $label;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge(['label' => $label], $this->getParamsForView()));
    }
    
    public function toExcelAction(){
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        $excel = new  \PHPExcel();
        
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->montaquery, 'label' => $sc->label, 'excel' => $excel));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function orcarenoAction() {
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $this->data = $this->getRequest()->getPost()->toArray();
        if ((isset($this->data['subOpcao']))&&($this->data['subOpcao'] == 'buscar'))  {
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            $sessionContainer->data = $this->data;
        }
        $this->formData = new \LivrariaAdmin\Form\Renovacao();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function gerarOrcarenoAction(){
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->orcareno($data);
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->dataOrcareno = $this->paginator;
        $sc->data         = $data;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));        
    }
    
    public function printPropostaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
                
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if(empty($sc->dataOrcareno))
            return;

        $admCod = $data['subOpcao'];
        $srvOrcamento = $this->getServiceLocator()->get("Livraria\Service\Orcamento");
        $srvRenovacao = $this->getServiceLocator()->get("Livraria\Service\Renovacao");
        $pdfCount = $orcaCount = $renoCount = 0;
        $pdfObj = null;
        // Gerar arquivo pdf com todas os orçamento e renovação
        foreach($sc->dataOrcareno as $arrayResul){
            if(!empty($admCod) AND $admCod != $arrayResul['administradora']['id']){
                continue;
            }
            // Verifica se o registro é renovação
            if ( isset($arrayResul['fechadoOrigemId'])){
                //Primeiro de todos pega a instacia do pdf
                if($pdfCount == 0){
                    $srvRenovacao->getPdfsRenovacao($arrayResul['id']);
                    $pdfObj = $srvRenovacao->getObjectPdf();
                }else{
                    if($renoCount == 0){
                        //Não é o primeiro de todos mas é o primeiro das renovações passa a instacia do pdf
                        $srvRenovacao->getPdfsRenovacao($arrayResul['id'], $pdfObj);                        
                    }else{
                        // Adiciona pagina no pdf
                        $srvRenovacao->getPdfsRenovacao($arrayResul['id']);
                    }
                }
                $renoCount++;
            }else{
                //Primeiro de todos pega a instacia do pdf
                if($pdfCount == 0){
                    $srvOrcamento->getPdfsOrcamento($arrayResul['id']);
                    $pdfObj = $srvOrcamento->getObjectPdf();
                }else{
                    if($orcaCount == 0){
                        //Não é o primeiro de todos mas é o primeiro dos orçamentos passa a instacia do pdf
                        $srvOrcamento->getPdfsOrcamento($arrayResul['id'], $pdfObj);                        
                    }else{
                        // Adiciona pagina no pdf
                        $srvOrcamento->getPdfsOrcamento($arrayResul['id']);
                    }
                }
                $orcaCount++;
            }
            $pdfCount++;
        }
        // Mandar arquivo para usuario fazer download
        $pdfObj->Output('Orcamento_Renovacao_' . $sc->data['inicio'] . '_' .  $sc->data['fim'] . '.pdf','D');
    }
    
    /**
     * Enviar email somente de renovações pendentes para administradoras verificarem
     */
    public function sendEmailRenovacaoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar servico que gerou os registro 
        $service = new $this->service($this->getEm());
        //Passa o localizador de serviço para pegar o servico de email e fazer o envio de email
        $resul = $service->sendEmailOnlyRenovacao($this->getServiceLocator(),$data['subOpcao']);
        if($resul){
            echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
        }else{
            echo '<h2>Erro !! Feche esta janela e tente novamente !!</h2>';
        }          
    }
    
    public function sendEmailAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
                
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if(empty($sc->dataOrcareno))
            return;

        $sm = $this->getServiceLocator()->get('Livraria\Service\Email');
        $formaPagto = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        $admCodFiltro = $data['subOpcao'];
        $admCod = $seq =  $totRenov =  $totOrcam = 0;
        $admNome  = '';
        $admEmai  = '';
        $registros = [];
        // Enviar email com a Relação de todas os orçamento e renovação não fechados
        foreach($sc->dataOrcareno as $arrayResul){
            if(!empty($admCodFiltro) AND $admCodFiltro != $arrayResul['administradora']['id']){
                continue;
            }
            if($arrayResul['administradora']['email'] == 'NAO'){
                $arrayResul['administradora']['email'] = $this->mailDefault; 
            }
            if($admCod != $arrayResul['administradora']['id']){
                if($admEmai != 'NAO' AND $admCod != 0){
                    $this->sendEmailForOrcaReno($sm, $admNome, $admEmai, $registros);
                }
                $admCod    = $arrayResul['administradora']['id'];
                $admNome   = $arrayResul['administradora']['nome'];
                $admEmai   = $arrayResul['administradora']['email'];
                $registros = [];
                $seq = 0 ;
            }
            $registros[$seq][] = $arrayResul['refImovel'];
            $registros[$seq][] = $arrayResul['inicio']->format('d/m/Y');
            $registros[$seq][] = $arrayResul['locadorNome'];
            $registros[$seq][] = $arrayResul['locatarioNome'];
            $end = $arrayResul['imovel']['rua'] . ', ' . $arrayResul['imovel']['numero'];
            $end .= (!empty($arrayResul['imovel']['bloco']))? ' BL ' . $arrayResul['imovel']['bloco'] :'';
            $end .= (!empty($arrayResul['imovel']['apto']))? ' AP ' . $arrayResul['imovel']['apto'] :'';
            $end .= (!empty($arrayResul['imovel']['enderecos']['compl']))? ' - ' . $arrayResul['imovel']['enderecos']['compl'] :'';
            $registros[$seq][] = $end;
            //$registros[$seq][] = isset($formaPagto[$arrayResul['formaPagto']]) ? $formaPagto[$arrayResul['formaPagto']]  : $arrayResul['formaPagto'];
            //$registros[$seq][] = number_format($arrayResul['premioTotal'], 2, ',', '.');
            $registros[$seq][] = ($arrayResul['orcaReno'] == 'reno') ? 'Renovação' : 'Orçamento';
            $seq++;
        }
        if($admEmai != 'NAO' AND $admCod != 0){
            $this->sendEmailForOrcaReno($sm, $admNome, $admEmai, $registros);
        }
        echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
    }
    
    /**
     * Faz o envio da listagem de seguros a renovar PS deveria estar no serviço porem não foi colocado
     * @param type $sm
     * @param type $admNome
     * @param type $admEmai
     * @param type $registros
     */
    public function sendEmailForOrcaReno(&$sm, &$admNome, &$admEmai, &$registros) {
        $sm->enviaEmail(['nome' => $admNome, 'emailNome' => $admNom,
                             'email' => $admEmai, 
                             'subject' => $admNome . ' - Seguros não fechados',
                             'data' => $registros]);
    }
    
    /**
     * Tela com filtros para gerar Reltorio exibir em tela e dowload para excel
     * @return \Zend\View\Model\ViewModel
     */
    public function custoRenovacaoAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Renovacao();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function gerarCustoRenovacaoAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->getCustoRenovacao($data);
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->dataOrcareno = $this->paginator;
        $sc->data         = $data;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));  
    }
    
    public function toExcel2Action(){
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->dataOrcareno));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    /**
     * Tela de filtro para gerar Relatorio do Mapa da renovação
     * @return \Zend\View\Model\ViewModel
     */    
    public function mapaRenovacaoAction(){
        $mypdo = new Mysql();
        $data = $mypdo->q('Select * from parametro_sis')->fetch();
        //var_dump($data);
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Relatorio($this->getEm());
        $this->formData->setMapaRenovacao();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());        
    }
    
    /**
     * Tela que Lista os seguros a serem renovados com base nos filtros do usurio
     * @return \Zend\View\Model\ViewModel
     */
    public function listarMapaRenovacaoAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->mapaRenovacao($data);
        $data['inicio'] = $service->getFiltroTratado('inicio');
        $data['fim']    = $service->getFiltroTratado('fim');
        $formaPagto = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->mapaRenovacao = $this->paginator;
        $sc->data          = $data;
        $sc->formaPagto    = $formaPagto;
        $data['upAluguel'] = empty($data['upAluguel']) ? 1 : 1 + ($service->strToFloat($data['upAluguel'], 'float') / 100);
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data, 'formaPagto' => $formaPagto]));  
    }
    
    public function gerarMapaAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $service = new $this->service($this->getEm());
        $service->gerarMapa($sc, $data['id']);
        $this->paginator = $sc->mapaRenovacao;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $sc->data]));  
    }
    
    /**
     * Enviar email das renovações a fazer para administradoras verificarem
     */
    public function sendEmailMapaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar servico que gerou os registro 
        $service = new $this->service($this->getEm());
        //Passa o localizador de serviço para pegar o servico de email e fazer o envio de email
        $resul = $service->sendEmailMapaRenovacao($this->getServiceLocator(),$data['id']);
        if($resul){
            echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
        }else{
            echo '<h2>Erro !! Feche esta janela e tente novamente !!</h2>';
        }          
    }


    /**
     * Tela inicial para gerar Relatórios de imoveis Desocupados
     * @return \Zend\View\Model\ViewModel
     */
    public function imoveisDesocupadosAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Relatorio($this->getEm());
        $this->formData->setImovelDesocupado();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());         
    }

    /**
     * Listar imoveis desocupados com botao para enviar email ou gerar excel
     * @return \Zend\View\Model\ViewModel
     */
    public function listarImoveisDesocupadosAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->listaImoDesoc($data);
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));         
    }
    
    /**
     * Enviar email dos imoveis desocupados para administradoras verificarem
     */
    public function sendEmailImoDesoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar servico que gerou os registro 
        $service = new $this->service($this->getEm());
        //Passa o localizador de serviço para pegar o servico de email e fazer o envio de email
        $resul = $service->sendEmailImoveisDesocupados($this->getServiceLocator(),$data['id']);
        if($resul){
            echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
        }else{
            echo '<h2>Erro !! Feche esta janela e tente novamente !!</h2>';
        }        
    }
    
    /**
     * Enviar excel com listagem dos imoveis desocupados para usuario fazer download.
     * @return \Zend\View\Model\ViewModel
     */
    public function toExcelImoDesoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->ImoveisDesocu,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;        
    }
    
    /**
     * Tela inicio com filtros para usuario montar sue pesquisa
     * @return \Zend\View\Model\ViewModel
     */
    public function fechamentoSeguroAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Relatorio($this->getEm());
        $this->formData->setSeguroFechados();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());         
    }
    
    /**
     * Gerar lista de seguros para exibiçao e envio de email
     * @return \Zend\View\Model\ViewModel
     */
    public function listarFechamentoSeguroAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->listaFechaSeguro($data);
        $data['fim'] = $service->getFiltroTratado('fim')->format('d/m/Y');
        $formaPagto = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data, 'formaPagto' => $formaPagto]));   
    }
    
    /**
     * Faz o envio de email para administradoras com seguro fechados
     */
    public function sendSegFechAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar servico que gerou os registro 
        $service = new $this->service($this->getEm());
        //Passa o localizador de serviço para pegar o servico de email e fazer o envio de email
        $resul = $service->sendEmailSegurosFechado($this->getServiceLocator(),$data['id']);
        if($resul){
            echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
        }else{
            echo '<h2>Erro !! Feche esta janela e tente novamente !!</h2>';
        }          
    }
    
    /**
     * Enviar excel com listagem dos seguros fechados para usuario fazer download.
     * @return \Zend\View\Model\ViewModel
     */
    public function toExcelSegFechAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $formaPagto = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->fechaSeguro,'admFiltro' => $data['id'], 'formaPagto' => $formaPagto));
        $viewModel->setTerminal(true);
        return $viewModel;        
    }
    
    public function comissaoSeguroAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Relatorio($this->getEm());
        $this->formData->setComissaoSeguro();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());         
    }
    
    public function listarComissaoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->gerarComissao($data);
        $data['inicio'] = $service->getFiltroTratado('inicio')->format('d/m/Y');
        $data['fim'] = $service->getFiltroTratado('fim')->format('d/m/Y');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));         
    }
    
    /**
     * Enviar excel com listagem dos seguros fechados separados por comissao para usuario fazer download.
     * @return \Zend\View\Model\ViewModel
     */
    public function toExcelComissaoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->comissao,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;        
    }
    
    public function buscarRelatorioAction(){
        $data = $this->filtrosDaPaginacao();
        //usuario admin pode ver tudo os outros são filtrados
        $user = $this->getIdentidade();
        if($user->getTipo() != 'admin'){
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $sessionContainer->administradora = $user->getAdministradora();
                //$this->verificaUserAction(FALSE);
            }
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['administradoraDesc'] = $sessionContainer->administradora['nome'];
        }
        /* @var $this->formData \LivrariaAdmin\Form\Relatorio */
        $this->formData = new $this->form($this->getEm());
        $this->formData->setMapaRenovacao();
        $this->formData->setData((is_null($data)) ? [] : $data);
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());         
    }
    
    public function listarRelatorioAction(){
        $data = $this->filtrosDaPaginacao();
        /* @var $svr \Livraria\Service\Relatorio */
        $srv = new $this->service($this->getEm());
        $this->paginator = $srv->getRelatorio($data);
        /* @var $param \Livraria\Entity\ParametroSisRepository */
        $param = $this->getEm()->getRepository('Livraria\Entity\ParametroSis');
        $formaPagto = $param->fetchPairs('formaPagto');
        $comissaoAlias = $param->fetchPairs('comissaoApelido');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data, 'formaPagto' => $formaPagto, 'comissaoAp' => $comissaoAlias]));         
    }
    
}
