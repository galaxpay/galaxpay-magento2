<p align="center"><img src ="https://www.galaxpay.com.br/view/general/images/logo-verde.png" width="40%" height="40%" /></p>

##
# Galax Pay - Magento 2

# Descrição
A integração do módulo da Galax Pay permite a venda através do Magento 2 de forma transparente.
Formas de pagamento: Cartão de Crédito, Boleto e Pix. (Lembrando que estas deverão estar ativas na conta da empresa no Galax Pay)

# Requisitos
- PHP **7.x.x** ou superior
- MySQL **5.6.x** ou superior
- cURL habilitado para o PHP
- Certificado SSL
- Conta ativa no [Galax Pay](https://www.galaxpay.com.br "Galax Pay")
- Não são permitidas compras como convidado.
- É mandatório instalação dos módulos abaixo citados no item 3 da Instalação para que o plugin funcione corretamente em uma conta padrão Galax Pay.
- Deverá estar habilitado na conta o módulo de webservice.

# Instalação
É possível realizar a instalação do módulo da Galax Pay para Magento 2 via [.zip](https://github.com/galaxpay/galaxpay-magento2/archive/main.zip), via [Git](https://github.com) ou via [Composer](https://getcomposer.org).

#### Mais indicada: via [composer](https://getcomposer.org)
- Vá até o diretório raíz do Magento e adicione o módulo
> `composer require galaxpay/galaxpay-magento2`
- Atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **GalaxPay_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`
- Habilitar o módulo do Galax Pay
`bin/magento module:enable GalaxPay_Payment`

#### Via [git](https://github.com)
- Vá até o diretório raíz do Magento e adicione o módulo
> `git clone https://github.com/galaxpay/galaxpay-magento2.git app/code/GalaxPay/Payment/`
- Atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **GalaxPay_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`

#### Via [.zip](https://github.com/galaxpay/galaxpay-magento2/archive/main.zip)
- Crie a(s) seguinte(s) pasta(s) dentro da pasta **app** do Magento
> `code/GalaxPay/Payment`
- Faça o download do [.zip]
- O caminho deve ser **app/code/GalaxPay/Payment**
- Extraia os arquivos do **.zip** na pasta **Payment**
- No diretório raíz, atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **GalaxPay_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`



# Configuração

1. Configurando sua conta no Magento
    - No painel de Administração do Magento, acesse *Galax Pay -> Configuração*
      - Selecione o ambiente que seja utilizar (sandbox ou produção) 
      - Informe o ID da sua conta Galax Pay
      - Informe o token da API de sua conta Galax Pay
      - Você deve copiar o link de configuração dos Webhooks, para inseri-lo na plataforma da Galax Pay dentro do módulo Webservice. Também nesse módulo você deverá marcar para receber o webhook "transaction.updateStatus" da APIv2
      - Após informar a url do webhook no Galax Pay, será gerado um "Token de segurança do Webhook" que deverá ser usado para preencher o campo Token Webhook dentro da configuração do magento.
2. Habilitando/Configurando os métodos de pagamento
    - Em *Lojas -> Vendas -> Métodos de pagamento*, configure e habilite o método de pagamento **Galax Pay - Cartão de Crédito**,
    **Galax Pay - Boleto** e **Galax Pay - Pix**
3. Instalação de módulos adicionais obrigatórios:
      - https://github.com/m2-systemcode/Base
      - https://awesomeopensource.com/project/m2-systemcode/BrazilCustomerAttributes?categoryPage
        Você deverá configurar o módulo  com CPF e CNPJ obrigatórios e únicos.
      - https://github.com/m2-magedev/BrazilZipCode 


## Dúvidas
Caso necessite de informações sobre a plataforma ou a API, por favor, siga através do canal 
https://docs.galaxpay.com.br/