<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;
/**
 * ClasseAtividade
 * Fomulario para manipular os dados da entity
 */
class ClasseAtividade extends Form {
    
    /**
     *
     * @var \Livraria\Entity\ClasseTaxa
     */
    protected $classeTaxas;  
    
    /**
     *
     * @var \Livraria\Entity\Atividade 
     */
    protected $atividades;  
    
    /**
     * Todos os registros de \Livraria\Entity\Seguradora
     * @var array  
     */
    protected $seguradoras;

    public function __construct($name = null, $em = null) {
        parent::__construct('classeAtividade');
        
        $this->classeTaxas = $em->getRepository('Livraria\Entity\Classe')->fetchPairs();
        $this->atividades = $em->getRepository('Livraria\Entity\Atividade')->fetchPairs();
        $this->$seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseAtividadeFilter);

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

        $classe = new Select();
        $classe->setLabel("*Classe")
                ->setName("classeTaxas")
                ->setAttribute("id","classeTaxas")
                ->setOptions(array('value_options' => $this->classeTaxas)
        );
        $this->add($classe);

        $classe = new Select();
        $classe->setLabel("*Atividade")
                ->setName("atividade")
                ->setAttribute("id","atividade")
                ->setOptions(array('value_options' => $this->atividades)
        );
        $this->add($classe);

        $seguradora = new Select();
        $seguradora->setLabel("Seguradora")
                ->setName("seguradora")
                ->setAttribute("id","seguradora")
                ->setOptions(array('value_options' => $this->seguradoras)
        );
        $this->add($seguradora);
     
        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Salvar',
                'class' => 'btn-success'
            )
        ));
    }

}
