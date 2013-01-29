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
                'controller' => 'seguradoras',
                'pages' => array(
                    'seguradoras' => array(
                        'label' => 'Seguradora',
                        'route' => 'livraria-admin',
                        'controller' => 'seguradoras',
                        'pages' => array(
                            'seguradora1' => array(
                                'label' => 'Nova',
                                'route' => 'livraria-admin',
                                'controller' => 'seguradoras',
                                'action' => 'new',
                            ),
                            'seguradora2' => array(
                                'label' => 'Alterar',
                                'route' => 'livraria-admin',
                                'controller' => 'seguradoras',
                                'action' => 'index',
                            ),
                            'seguradora3' => array(
                                'label' => 'Listar_Taxas',
                                'route' => 'livraria-admin',
                                'controller' => 'taxas',
                                'action' => 'index',
                            ),
                            'seguradora4' => array(
                                'label' => 'Listar_Classes',
                                'route' => 'livraria-admin',
                                'controller' => 'classes',
                                'action' => 'index',
                            ),
                            'seguradora5' => array(
                                'label' => 'Nova_Taxa',
                                'route' => 'livraria-admin',
                                'controller' => 'taxas',
                                'action' => 'new',
                            ),
                        ),
                    ),
                    'administradora' => array(
                        'label' => 'Administradoras',
                        'route' => 'livraria-admin',
                        'controller' => 'administradoras',
                        'pages' => array(
                            'administradora1' => array(
                                'label' => 'Nova_Administradora',
                                'route' => 'livraria-admin',
                                'controller' => 'administradoras',
                                'action' => 'new',
                            ),
                            'administradora2' => array(
                                'label' => 'Lista_Administradora',
                                'route' => 'livraria-admin',
                                'controller' => 'administradoras',
                                'action' => 'index',
                            ),
                            'administradora3' => array(
                                'label' => 'Pesquisar',
                                'route' => 'livraria-admin',
                                'controller' => 'administradoras',
                                'action' => 'busca',
                            ),
                        ),
                    ),
                    'atividades' => array(
                        'label' => 'Atividades',
                        'route' => 'livraria-admin',
                        'controller' => 'atividades',
                        'pages' => array(
                            'atividades1' => array(
                                'label' => 'Nova_Atividade',
                                'route' => 'livraria-admin',
                                'controller' => 'atividades',
                                'action' => 'new',
                            ),
                            'atividades2' => array(
                                'label' => 'Lista_Atividade',
                                'route' => 'livraria-admin',
                                'controller' => 'atividades',
                                'action' => 'index',
                            ),
                            'atividades3' => array(
                                'label' => 'Pesquisar',
                                'route' => 'livraria-admin',
                                'controller' => 'atividades',
                                'action' => 'busca',
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
