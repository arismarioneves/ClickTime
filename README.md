# ClickTime - Gerenciamento de Tempo e Lucro

Um sistema web para gerenciar tempo e calcular lucro mensal baseado no tempo gasto nas tasks do ClickUp.

## 🚀 Funcionalidades

- **Dashboard Intuitivo**: Visualize horas trabalhadas, ganhos e total de tasks em tempo real
- **Integração ClickUp**: Conecta diretamente com a API do ClickUp para buscar dados de tempo
- **Perfil do Usuário**: Exibe nome, foto e workspace diretamente do ClickUp
- **Períodos Flexíveis**: Semana atual, mês atual, mês anterior ou período personalizado
- **Relatório Detalhado**: Tabela com Data, Projeto, Atividade, Horas, Status e Observação
- **Exportar CSV**: Exporta o relatório para CSV compatível com Excel
- **Copiar Tabela**: Copia o relatório formatado para colar em planilhas
- **Barra de Progresso**: Visualize o peso relativo de cada task no período
- **Armazenamento Local**: Configurações e perfil salvos no navegador

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
   ├── favicon.ico
   ├── logo.png
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
   - Por padrão exibe dados do mês atual
   - Alterne entre Esta Semana, Mês Atual, Mês Anterior ou Personalizado
   - Veja horas trabalhadas, ganhos estimados e total de tasks

4. **Exporte o relatório**:
   - Clique em "Gerar Relatório" para abrir a tabela detalhada
   - Use "Exportar CSV" para baixar o arquivo ou "Copiar Tabela" para colar em planilhas

## 🔒 Segurança

- O token da API é armazenado apenas no localStorage do navegador
- As requisições são feitas server-side via PHP
- Nenhum dado sensível é armazenado permanentemente no servidor

## 🐛 Solução de Problemas

### "Erro ao carregar dados do ClickUp"
- Verifique se seu token da API está correto
- Confirme se você tem permissões nas tasks/projetos
- Verifique se a extensão cURL está habilitada no PHP

### "Método não permitido"
- Certifique-se de que o servidor está configurado para executar PHP
- Verifique se o arquivo `api.php` está acessível

### Dados não aparecem
- Confirme se há time entries registrados no período selecionado
- Verifique se as tasks estão atribuídas a você no ClickUp

## 📊 Como Funciona

1. **Frontend (HTML/JS)**: Interface do usuário e gerenciamento de estado
2. **Backend (PHP)**: Comunicação segura com a API do ClickUp
3. **API ClickUp v2**: Fonte dos dados de tempo, tasks e perfil do usuário
4. **LocalStorage**: Armazenamento das configurações e cache do perfil

## 🤝 Contribuições

Sugestões e melhorias são bem-vindas! Sinta-se à vontade para:
- Reportar bugs
- Sugerir novas funcionalidades
- Contribuir com código

---

**Otimize seu controle de tempo e produtividade!**
