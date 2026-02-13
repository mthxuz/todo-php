# To-Do PHP

Mini projeto CRUD em PHP utilizando PDO + SQLite.

## 🚀 Como rodar

```bash
cp .env.example .env
php -S 0.0.0.0:8000
'''
Acesse: `http://localhost:8000`

## 🔐 Boas práticas de segurança aplicadas

- Configuração sensível em `.env` (ex.: caminho do banco).
- Arquivo `.env` ignorado no Git via `.gitignore`.
- Banco movido para `storage/` e com tentativa de permissão restrita (`0600`) na criação.
- Ações sensíveis (`toggle`, `delete`, `edit`, `add`) usam `POST` + proteção CSRF.
- Headers de segurança básicos adicionados:
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: no-referrer`

## 🧯 Procedimentos recomendados para repositório público

1. Remover qualquer segredo já commitado anteriormente (se houver).
2. Rotacionar credenciais que já foram expostas.
3. Configurar *branch protection* e revisão obrigatória.
4. Ativar varredura de segredo no GitHub (Secret scanning / Dependabot).
5. Usar variáveis de ambiente na hospedagem (nunca subir `.env`).