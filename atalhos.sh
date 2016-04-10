#!/bin/bash
#pega o diretorio corrente
DIR=`pwd '{print $1}'`

# lista de comando a serem executados
case "$1" in
"composer") php /var/www/composer.phar $2 $3 $4 $5;;
"zf") 
         php $DIR/vendor/zendframework/zftool/zf.php $2 $3 $4 $5;
    echo php $DIR/vendor/zendframework/zftool/zf.php $2 $3 $4 $5;;
"serve") 
         $DIR/srv.sh $2 $3 $4 $5;
    echo $DIR/srv.sh $2 $3 $4 $5;;
"serve2") 
         $DIR/srv2.sh $2 $3 $4 $5;
    echo $DIR/srv2.sh $2 $3 $4 $5;;
"serve2") 
         $DIR/srv2.sh $2 $3 $4 $5;
    echo $DIR/srv2.sh $2 $3 $4 $5;;
"import") 
         php $DIR/public/index.php data-fixture:import --append $2 $3 $4 $5;
    echo php $DIR/public/index.php data-fixture:import --append $2 $3 $4 $5;;
"valida") 
          php $DIR/public/index.php  orm:validate-schema $2 $3 $4 $5;
    echo  php $DIR/public/index.php  orm:validate-schema $2 $3 $4 $5;;
"atualiza") 
          php $DIR/public/index.php orm:schema-tool:update  --force --dump-sql $2 $3 $4 $5;
    echo  php $DIR/public/index.php orm:schema-tool:update  --force --dump-sql $2 $3 $4 $5;;
"pjt") 
          cd $DIR $2 $3 $4 $5;
    echo  cd $DIR $2 $3 $4 $5;;
"debug_on") 
          php $DIR/public/index.php setDebugOn $2 $3 $4 $5;
    echo  php $DIR/public/index.php setDebugOn $2 $3 $4 $5;;
"debug_off") 
          php $DIR/public/index.php setDebugOff $2 $3 $4 $5;
    echo  php $DIR/public/index.php setDebugOff $2 $3 $4 $5;;
*) 
    echo "Parametro invalido {$1}"
esac
