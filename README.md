# Documento di Raccolta Requisiti  
## Plugin WordPress Custom – Area Riservata con Gestione Documenti e Registrazione Approvata

---

## 1. Obiettivo del progetto

Sviluppo di un **plugin WordPress custom** che permetta la creazione di un’**area riservata** con le seguenti caratteristiche principali:

- Registrazione utenti dal frontend
- Approvazione manuale degli utenti da parte di un **Admin Area Riservata**
- Gestione documenti sensibili per singolo utente
- Area Admin **esclusivamente nel frontend**
- **Separazione totale** dal ruolo Administrator di WordPress
- Accesso ai file **strettamente riservato** al singolo utente assegnatario

Il sistema deve essere progettato considerando che i file possono contenere **dati sensibili**.

---

## 2. Ruoli e stati utente

### 2.1 Ruoli (obbligatori)

#### Admin Area Riservata (Portal Admin)
- Ruolo custom creato dal plugin
- Gestisce utenti e file **solo dal frontend**
- NON ha accesso al backend WordPress (`/wp-admin`)
- NON può:
  - installare plugin
  - modificare temi
  - accedere a impostazioni WordPress
  - gestire utenti WordPress globali al di fuori del perimetro del portale

#### Utente Area Riservata (Portal User)
- Utente registrato al portale
- Può accedere all’area riservata solo se approvato
- Può vedere e scaricare **solo** i file assegnati a lui

#### Administrator WordPress
- Ruolo WordPress standard
- Non necessario per l’uso operativo quotidiano del portale
- Serve solo per manutenzione tecnica del sito

---

### 2.2 Stati utente

Gli utenti del portale devono avere uno **stato**:

- `pending` – registrato ma non approvato
- `approved` – approvato, accesso consentito
- `rejected` / `disabled` – accesso negato

---

## 3. Registrazione e approvazione utenti

### 3.1 Registrazione frontend
- Form di registrazione accessibile pubblicamente
- Campi minimi:
  - Email
  - Password (o password generata)
  - Nome (eventuale cognome)
- Alla registrazione:
  - l’utente viene creato con stato `pending`
  - non ha accesso ai documenti

### 3.2 Approvazione
- L’Admin Area Riservata può:
  - approvare
  - rifiutare
  - disabilitare un utente
- L’approvazione abilita l’accesso all’area riservata

### 3.3 Blocco utenti pending
- Gli utenti `pending` **non devono poter accedere ai documenti**
- La modalità di blocco deve essere configurabile:
  - Login consentito ma area bloccata con messaggio
  - oppure login completamente negato

---

## 4. Area Admin (Frontend)

### 4.1 Accesso
- Accessibile solo agli utenti con ruolo **Admin Area Riservata**
- Accesso esclusivamente frontend

### 4.2 Funzionalità principali
- Dashboard con sezioni:
  - Richieste utenti pending
  - Gestione utenti approvati/disabilitati
  - Gestione file/documenti

### 4.3 Gestione utenti
- Visualizzazione elenco utenti del portale
- Azioni:
  - approva / rifiuta
  - disabilita / riattiva
  - reset password
  - creazione manuale utenti (opzionale ma prevista)

---

## 5. Gestione documenti (dati sensibili)

### 5.1 Caricamento file
- Upload file dal frontend (Admin Area Riservata)
- Validazioni obbligatorie:
  - whitelist estensioni
  - dimensione massima
  - sanitizzazione nome file

### 5.2 Assegnazione file
- Ogni file deve essere associato ad **un singolo utente**
- L’utente assegnato è l’unico che può vederlo e scaricarlo

### 5.3 “Cartella” utente
- Concetto di cartella per utente:
  - logica (raggruppamento UI)
  - opzionalmente fisica lato filesystem
- L’Admin vede i file raggruppati per utente

---

## 6. Area utente (Frontend)

### 6.1 Dashboard utente
- Accessibile solo agli utenti `approved`
- Sezione “I miei documenti”
- Visualizza solo i file assegnati all’utente loggato

### 6.2 Download
- Download diretto solo tramite sistema controllato
- Nessun accesso diretto al percorso reale del file

---

## 7. Sicurezza (requisiti critici)

### 7.1 File non pubblici
- I file **non devono essere accessibili pubblicamente**
- Non devono essere raggiungibili tramite URL diretto
- Preferibilmente salvati:
  - fuori dal web root
  - oppure in directory non servita direttamente dal web server

### 7.2 Download protetto
Ogni download deve:
1. verificare che l’utente sia loggato
2. verificare che l’utente sia `approved`
3. verificare che il file sia assegnato a quell’utente
4. servire il file tramite PHP (proxy), non direttamente

### 7.3 Protezione link
- I link di download non devono essere riutilizzabili
- Devono includere token/nonce o controllo sessione
- La condivisione di un link non deve consentire accessi non autorizzati

### 7.4 Blocco backend WordPress
- Il ruolo **Admin Area Riservata**:
  - non deve poter accedere a `/wp-admin`
  - deve essere reindirizzato al frontend
- Tutti i controlli devono essere server-side

### 7.5 Audit (consigliato)
- Log di:
  - upload file
  - assegnazione file
  - download file
  - tentativi di accesso non autorizzati

---

## 8. Requisiti non funzionali

- Sicurezza elevata (dati sensibili)
- Interfaccia semplice e integrata nel tema
- Scalabilità (gestione numerosi utenti e file)
- Manutenibilità del codice
- Compatibilità con plugin di sicurezza/cache standard

---

## 9. Fuori scope (non richiesto)
- Pagamenti o abbonamenti
- Firma digitale documenti
- Upload file da parte dell’utente
- Integrazioni CRM o sistemi esterni

---

## 10. Criteri di accettazione

1. Un Admin Area Riservata non può accedere a `/wp-admin`
2. Un utente pending non può accedere ai documenti
3. Un utente approvato vede solo i propri file
4. Un URL diretto al file non consente il download
5. Un file condiviso non è accessibile da altri utenti

---

## 11. Note finali

Questo documento definisce **cosa** il plugin deve fare.  
Le scelte implementative (hook, CPT, REST, filesystem) sono demandate alla fase di specifica tecnica e sviluppo, nel rispetto dei requisiti sopra descritti.

---
