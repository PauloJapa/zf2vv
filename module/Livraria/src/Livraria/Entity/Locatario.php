<?php

namespace Livraria\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Locatario
 * Entity para manipular os registros de locatarios
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 *
 * @ORM\Table(name="locatario")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Livraria\Entity\LocatarioRepository")
 */
class Locatario
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $nome
     *
     * @ORM\Column(name="nome", type="string", length=200, nullable=false)
     */
    private $nome;

    /**
     * @var string $tipo
     *
     * @ORM\Column(name="tipo", type="string", length=10, nullable=false)
     */
    private $tipo;

    /**
     * @var string $cpf
     *
     * @ORM\Column(name="cpf", type="string", length=45, nullable=true)
     */
    private $cpf;

    /**
     * @var string $cnpj
     *
     * @ORM\Column(name="cnpj", type="string", length=45, nullable=true)
     */
    private $cnpj;

    /**
     * @var string $tel
     *
     * @ORM\Column(name="tel", type="string", length=255, nullable=true)
     */
    private $tel;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

    /**
     * @var Enderecos
     *
     * @ORM\ManyToOne(targetEntity="Livraria\Entity\Endereco")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="enderecos_id", referencedColumnName="id")
     * })
     */
    private $endereco;

    /** 
     * Instacia um novo objeto se passado o parametro de dados
     * Faz automaticamente todos os seters com a classe configurator
     * Tambem carrega as data de criadoEm e alteradoEm atuais 
     * @param Array $option
     */    
    public function __construct($options = null) {
        Configurator::configure($this, $options);
    }
    
    /**
     * 
     * @return int $id do registro
     */
    public function getId() {
        return $this->id;
    }


    /** 
     * Setar o id do registro
     * @param Int $id
     * @return \Livraria\Entity\Locatario
     */ 
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Nome do locatario
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * 
     * @param string $nome
     * @return \Livraria\Entity\Locatario
     */
    public function setNome($nome) {
        $this->nome = $nome;
        return $this;
    }

    /**
     * Valores Fisica|Juridica
     * @return String
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Valores Fisica|Juridica
     * @param string $tipo
     * @return \Livraria\Entity\Locatario
     */
    public function setTipo($tipo) {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * CPF no formato 000.000.000-00
     * @return string 
     */
    public function getCpf() {
        return $this->cpf;
    }

    /**
     * CPF no formato 000.000.000-00
     * @param string $cpf
     * @return \Livraria\Entity\Locatario
     */
    public function setCpf($cpf) {
        //$this->cpf = $this->formatarCPF_CNPJ($cpf);
        $this->cpf = $cpf;
        return $this;
    }

    /**
     * CNPJ no formato 00.000.000/0001-00
     * @return string
     */
    public function getCnpj() {
        return $this->cnpj;
    }

    /**
     * 
     * @param string $cnpj
     * @return \Livraria\Entity\Locatario
     */
    public function setCnpj($cnpj) {
        //$this->cnpj = $this->formatarCPF_CNPJ($cnpj);
        $this->cnpj = $cnpj;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getTel() {
        return $this->tel;
    }

    /**
     * 
     * @param string $tel
     * @return \Livraria\Entity\Locatario
     */
    public function setTel($tel) {
        $this->tel = $tel;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * 
     * @param string $email
     * @return \Livraria\Entity\Locatario
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * A = ativo, B = bloqueado, C = cancelado
     * @return string 
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * A = ativo, B = bloqueado, C = cancelado
     * @param string $status
     * @return \Livraria\Entity\Locatario
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * Dados do endereço do locatario se houver
     * @return \Livraria\Entity\Endereco
     */
    public function getEndereco() {
        return $this->endereco;
    }

    /**
     * 
     * @param \Livraria\Entity\Endereco $endereco
     * @return \Livraria\Entity\Locatario
     */
    public function setEndereco(Endereco $endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    /**
     * 
     * @return array com todos os campos formatados para o form
     */
    public function toArray() {
        $data = $this->getEndereco()->toArray();
        $data['id']    = $this->getId();
        $data['nome']  = $this->getNome();
        $data['tipo']  = $this->getTipo();
        $data['cpf']   = $this->getCpf();
        $data['cnpj']  = $this->getCnpj();
        $data['tel']   = $this->getTel();
        $data['email'] = $this->getEmail();
        $data['status'] = $this->getStatus();
        return $data ;
    }
    
    /**
     * Metodo magico para retornar o nome do locatario
     * @return string
     */
    public function __toString() {
        return $this->nome;
    }

    /**
     * Coloca a mascara no campo digitado 
     * Ou retorna campo limpo livre da formatação
     * @param string  $campo
     * @param boolean $formatado
     * @return string 
     */
    public function formatarCPF_CNPJ($campo, $formatado = true){
	//retira formato
	$codigoLimpo = ereg_replace("[' '-./ t]",'',$campo);
	// pega o tamanho da string menos os digitos verificadores
	$tamanho = (strlen($codigoLimpo) -2);
	//verifica se o tamanho do código informado é válido
	if ($tamanho != 9 && $tamanho != 12){
		return false; 
	}
 
	if ($formatado){ 
		// seleciona a máscara para cpf ou cnpj
		$mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##'; 
 
		$indice = -1;
		for ($i=0; $i < strlen($mascara); $i++) {
			if ($mascara[$i]=='#') $mascara[$i] = $codigoLimpo[++$indice];
		}
		//retorna o campo formatado
		$retorno = $mascara;
 
	}else{
		//se não quer formatado, retorna o campo limpo
		$retorno = $codigoLimpo;
	}
 
	return $retorno;
 
    }

}
