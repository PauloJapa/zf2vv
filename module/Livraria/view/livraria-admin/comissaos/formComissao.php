
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
$this->FormDefault(['legend' => 'Dados da Comissao', 'hidden' => 'id'],'inicio',$this, $form),
    "<td>\r",
        $this->FormDefault(['administradora' => 'select', 'inicio' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['comissao' => 'float', 'fim' => 'calend']),
    "</td><td>\r",
        $this->FormDefault(['status' => 'select','subOpcao' => 'hidden']),
    "</td>\r",      

"</tr>\r",
"</table>\r",        
$this->FormDefault(['legend'=>'Multiplos'],'fieldIni'),
    "<td>\n",
        $this->FormDefault(['multIncendio' => 'float']),
    "</td><td>\n",
        $this->FormDefault(['multConteudo' => 'float']),
    "</td><td>\n",
        $this->FormDefault(['multAluguel' => 'float']),
    "</td><td>\n",
        $this->FormDefault(['multEletrico' => 'float']),
    "</td><td>\n",
        $this->FormDefault(['multVendaval' => 'float']),
    "</td>\n",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['submit' => 'enviar'],'fim');
?>
<script language="javascript">
    var dateFormat = 'dd/mm/yyyy';
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    function salvar(){
        envia(tar,'salvar',formName);
        return false;
    }
    function voltar(){
        var tar = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'index')); ?>";
        envia(tar,'',formName);
    }
</script>