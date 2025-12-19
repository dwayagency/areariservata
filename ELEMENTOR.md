# Guida Elementor - Area Riservata

## 🎨 Integrazione Elementor

Il plugin **Area Riservata** è completamente integrato con **Elementor Page Builder**, permettendoti di costruire pagine professionali usando i widget drag-and-drop.

---

## 📦 Widget Disponibili

Quando Elementor è attivo, troverai una nuova categoria **"Area Riservata"** nel pannello widget con 5 widget personalizzati:

### 1. 🔐 Login
**Nome Widget:** Area Riservata - Login  
**Uso:** Form di accesso per utenti registrati  
**Shortcode Equivalente:** `[ar_login]`

**Dove usarlo:**
- Pagina `/login`
- Sidebar
- Header (per login dropdown)

### 2. 📝 Registrazione
**Nome Widget:** Area Riservata - Registrazione  
**Uso:** Form di registrazione nuovi utenti  
**Shortcode Equivalente:** `[ar_register]`

**Dove usarlo:**
- Pagina `/registrazione`
- Landing page pubbliche

### 3. 📄 Dashboard Utente
**Nome Widget:** Area Riservata - Dashboard Utente  
**Uso:** Area privata con documenti dell'utente  
**Shortcode Equivalente:** `[ar_dashboard]`

**Dove usarlo:**
- Pagina `/area-riservata`
- Area membri protetta

**Visibilità:** Solo utenti loggati e approvati

### 4. ⚙️ Dashboard Admin
**Nome Widget:** Area Riservata - Dashboard Admin  
**Uso:** Pannello gestione per Portal Admin  
**Shortcode Equivalente:** `[ar_admin]`

**Dove usarlo:**
- Pagina `/area-admin`
- Sezione amministrativa frontend

**Visibilità:** Solo Portal Admin (non hanno accesso a wp-admin)

### 5. 🔑 Reset Password
**Nome Widget:** Area Riservata - Reset Password  
**Uso:** Recupero password dimenticata  
**Shortcode Equivalente:** `[ar_password_reset]`

**Dove usarlo:**
- Pagina `/password-reset`
- Link dal form login

---

## 🚀 Come Usare i Widget

### Metodo 1: Elementor Editor

1. **Apri/Crea una pagina** con Elementor
2. Nel pannello sinistro, cerca **"Area Riservata"**
3. **Trascina il widget** desiderato nella pagina
4. Il widget è pronto - nessuna configurazione richiesta!

### Metodo 2: Ricerca Rapida

1. Nell'editor Elementor, usa la **barra di ricerca**
2. Digita: `area riservata` o `login` o `dashboard`
3. I widget appariranno nei risultati

---

## 💡 Setup Consigliato con Elementor

### Struttura Pagine Raccomandata

```
Home (pubblica)
├── Login → Widget "Login"
├── Registrazione → Widget "Registrazione"
├── Password Reset → Widget "Reset Password"
│
Area Protetta (utenti loggati)
├── Area Riservata → Widget "Dashboard Utente"
│
Area Admin (Portal Admin)
└── Area Admin → Widget "Dashboard Admin"
```

### Template Elementor Pro

Se hai **Elementor Pro**, puoi creare:

**Header Personalizzato:**
- Aggiungi widget Login in un popup
- Mostra nome utente se loggato
- Link rapido a Dashboard

**Footer Protetto:**
- Link diretti alle aree riservate
- Solo per utenti loggati

**Popup Modale:**
- Login popup invece di pagina dedicata
- Registrazione in lightbox

---

## 🎨 Personalizzazione Design

### Colori del Plugin

I widget usano automaticamente i colori configurati in:
```
WordPress Admin > Impostazioni > Area Riservata Colori
```

Caratteristiche:
- **Sistema HSL** per flessibilità massima
- **Live Preview** delle modifiche
- **8 Preset** pronti all'uso
- Applica a **tutti i widget** contemporaneamente

### Stili Elementor

Anche se i colori principali sono gestiti globalmente, puoi:

1. **Aggiungere Margini/Padding**
   - Tab "Advanced" del widget
   - Sezione "Margin" e "Padding"

2. **Sfondi Custom**
   - Tab "Style" > Background
   - Utile per sezioni colorate

3. **Animazioni**
   - Tab "Advanced" > Motion Effects
   - Entrance animations per effetto WOW

---

## 📋 Esempi Pratici

### Esempio 1: Pagina Login Elegante

1. Crea nuova pagina "Login"
2. **Sezione Header:**
   - Heading: "Accedi all'Area Riservata"
   - Sottotitolo con descrizione
3. **Sezione Main:**
   - Widget "Login" centrato
   - Larghezza contenuto: 600px
4. **Sezione Footer:**
   - Text Editor: "Non hai un account?"
   - Button: Link a `/registrazione`

### Esempio 2: Dashboard con Sidebar

**Layout a 2 Colonne:**

**Colonna Sinistra (30%):**
- Widget Text: Benvenuto + nome utente
- Nav Menu: link veloci
- Widget HTML: istruzioni uso

**Colonna Destra (70%):**
- Widget "Dashboard Utente"
- Mostra documenti in tabella

### Esempio 3: Area Admin Professionale

**Sezione Hero:**
- Background sfumato
- Heading: "Pannello Amministrazione"
- Stats box (usando widget Counter)

**Sezione Main:**
- Widget "Dashboard Admin" full-width
- Tab integrate già presenti

---

## 🔧 Risoluzione Problemi

### Widget non appare nel pannello

**Soluzione:**
1. Verifica che Elementor sia installato e attivo
2. Svuota cache Elementor: Tools > Regenerate CSS
3. Ricarica l'editor

### Widget mostra solo anteprima placeholder

**Normale!** L'anteprima definitiva è visibile solo nel **frontend** per motivi di sicurezza.

### Stili non si applicano

1. Verifica che il file CSS del plugin sia caricato
2. Kontrolla: Elementor > Tools > Regenerate CSS & Data
3. Svuota cache browser

---

## 🎯 Best Practices

✅ **DO:**
- Usa un widget per pagina tematica
- Controlla visibilità con Elementor Pro (se disponibile)
- Testa sempre da loggato E non loggato
- Usa sezioni colorate per separare contenuti

❌ **DON'T:**
- Non mettere più widget Dashboard sulla stessa pagina
- Non usare widget Admin su pagine pubbliche
- Non dimenticare di linkare le pagine tra loro

---

## 📱 Responsive Design

I widget sono **100% responsive** per default:

- **Desktop:** Layout completo con tabelle
- **Tablet:** Tabelle scrollabili horizontal
- **Mobile:** Stack verticale ottimizzato

### Test Responsive in Elementor

1. Icona "Responsive Mode" (bottom bar)
2. Testa: Desktop > Tablet > Mobile
3. Aggiusta padding/margins se necessario

---

## 🚀 Funzionalità Avanzate

### Conditional Display (Elementor Pro)

Mostra widget solo a:
- Utenti loggati
- Ruoli specifici (portal_user, portal_admin)
- In base a user meta (ar_user_status)

### Dynamic Content (Elementor Pro)

Usa shortcode `[ar_dashboard]` in:
- Template Builder
- Theme Builder
- Archive pages

---

## ✅ Checklist Setup Completo

- [ ] Installato Elementor
- [ ] Plugin Area Riservata attivato
- [ ] Categoria "Area Riservata" visibile
- [ ] Creata pagina Login con widget
- [ ] Creata pagina Registrazione con widget
- [ ] Creata pagina Area Riservata con widget Dashboard
- [ ] Creata pagina Area Admin con widget Admin
- [ ] Creata pagina Password Reset con widget
- [ ] Personalizzati colori da wp-admin
- [ ] Testato da loggato/non loggato
- [ ] Verificato responsive mobile

---

## 💬 Supporto

Per assistenza:
1. Leggi [`INSTALL.md`](./INSTALL.md) per setup completo
2. Controlla [`SHORTCODES.md`](./SHORTCODES.md) per alternative
3. Vedi [`FEATURES.md`](./FEATURES.md) per feature complete

---

**🎉 Buon lavoro con Elementor e Area Riservata!**
