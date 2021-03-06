<?php

namespace Livraria;

return array(
    'router' => array(
        'routes' => array(
            'livraria-home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'livraria-admin/auth',
                        'action' => 'index',
                    ),
                ),
            ),
            'livraria-admin-interna' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/admin/[:controller[/:action]][/:id]',
                    'constraints' => array(
                        'id'=> '[0-9]+'
                    )
                ),
            ),
            'livraria-admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/admin/[:controller[/:action][/page/:page]]',
                    'defaults' => array(
                        'action' => 'index',
                        'page' => 1
                    ),
                ),
            ),
            'livraria-admin-auth' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/auth',
                    'defaults' => array(
                        'action' => 'index',
                        'controller'=>'livraria-admin/auth'
                    ),
                ),
            ),
            'livraria-admin-logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/auth/logout',
                    'defaults' => array(
                        'action' => 'logout',
                        'controller'=>'livraria-admin/auth'
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Livraria\Controller\Index' => 'Livraria\Controller\IndexController',
            'categorias' => 'LivrariaAdmin\Controller\CategoriasController',
            'bairros' => 'LivrariaAdmin\Controller\BairrosController',
            'cidades' => 'LivrariaAdmin\Controller\CidadesController',
            'estados' => 'LivrariaAdmin\Controller\EstadosController',
            'paises' => 'LivrariaAdmin\Controller\PaisesController',
            'enderecos' => 'LivrariaAdmin\Controller\EnderecosController',
            'administradoras' => 'LivrariaAdmin\Controller\AdministradorasController',
            'classes' => 'LivrariaAdmin\Controller\ClassesController',
            'seguradoras' => 'LivrariaAdmin\Controller\SeguradorasController',
            'livros' => 'LivrariaAdmin\Controller\LivrosController',
            'users' => 'LivrariaAdmin\Controller\UsersController',
            'taxas' => 'LivrariaAdmin\Controller\TaxasController',
            'taxaAjustes' => 'LivrariaAdmin\Controller\TaxaAjustesController',
            'atividades' => 'LivrariaAdmin\Controller\AtividadesController',
            'classeAtividades' => 'LivrariaAdmin\Controller\ClasseAtividadesController',
            'comissaos' => 'LivrariaAdmin\Controller\ComissaosController',
            'logs' => 'LivrariaAdmin\Controller\LogsController',
            'locatarios' => 'LivrariaAdmin\Controller\LocatariosController',
            'imovels' => 'LivrariaAdmin\Controller\ImovelsController',
            'locadors' => 'LivrariaAdmin\Controller\LocadorsController',
            'orcamentos' => 'LivrariaAdmin\Controller\OrcamentosController',
            'fechados' => 'LivrariaAdmin\Controller\FechadosController',
            'renovacaos' => 'LivrariaAdmin\Controller\RenovacaosController',
            'index' => 'LivrariaAdmin\Controller\IndexController',
            'multiplosMinimos' => 'LivrariaAdmin\Controller\MultiplosMinimosController',
            'parametroSis' => 'LivrariaAdmin\Controller\ParametroSisController',
            'relatorios' => 'LivrariaAdmin\Controller\RelatoriosController',
            'exportar' => 'LivrariaAdmin\Controller\ExportarController',
            'importar' => 'LivrariaAdmin\Controller\ImportarController',
            'pendentes' => 'LivrariaAdmin\Controller\PendentesController',
            'livraria-admin/auth' => 'LivrariaAdmin\Controller\AuthController',
        ),
    ),
    
    'module_layouts' => array(
      'Livraria' => 'layout/layout',
      'LivrariaAdmin' => 'layout/layout-admin'
    ),
    
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'livraria/index/index' => __DIR__ . '/../view/livraria/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'NavAdmin'   => 'Livraria\Navigation\Service\AdminNavigationFactory',
            'NavNoAdmin'   => 'Livraria\Navigation\Service\NoAdminNavigationFactory',
            'NavUser'    => 'Livraria\Navigation\Service\UserNavigationFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ),
            ),
        ),
    ),
);