<?php

namespace Livraria;

use Zend\Mvc\ModuleRouteListener,
    Zend\Mvc\MvcEvent,
    Zend\ModuleManager\ModuleManager;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;
use Livraria\Model\CategoriaTable;
use Livraria\Service\Categoria as CategoriaService;
use Livraria\Service\Bairro as BairroService;
use Livraria\Service\Cidade as CidadeService;
use Livraria\Service\Estado as EstadoService;
use Livraria\Service\Pais as PaisService;
use Livraria\Service\Livro as LivroService;
use Livraria\Service\Endereco as EnderecoService;
use Livraria\Service\Administradora as AdministradoraService;
use Livraria\Service\Classe as ClasseService;
use Livraria\Service\Seguradora as SeguradoraService;
use Livraria\Service\User as UserService;
use Livraria\Service\TaxaAjuste as TaxaAjusteService;
use Livraria\Service\Taxa as TaxaService;
use Livraria\Service\ClasseAtividade as ClasseAtividadeService;
use Livraria\Service\Atividade as AtividadeService;
use Livraria\Service\Comissao as ComissaoService;
use Livraria\Service\Log as LogService;
use Livraria\Service\Locatario as LocatarioService;
use Livraria\Service\Locador as LocadorService;
use Livraria\Service\Imovel as ImovelService;
use Livraria\Service\Orcamento as OrcamentoService;
use Livraria\Service\Renovacao as RenovacaoService;
use Livraria\Service\Fechados as FechadosService;
use Livraria\Service\MultiplosMinimos as MultiplosMinimosService;
use Livraria\Service\ParametroSis as ParametroSisService;
use LivrariaAdmin\Form\Livro as LivroFrm;
use LivrariaAdmin\Form\Endereco as EnderecoFrm;
use LivrariaAdmin\Form\Administradora as AdministradoraFrm;
use LivrariaAdmin\Form\Classe as ClasseFrm;
use LivrariaAdmin\Form\Seguradora as SeguradoraFrm;
use LivrariaAdmin\Form\User as UserFrm;
use LivrariaAdmin\Form\Taxa as TaxaFrm;
use LivrariaAdmin\Form\ClasseAtividade as ClasseAtividadeFrm;
use LivrariaAdmin\Form\Comissao as ComissaoFrm;
use Livraria\Auth\Adapter as AuthAdapter;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Module {

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/' . __NAMESPACE__ . "Admin",
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap($e) {
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
                    $controller = $e->getTarget();
                    $controllerClass = get_class($controller);
                    $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
                    $config = $e->getApplication()->getServiceManager()->get('config');
                    if (isset($config['module_layouts'][$moduleNamespace])) {
                        $controller->layout($config['module_layouts'][$moduleNamespace]);
                    }
                }, 98);
    }

    public function init(ModuleManager $moduleManager) {

        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach("LivrariaAdmin", 'dispatch', function($e) {
                    $auth = new AuthenticationService;
                    $auth->setStorage(new SessionStorage("LivrariaAdmin"));

                    $controller = $e->getTarget();
                    $matchedRoute = $controller->getEvent()->getRouteMatch()->getMatchedRouteName();

                    if (!$auth->hasIdentity() and ($matchedRoute == "livraria-admin" or $matchedRoute == "livraria-admin-interna")) {
                        return $controller->redirect()->toRoute('livraria-admin-auth');
                    }
                }, 99);
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Livraria\Model\CategoriaService' => function($service) {
                    $dbAdapter = $service->get('Zend\Db\Adapter\Adapter');
                    $categoriaTable = new CategoriaTable($dbAdapter);
                    $categoriaService = new Model\CategoriaService($categoriaTable);
                    return $categoriaService;
                },
                'Livraria\Service\Categoria' => function($service) {
                    return new CategoriaService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Bairro' => function($service) {
                    return new BairroService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Cidade' => function($service) {
                    return new CidadeService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Estado' => function($service) {
                    return new EstadoService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Pais' => function($service) {
                    return new PaisService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Livro' => function($service) {
                    return new LivroService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Endereco' => function($service) {
                    return new EnderecoService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Administradora' => function($service) {
                    return new AdministradoraService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Classe' => function($service) {
                    return new ClasseService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Seguradora' => function($service) {
                    return new SeguradoraService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\User' => function($service) {
                    return new UserService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Taxa' => function($service) {
                    return new TaxaService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\TaxaAjuste' => function($service) {
                    return new TaxaAjusteService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\ClasseAtividade' => function($service) {
                    return new ClasseAtividadeService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Atividade' => function($service) {
                    return new AtividadeService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Comissao' => function($service) {
                    return new ComissaoService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Log' => function($service) {
                    return new LogService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Locatario' => function($service) {
                    return new LocatarioService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Locador' => function($service) {
                    return new LocadorService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Imovel' => function($service) {
                    return new ImovelService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Orcamento' => function($service) {
                    return new OrcamentoService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Renovacao' => function($service) {
                    return new RenovacaoService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\Fechados' => function($service) {
                    return new FechadosService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\MultiplosMinimos' => function($service) {
                    return new MultiplosMinimosService($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Service\ParametroSis' => function($service) {
                    return new ParametroSisService($service->get('Doctrine\ORM\EntityManager'));
                },
                'LivrariaAdmin\Form\Livro' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    $categorias = $em->getRepository('Livraria\Entity\Categoria')->fetchPairs();
                    return new LivroFrm(null, $categorias);
                },
                'LivrariaAdmin\Form\Endereco' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new EnderecoFrm(null, $em);
                },
                'LivrariaAdmin\Form\Administradora' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new AdministradoraFrm(null, $em);
                },
                'LivrariaAdmin\Form\Classe' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new ClasseFrm(null, $em);
                },
                'LivrariaAdmin\Form\Seguradora' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new SeguradoraFrm(null, $em);
                },
                'LivrariaAdmin\Form\User' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new UserFrm(null, $em);
                },
                'LivrariaAdmin\Form\Taxa' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new TaxaFrm(null, $em);
                },
                'LivrariaAdmin\Form\ClasseAtividade' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new ClasseAtividadeFrm(null, $em);
                },
                'LivrariaAdmin\Form\Comissao' => function($service) {
                    $em = $service->get('Doctrine\ORM\EntityManager');
                    return new ComissaoFrm(null, $em);
                },
                'Livraria\Auth\Adapter' => function($service) {
                    return new AuthAdapter($service->get('Doctrine\ORM\EntityManager'));
                },
                'Livraria\Mail\Transport' => function($service) {
                    $config = $service->get('Config');
                    $transport = new SmtpTransport;
                    $options = new SmtpOptions($config['mail']);
                    $transport->setOptions($options);
                    return $transport;
                },
                'Livraria\Service\Email' => function($service) {
                    return new Service\Email($service->get('Doctrine\ORM\EntityManager'),
                                             $service->get('Livraria\Mail\Transport'),
                                             $service->get('View'));
                },
            ),
        );
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'UserIdentity' => '\Livraria\View\Helper\UserIdentity',
                'FormDefault'  => '\Livraria\View\Helper\FormDefault',
                'ViewIndex'    => '\Livraria\View\Helper\ViewIndex',
            )
        );
    }

}
