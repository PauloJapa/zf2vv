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
               // 'pages' => array(
               //     'home' => array(
               //         'label' => 'Home',
               //         'route' => 'livraria-home',
               //     ),
               // ),
            ),
        ),
        'admin' => array(
            // And finally, here is where we define our page hierarchy
            'home' => array(
                'label' => 'Home',
                'route' => 'livraria-home',
            ),
            'administradora' => array(
                'label' => 'Administradoras',
                'route' => 'livraria-admin',
                'controller' => 'administradoras',
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
            'users' => array(
                'label' => 'Users',
                'route' => 'livraria-admin',
                'controller' => 'users',
            ),
            'logout' => array(
                'label' => 'Logout',
                'route' => 'livraria-admin-logout',
            ),
        ),
        'user' => array(
            // And finally, here is where we define our page hierarchy
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
