<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Stdlib\Hydrator;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use SisBase\Mail\Mail;

class Email extends AbstractService
{

    protected $transport;
    protected $view;
    
    public function __construct(EntityManager $em, SmtpTransport $transport, $view) 
    {
        parent::__construct($em);
        $this->transport = $transport;
        $this->view = $view;
    }
    
    public function enviaEmail(array $dataEmail, $template='add-user') {
        $baseUrl = $this->em->getRepository('Livraria\Entity\ParametroSis')->findKey('baseUrl')[0]->getDescricao();
        try {
            $dataEmail['baseUrl'] = $baseUrl;
            $dataEmail['email'] = 'watakabe98@hotmail.com';
            $dataEmail['email'] = 'marisa@vilavelha.com.br';
            $mail = new Mail($this->transport, $this->view, $template);
            $mail->setSubject($dataEmail['subject'])
                    ->setTo($dataEmail['email'])
                    ->setData($dataEmail)
                    ->prepare()
                    ->send();
        } catch (Exception $e) {
            die(print_r($e, true));
        }
    }
    
}
