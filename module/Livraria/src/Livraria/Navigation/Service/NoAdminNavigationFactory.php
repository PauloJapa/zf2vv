<?php

namespace Livraria\Navigation\Service;

use Zend\Navigation\Service\AbstractNavigationFactory;

class NoAdminNavigationFactory extends AbstractNavigationFactory{
    protected function getName() {
        return 'noRoot';
    }
}

