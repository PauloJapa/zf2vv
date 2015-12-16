<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends CrudController {
    
    public function emailAction(){
        /* @var $srvEmail \Livraria\Service\Email */
        $srvEmail = $this->getServiceLocator()->get('Livraria\Service\Email');
        $dataEmail = [
            'email'     => 'watakabe05@gmail.com',
            'emailNome' => 'Paulo Sis',
            'subject'   => 'testando email',
            'data'      => [
                'um',
                'dois',
                'tres',
                'quatro',
                'cinco',
                'seis',
                'sete',
            ],
        ];
        echo '<p>Enviando email</p>';
        echo '<pre> original ' , var_dump($dataEmail), '</pre>';
        $srvEmail->enviaEmail($dataEmail);
        echo '<pre> alterado ' , var_dump($dataEmail), '</pre>';
        echo '<p>terminou o envio do email</p>';
    }
    
    public function bemVindoImoAction() {
        return new ViewModel(array());
    }

    public function bemVindoAction() {
        if($this->getIdentidade()->getTipo() == "admin"){
            return new ViewModel(array());
        }else{
            return $this->redirect()->toRoute('livraria-admin', array('controller' => 'Index','action'=>'bemVindoImo'));
        }
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

    public function suporteAction() {
        return new ViewModel(array());
    }

    public function consultaAction() {
        return new ViewModel(array());
    }

    public function imprimirAction() {
        return new ViewModel(array());
    }

}
