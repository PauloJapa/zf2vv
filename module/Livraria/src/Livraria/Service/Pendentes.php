<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;

/**
 * Pendentes
 * Faz a busca por seguros pendentes em Orçamento e Renovação
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Pendentes extends AbstractService{

    
    /**
     * Objeto com SessionContainer
     * @var object 
     */
    protected $sc;
    
    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    /**
     * Retorna Instancia do Session Container
     * @return object 
     */
    public function getSc(){
        if($this->sc)
            return $this->sc;
        $this->sc = new SessionContainer("LivrariaAdmin");
        return $this->sc;
    }
    
    public function getPendentes($data){
        //Trata os filtro para data anual
        $this->data['inicio'] = '01/' . $data['mesFiltro'] . '/' . $data['anoFiltro'];
        $this->dateToObject('inicio');
        $this->data['fim'] = clone $this->data['inicio'];
        $this->data['fim']->add(new \DateInterval('P1M'));
        $this->data['fim']->sub(new \DateInterval('P1D'));
        $this->data['administradora'] = $data['administradora'];
        
        $orcamento = $this->em->getRepository("Livraria\Entity\Orcamento")->getPendentes($this->data);
        $renovacao = $this->em->getRepository("Livraria\Entity\Renovacao")->getPendentes($this->data);
        
        
        $lista = array_merge($orcamento, $renovacao);
        $ordena = [];
        foreach ($lista as $key => $value) {
            $ordena[$key] = $value['locatario']['nome'];
        }
        
        array_multisort($ordena, SORT_ASC, SORT_STRING, $lista);
        //Armazena lista no cache para gerar outra saidas
        $this->getSc()->lista = $lista;
        
        return $lista;
    }
    
    
}