<?php

namespace LivrariaAdmin\Form;

/**
 * Description of Relatorio
 * Form com campos para serem filtrados no relatorio
 * @author Paulo Watakabe watakabe05@gmail.com
 */
class Relatorio  extends AbstractForm {
    
    
    public function __construct($em=null) {
        parent::__construct('relatorio');
        $this->em = $em;
        
        $this->setAttribute('method', 'post');

        
        $this->setInputSubmit('gerar', 'Gerar', ['onClick' => 'return processa()']);
        
    }
    
    public function setQuery(){
        
        $options =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesNiver', 'Mês de aniversário',$options);
        
        $campos = [
            "ld.nome" => "Locador",
            "ld.Doc" => "CPF/CNPJ - Locador",
            "lc.nome" => "Locatário",
            "lc.Doc" => "CPF/CNPJ - Locatario",
            "i.rua" => "Endereço|Rua",
            "i.numero" => "Numero",
            "b.nome" => "Bairro",
            "c.nome" => "Cidade",
            "uf.sigla" => "Estado|UF",
            "o.inicio" => "Vigência Inicial",
            "o.fim" => "Vigência Final",
            "at.descricao" => "Ocupacão",
            "ad.id" => "UE",
            "o.status" => "Status",
            "o.refImovel" => "Ref. Imóvel",
        ];
        $this->setInputSelect('filtro[]', 'Filtrar', $campos);
        
        
        $comando = [
            ">" => "Maior que",
            "<" => "Menor que",
            "=" => "Exatamente igual",
            "LIKE" => "Parecido com",
            "<>" => "Diferente",
        ];
        $this->setInputSelect('comando[]', 'Instrução', $comando);
        
        $this->setInputText('valor[]', 'Valor');

        $order = [
            "o.refImovel" => "Referência do Imóvel",
            "o.locador" => "Cliente",
            "o.locatario" => "Locatário",
            "o.mesNiver" => "Mês de Aniversário",
        ];
        $this->setInputSelect('orderBy', 'Ordenar por:', $order);
        
        $limit = [
            '100' => 'Maximo 100 Registros',
            '1000' => 'Maximo 1.000 Registros',
            '10000' => 'Maximo 10.000 Registros',
            '0' => 'Todos  Registros',
        ];
        $this->setInputSelect('limit', 'Maximo de Resultado:', $limit);
        $this->get('limit')->setValue('100');
    }
    
    public function setMapaRenovacao(){
        
        $meses =['01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'];
        $this->setInputSelect('mesFiltro', '*Mês',$meses);
        
        $anoAtual = date('Y');
        $anoAtual++;
        for ($i = 0; $i < 5; $i++){
            $arrayAnos[$anoAtual] = $anoAtual;
            $anoAtual--;
        }
        $this->setInputSelect('anoFiltro', '*Ano',$arrayAnos);
        
        $this->setInputHidden('administradora');
        $this->setInputText('administradoraDesc', 'Administradora', ['placeholder' => 'Pesquise digitando ou em branco para Todas!','onKeyUp' => 'autoCompAdministradora();', 'autoComplete' => 'off']);
        
    }
    
    public function setImovelDesocupado(){
        $this->setInputHidden('administradora');
        $attributes = ['placeholder' => 'Pesquise digitando a Administradora aqui!',
                       'onKeyUp' => 'autoCompAdministradora();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('administradoraDesc', 'Pertence a administradora', $attributes); 

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio', $attributes);
        
        $this->setInputText('fim', '*Fim', $attributes);
    }
 
}