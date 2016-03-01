<style type="text/css">
.form-horizontal .control-group>label{float:left;width:450px;padding-top:5px;text-align:right;}
#mensagen {
    left:50px;
    margin:0;
    padding:10px;
    position:absolute;
    top:50%;
    width:450px;
    background-color: #ffffff;
    border: solid #000 1px;
}
</style>
<?php if(count($flashMessages)) : ?>
<div id="mensagen">
    <table width="100%">
        <tr>
            <td>
                <div class="control-group error">
                    <ul class="help-inline">
                        <?php foreach ($flashMessages as $msg) : ?>
                        <li><?php echo $msg; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </td>
            <td valign='top'>
                <a href="javascript:fecharPop('mensagen');">Fechar <i class="icon-remove-circle"></i></a>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>

<p><span class="add-on hand" onClick="voltar();"><i class="icon-backward"></i>Voltar</span></p>
<?php
$user = $this->UserIdentity('LivrariaAdmin');

$form->prepare();
//var_dump($form);
echo 
$this->FormDefault(['legend' => 'Dados sobre o seguro ADM: ' . $this->administradora['nome'], 'hidden' => 'id'],'inicio',$this, $form),
    "<td>",
        $this->FormDefault(['comissaoEnt','administradora','administradoraDesc','ajaxStatus','autoComp','subOpcao','locador','imovel','imovelTel','imovelStatus','locatario','atividade','taxa','canceladoEm','codano','numeroParcela','premio','premioLiquido','fechadoId','taxaIof','user','status','multiplosMinimos','scrolX','scrolY','fechadoOrigemId','mensalSeq','orcaReno','gerado'],'hidden'),
        $this->FormDefault(['proposta' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['seguroEmNome' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['criadoEm' => 'calend']),
    "</td>", PHP_EOL;
        
//    if($user->getNome() == 'Paulo Cordeiro Watakabe'){
//        echo
//    "</tr><tr>",
//        "<td>\n",
//            $this->formRow($form->get('content')),
//        "</td><td>",
//            $this->FormDefault(['importar'], 'submitOnly'),
//        "</td><td>",
//        "</td>\n";
//    }   

$beforeTableLocador = "<p class='btn btn-warning'>Lembre-se Pesquise Primeiro para evitar duplicidade<span class='icon-ok-circle'></span></p>" . PHP_EOL ;

$beforeTableLocatario = "<p class='btn btn-warning'>Lembre-se Pesquise Primeiro para evitar duplicidade<span class='icon-ok-circle'></span></p>" . PHP_EOL ;

$beforeTableImovel = "<div id='buttonImovel'>" . PHP_EOL .
        "<p class='btn btn-warning'>Lembre-se Pesquise Primeiro para evitar duplicidade<span class='icon-ok-circle'></span></p><br />" . PHP_EOL .
        '<a class="btn btn-success" href="javascript:autoCompImoveis();">Exibir Imoveis cadastrados desse locador <i class="icon-search"></i></a>' .
        '<br /><span id="popImoveis" style="position:absolute"></span>' .
        "</div>" . PHP_EOL . "<div id='showImovel'>" . PHP_EOL;

echo 
  "</tr>", PHP_EOL,
"</table>", PHP_EOL,
        
$this->FormDefault(['legend' => 'Dados do Locador:', 'beforeTable' => $beforeTableLocador],'fieldIni'),
    "<td>",
        $this->FormDefault(['name' => 'locadorNome','icone' => 'icon-search','js' => "autoCompLocador('lupa')",'span' => "popLocador' style='position:absolute"],'icone'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['tipoLoc' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cpfLoc','cnpjLoc'],'text'),
    "</td>", PHP_EOL,
"</tr><tr>", PHP_EOL,       
    "<td colspan='3' align='center'>",
        $this->FormDefault(['nwLocador' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['edLocador' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['svLocador' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['ccLocador' => 'buttonOnly']),
    "</td>", PHP_EOL,
$this->FormDefault([],'fieldFim'), 
        
$this->FormDefault(['legend' => 'Dados do Locatario:', 'beforeTable' => $beforeTableLocatario],'fieldIni'),
    "<td>",
        $this->FormDefault(['name' => 'locatarioNome','icone' => 'icon-search','js' => 'autoCompLocatario()','span' => "popLocatario' style='position:absolute"],'icone'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['tipo' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cpf','cnpj'],'text'),
    "</td>", PHP_EOL,
"</tr><tr>", PHP_EOL,       
    "<td colspan='3' align='center'>",
        $this->FormDefault(['nwLocatario' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['edLocatario' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['svLocatario' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['ccLocatario' => 'buttonOnly']),
    "</td>", PHP_EOL,
$this->FormDefault([],'fieldFim'), 
        

$this->FormDefault(['legend' => 'Dados do Imovel:', 'hidden' => 'idEnde', 'beforeTable' => $beforeTableImovel],'fieldIni'),

    "<td nowrap>", PHP_EOL,
        $this->FormDefault(['name' => 'cep','js' => 'buscarEndCep()', 'icone' => 'icon-search', 'span' => 'checar'],'icone'),
    "</td><td colspan='3'>", PHP_EOL,
        $this->FormDefault(['refImovel' => 'text']),
    "</td>",        
"</tr><tr>", PHP_EOL,       
    "<td>",
        $this->FormDefault(['ajaxStatus' => 'hidden']),
        $this->FormDefault(['rua' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['numero' => 'text']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['apto'], 'text'),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['bloco'], 'text'),
    "</td>", PHP_EOL,
"</tr><tr>", PHP_EOL,        
    "<td colspan='3'>", PHP_EOL,
        $this->FormDefault(['compl' => 'text']),
    "</td>", PHP_EOL,
"</tr>", PHP_EOL,   
"</table>", PHP_EOL,   
"<table style='width : 100% ;'>", PHP_EOL,        
"<tr valign='top'>", PHP_EOL,   
    "<td>",
        $this->FormDefault(['bairro' => 'hidden', 'bairroDesc' => 'text']),
        "<br /><span id='popBairro' style='position:absolute'></span>",
    "</td><td>", PHP_EOL,
        $this->FormDefault(['cidade' => 'hidden', 'cidadeDesc' => 'text']),
        "<br /><span id='popCidade' style='position:absolute'></span>",
    "</td><td>", PHP_EOL,
        $this->FormDefault(['estado' => 'select']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['pais' => 'select']),
    "</td>", PHP_EOL,
    "</tr><tr>", PHP_EOL,
        
    "<td colspan='4' align='center'>",
        '<a class="btn btn-success" id="nwImovel" href="javascript:newImoveis();">Incluir novo Imovel <i class="icon-plus-sign"></i></a>' .
        '&nbsp;&nbsp;&nbsp;&nbsp;',   
        $this->FormDefault(['edImovel' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['svImovel' => 'buttonOnly']),
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        $this->FormDefault(['ccImovel' => 'buttonOnly']),
    "</td>", PHP_EOL,

$this->FormDefault(['afterTable' => "</div>" .  PHP_EOL],'fieldFim'),  
        
"<table style='width : 100% ;'>", PHP_EOL,        
"<tr valign='top'>", PHP_EOL,  
    "<td>",
        $this->FormDefault(['inicio' => 'calend']),
        $this->FormDefault(['validade' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['fim' => 'calend']),
        $this->FormDefault(['mesNiver' => 'select']),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['ocupacao' => 'radio']),
    "</td><td>", PHP_EOL,
        $this->FormDefault(['name' => 'atividadeDesc','icone' => 'icon-search','js' => 'autoCompAtividade()','span' => "popAtividade' style='position:absolute"],'icone'),
    "</td>", PHP_EOL,
  "</tr><tr>", PHP_EOL,
    "<td>",
        $this->FormDefault(['codigoGerente' => 'text']),
    "</td>", PHP_EOL,
  "</tr>", PHP_EOL,
"</table>", PHP_EOL;
        
    // Usuario Administrador pode alterar seguradora e valor da comissão    
    if ($user->getTipo() != 'admin') {
        echo $this->FormDefault(['seguradora', 'comissao','assist24'], 'hidden');
    } else {
        echo 
        $this->FormDefault(['legend' => 'Parametros do Administrador:'],'fieldIni'),
            "<td>",
                $this->FormDefault(['comissao' => 'select']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['seguradora' => 'select']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['assist24' => 'radio']),
            "</td><td>", PHP_EOL,
                $this->FormDefault(['logOrca' => 'buttonOnly']),
            "</td>", PHP_EOL,
        $this->FormDefault([],'fieldFim');
    }

echo 
        
$this->FormDefault(['legend' => 'Coberturas'],'fieldIni'),
    "<td>",
        $this->FormDefault(['tipoCobertura' => 'selectLine']),
        $this->FormDefault(['formaPagto' => 'selectLine']),
        $this->FormDefault(['valorAluguel' => 'floatLine']),
        
        $this->FormDefault(['name' => 'incendio','icone' => 'icon-pencil','js' => "setEmpty('incendio')"],'iconeLine'),
        $this->FormDefault(['name' => 'conteudo','icone' => 'icon-pencil','js' => "setEmpty('conteudo')"],'iconeLine'),
        $this->FormDefault(['name' => 'aluguel','icone' => 'icon-pencil','js' => "setEmpty('aluguel')"],'iconeLine'),
        $this->FormDefault(['name' => 'eletrico','icone' => 'icon-pencil','js' => "setEmpty('eletrico')"],'iconeLine'),
        $this->FormDefault(['name' => 'vendaval','icone' => 'icon-pencil','js' => "setEmpty('vendaval')"],'iconeLine'),
        
        $this->FormDefault(['premioTotal' => 'moedaLine']),
        $this->FormDefault(['parcelaVlr' => 'moedaLine']),
    "</td><td style='vertical-align: middle; width:20%;'>", PHP_EOL,
        $this->FormDefault(['calcula'=>'submit']),
        "<br /><br /><br /><p class='btn btn-warning' id='aviso'>Lembre-se de Salvar <span class='icon-ok-circle'></span></p>",
        $this->FormDefault(['enviar'=>'submit']),
    "</td>", PHP_EOL,        
    "</tr><tr>",
    "<td colspan='2'>",
        $this->FormDefault(['observacao' => 'textArea']),
    "</td>", PHP_EOL,        
        
$this->FormDefault([],'fieldFim'),
        
$this->FormDefault(['getpdf','fecha','novoOrca'],'submits');

$this->FormDefault([],'fim');

$log = isset($this->param['log']) ? $this->param['log'] : 'logOrcamento';
$tar = isset($this->param['tar']) ? $this->param['tar'] : '/admin/orcamentos/escolheAdm';
$prt = isset($this->param['prt']) ? $this->param['prt'] : '/admin/orcamentos/printProposta';
$bak = isset($this->param['bak']) ? $this->param['bak'] : 'listarOrcamentos';

echo $this->headScript()->appendFile('/js/formOrcamento.js')
?> 

<script language="javascript">
 
//    var imprime = '<?php echo $this->imprimeProp ?>';
    var avisa = '<?php echo $this->avisaCalc ?>';
    var param = <?php echo json_encode($this->param); ?>;
    var user = '<?php echo $user->getTipo(); ?>';
    var hoje = <?php echo date('Ymd'); ?>;    
    var tar = '<?php echo $this->url($this->matchedRouteName,$this->params); ?>';
    var formName = '<?php echo $this->formName ?>';
    
    var VARS_AMBIENTE = new Array();    
    VARS_AMBIENTE['autoCompLocatario_servico'] = "<?php echo $this->url('livraria-admin',array('controller'=>'locatarios','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['saveLocatario_url']         = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'locatarios','action'=>'save')); ?>";
    VARS_AMBIENTE['autoCompLocador_servico']   = "<?php echo $this->url('livraria-admin',array('controller'=>'locadors','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['saveLocador_url']           = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'locadors','action'=>'save')); ?>";
    VARS_AMBIENTE['autoCompImoveis_servico']   = "<?php echo $this->url('livraria-admin',array('controller'=>'imovels','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['saveimovel_url']            = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'imovels','action'=>'save')); ?>";
    VARS_AMBIENTE['autoCompBairro_servico']    = "<?php echo $this->url('livraria-admin',array('controller'=>'bairros','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['autoCompCidade_servico']    = "<?php echo $this->url('livraria-admin',array('controller'=>'cidades','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['autoCompAtividade_servico'] = "<?php echo $this->url('livraria-admin',array('controller'=>'atividades','action'=>'autoComp')); ?>";
    VARS_AMBIENTE['viewLogsOrcamento_target']  = "<?php echo $this->url($this->matchedRouteName,array('controller'=> 'logs','action'=>$log)); ?>";
    VARS_AMBIENTE['importarFile_tar']          = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>'importar')); ?>";
    VARS_AMBIENTE['fechar_tar']                = '<? echo $tar ?>' ;
    VARS_AMBIENTE['newOrcamento_tar']          = '<? echo $tar ?>';
    VARS_AMBIENTE['doPrintProp_tar']           = "<? echo $prt ?>" ;
    VARS_AMBIENTE['voltar_adm']                = '<? echo $this->administradora['nome']; ?>' ;
    VARS_AMBIENTE['voltar_tar']                = "<?php echo $this->url($this->matchedRouteName,array('controller'=> $this->params['controller'],'action'=>$bak )); ?>" ;
    //VARS_AMBIENTE[''] =  ;
    
    // Ocultar campos no inicio do calculo
    $('#popcodigoGerente').hide(); 
    $('#popmesNiver').hide(); 
    $('#popparcelaVlr').hide(); 
    
    // Verificar cpf ou cnpj do locador e locatario
    // Se não tiver salvo o orçamento não exibe o botao de fechar
    // Oculta select pais.

    setTimeout(function(){
            checkPrintProp();
            showTipo();
            setButtonFechaOrc();
            setOcultar();
            showIncOrIncCon();
            setComissao();
            setCobertura(true);
            formataDoc();
            travaFormaPagto();
            setMesNiverOfMensal();
            checkAvisoCalc();
            checkShowLocatario("ini");
            checkShowLocador("ini");
            checkShowImoveis();
        }
        ,500
    );
    window.setTimeout("scroll(document.getElementById('scrolX').value,document.getElementById('scrolY').value)", 600);
    
    var com = '<?php echo $this->comissao ; ?>';
    var mesNiver = '<?php echo $this->mesNiver ; ?>';
    if(com != ''){
         window.setTimeout("$('#comissao').val(com)",1000);        
    }
</script>   