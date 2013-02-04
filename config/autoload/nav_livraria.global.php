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
                                'label' => 'Nova_Seguradora',
                                'route' => 'livraria-admin',
                                'controller' => 'seguradoras',
                                'action' => 'new',
                            ),
                            'seguradora2' => array(
                                'label' => 'Listar_Seguradoras',
                                'route' => 'livraria-admin',
                                'controller' => 'seguradoras',
                                'action' => 'index',
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
                            'administradora4' => array(
                                'label' => 'Listar_Comissões',
                                'route' => 'livraria-admin',
                                'controller' => 'comissaos',
                                'action' => 'index',
                            ),
                            'administradora5' => array(
                                'label' => 'Nova_Comissão',
                                'route' => 'livraria-admin',
                                'controller' => 'comissaos',
                                'action' => 'new',
                            ),
                        ),
                    ),
                    'Taxas' => array(
                        'label' => 'Taxas',
                        'route' => 'livraria-admin',
                        'controller' => 'taxas',
                        'pages' => array(
                            'taxas1' => array(
                                'label' => 'Nova_Taxa',
                                'route' => 'livraria-admin',
                                'controller' => 'taxas',
                                'action' => 'new',
                            ),
                            'taxas2' => array(
                                'label' => 'Listar_Taxas',
                                'route' => 'livraria-admin',
                                'controller' => 'taxas',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'classe' => array(
                        'label' => 'Classes',
                        'route' => 'livraria-admin',
                        'controller' => 'classes',
                        'pages' => array(
                            'classe1' => array(
                                'label' => 'Nova_Classe',
                                'route' => 'livraria-admin',
                                'controller' => 'classes',
                                'action' => 'new',
                            ),
                            'classe2' => array(
                                'label' => 'Listar_Classe',
                                'route' => 'livraria-admin',
                                'controller' => 'classes',
                                'action' => 'index',
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
                    'classeAtividade' => array(
                        'label' => 'Classes_Atividades',
                        'route' => 'livraria-admin',
                        'controller' => 'classeAtividades',
                        'pages' => array(
                            'classe1' => array(
                                'label' => 'Nova_Classes_Atividades',
                                'route' => 'livraria-admin',
                                'controller' => 'classeAtividades',
                                'action' => 'new',
                            ),
                            'classe2' => array(
                                'label' => 'Listar_Classes_Atividades',
                                'route' => 'livraria-admin',
                                'controller' => 'classeAtividades',
                                'action' => 'index',
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
                        'pages' => array(
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
                        ),
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
                'controller' => 'logs',
                'pages' => array(
                    'logs' => array(
                        'label' => 'Rastreamento',
                        'route' => 'livraria-admin',
                        'controller' => 'logs',
                        'pages' => array(
                            'logs1' => array(
                                'label' => 'Listar_Ações',
                                'route' => 'livraria-admin',
                                'controller' => 'logs',
                                'action' => 'index',
                            ),
                            'logs2' => array(
                                'label' => 'Inserir_Ação',
                                'route' => 'livraria-admin',
                                'controller' => 'logs',
                                'action' => 'new',
                            ),
                        ),
                    ),
                ),
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
