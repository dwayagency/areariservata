# Area Riservata - Shortcodes Reference

## Shortcodes Disponibili

### `[ar_register]`
**Descrizione**: Mostra il form di registrazione per nuovi utenti.

**Utilizzo**:
```
[ar_register]
```

**Dove usarlo**: Pagina "Registrazione" pubblica

**Comportamento**:
- Se l'utente è già loggato, mostra un messaggio
- Raccoglie: nome, cognome, email, password
- Crea utente con stato `pending`
- Invia notifica agli admin

---

### `[ar_login]`
**Descrizione**: Mostra il form di login per utenti esistenti.

**Utilizzo**:
```
[ar_login]
```

**Dove usarlo**: Pagina "Login"

**Comportamento**:
- Se già loggato, mostra messaggio + link logout
- Permette login con email e password
- Opzione "Ricordami"
- Reindirizza a `/area-riservata/` dopo login

---

### `[ar_dashboard]`
**Descrizione**: Mostra la dashboard utente con i documenti assegnati.

**Utilizzo**:
```
[ar_dashboard]
```

**Dove usarlo**: Pagina "Area Riservata" (solo per utenti loggati)

**Comportamento**:
- Richiede login
- Se l'utente è `pending`, mostra messaggio di attesa
- Se `approved`, mostra tabella con documenti
- Permette il download dei documenti assegnati

---

### `[ar_admin]`
**Descrizione**: Mostra la dashboard admin per gestire utenti e documenti.

**Utilizzo**:
```
[ar_admin]
```

**Dove usarlo**: Pagina "Area Admin" (solo per Portal Admin)

**Comportamento**:
- Richiede login + capability `ar_manage_users`
- Mostra tabs:
  - Richieste Pendenti
  - Utenti
  - Documenti
  - Carica Documento
  - Crea Utente
  - Log Attività

---

## Esempi di Implementazione

### Pagina Registrazione
```
Titolo pagina: Registrazione
Slug: registrazione

Contenuto:
[ar_register]
```

### Pagina Login
```
Titolo pagina: Login
Slug: login

Contenuto:
[ar_login]
```

### Pagina Area Riservata
```
Titolo pagina: Area Riservata
Slug: area-riservata

Contenuto:
[ar_dashboard]
```

### Pagina Area Admin
```
Titolo pagina: Area Admin
Slug: area-admin

Contenuto:
[ar_admin]
```

---

## Link di Navigazione Consigliati

Aggiungere questi link nel menu WordPress:

- **Home** → `/`
- **Registrazione** → `/registrazione/`
- **Login** → `/login/`
- **Area Riservata** → `/area-riservata/` (mostra solo se loggato)
- **Area Admin** → `/area-admin/` (mostra solo per Portal Admin)

---

## Note sulla Sicurezza

✅ I shortcodes gestiscono automaticamente:
- Controllo autenticazione
- Controllo permessi
- Verifica stato utente (pending/approved)
- Protezione CSRF con nonce
- Sanitizzazione input

❌ Non è necessario aggiungere controlli custom.
