# ClickTime - Gerenciamento de Tempo e Lucro

Um sistema web para gerenciar tempo e calcular lucro mensal baseado no tempo gasto nas tasks do ClickUp.

## 🚀 Funcionalidades

- **Dashboard Intuitivo**: Visualize suas horas trabalhadas e ganhos em tempo real
- **Integração ClickUp**: Conecta diretamente com a API do ClickUp para buscar dados de tempo
- **Períodos Flexíveis**: Visualize dados por semana ou mês
- **Configuração Simples**: Apenas token da API e preço por hora
- **Top Tasks**: Veja quais tasks você gastou mais tempo
- **Armazenamento Local**: Suas configurações ficam salvas no navegador

## 📋 Pré-requisitos

- Servidor web com PHP (Apache, Nginx, etc.)
- PHP 7.0 ou superior
- Extensão cURL habilitada no PHP
- Token da API do ClickUp

## 🛠️ Instalação

1. **Clone ou baixe os arquivos** para seu servidor web:
   ```
   ClickTime/
   ├── index.html
   ├── api.php
   | ...
   └── README.md
   ```

2. **Configure seu servidor web** para servir os arquivos PHP

3. **Obtenha seu token da API do ClickUp**:
   - Acesse: https://clickup.com/api/developer-portal/authentication/#personal-token
   - Gere um Personal Token
   - Copie o token gerado

## 🎯 Como Usar

1. **Acesse o sistema** através do seu navegador

2. **Configure suas credenciais**:
   - Cole seu token da API do ClickUp
   - Defina seu preço por hora em R$
   - Clique em "Salvar Configurações"

3. **Visualize seus dados**:
   - Por padrão, o sistema mostra dados do mês atual
   - Use os botões para alternar entre "Esta Semana" e "Este Mês"
   - Veja suas horas trabalhadas, ganhos e top tasks

## 🔒 Segurança

- O token da API é armazenado apenas no localStorage do seu navegador
- As requisições são feitas server-side através do PHP
- Não há armazenamento permanente de dados sensíveis no servidor

## 🐛 Solução de Problemas

### "Erro ao carregar dados do ClickUp"
- Verifique se seu token da API está correto
- Confirme se você tem permissões nas tasks/projetos
- Verifique se a extensão cURL está habilitada no PHP

### "Método não permitido"
- Certifique-se de que o servidor está configurado para executar PHP
- Verifique se o arquivo api.php está acessível

### Dados não aparecem
- Confirme se você tem time entries registrados no período selecionado
- Verifique se as tasks estão atribuídas a você no ClickUp

## 📊 Como Funciona

1. **Frontend (HTML/JS)**: Interface do usuário e gerenciamento de estado
2. **Backend (PHP)**: Comunicação segura com a API do ClickUp
3. **API ClickUp**: Fonte dos dados de tempo e tasks
4. **LocalStorage**: Armazenamento das configurações do usuário

## 🤝 Contribuições

Sugestões e melhorias são bem-vindas! Sinta-se à vontade para:
- Reportar bugs
- Sugerir novas funcionalidades
- Contribuir com código

---

**Otimize seu controle de tempo e produtividade!**