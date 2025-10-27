# 📦 Installazione via Composer - Konverty Affiliate Tracker

Guida per installare il plugin Konverty tramite Composer (metodo consigliato).

---

## ✅ PREREQUISITI

- Magento 2.3.x o 2.4.x installato
- Composer installato
- Accesso SSH al server
- Permessi di scrittura su cartella Magento

---

## 🚀 INSTALLAZIONE RAPIDA (3 comandi)

### Step 1: Aggiungi Repository

```bash
cd /path/to/your/magento

composer config repositories.konverty-tracker vcs https://github.com/konverty/magento2-affiliate-tracker
```

### Step 2: Installa Plugin

```bash
composer require konverty/magento2-affiliate-tracker
```

### Step 3: Abilita e Configura

```bash
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

✅ **FATTO!** Il plugin è installato.

---

## ⚙️ CONFIGURAZIONE

### 1. Accedi all'Admin Magento

```
https://tuosito.com/admin
```

### 2. Vai alla Configurazione

```
Stores → Configuration → Konverty → Affiliate Tracker
```

### 3. Imposta Questi Valori

| Campo | Valore | Note |
|-------|--------|------|
| **Enable Tracking** | `Yes` | Abilita il tracking |
| **Tracking Endpoint URL** | `https://admin.konverty.com/trackShopify.jsp` | Fornito da Konverty |
| **Webhook Endpoint URL** | `https://admin.konverty.com/webhookShopify.jsp` | Fornito da Konverty |
| **Cookie Lifetime** | `60` giorni | Default raccomandato |
| **Debug Mode** | `No` | Solo per test |

### 4. Salva Configurazione

- Click **"Save Config"**
- Click **"Flush Cache"** (in alto a destra)

---

## 🧪 TEST FUNZIONAMENTO

### Test 1: Verifica Pixel Caricato

**1. Visita il tuo sito:**
```
https://tuosito.com
```

**2. Apri Developer Tools (F12)**

**3. Vai alla tab "Network"**

**4. Ricarica pagina**

**5. Cerca file:**
```
konverty-pixel.js
```

✅ **Se presente:** Pixel caricato correttamente

---

### Test 2: Test Affiliazione

**1. Visita con parametro affiliate:**
```
https://tuosito.com/?affiliate_id=TEST123
```

**2. In Developer Tools → Application → Cookies**

**3. Cerca cookie:**
```
aff_affiliate_id = TEST123
```

✅ **Se presente:** Tracking funzionante

---

### Test 3: Test Ordine

**1. Con cookie attivi, completa un ordine test**

**2. Sulla success page, in Network tab cerca:**
```
POST trackShopify.jsp
```

✅ **Se presente:** Vendita tracciata

---

## 🔄 AGGIORNAMENTO PLUGIN

Quando esce una nuova versione:

```bash
cd /path/to/your/magento

composer update konverty/magento2-affiliate-tracker
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## 🗑️ DISINSTALLAZIONE

```bash
cd /path/to/your/magento

php bin/magento module:disable Konverty_AffiliateTracker
composer remove konverty/magento2-affiliate-tracker
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## 🐛 TROUBLESHOOTING

### Problema: "Package not found"

**Causa:** Repository non aggiunto

**Soluzione:**
```bash
composer config repositories.konverty-tracker vcs https://github.com/konverty/magento2-affiliate-tracker
```

---

### Problema: "Module not found after installation"

**Causa:** Modulo non abilitato

**Soluzione:**
```bash
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
```

---

### Problema: "Pixel non caricato"

**Causa 1:** Cache non pulita

**Soluzione:**
```bash
php bin/magento cache:flush
```

**Causa 2:** Static content non deployato

**Soluzione:**
```bash
php bin/magento setup:static-content:deploy -f
```

**Causa 3:** Tracking disabilitato in config

**Soluzione:**
```
Admin → Stores → Configuration → Konverty → Enable Tracking = Yes
```

---

### Problema: "Cookie non salvati"

**Verifica:**
1. Tracking abilitato in config
2. JavaScript non bloccato da AdBlocker
3. Browser permette cookie

---

### Problema: "Vendite non tracciate"

**Verifica:**
1. Cookie presenti prima dell'ordine
2. Track Sales abilitato in config
3. Endpoint corretto in config
4. Network tab per POST a trackShopify.jsp

---

## 📞 SUPPORTO

### Prima di Contattare

1. ✅ Verifica che Magento sia 2.3+ o 2.4+
2. ✅ Verifica che Composer sia installato
3. ✅ Controlla log: `var/log/system.log`
4. ✅ Prova con browser diverso
5. ✅ Disabilita AdBlocker per test

### Contatti

- 📧 **Email:** info@konverty.com
- 🐛 **Issues:** https://github.com/konverty/magento2-affiliate-tracker/issues
- 📖 **Documentazione:** [README.md](README.md)

---

## 🎯 CHECKLIST POST-INSTALLAZIONE

- [ ] Plugin installato via Composer
- [ ] Modulo abilitato (`php bin/magento module:status`)
- [ ] Configurazione completata in admin
- [ ] Cache pulita
- [ ] Static content deployato
- [ ] Test pixel caricato (DevTools)
- [ ] Test cookie salvati
- [ ] Test ordine tracciato
- [ ] Endpoint Konverty configurati
- [ ] Debug mode disabilitato (production)

---

## 💡 NOTE IMPORTANTI

### Modalità Production

Assicurati che Debug Mode sia `No` in produzione per performance ottimali.

### Compatibilità

- ✅ Magento 2.3.x
- ✅ Magento 2.4.x
- ✅ PHP 7.4+
- ✅ Composer 2.x

### Performance

Il plugin è ottimizzato e aggiunge:
- ~6 KB JavaScript (async)
- 1 cookie per parametro affiliazione
- 1-2 POST per conversione
- **Impatto:** < 0.1s sul page load

---

## 📚 ALTRE GUIDE

- **[INSTALL.md](INSTALL.md)** - Installazione manuale (senza Composer)
- **[TESTING.md](TESTING.md)** - Test completi e troubleshooting
- **[README.md](README.md)** - Documentazione completa
- **[GITHUB_PUBLISH.md](GITHUB_PUBLISH.md)** - Per maintainer/developer

---

**🎉 Installazione Completata! Il tuo sito ora traccia le affiliazioni Konverty!**

_Ultima modifica: 25 Ottobre 2025_

