# 🗂️ Gestor Kanban

Sistema web de gerenciamento de projetos baseado na metodologia **Kanban**, desenvolvido com **Laravel**.

---

## 🚀 Funcionalidades

* 📌 Criação de projetos (quadros)
* 📋 Colunas fixas:

  *Colunas podem ser editadas, colocando nomes de acordo com a necessidade. 

* 🧩 Criação e movimentação de tarefas
* 👤 Definição de responsável por tarefa
* 📅 Controle de datas (início e conclusão)
* 🎨 Identificação visual por cores (baseado no prazo)
* 🔐 Sistema de autenticação (login e cadastro)

---

## 🛠️ Tecnologias utilizadas

* PHP
* Laravel
* MySQL
* HTML5
* CSS3
* JavaScript

---

## ⚙️ Como rodar o projeto

### 1. Clonar o repositório

```bash
git clone https://github.com/Klisnmanny/gestor-kanban.git
```

### 2. Acessar a pasta

```bash
cd gestor-kanban
```

### 3. Instalar dependências

```bash
composer install
```

### 4. Criar arquivo .env

```bash
cp .env.example .env
```

### 5. Configurar banco de dados

No arquivo `.env`, configure:

```env
DB_DATABASE=nome_do_banco
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Gerar chave da aplicação

```bash
php artisan key:generate
```

### 7. Rodar migrations

```bash
php artisan migrate
```

### 8. Iniciar o servidor

```bash
php artisan serve
```

---

## 🔐 Segurança

* Proteção contra CSRF
* Validação de dados no backend
* Senhas criptografadas
* Controle de acesso por usuário

---

## 📊 Objetivo do projeto

Este projeto foi desenvolvido com foco em:

* Prática de desenvolvimento com Laravel
* Estruturação de sistemas reais
* Organização de tarefas e fluxo de trabalho

---

## 📌 Melhorias futuras

* Drag and drop nas tarefas
* Notificações em tempo real
* Sistema de comentários nas tarefas
* Dashboard com métricas

---

## 👨‍💻 Autor

Desenvolvido por **Washington Klisnmanny**

---

## 📄 Licença

Este projeto está sob a licença MIT.
