<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
//use Zend\Stdlib\Hydrator;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use SisBase\Mail\Mail;

class Email extends AbstractService
{

    protected $transport;
    protected $view;
    protected $cco;
    protected $cc;
    
    public function __construct(EntityManager $em, SmtpTransport $transport, $view) 
    {
        parent::__construct($em);
        $this->transport = $transport;
        $this->view = $view;
    }
    
    public function enviaEmail(array &$dataEmail, $template='add-user') {
        $baseUrl = $this->em->getRepository('Livraria\Entity\ParametroSis')->findKey('baseUrl')[0]->getDescricao();
        try {
            $dataEmail['baseUrl'] = $baseUrl;
            $mail = new Mail($this->transport, $this->view, $template);
            $this->setMailTo($dataEmail);
            $mail->setSubject($dataEmail['subject'])
                    ->setTo($dataEmail['email'], $dataEmail['emailNome'])
                    ->setData($dataEmail)
                    ->prepare();
            if($this->cc){
                if(is_array($this->cc)){
                    foreach ($this->cc as $key => $value) {
                        $mail->getMessage()->addCc($value, 'Sistema Locação ' . ($key + 1));  
                    }
                }else{
                    $mail->getMessage()->addCc($this->cc, 'Sistema Locação'); 
                }
                echo '<pre>Lista copia' , var_dump($this->cc), '</pre>';
            }
            if($this->cco){
                $mail->getMessage()->addBcc($this->cco, 'Testes Locação');
            }
            $mail->send();
        } catch (Exception $e) {
            die(print_r($e, true));
        }
    }
    
    public function setMailTo(&$dataEmail) {
        /* @var $entity \Livraria\Entity\ParametroSis */
//        $this->cco = 'watakabe98@hotmail.com';
        $this->cc  = 'incendiolocacao@vilavelha.com.br' ;
        if(substr($dataEmail['email'], 0, 16) == 'REDIRECIONAEMAIL'){
            $entity = $this->em->getRepository('Livraria\Entity\ParametroSis')->findKey($dataEmail['email'])[0];
            $emails = explode(';', $entity->getConteudo());
            $this->cc = [];
            foreach ($emails as $key => $email) {
                if($key == 0){
                    $dataEmail['email'] = $email;
                    continue;
                }
                $this->cc[] = $email;
            }
        }
        
        switch ($this->getIdentidade()->getEmail()) {
            case 'watakabe05@gmail.com':
                $dataEmail['subject'] .=  '(' . $dataEmail['email'] . ')';
                $dataEmail['email'] = 'watakabe98@hotmail.com'; 
                $dataEmail['emailNome'] = 'Paulo Sistema';
//                $this->cc = [
//                    'watakabe05@gmail.com', 
//                    'dorival@vilavelha.com.br', 
//                    'alessandroalzani@vilavelha.com.br',
//                    'deploy.microsoft@dedalusprime.com.br',
//                ];
                $this->cc = false;
                $this->cco = false;
                break;
//            case 'marisa':
//                $dataEmail['email'] = 'marisa@vilavelha.com.br'; 
//                $dataEmail['emailNome'] = 'Marisa Vila Velha';
//                break;
            default:
                if(!isset($dataEmail['email'])){
                    $dataEmail['email']     = 'incendiolocacao@vilavelha.com.br';                    
                }
                if(is_null($dataEmail['email'])){
                    $dataEmail['email']     = 'incendiolocacao@vilavelha.com.br';
                }
                if(strtoupper($dataEmail['email']) == 'NAO' OR empty($dataEmail['email']) OR $dataEmail['email'] == 'NATALIAOCTAVIANO@VILAVELHA.COM.BR' OR $dataEmail['email'] == '.'){
                    $dataEmail['email']     = 'incendiolocacao@vilavelha.com.br';                    
                }
                if($dataEmail['email'] == 'incendiolocacao@vilavelha.com.br' ){
                    $this->cc = FALSE;
                    $dataEmail['emailNome'] = 'Sis. ADM sem EMAIL';
                }
                if(!isset($dataEmail['emailNome'])){
                    $dataEmail['emailNome'] = 'Incendio Locação Sistemas';
                }
                break;
        }
    }
    
}
