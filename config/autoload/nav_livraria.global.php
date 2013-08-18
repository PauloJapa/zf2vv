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
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'bemVindo',
            ),
            'cadastros' => array(
                'label' => 'Cadastro',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'cadastro',
                'pages' => array(
                    'seguradoras' => array(
                        'label' => 'Seguradora',
                        'route' => 'livraria-admin',
                        'controller' => 'seguradoras',
                        'pages' => array(
                            'seguradora1' => array(
                                'label' => 'Seguradoras',
                                'route' => 'livraria-admin',
                                'controller' => 'seguradoras',
                                'action' => 'index',
                            ),
                            'taxas1' => array(
                                'label' => 'Taxa_Coberturas',
                                'route' => 'livraria-admin',
                                'controller' => 'taxas',
                                'action' => 'index',
                            ),
                            'Mult_Min1' => array(
                                'label' => 'Limites_de_Contratação',
                                'route' => 'livraria-admin',
                                'controller' => 'multiplosMinimos',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'administradora' => array(
                        'label' => 'Administradora',
                        'route' => 'livraria-admin',
                        'controller' => 'administradoras',
                        'pages' => array(
                            'administradora1' => array(
                                'label' => 'Administradoras',
                                'route' => 'livraria-admin',
                                'controller' => 'administradoras',
                                'action' => 'index',
                            ),
                            'administradora2' => array(
                                'label' => 'Comissão_Paramentros',
                                'route' => 'livraria-admin',
                                'controller' => 'comissaos',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'classe' => array(
                        'label' => 'Classe',
                        'route' => 'livraria-admin',
                        'controller' => 'classes',
                        'pages' => array(
                            'classe1' => array(
                                'label' => 'Classes',
                                'route' => 'livraria-admin',
                                'controller' => 'classes',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'atividades' => array(
                        'label' => 'Atividade',
                        'route' => 'livraria-admin',
                        'controller' => 'atividades',
                        'pages' => array(
                            'atividades1' => array(
                                'label' => 'Atividades',
                                'route' => 'livraria-admin',
                                'controller' => 'atividades',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'classeAtividade' => array(
                        'label' => 'Classes Atividade',
                        'route' => 'livraria-admin',
                        'controller' => 'classeAtividades',
                        'pages' => array(
                            'classeAtividade1' => array(
                                'label' => 'Classes_Atividades',
                                'route' => 'livraria-admin',
                                'controller' => 'classeAtividades',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'users' => array(
                        'label' => 'Usuario',
                        'route' => 'livraria-admin',
                        'controller' => 'users',
                        'pages' => array(
                            'users1' => array(
                                'label' => 'Usuarios',
                                'route' => 'livraria-admin',
                                'controller' => 'users',
                                'action' => 'index',
                            ),
                            'users2' => array(
                                'label' => 'Altera Senha',
                                'route' => 'livraria-admin',
                                'controller' => 'users',
                                'action' => 'alteraSenha',
                            ),
                        ),
                    ),
                    'locatarios' => array(
                        'label' => 'Locatario',
                        'route' => 'livraria-admin',
                        'controller' => 'locatarios',
                        'pages' => array(
                            'locatarios1' => array(
                                'label' => 'Locatarios',
                                'route' => 'livraria-admin',
                                'controller' => 'locatarios',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'locadors' => array(
                        'label' => 'Locador',
                        'route' => 'livraria-admin',
                        'controller' => 'locadors',
                        'pages' => array(
                            'locadors1' => array(
                                'label' => 'Locadores',
                                'route' => 'livraria-admin',
                                'controller' => 'locadors',
                                'action' => 'index',
                            ),
                            'locadors2' => array(
                                'label' => 'Imoveis',
                                'route' => 'livraria-admin',
                                'controller' => 'imovels',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'Parametro' => array(
                        'label' => 'Parametro',
                        'route' => 'livraria-admin',
                        'controller' => 'parametroSis',
                        'pages' => array(
                            'Parametro1' => array(
                                'label' => 'Parametros',
                                'route' => 'livraria-admin',
                                'controller' => 'parametroSis',
                                'action' => 'index',
                            ),
                        ),
                    ),
                ),                
            ),
            'calculo' => array(
                'label' => 'Calculo',
                'route' => 'livraria-admin',
                'controller' => 'orcamentos',
                'action' => 'new',
            ),
            'consultas' => array(
                'label' => 'Consultas',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'consulta',
                'pages' => array(
                    'orcamentos' => array(
                        'label' => 'Orçamento',
                        'route' => 'livraria-admin',
                        'controller' => 'orcamentos',
                        'action' => 'listarOrcamentos',
                    ),
                    'renovacaos' => array(
                        'label' => 'Renovação',
                        'route' => 'livraria-admin',
                        'controller' => 'renovacaos',
                        'action' => 'index',
                        'pages' => array(
                            'renovacao1' => array(
                                'label' => 'Listar Renovados',
                                'route' => 'livraria-admin',
                                'controller' => 'renovacaos',
                                'action' => 'listarRenovados',
                            ),
                            'renovacao2' => array(
                                'label' => 'Renovação',
                                'route' => 'livraria-admin',
                                'controller' => 'renovacaos',
                                'action' => 'buscar',
                            ),
                            'renovacao3' => array(
                                'label' => 'Fechados',
                                'route' => 'livraria-admin',
                                'controller' => 'renovacaos',
                                'action' => 'buscarAbertos',
                            ),
                        ),
                    ),
                    'fechados' => array(
                        'label' => 'Fechados',
                        'route' => 'livraria-admin',
                        'controller' => 'fechados',
                        'action' => 'listarFechados',
                    ),
                ),
            ),
            'relatorios' => array(
                'label' => 'Relatórios',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'relatorios',
                'pages' => array(
                    'relatorios1' => array(
                        'label' => 'Consulta Query',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'query',
                    ),
                    'relatorios2' => array(
                        'label' => 'Orçamento/Renovação',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'orcareno',
                    ),
                    'relatorios3' => array(
                        'label' => 'Custo Renovação',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'custoRenovacao',
                    ),
                    'relatorios4' => array(
                        'label' => 'Mapa de Renovação',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'mapaRenovacao',
                    ),
                    'relatorios5' => array(
                        'label' => 'Imóveis Desocupados',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'imoveisDesocupados',
                    ),
                    'relatorios6' => array(
                        'label' => 'Pesquisa Seguros Fechados',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'fechamentoSeguro',
                    ),
                    'relatorios7' => array(
                        'label' => 'Pré Fatura',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'buscarRelatorio',
                    ),
                    'relatorios8' => array(
                        'label' => 'Resumo Seguros Fechados',
                        'route' => 'livraria-admin',
                        'controller' => 'fechados',
                        'action' => 'buscar',
                    ),
                    'relatorios9' => array(
                        'label' => 'Seguros Pendentes',
                        'route' => 'livraria-admin',
                        'controller' => 'pendentes',
                        'action' => 'index',
                    ),
                    'relatorios10' => array(
                        'label' => 'Comissão de Seguro',
                        'route' => 'livraria-admin',
                        'controller' => 'relatorios',
                        'action' => 'comissaoSeguro',
                    ),
                ),
            ),
            'auditoria' => array(
                'label' => 'Auditoria',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'auditoria',
                'pages' => array(
                    'logs' => array(
                        'label' => 'Rastreamento',
                        'route' => 'livraria-admin',
                        'controller' => 'logs',
                        'action' => 'index',
                    ),
                    'logOrcamento' => array(
                        'label' => 'Logs_de_Orçamento',
                        'route' => 'livraria-admin',
                        'controller' => 'logs',
                        'action' => 'logOrcamento',
                    ),
                    'logFechados' => array(
                        'label' => 'Logs_de_Fechados',
                        'route' => 'livraria-admin',
                        'controller' => 'logs',
                        'action' => 'logFechados',
                    ),
                    'logRenovacao' => array(
                        'label' => 'Logs_de_Renovação',
                        'route' => 'livraria-admin',
                        'controller' => 'logs',
                        'action' => 'logRenovacao',
                    ),
                ),
            ),
            'exportar' => array(
                'label' => 'Exportar',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'exportar',
                'pages' => array(
                    'exportar1' => array(
                        'label' => 'Maritima',
                        'route' => 'livraria-admin',
                        'controller' => 'exportar',
                        'action' => 'maritima',
                    ),
                    'exportar2' => array(
                        'label' => 'C.O.L.',
                        'route' => 'livraria-admin',
                        'controller' => 'exportar',
                        'action' => 'col',
                    ),
                    'exportar3' => array(
                        'label' => 'Emissão_de_Cartão',
                        'route' => 'livraria-admin',
                        'controller' => 'exportar',
                        'action' => 'cartao',
                    ),
                ),
            ),
            'importar' => array(
                'label' => 'Importar',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'importar',
                'pages' => array(
                    'importar1' => array(
                        'label' => 'Orçamento',
                        'route' => 'livraria-admin',
                        'controller' => 'importar',
                        'action' => 'selecionar',
                    ),
                ),
            ),
            'logout' => array(
                'label' => 'Logout',
                'route' => 'livraria-admin-logout',
            ),
        ),
        'user' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'bemVindo',
            ),
            'senha' => array(
                'label' => 'Alterar Senha',
                'route' => 'livraria-admin',
                'controller' => 'users',
                'action' => 'alteraSenha',
            ),
            'calculo' => array(
                'label' => 'Calculo',
                'route' => 'livraria-admin',
                'controller' => 'orcamentos',
                'action' => 'new',
            ),
            'consultas' => array(
                'label' => 'Consultas',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'consulta',
                'pages' => array(
                    'orcamentos' => array(
                        'label' => 'Orçamento',
                        'route' => 'livraria-admin',
                        'controller' => 'orcamentos',
                        'action' => 'listarOrcamentos',
                    ),
                    'fechados' => array(
                        'label' => 'Fechados',
                        'route' => 'livraria-admin',
                        'controller' => 'fechados',
                        'action' => 'listarFechados',
                    ),
                ),
            ),
            'imprimir' => array(
                'label' => 'Imprimir',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'imprimir',
                'pages' => array(
                    'relatorios' => array(
                        'label' => 'Seguros Pendentes',
                        'route' => 'livraria-admin',
                        'controller' => 'pendentes',
                        'action' => 'index',
                    ),
                ),
            ),
            'fatura' => array(
                'label' => 'Pré Fatura',
                'route' => 'livraria-admin',
                'controller' => 'relatorios',
                'action' => 'buscarRelatorio',
            ),
            'suporte' => array(
                'label' => 'Suporte',
                'route' => 'livraria-admin',
                'controller' => 'index',
                'action' => 'suporte',
            ),
            'logout' => array(
                'label' => 'Sair',
                'route' => 'livraria-admin-logout',
            ),
        ),
    ),
);
