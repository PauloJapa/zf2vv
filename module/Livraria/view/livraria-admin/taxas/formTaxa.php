<?php if(count($flashMessages)) : ?>
<div class="control-group error">
<ul class="help-inline">
    <?php foreach ($flashMessages as $msg) : ?>
    <li><?php echo $msg; ?></li>
    <?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
<?php
$form->prepare();
echo 
$this->FormDefault(['legend' => 'Dados da Taxa', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['subOpcao' => 'hidden']),
        $this->FormDefault(['seguradora' => 'select']),
        $this->FormDefault(['inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['classe' => 'select']),
        $this->FormDefault(['fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select']),
    "</td>\r",
"</tr>\r",
"</table>\r",
        
$this->FormDefault(['legend'=>'Taxas Anuais'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['incendio' => 'float']),
        $this->FormDefault(['eletrico' => 'float']),
    "</td><td>\r",
        $this->FormDefault(['aluguel' => 'float']),
        $this->FormDefault(['desastres' => 'float']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudo' => 'float']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['legend'=>'Taxas Mensais'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['incendioMen' => 'float']),
        $this->FormDefault(['eletricoMen' => 'float']),
    "</td><td>\r",
        $this->FormDefault(['aluguelMen' => 'float']),
        $this->FormDefault(['desastresMen' => 'float']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudoMen' => 'float']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['submit' => 'enviar'],'fim');

require 'index.phtml';

?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function buscaSeguradora(){
        envia(tar,'buscar',formName);
    }
    function buscaClasse(){
        envia(tar,'buscar',formName);
    }
    function salvar(){
        envia(tar,'salvar',formName);
    }
</script>
