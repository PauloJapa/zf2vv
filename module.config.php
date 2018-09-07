<?php

namespace Application;

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


$child_routes = array(
    'paginator' => array(
        'type' => 'Segment',
        'options' => array(
            'route' => '/[:controller[/:action[/page/:page]]]',
            'constraints' => array(
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                'page'       => '\d+'
            ),
            'defaults' => array(
            )
        )
    ),
    'paginatorAjax' => array(
        'type' => 'Segment',
        'options' => array(
            'route' => '/[:controller[/:action[/:ajax[/page/:page]]]]',
            'constraints' => array(
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                'ajax'       => '[a-zA-Z][a-zA-Z0-9_-]*',
                'page'       => '\d+'
            ),
            'defaults' => array(
            )
        )
    ),
    'ajax' => array(
        'type' => 'Segment',
        'options' => array(
            'route' => '/[:controller[/:action[/ajax/:ajax]]]',
            'constraints' => array(
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                'ajax'       => '[a-zA-Z][a-zA-Z0-9_-]*',
            ),
            'defaults' => array(
            )
        )
    ),
    'default' => array(
        'type' => 'Segment',
        'options' => array(
            'route' => '/[:controller[/:action[/:id[/:ajax]]]]',
            'constraints' => array(
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                'id'         => '\d+'
            ),
            'defaults' => array(
            ),
        ),
    ),
);

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Auth',
                        'action' => 'index',
                    ),
                ),
            ),
            'user-register' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/register',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'register',
                    ),
                ),
            ),
            'user-activate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/register/activate[/:key]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\index',
                        'action' => 'activate',
                    ),
                ),
            ),
            'user-reset' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/reset/passwd[/:key]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Usuarios',
                        'action' => 'reset',
                    ),
                ),
            ),
            'application-auth' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/auth[/:ajax]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Auth',
                        'action' => 'index'
                    )
                )
            ),
            'application-logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/auth/logout',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Auth',
                        'action' => 'logout'
                    )
                )
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'app' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/app',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => &$child_routes,
            ),
            'end' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/end',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Endereco\Controller',
                        'controller' => 'Countrys',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => &$child_routes,
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'Navigation' => 'Application\Navigation\MyNavigationFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            
//           AQUI FICAM OS APPLICATIONS CONTROLLER
            
            'Application\Controller\Index'                => 'Application\Controller\IndexController',
            'Application\Controller\Auth'                 => 'Application\Controller\AuthController',
            'Application\Controller\Usuarios'             => 'Application\Controller\UsuariosController',
            'Application\Controller\Parametros'           => 'Application\Controller\ParametrosController',
            'Application\Controller\Messenger'            => 'Application\Controller\MessengerController',
            'Application\Controller\Users'                => 'Application\Controller\UsersController',
            'Application\Controller\Grupos'               => 'Application\Controller\GruposController',
            'Application\Controller\Contatos'             => 'Application\Controller\ContatosController',
            'Application\Controller\AppRoles'             => 'Application\Controller\AppRolesController',
            'Application\Controller\AppResources'         => 'Application\Controller\AppResourcesController',
            'Application\Controller\AppPrivileges'        => 'Application\Controller\AppPrivilegesController',
            'Application\Controller\AppMenus'             => 'Application\Controller\AppMenusController',
            'Application\Controller\Mensagems'            => 'Application\Controller\MensagemsController',
            'Application\Controller\Enviados'             => 'Application\Controller\EnviadosController',
            'Application\Controller\Testes'               => 'Application\Controller\TestesController',
            'Application\Controller\ShellController'      => 'Application\Controller\ShellController',
            'Application\Controller\AppLogs'              => 'Application\Controller\AppLogsController',
            'Application\Controller\Images'               => 'Application\Controller\ImagesController',
            'Application\Controller\TableConfigs'         => 'Application\Controller\TableConfigsController',
            'Application\Controller\TableConfigColuns'    => 'Application\Controller\TableConfigColunsController',
            'Application\Controller\TableConfigPersonals' => 'Application\Controller\TableConfigPersonalsController',
            'Application\Controller\Chamados'             => 'Application\Controller\ChamadosController',
            'Application\Controller\ChamadoRespostas'     => 'Application\Controller\ChamadoRespostasController',

            
            
//           ENDEREÇO CONTROLLER
            
            'Endereco\Controller\Countrys'                  => 'Endereco\Controller\CountrysController',
            'Endereco\Controller\Bairros'                   => 'Endereco\Controller\BairrosController',
            'Endereco\Controller\Ufs'                       => 'Endereco\Controller\UfsController',
            'Endereco\Controller\Cidades'                   => 'Endereco\Controller\CidadesController',
            'Endereco\Controller\Enderecos'                 => 'Endereco\Controller\EnderecosController',
            'Endereco\Controller\Respostas'                 => 'Endereco\Controller\RespostasController',
            'Endereco\Controller\Perguntas'                 => 'Endereco\Controller\PerguntasController',
            'Endereco\Controller\ExamesCompls'              => 'Endereco\Controller\ExamesComplsController',
            'Endereco\Controller\QuestionarioPerguntas'     => 'Endereco\Controller\QuestionarioPerguntasController',
            'Endereco\Controller\Questionarios'             => 'Endereco\Controller\QuestionariosController',
            'Endereco\Controller\Cats'                      => 'Endereco\Controller\CatsController',
            'Endereco\Controller\Consultas'                 => 'Endereco\Controller\ConsultasController',
            'Endereco\Controller\Completes'                 => 'Endereco\Controller\CompletesController',
            
        ),
    ),
    'module_layouts' => array(
        'no' => 'layout/layout',
        'ok' => 'layout/layout-ajax'
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                //CRON RESULTS SCRAPER
                'console_setOffline' => array(
                    'type' => 'simple', // <- simple route is created by default, we can skip that
                    'options' => array(
                        'route' => 'setOffline',
                        'defaults' => array(
                            'controller' => 'Application\Controller\ShellController',
                            'action' => 'setoffline'
                        )
                    )
                ),
                'console_setGenerate' => array(
                    'type' => 'simple', // <- simple route is created by default, we can skip that
                    'options' => array(
                        'route' => 'setGenerate',
                        'defaults' => array(
                            'controller' => 'Application\Controller\ShellController',
                            'action' => 'setGenerate',
                        )
                    )
                ),
                'console_setDebugOn' => array(
                    'type' => 'simple', // <- simple route is created by default, we can skip that
                    'options' => array(
                        'route' => 'setDebugOn',
                        'defaults' => array(
                            'controller' => 'Application\Controller\ShellController',
                            'action' => 'setDebugOn',
                        )
                    )
                ),
                'console_setDebugOff' => array(
                    'type' => 'simple', // <- simple route is created by default, we can skip that
                    'options' => array(
                        'route' => 'setDebugOff',
                        'defaults' => array(
                            'controller' => 'Application\Controller\ShellController',
                            'action' => 'setDebugOff',
                        )
                    )
                ),
            ),
        ),
    ),
    // Doctrine configuration
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'Endereco_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Endereco/Entity',
                ),
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                    'Endereco\Entity' => 'Endereco_driver',
                )
            ),
        ),
        'data-fixture' => array(
            __NAMESPACE__ . '_fixture' => __DIR__ . '/../src/' . __NAMESPACE__ . '/Fixture',
            'Endereco_fixture'         => __DIR__ . '/../src/Endereco/Fixture',
        ),
    ),
);
