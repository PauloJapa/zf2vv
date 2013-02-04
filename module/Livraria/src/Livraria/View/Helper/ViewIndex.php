<?php

namespace Livraria\View\Helper;

use Zend\View\Helper\AbstractHelper;
/**
 * View Index
 * View Helper para exibir dados em tabelas
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ViewIndex extends AbstractHelper {

    /**
     * com propriedades a serem colocas no td. 
     * @var array $tdopt 
     */
    protected $tdopt;
    
    /**
     * com a lista do conteudo do cabeçalho.
     * @var arrya $coluns 
     */
    protected $coluns;
    
    /**
     * valor numero da coluna que vai ter os botões para edição.
     * @var int $editLine 
     */
    protected $editLine;
    
    /**
     * com a lista do conteudo do rodapé.
     * @var array $foot 
     */
    protected $foot;

    /**
     * Metodo chamado pela view ao executar esta classe ViewIndex
     * @param string $acao metodo a executado
     * @param string $options para os metodos simples
     * @param array  $options para os metodos com mais configuração
     */
    public function __invoke($acao,$options = null) {
        switch ($acao) {
            
        case 'table':
            $this->openTable($options);
            break;
            
        case 'caption':
            $this->renderCaption($options);
            break;
        
        case 'thead':
            $this->renderThead($options);
            break;
        
        case 'line':
            $this->renderLine($options);
            break;
        
        case 'tfoot':
            $this->renderTfoot($options);
            break;
        
        case 'close':
            $this->renderCloseTable($options);
            break;

        default:
            echo "<h1>error $acao </h1>";
            break;
        }      
    }

    /**
     * Abre a tag table com opções default ou opções passadas por parametro.
     * @param array $options
     */
    public function openTable($options) {
        if(is_null($options)){
            echo '<table class="table table-striped table-bordered table-hover table-condensed">' , "\n";
        }else{
           echo '<table';
            foreach ($options as $atributo => $value) {
                echo ' ', $atributo, '="', $value, '"';
            }
            echo '>', "\n";   
        }
    }

    /**
     * Renderiza o cabeçalho e configura classe para proximas chamadas
     * @param array $options
     * @return caso não for passado um array com coluns
     */
    public function renderThead($options) {
        if(isset($options['tdopt'])) 
            $this->tdopt = $options['tdopt'];
        
        if(!isset($options['coluns'])) 
            return ;
        
        $this->coluns = $options['coluns']; 
        
        if(isset($options['editLine'])) 
            $this->editLine = $this->getEditLine($options['editLine']);
        
        echo "<thead>\n<tr>\n";        
        foreach ($this->coluns as $value) {
            echo "\t<th>$value</th>\n";
        }        
        echo "<tr>\n</thead>\n<tbody>\n";
    }

    /**
     * Renderiza rodape da tabela conforme dados do array
     * @param array $options
     */
    public function renderTfoot($options) {
        $this->foot = $options;
        echo "</tbody>\n<tfoot>\n<tr>\n";        
        foreach ($this->foot as $value) {
            echo "\t<td>$value</td>\n";
        }        
        echo "<tr>\n</tfoot>\n";
    }

    /**
     * Renderiza a linha com os dados
     * Faz sterilização dos td conforme parametros se houver
     * Monta td para edição dos registro na posição configurada
     * @param array $options
     */
    public function renderLine($options) {
        if(isset($options['tr']))
            echo "<tr ", $options['tr'] , ">", "\n";
        else    
            echo "<tr>", "\n";
        
        foreach ($options['data'] as $key => $value) {
            if($key == $this->editLine){
                $this->renderEditLine($value);
            }else{
                if(isset($this->tdopt[$key]))
                    echo "\t<td ", $this->tdopt[$key], ">", $value, "</td>", "\n";
                else
                    echo "\t<td>", $value, "</td>", "\n";
            }
        }
        echo "</tr>", "\n";
    }

    /**
     * Renderiza td com botões para editar ou deletar registro
     * @param string $value
     */
    public function renderEditLine($value) {
        echo "\t<td>",
                '<span class="add-on hand" onClick="edit(\'', $value, '\')"><i class="icon-pencil"></i>Editar</span>',
                '<span class="add-on hand" onClick="del(\'', $value, '\')"><i class="icon-pencil"></i>Deletar</span>',
             "</td>\n";   
    }

    /**
     * Fecha a tag table 
     * Se tiver um rodape apenas fecha table
     * @param string $options
     */
    public function renderCloseTable($options) {
        if($this->foot)
            echo "</table>\n";
        else
            echo "</tbody>\n</table>\n";
    }

    /**
     * Renderiza a tag caption da tabela
     * @param string $options
     */
    public function renderCaption($options) {
        echo "\t<caption>",
                $options,
             "</caption>";
    }

    /**
     * configura o td de edição do registro 
     * Retorna um int
     * @param string $option
     * @return int
     */
    public function getEditLine($option) {
        switch ($option) {
            
            case 'first':
                return 0 ;
                break;
            
            case 'last':
                return (count($this->coluns) - 1 ) ;
                break;
            
            default:
                return $option ;
                break;
        }
    }

}