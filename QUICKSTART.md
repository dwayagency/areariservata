# Area Riservata - Guida Rapida

## 🎯 Setup in 5 Minuti

### 1️⃣ Attiva il Plugin
- Vai su **Plugin > Plugin installati**
- Trova "Area Riservata"
- Clicca **Attiva**

### 2️⃣ Crea le Pagine
Crea 4 pagine WordPress:

| Titolo | Slug | Contenuto |
|--------|------|-----------|
| Registrazione | `registrazione` | `[ar_register]` |
| Login | `login` | `[ar_login]` |
| Area Riservata | `area-riservata` | `[ar_dashboard]` |
| Area Admin | `area-admin` | `[ar_admin]` |

### 3️⃣ Crea il Primo Admin
- **Utenti > Aggiungi nuovo**
- Ruolo: **Admin Area Riservata**
- Inserisci email e password
- Salva

### 4️⃣ Testa il Sistema
1. **Logout** dall'account Administrator
2. Vai su `/login/`
3. Accedi con le credenziali del Portal Admin
4. Dovresti vedere `/area-admin/`

### 5️⃣ Primi Passi
- **Crea un utente di test** dal tab "Crea Utente"
- **Carica un documento** e assegnalo all'utente
- **Testa il download** accedendo come quell'utente

---

## 📱 Workflow Tipico

### Nuovo Utente
1. Utente va su `/registrazione/`
2. Compila il form
3. Riceve messaggio "In attesa di approvazione"
4. Admin riceve notifica email

### Portal Admin
1. Accede a `/area-admin/`
2. Vede richiesta nel tab "Richieste Pendenti"
3. Clicca **Approva**
4. Utente riceve email di approvazione
5. Admin carica documenti per l'utente

### Utente Approvato
1. Accede a `/login/`
2. Viene reindirizzato a `/area-riservata/`
3. Vede i suoi documenti
4. Scarica i file necessari

---

## 🔑 Ruoli

| Ruolo | Accesso | Permessi |
|-------|---------|----------|
| **Portal Admin** | `/area-admin/` (frontend) | Gestisce utenti e documenti, NO wp-admin |
| **Portal User** | `/area-riservata/` (frontend) | Vede solo i propri documenti |
| **Administrator** | `/wp-admin/` (backend) | Solo per manutenzione tecnica |

---

## 🔒 Sicurezza Garantita

✅ File non accessibili via URL diretto  
✅ Download solo tramite verifica PHP  
✅ Link con token temporaneo (nonce)  
✅ Log completo di ogni azione  
✅ Portal Admin bloccato da wp-admin  

---

## ⚠️ Checklist Finale

- [ ] Plugin attivato
- [ ] 4 pagine create con shortcodes
- [ ] Portal Admin creato
- [ ] Testato login Portal Admin
- [ ] Verificato blocco wp-admin
- [ ] Testato upload documento
- [ ] Testato download documento
- [ ] Verificato URL diretto bloccato

---

## 📖 Documentazione Completa

- **INSTALL.md** - Guida installazione dettagliata
- **SHORTCODES.md** - Reference shortcodes
- **README.md** - Requisiti del progetto

---

**Fatto! Il tuo portale sicuro è pronto! 🎉**
