# 🧪 Demo e Testing del Plugin Konverty

Questa cartella contiene risorse per testare il plugin senza dover installare un Magento completo.

---

## 📁 File Disponibili

### 1. **test-pixel-standalone.html** ⭐ Test Rapido
Pagina HTML standalone per testare il pixel JavaScript senza Magento.

**Caratteristiche:**
- ✅ Test tracciamento visite
- ✅ Test tracciamento vendite
- ✅ Gestione cookie visuale
- ✅ Console log integrata
- ✅ Simulazione ordini
- ⚡ **Funziona subito** senza installazioni

**Come usare:**
```bash
# Opzione 1: Apri direttamente nel browser
# (potrebbe avere limitazioni CORS)
open demo/test-pixel-standalone.html

# Opzione 2: Avvia server locale (raccomandato)
cd demo
python -m http.server 8080
# Poi apri: http://localhost:8080/test-pixel-standalone.html

# Opzione 3: Live Server (VS Code)
# Installa estensione "Live Server"
# Right-click su file → "Open with Live Server"
```

**Test disponibili:**
1. 📍 **Simula Visita** - Salva cookie `aff_*` e invia POST
2. 🛒 **Simula Vendita** - Traccia ordine fittizio con affiliazione
3. 🍪 **Mostra Cookie** - Visualizza cookie salvati
4. 📤 **Invia Dati Custom** - Test POST manuale all'endpoint

---

### 2. **DOCKER_SETUP.md** - Setup Completo Magento

Guida completa per installare Magento 2 con Docker in 15 minuti.

**Quando usare:**
- ✅ Vuoi testare il plugin in ambiente reale Magento
- ✅ Hai Docker Desktop installato
- ✅ Hai 8GB RAM disponibili

**Quick Start:**
```bash
# 1. Clone Docker Magento
git clone https://github.com/markshust/docker-magento
cd docker-magento

# 2. Setup (10 minuti)
bin/download 2.4.6 community
bin/setup magento.test

# 3. Installa plugin
cp -r ../magento-plugin/app/code/Konverty src/app/code/
bin/magento module:enable Konverty_AffiliateTracker
bin/magento setup:upgrade
bin/magento cache:flush

# 4. Test
open https://magento.test/?affiliate_id=TEST001
```

---

## 🎯 Quale Opzione Scegliere?

### Scenario 1: "Voglio solo vedere se il pixel funziona"
**→ Usa `test-pixel-standalone.html`**
- ⏱️ Tempo: 2 minuti
- 💻 Requisiti: Browser moderno
- ✅ Pro: Immediato, no setup
- ⚠️ Contro: Non testa integrazione Magento reale

### Scenario 2: "Voglio testare tutto (pixel + observer + webhook)"
**→ Usa `DOCKER_SETUP.md`**
- ⏱️ Tempo: 15-20 minuti
- 💻 Requisiti: Docker Desktop, 8GB RAM
- ✅ Pro: Ambiente completo, test realistici
- ⚠️ Contro: Richiede setup iniziale

### Scenario 3: "Ho già un Magento locale/server"
**→ Segui `../INSTALL.md`**
- ⏱️ Tempo: 5 minuti
- 💻 Requisiti: Magento 2.3+ funzionante
- ✅ Pro: Usa infrastruttura esistente
- ⚠️ Contro: Devi avere Magento già installato

---

## 📊 Confronto Opzioni Testing

| Feature | Standalone HTML | Docker Magento | Magento Esistente |
|---------|----------------|----------------|-------------------|
| **Setup Time** | 2 min | 15 min | 5 min |
| **Test Pixel JS** | ✅ | ✅ | ✅ |
| **Test Observer** | ❌ | ✅ | ✅ |
| **Test Webhook** | ❌ | ✅ | ✅ |
| **Test Admin Config** | ❌ | ✅ | ✅ |
| **Ordini Reali** | ❌ | ✅ | ✅ |
| **Requisiti** | Browser | Docker | Magento |
| **Complessità** | 🟢 Facile | 🟡 Media | 🟡 Media |

---

## 🚀 Quick Start - Test Pixel Standalone

### 1. Avvia Server Locale

```bash
cd demo

# Python 3
python -m http.server 8080

# Python 2
python -m SimpleHTTPServer 8080

# Node.js (se hai http-server installato)
npx http-server -p 8080

# PHP
php -S localhost:8080
```

### 2. Apri Browser

```
http://localhost:8080/test-pixel-standalone.html
```

### 3. Test Sequenza

**Test A: Visita**
1. Click "📍 Simula Visita con Affiliate"
2. Verifica cookie salvati (click "🍪 Mostra Cookie")
3. Apri DevTools → Network
4. Cerca POST a `trackShopify.jsp`

**Test B: Vendita**
1. Assicurati di avere cookie attivi (Test A)
2. Click "🛒 Simula Ordine Completato"
3. Verifica POST con `type: "sale"`
4. Controlla che cookie siano eliminati dopo 1 secondo

**Test C: Console**
1. Osserva il log in tempo reale nella sezione "📊 Console Log"
2. Ogni azione viene loggata con timestamp

---

## 🐛 Troubleshooting Demo

### Test Standalone HTML

**Problema: "CORS error" nella console**
```
❌ Access to fetch at 'https://admin.konverty.com/trackShopify.jsp' 
   from origin 'file://' has been blocked by CORS policy
```
**Soluzione:** Usa server locale invece di aprire file direttamente:
```bash
python -m http.server 8080
```

**Problema: Cookie non salvati**
- ✅ Usa `http://localhost:8080` invece di `file://`
- ✅ Verifica che browser non blocchi cookie
- ✅ Chrome: Settings → Privacy → Allow cookies

**Problema: POST non visibile nel Network tab**
```javascript
// Il pixel usa mode: 'no-cors' quindi la risposta non è visibile
// Questo è normale! Controlla invece:
```
1. Tab Network → Filtra: `trackShopify`
2. Status dovrebbe essere `(unknown)` per no-cors
3. Verifica su backend che la richiesta sia arrivata

### Docker Setup

**Problema: "Port 80 already in use"**
```bash
# Windows - Ferma IIS/XAMPP
net stop w3svc

# Mac - Ferma Apache
sudo apachectl stop

# Verifica porta
netstat -ano | findstr :80
```

**Problema: Container molto lento**
```bash
# Aumenta RAM Docker Desktop
# Settings → Resources → Memory: 8GB

# Disabilita 2FA per velocizzare
bin/magento module:disable Magento_TwoFactorAuth
```

**Problema: "Magento not found"**
```bash
# Ricontrolla download
bin/download 2.4.6 community

# Verifica src/
ls -la src/
# Dovrebbe contenere app/, bin/, etc.
```

---

## 📸 Screenshot Demo Standalone

```
┌─────────────────────────────────────────────────────┐
│  🧪 Test Konverty Pixel - Standalone               │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Test 1: Tracciamento Visite [Nessun Cookie]      │
│  ┌───────────────────────────────────────────────┐ │
│  │ [📍 Simula Visita] [🍪 Mostra Cookie]        │ │
│  │                                               │ │
│  │ Cookie Salvati:                               │ │
│  │ • aff_affiliate_id=TEST001                    │ │
│  │ • aff_utm_source=facebook                     │ │
│  │ • aff_utm_campaign=summer2025                 │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  Test 2: Tracciamento Vendita                      │
│  ┌───────────────────────────────────────────────┐ │
│  │ [🛒 Simula Ordine Completato]                │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  📊 Console Log                                     │
│  ┌───────────────────────────────────────────────┐ │
│  │ [10:30:15] ✓ Pagina test caricata            │ │
│  │ [10:30:20] Simulando visita con affiliate... │ │
│  │ [10:30:21] ✓ Cookie salvati con successo     │ │
│  │ [10:30:22] ✓ POST inviato a trackShopify.jsp │ │
│  │ [10:31:05] Simulando vendita...              │ │
│  │ [10:31:06] ✓ Vendita tracciata               │ │
│  │ [10:31:07] ⚠ Cookie eliminati dopo 1 secondo │ │
│  └───────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

---

## 📚 Risorse Aggiuntive

### Per Test Standalone
- MDN Web Docs - Fetch API: https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
- Chrome DevTools: https://developer.chrome.com/docs/devtools/

### Per Docker Magento
- Docker Magento Repo: https://github.com/markshust/docker-magento
- Magento DevDocs: https://devdocs.magento.com/
- Docker Docs: https://docs.docker.com/

### Video Tutorial (da creare)
- [ ] Setup Docker Magento (YouTube)
- [ ] Test Pixel Standalone (Loom)
- [ ] Walkthrough completo plugin (YouTube)

---

## 🎓 Prossimi Passi

Dopo aver testato con demo:

1. **Se tutto funziona** → Installa su Magento production
   - Segui `../INSTALL.md`
   - Configura endpoint reali
   - Test su staging prima di production

2. **Se trovi bug** → Report
   - Documenta nel file `TESTING.md`
   - Include log console e network
   - Specifica browser e versione

3. **Se vuoi personalizzare** → Developer Mode
   - Modifica `konverty-pixel.js`
   - Test con standalone prima
   - Deploy su Magento dopo verifica

---

## ✅ Checklist Testing

### Test Standalone HTML
- [ ] Server locale avviato
- [ ] Pagina aperta su `localhost:8080`
- [ ] DevTools aperto (F12)
- [ ] Test visita eseguito
- [ ] Cookie verificati
- [ ] Test vendita eseguito
- [ ] POST visibili in Network tab
- [ ] Console log pulito (no errori)

### Test Docker Magento
- [ ] Docker Desktop running
- [ ] Magento installato e accessibile
- [ ] Plugin copiato e abilitato
- [ ] Configurazione completata in admin
- [ ] Test visita frontend
- [ ] Test ordine completo
- [ ] Test webhook (shipment/cancel)
- [ ] Log controllati (`bin/magento tail`)

---

**Buon Testing!** 🚀

Per domande o problemi, consulta:
- `../README.md` - Documentazione completa
- `../TESTING.md` - Guida test avanzati
- `../INSTALL.md` - Installazione production


