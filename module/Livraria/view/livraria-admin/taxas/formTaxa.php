
<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
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
$this->FormDefault(['legend' => 'Dados da Cobertura', 'hidden' => 'id'],'inicio',$this, $form),
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
        
$this->FormDefault(['legend'=>'Coberturas Anuais'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['incendio' => 'float4']),
        $this->FormDefault(['eletrico' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['aluguel' => 'float4']),
        $this->FormDefault(['desastres' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudo' => 'float4']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['legend'=>'Coberturas Mensais'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['incendioMen' => 'float4']),
        $this->FormDefault(['eletricoMen' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['aluguelMen' => 'float4']),
        $this->FormDefault(['desastresMen' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudoMen' => 'float4']),
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
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
</script>
