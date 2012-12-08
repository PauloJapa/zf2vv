<?php

namespace Livraria\Navigation\Service;

use Zend\Navigation\Service\AbstractNavigationFactory;

class UserNavigationFactory extends AbstractNavigationFactory{
    protected function getName() {
        return 'user';
    }
}

