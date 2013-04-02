
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
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['seguradora' => 'select']),
        $this->FormDefault(['comissao' => 'select']),
    "</td><td>\r",
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['classe' => 'select']),
        $this->FormDefault(['validade' => 'select']),
    "</td><td><br /><br /><br />\r",
        $this->FormDefault(['status' => 'select']),
        $this->FormDefault(['ocupacao' => 'select']),
    "</td>\r";
        
    if($this->UserIdentity('LivrariaAdmin')->getNome() == 'Paulo Cordeiro Watakabe'){
        echo
    "</tr><tr>",
        "<td>\n",
            $this->formRow($form->get('content')),
        "</td><td>",
            $this->FormDefault(['importar'], 'submitOnly'),
        "</td><td>",
        "</td>\n";
    }        
echo 
"</tr>\r",
"</table>\r",
        
$this->FormDefault(['legend'=>'Coberturas'],'fieldIni'),
    "<td>\r",
        $this->FormDefault(['incendio' => 'float4']),
        $this->FormDefault(['eletrico' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudo' => 'float4']),
        $this->FormDefault(['vendaval' => 'float4']),
    "</td><td>\r",
        $this->FormDefault(['aluguel' => 'float4']),
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
    function importarFile(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
        envia(tar,'',formName);
        return false;
    }
</script>
