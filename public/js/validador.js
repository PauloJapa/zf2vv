$().ready(function() {

   // Atribuindo a função validate para o formulário form-contato
    $('#form-contato').validate({
        
        // Função que determinada as regras de validação do formulário
         rules:{

            // Pegando o campo CPF para inserir regras de validação
            cpf: {
                // o required faz com que o preenchimento do campo sejá obrigatório
                required : true,
                // o cpf faz com que o cpf digitado seja um cpf valido
                cpf      : 'both'
            },

            // Pegando o campo CNPJ para inserir regras de validação
            cnpj: {
                // o required faz com que o preenchimento do campo sejá obrigatório
                required : true,
                // o CNPJ faz com que o cpf digitado seja um CNPJ valido
                cnpj     : 'both'
            },

        },

        // Atribuindo mensagens personalizadas para as validações
        messages:{
            
            // Seleciona as mensagens do campo CPF
            cpf: {
                // Atribui uma mensagem padrão para o required do CPF
                required : "O CPF é obrigatório.",
                // Atribui uma mensagem padrão para a função CPF do campo CPF
                cpf      : "O CPF digitado é invalido"
            },

            // Seleciona as mensagens do campo CNPJ
            cnpj: {
                // Atribui uma mensagem padrão para o required do CNPJ
                required : "O CNPJ é obrigatório.",
                // Atribui uma mensagem padrão para a função CNPJ do campo CNPJ
                cnpj     : "O CNPJ digitado é invalido"
            },

        } 

    });

});