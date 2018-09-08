
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
    "\r<td>",
        $this->FormDefault(['subOpcao' => 'hidden']),
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['seguradora' => 'select']),
        $this->FormDefault(['comissao' => 'select']),
    "</td>\r<td>",
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['classe' => 'select']),
        $this->FormDefault(['validade' => 'select']),
    "</td>\r<td>",
        $this->FormDefault(['status' => 'select']),
        $this->FormDefault(['tipoCobertura' => 'select']),
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
        $this->FormDefault(['incendio' => 'float8']),
        $this->FormDefault(['eletrico' => 'float8']),
    "</td><td>\r",
        $this->FormDefault(['incendioConteudo' => 'float8']),
        $this->FormDefault(['vendaval' => 'float8']),
    "</td><td>\r",
        $this->FormDefault(['aluguel' => 'float8']),
        $this->FormDefault(['respcivil' => 'float8']),
    "</td>\r",
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['submit' => 'enviar'],'fim');

$noFilter=true;

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
