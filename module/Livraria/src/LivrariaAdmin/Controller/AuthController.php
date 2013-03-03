<?php

namespace LivrariaAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

use Zend\Session\Container as SessionContainer;

use LivrariaAdmin\Form\Login as LoginForm;

class AuthController extends AbstractActionController {

    public function indexAction() {

        $form = new LoginForm;
        $error = false;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $request->getPost()->toArray();

                $auth = new AuthenticationService;

                $sessionStorage = new SessionStorage("LivrariaAdmin");
                $auth->setStorage($sessionStorage);

                $authAdapter = $this->getServiceLocator()->get('Livraria\Auth\Adapter');
                $authAdapter->setUsername($data['email'])
                        ->setPassword($data['password']);

                $result = $auth->authenticate($authAdapter);

                if ($result->isValid()) {
                    $sessionStorage->write($auth->getIdentity()['user'], null);
               //     $sessionStorage->write($auth->getIdentity()['tipo'], null);
                    return $this->redirect()->toRoute("livraria-admin", array('controller' => 'administradoras'));
                }else
                    $error = true;
            }
        }
        
        return new ViewModel(array('form'=>$form,'error'=>$error));
    }
    
    public function logoutAction() {
        $auth = new AuthenticationService;
        $auth->setStorage(new SessionStorage('LivrariaAdmin'));
        $auth->clearIdentity();
        
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        $sessionContainer->setExpirationSeconds(1);
        
        return $this->redirect()->toRoute('livraria-admin-auth');
    }

}
