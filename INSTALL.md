# Installazione e Guida Utilizzo
## Plugin WordPress: Area Riservata

---

## 📋 Requisiti

- WordPress 5.0 o superiore
- PHP 7.4 o superiore
- MySQL 5.6 o superiore
- Supporto per `.htaccess` (Apache) o configurazione equivalente per Nginx

---

## 🚀 Installazione

### Metodo 1: Upload tramite WordPress Admin

1. Comprimere la cartella `area-riservata` in un file `.zip`
2. Accedere a WordPress Admin (`/wp-admin`)
3. Andare su **Plugin > Aggiungi nuovo**
4. Cliccare su **Carica plugin**
5. Selezionare il file `.zip` e cliccare **Installa ora**
6. Cliccare su **Attiva plugin**

### Metodo 2: Upload manuale via FTP

1. Caricare la cartella `area-riservata` nella directory `/wp-content/plugins/`
2. Accedere a WordPress Admin
3. Andare su **Plugin**
4. Trovare "Area Riservata" e cliccare **Attiva**

### Cosa succede all'attivazione

Il plugin automaticamente:
- ✅ Crea i ruoli custom: `portal_admin` e `portal_user`
- ✅ Crea le tabelle del database: `wp_ar_documents` e `wp_ar_audit_log`
- ✅ Crea la directory sicura: `wp-content/uploads/area-riservata-secure/`
- ✅ Genera il file `.htaccess` per bloccare l'accesso diretto ai file

---

## ⚙️ Configurazione Iniziale

### 1. Creare le Pagine WordPress

Creare le seguenti pagine tramite **Pagine > Aggiungi nuova**:

#### Pagina Registrazione
- **Titolo**: Registrazione
- **Slug**: `registrazione`
- **Contenuto**: `[ar_register]`
- **Pubblicare**

#### Pagina Login
- **Titolo**: Login
- **Slug**: `login`
- **Contenuto**: `[ar_login]`
- **Pubblicare**

#### Pagina Area Riservata (Utenti)
- **Titolo**: Area Riservata
- **Slug**: `area-riservata`
- **Contenuto**: `[ar_dashboard]`
- **Pubblicare**

#### Pagina Admin Area (Portal Admin)
- **Titolo**: Area Admin
- **Slug**: `area-admin`
- **Contenuto**: `[ar_admin]`
- **Pubblicare**

### 2. Creare il Primo Portal Admin

Poiché solo un amministratore WordPress può creare il primo Portal Admin:

1. Accedere a **Utenti > Aggiungi nuovo**
2. Compilare i campi (email, password, nome)
3. Nel campo **Ruolo**, selezionare **Admin Area Riservata**
4. Cliccare **Aggiungi nuovo utente**

⚠️ **IMPORTANTE**: Questo utente NON potrà accedere a `/wp-admin`, ma potrà gestire tutti gli utenti e documenti dal frontend (`/area-admin/`).

### 3. Testare l'accesso

1. **Logout** dall'account Administrator
2. Accedere alla pagina `/login/`
3. Usare le credenziali del Portal Admin appena creato
4. Verificare di essere reindirizzati a `/area-admin/`

---

## 📖 Guida Utilizzo

### Per Portal Admin

#### Gestire Richieste di Registrazione

1. Accedere a `/area-admin/`
2. Nel tab **"Richieste Pendenti"** vedere tutti gli utenti in attesa
3. Cliccare **Approva** per attivare l'utente
4. Cliccare **Rifiuta** per negare l'accesso

#### Caricare un Documento

1. Andare sul tab **"Carica Documento"**
2. Selezionare l'utente a cui assegnare il file
3. Selezionare il file (max 10MB)
4. Cliccare **Carica Documento**

**Formati supportati**: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP

#### Gestire Utenti

Nel tab **"Utenti"**:
- Vedere tutti gli utenti approvati
- **Disabilitare** temporaneamente un utente (può essere riabilitato)
- Vedere quanti documenti ha ogni utente

#### Creare Utente Manualmente

1. Tab **"Crea Utente"**
2. Compilare nome, cognome, email, password
3. Scegliere se approvare automaticamente
4. Cliccare **Crea Utente**

#### Monitorare le Attività

Nel tab **"Log Attività"** vedere:
- Tutte le azioni recenti
- Chi ha scaricato quali documenti
- Tentativi di accesso non autorizzati

---

### Per Portal User

#### Registrazione

1. Andare su `/registrazione/`
2. Compilare il form con nome, cognome, email e password
3. Cliccare **Registrati**
4. Attendere l'approvazione da parte di un Admin

#### Login

1. Andare su `/login/`
2. Inserire email e password
3. Se approvati, si verrà reindirizzati a `/area-riservata/`
4. Se pendenti, verrà mostrato un messaggio di attesa

#### Visualizzare e Scaricare Documenti

1. Accedere a `/area-riservata/`
2. Nella sezione **"I Miei Documenti"** vedere tutti i file assegnati
3. Cliccare **Scarica** per scaricare un file

---

## 🔒 Sicurezza

### Come funziona la protezione dei file

1. **Storage sicuro**: I file sono salvati in `wp-content/uploads/area-riservata-secure/` che è protetto da `.htaccess`
2. **Nessun URL diretto**: Non è possibile accedere ai file tramite URL diretto
3. **Download controllato**: Ogni download passa attraverso un sistema PHP che verifica:
   - L'utente è loggato?
   - L'utente è approvato?
   - Il file è assegnato a questo utente?
   - Il token (nonce) è valido?

4. **Link temporanei**: I link di download contengono un nonce che scade e non può essere riutilizzato

5. **Audit log**: Ogni azione viene registrata con timestamp e IP

### Blocco wp-admin per Portal Admin

Il ruolo **Portal Admin**:
- ❌ Non può accedere a `/wp-admin`
- ❌ Non può installare plugin
- ❌ Non può modificare il tema
- ✅ Può gestire utenti del portale
- ✅ Può caricare e gestire documenti
- ✅ Può vedere i log di audit

Se un Portal Admin prova ad accedere a `/wp-admin`, viene automaticamente reindirizzato alla pagina frontend `/area-admin/`.

---

## 🛠️ Personalizzazione

### Modificare i Formati File Consentiti

Editare il file `/includes/class-ar-documents.php` alla riga ~20:

```php
private $allowed_types = array(
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    // Aggiungere altri formati qui
);
```

### Modificare la Dimensione Massima File

Nel stesso file, linea ~32:

```php
// Max file size: 10MB (default)
private $max_file_size = 10485760;

// Per 20MB:
private $max_file_size = 20971520;

// Per 50MB:
private $max_file_size = 52428800;
```

⚠️ Verificare anche i limiti PHP in `php.ini`:
- `upload_max_filesize`
- `post_max_size`
- `memory_limit`

### Personalizzare gli Stili CSS

Il file CSS principale è in `/assets/css/area-riservata.css`.

Puoi sovrascrivere gli stili aggiungendo CSS custom nel tema:

```css
/* Nel file style.css del tema */
.ar-btn-primary {
    background: #your-color !important;
}
```

---

## 🔧 Troubleshooting

### Problema: I file non vengono caricati

**Soluzione**:
1. Verificare i permessi della cartella `wp-content/uploads/area-riservata-secure/`
2. Permessi consigliati: `755` per directory, `644` per file
3. Verificare i limiti PHP (vedere sopra)

### Problema: Portal Admin può ancora accedere a wp-admin

**Soluzione**:
1. Verificare che l'utente abbia SOLO il ruolo `portal_admin`
2. Se ha anche `administrator`, rimuoverlo
3. Svuotare la cache del browser

### Problema: Download dei file non funziona

**Soluzione**:
1. Verificare che `.htaccess` sia presente in `wp-content/uploads/area-riservata-secure/`
2. Verificare che il server supporti `.htaccess` (Apache)
3. Per Nginx, aggiungere questa configurazione:

```nginx
location ~* ^/wp-content/uploads/area-riservata-secure/ {
    deny all;
    return 403;
}
```

### Problema: Email di notifica non vengono inviate

**Soluzione**:
1. Installare un plugin SMTP come "WP Mail SMTP"
2. Configurare un servizio email affidabile
3. Testare l'invio email da WordPress

---

## 📊 Database

### Tabelle Create

#### `wp_ar_documents`
Memorizza i metadati dei documenti:
- ID documento
- ID utente assegnatario
- Nome file (sanitizzato e originale)
- Path completo
- Dimensione e tipo MIME
- Data upload
- Stato (active/deleted)

#### `wp_ar_audit_log`
Log di tutte le attività:
- ID utente che ha eseguito l'azione
- Tipo di azione
- ID documento (se applicabile)
- IP address
- Timestamp
- Dettagli aggiuntivi (JSON)

### Pulizia Database

Per pulire i log più vecchi di 1 anno (opzionale):

```php
// Aggiungere in functions.php del tema o in un cron job
$audit = AR_Audit::get_instance();
$audit->cleanup_old_logs(365); // 365 giorni
```

---

## 🗑️ Disinstallazione

### Disattivazione
La disattivazione del plugin:
- Mantiene i ruoli utente
- Mantiene le tabelle database
- Mantiene i file caricati

### Rimozione Completa

1. Disattivare il plugin
2. Eliminare il plugin da **Plugin > Plugin installati**
3. **Manualmente** eliminare (se desiderato):
   - Tabelle: `wp_ar_documents` e `wp_ar_audit_log`
   - Directory: `wp-content/uploads/area-riservata-secure/`
   - Ruoli: verranno rimossi automaticamente

---

## 📞 Supporto

Per problemi o domande:
- Verificare prima questa guida
- Controllare i log di WordPress (`/wp-content/debug.log` se WP_DEBUG attivo)
- Controllare i log del server

---

## ✅ Checklist Verifica Installazione

- [ ] Plugin attivato
- [ ] Tabelle database create
- [ ] Directory sicura creata
- [ ] File `.htaccess` presente
- [ ] Pagine WordPress create (registrazione, login, area-riservata, area-admin)
- [ ] Primo Portal Admin creato
- [ ] Testato login come Portal Admin
- [ ] Verificato blocco wp-admin per Portal Admin
- [ ] Testata registrazione nuovo utente
- [ ] Testato upload documento
- [ ] Testato download documento
- [ ] Verificato che URL diretto ai file sia bloccato

---

*Plugin sviluppato secondo i requisiti del documento README.md*
