<?php
return array(
    // All navigation-related configuration is collected in the 'navigation' key
    'navigation' => array(
        // The DefaultNavigationFactory we configured in (1) uses 'default' as the sitemap key
        'default' => array(
            // And finally, here is where we define our page hierarchy
            'home' => array(
                'label' => 'Home',
                'route' => 'livraria-home',
            ),
            'Login' => array(
                'label' => 'Login',
                'route' => 'livraria-admin-auth',
            ),
        ),
        'admin' => array(
            // And finally, here is where we define our page hierarchy
            'home' => array(
                'label' => 'Home',
                'route' => 'livraria-home',
            ),
            'cadastros' => array(
                'label' => 'Cadastro',
                'route' => 'livraria-admin',
                'controller' => '',
                'pages' => array(
                    'administradora' => array(
                        'label' => 'Administradoras',
                        'route' => 'livraria-admin',
                        'controller' => 'administradoras',
                        'pages' => array(
                            'home' => array(
                                'label' => 'Menu1',
                                'route' => 'livraria-admin',
                                'controller' => '1',
                            ),
                            'home2' => array(
                                'label' => 'Menu22',
                                'route' => 'livraria-admin',
                                'controller' => '2',
                            ),
                            'home3' => array(
                                'label' => 'Menu333',
                                'route' => 'livraria-admin',
                                'controller' => '3',
                            ),
                            'home4' => array(
                                'label' => 'Menu4444',
                                'route' => 'livraria-admin',
                                'controller' => '4',
                            ),
                        ),
                    ),
                    'users' => array(
                        'label' => 'Users',
                        'route' => 'livraria-admin',
                        'controller' => 'users',
                    ),
                    'endereco' => array(
                        'label' => 'Endereço',
                        'route' => 'livraria-admin',
                        'controller' => 'enderecos',
                    ),
                    'bairro' => array(
                        'label' => 'Bairros',
                        'route' => 'livraria-admin',
                        'controller' => 'bairros',
                    ),
                    'cidade' => array(
                        'label' => 'Cidade',
                        'route' => 'livraria-admin',
                        'controller' => 'cidades',
                    ),
                    'estado' => array(
                        'label' => 'Estado',
                        'route' => 'livraria-admin',
                        'controller' => 'estados',
                    ),
                    'pais' => array(
                        'label' => 'Pais',
                        'route' => 'livraria-admin',
                        'controller' => 'paises',
                    ),
                    'categoria' => array(
                        'label' => 'Categoria',
                        'route' => 'livraria-admin',
                        'controller' => 'categorias',
                    ),
                    'livros' => array(
                        'label' => 'Livro',
                        'route' => 'livraria-admin',
                        'controller' => 'livros',
                    ),
                ),                
                
            ),
            'contratos' => array(
                'label' => 'Contratos',
                'route' => 'livraria-admin',
                'controller' => 'contratos',
            ),
            'relatorios' => array(
                'label' => 'Relatórios',
                'route' => 'livraria-admin',
                'controller' => 'relatorios',
            ),
            'auditoria' => array(
                'label' => 'Auditoria',
                'route' => 'livraria-admin',
                'controller' => 'auditorias',
            ),
            'exportar' => array(
                'label' => 'Exportar',
                'route' => 'livraria-admin',
                'controller' => 'exportar',
            ),
            'logout' => array(
                'label' => 'Logout',
                'route' => 'livraria-admin-logout',
            ),
        ),
        'user' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'livraria-home',
            ),
            'livros' => array(
                'label' => 'Livros',
                'route' => 'livraria-admin',
                'controller' => 'livros',
            ),
            'logout' => array(
                'label' => 'Logout',
                'route' => 'livraria-admin-logout',
            ),
        ),
    ),
);
