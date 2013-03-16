<?php

namespace LivrariaAdmin\Form;

class Comissao extends AbstractForm {
    
    protected $administradoras;    

    public function __construct($name = null, $em = null) {
        $this->em = $em;
        parent::__construct('comissao');
        
        $this->administradoras = $em->getRepository('Livraria\Entity\Administradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ComissaoFilter);

        $this->setInputHidden('id');

        $this->setInputText('comissao', '*Comissao', ['placeholder' => 'XX,XX']);

        $calend = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $calend);
        $this->setInputText('fim', 'Fim da Vigência', $calend);
        
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status);
        
        $attributos = ['class'=>'input-small'];
        $this->setInputText('multAluguel', 'Multiplo para Aluguel',$attributos);
        $this->setInputText('multConteudo', 'Multiplo para Conteudo',$attributos);
        $this->setInputText('multIncendio', 'Multiplo para Incendio',$attributos);
        $this->setInputText('multEletrico', 'Multiplo para Eletrica',$attributos);
        $this->setInputText('multVendaval', 'Multiplo para Vendaval',$attributos);
        
        $this->setInputSelect('administradora', '*Administradora', $this->administradoras);

        $this->setInputSubmit('enviar', 'Salvar');
    }

}
