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

        $comissao = [];
        $this->setInputSelect('comissao', '*Comissão Comercial', $comissao);
        
        $this->setInputSelect('comissaoRes', '*Comissão Residencial', $comissao);

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
        
        $this->setInputText('multAluguelRes' , 'Multiplo para Aluguel Res.',$attributos);
        $this->setInputText('multConteudoRes', 'Multiplo para Conteudo Res.',$attributos);
        $this->setInputText('multIncendioRes', 'Multiplo para Incendio Res.',$attributos);
        $this->setInputText('multEletricoRes', 'Multiplo para Eletrica Res.',$attributos);
        $this->setInputText('multVendavalRes', 'Multiplo para Vendaval Res.',$attributos);
        
        $atribAdmistrodora = ['onChange'=>'getLastAdmComissao(this)'];
        $this->setInputSelect('administradora', '*Administradora', $this->administradoras, $atribAdmistrodora);

        $this->setInputSubmit('enviar', 'Salvar');
    }
    
    public function setComissaoOptions($adm){
        $entAdm = $this->em->find('Livraria\Entity\Administradora', $adm);
        if($entAdm){
            $param = str_pad($entAdm->getSeguradora()->getId(), 3, '0', STR_PAD_LEFT);
            $comissao = $this->getParametroSelect('comissaoParam' . $param);
            $this->setInputSelect('comissao', '*Comissão Comercial', $comissao);        
            $this->setInputSelect('comissaoRes', '*Comissão Residencial', $comissao);
        }
    }

}
