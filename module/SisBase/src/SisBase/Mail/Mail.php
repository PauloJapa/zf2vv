<?php

namespace SisBase\Mail;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Message;

use Zend\View\Model\ViewModel;

use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;


class Mail 
{

    protected $transport;
    protected $view;
    protected $body;
    protected $message;
    protected $subject;
    protected $to;
    protected $toName;
    protected $data;
    protected $page;
    
    public function __construct(SmtpTransport $transport, $view, $page) 
    {
        $this->transport = $transport;
        $this->view = $view;
        $this->page = $page;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    public function setTo($to,$name=null)
    {
        $this->to = $to;
        $this->toName = $name;
        return $this;
    }
    
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function renderView($page, array $data)
    {
        $model = new ViewModel;
        $model->setTemplate("mailer/{$page}.phtml");
        $model->setOption('has_parent',true);
        $model->setVariables($data);
        
        return $this->view->render($model);
    }
    
    public function prepare()
    {
        $html = new MimePart($this->renderView($this->page, $this->data));
        $html->type = "text/html";
        
        $body = new MimeMessage();
        $body->setParts(array($html));
        $this->body = $body;
        
        $config = $this->transport->getOptions()->toArray();
        
        $this->message = new Message;
        $this->message->addFrom($config['connection_config']['from'], $config['connection_config']['fromName'])
                ->addTo($this->to,$this->toName)
                ->setSubject($this->subject)
                ->setBody($this->body)
                ->setEncoding('UTF-8');
        
        return $this;
    }
    /**
     * 
     * @return Message
     */
    public function getMessage() {
        return $this->message;
    }
    
    public function send()
    {
        $this->transport->send($this->message);
    }
    
}
