<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends CrudController {

    public function bemVindoImoAction() {
        return new ViewModel(array());
    }

    public function bemVindoAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }
    
    public function cadastroAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }
    
    public function contratosAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }
    
    public function relatoriosAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }
    
    public function auditoriaAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }
    
    public function exportarAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }

    public function importarAction() {
        $this->verificaSeUserAdmin();
        return new ViewModel(array());
    }

}
