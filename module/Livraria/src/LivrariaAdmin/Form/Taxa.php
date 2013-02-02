<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Taxa extends Form {
    /**
     * Registros para preencher o input select
     * @var array 
     */
    protected $classes;  
    /**
     * Registros para preencher o input select
     * @var array 
     */  
    protected $seguradoras;  
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct($name = null, $em = null) {
        parent::__construct('taxa');
        $this->em = $em;
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new TaxaFilter);    

        $this->add(array(
            'name' => 'subOpcao',
            'attributes' => array(
                'id' => 'subOpcao'
            )
        ));

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'id' => 'id',
            )
        ));
        
        $this->add(array(
            'name' => 'inicio',
            'options' => array(
                'type' => 'text',
                'label' => '*Inicio da Vigência'
            ),
            'attributes' => array(
                'id' => 'inicio',
                'placeholder' => 'dd/mm/yyyy',
                'onClick' => "displayCalendar(this,dateFormat,this)"
            )
        ));
        
        $this->add(array(
            'name' => 'fim',
            'options' => array(
                'type' => 'text',
                'label' => '*Fim da Vigência'
            ),
            'attributes' => array(
                'id' => 'fim',
                'placeholder' => 'dd/mm/yyyy',
                'onClick' => "displayCalendar(this,dateFormat,this)"
            )
        ));

        $status = new Select();
        $status->setLabel("*Situação")
                ->setName("status")
                ->setOptions(array('value_options' => array('A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'))
        );
        $this->add($status);

        $this->add(array(
            'name' => 'incendio',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ incêndio'
            ),
            'attributes' => array(
                'id' => 'incendio',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'incendioConteudo',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ incêndio + conteúdo'
            ),
            'attributes' => array(
                'id' => 'incendioConteudo',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'aluguel',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ aluguel'
            ),
            'attributes' => array(
                'id' => 'aluguel',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'eletrico',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ eletrica'
            ),
            'attributes' => array(
                'id' => 'eletrico',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'desastres',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ desastres'
            ),
            'attributes' => array(
                'id' => 'desastres',
                'placeholder' => 'XXX,XX'
            )
        ));

        $classe = new Select();
        $classe->setLabel("*Classe")
                ->setName("classe")
                ->setAttribute("id","classe")
                ->setAttribute("onChange","buscaClasse()");
        $this->add($classe);

        $seguradora = new Select();
        $seguradora->setLabel("*Seguradora")
                ->setName("seguradora")
                ->setAttribute("id","seguradora")
                ->setAttribute("onChange","buscaSeguradora()")
                ->setOptions(array('value_options' => $this->seguradoras)
        );
        $this->add($seguradora);
     
        $this->add(array(
            'name' => 'enviar',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Salvar',
                'class' => 'btn-success',
                'onClick' => 'salvar()'
            )
        ));
    }
    
    public function reloadSelectClasse(array $filtro){
        $this->classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs($filtro);
        $classe = new Select();
        $classe->setLabel("*Classe")
                ->setName("classe")
                ->setAttribute("id","classe")
                ->setAttribute("onChange","buscaClasse()")
                ->setOptions(array('value_options' => $this->classes)
        );
        $this->add($classe);
    }

}
