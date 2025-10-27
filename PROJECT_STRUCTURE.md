# 📁 Struttura Completa Progetto

```
magento-plugin/
│
├── 📄 README.md                          # Documentazione completa utente
├── 📄 INSTALL.md                         # Guida installazione rapida
├── 📄 CHANGELOG.md                       # Log versioni e modifiche
├── 📄 TESTING.md                         # Guida testing completa
├── 📄 PROJECT_STRUCTURE.md               # Questo file
├── 📄 .gitignore                         # File da ignorare in git
│
└── 📂 app/code/Konverty/AffiliateTracker/
    │
    ├── 📄 registration.php               # Registrazione modulo Magento
    ├── 📄 composer.json                  # Dipendenze e autoload
    │
    ├── 📂 etc/                           # Configurazioni XML
    │   ├── 📄 module.xml                 # Definizione modulo
    │   ├── 📄 config.xml                 # Valori default configurazione
    │   ├── 📄 events.xml                 # Registrazione observer
    │   ├── 📄 acl.xml                    # Permessi admin
    │   │
    │   └── 📂 adminhtml/
    │       └── 📄 system.xml             # Configurazione backend
    │
    ├── 📂 Block/                         # Block PHP per template
    │   ├── 📄 Pixel.php                  # Block configurazione pixel
    │   └── 📄 OrderSuccess.php           # Block dati ordine
    │
    ├── 📂 Helper/                        # Utility e helper
    │   └── 📄 Data.php                   # Helper configurazioni e API
    │
    ├── 📂 Observer/                      # Observer eventi Magento
    │   ├── 📄 OrderCompleteObserver.php  # Log ordine completato
    │   ├── 📄 OrderCancelObserver.php    # Webhook ordine annullato
    │   ├── 📄 CreditmemoSaveObserver.php # Webhook rimborso
    │   └── 📄 OrderShipmentObserver.php  # Webhook spedizione
    │
    └── 📂 view/
        └── 📂 frontend/
            ├── 📂 layout/                # Layout XML
            │   ├── 📄 default.xml        # Layout globale (tutte pagine)
            │   └── 📄 checkout_onepage_success.xml  # Success page
            │
            ├── 📂 templates/             # Template PHTML
            │   ├── 📄 pixel-config.phtml # Inject configurazione JS
            │   └── 📄 order-success.phtml # Tracking vendita
            │
            └── 📂 web/
                └── 📂 js/
                    └── 📄 konverty-pixel.js  # JavaScript pixel tracker
```

---

## 📊 Dimensioni Stimate

| Tipo File | Quantità | Dimensione Totale |
|-----------|----------|-------------------|
| PHP | 8 file | ~30 KB |
| JavaScript | 1 file | ~6 KB |
| XML | 6 file | ~8 KB |
| PHTML | 2 file | ~2 KB |
| Markdown | 5 file | ~50 KB |
| **TOTALE** | **22 file** | **~96 KB** |

---

## 🔗 Relazioni tra File

### Flusso Configurazione

```
module.xml
    ↓
config.xml (default values)
    ↓
adminhtml/system.xml (admin UI)
    ↓
Helper/Data.php (read config)
    ↓
Block/Pixel.php (expose to template)
    ↓
pixel-config.phtml (inject in HTML)
```

### Flusso Tracking Visite

```
User visit with ?affiliate_id=XXX
    ↓
default.xml loads konverty-pixel.js
    ↓
pixel-config.phtml injects window.konvertyConfig
    ↓
konverty-pixel.js captures URL params
    ↓
Saves cookies (aff_*)
    ↓
POST to trackShopify.jsp (type: "visit")
```

### Flusso Tracking Vendite

```
User completes checkout
    ↓
checkout_onepage_success.xml loads OrderSuccess block
    ↓
OrderSuccess.php fetches order data
    ↓
order-success.phtml calls window.Konverty.trackSale()
    ↓
konverty-pixel.js reads affiliate cookies
    ↓
POST to trackShopify.jsp (type: "sale")
    ↓
Deletes affiliate cookies
```

### Flusso Webhook

```
Admin creates shipment/cancel/refund
    ↓
events.xml triggers observer
    ↓
OrderShipmentObserver.php (or Cancel/Creditmemo)
    ↓
Helper/Data.php sendWebhook()
    ↓
POST to webhookShopify.jsp
    ↓
Backend updates affiliate_sales.id_status
```

---

## 🎯 File Principali per Funzionalità

### Tracking Visite
- `view/frontend/web/js/konverty-pixel.js` (linee 80-150)
- `Helper/Data.php::isTrackVisitsEnabled()`

### Tracking Vendite
- `view/frontend/web/js/konverty-pixel.js` (linee 150-250)
- `Block/OrderSuccess.php::getOrderDataJson()`
- `view/frontend/templates/order-success.phtml`

### Webhook Spedito
- `Observer/OrderShipmentObserver.php`
- `Helper/Data.php::sendWebhook()`
- `etc/events.xml` (evento `sales_order_shipment_save_after`)

### Webhook Annullato
- `Observer/OrderCancelObserver.php`
- `etc/events.xml` (evento `order_cancel_after`)

### Webhook Rimborsato
- `Observer/CreditmemoSaveObserver.php`
- `etc/events.xml` (evento `sales_order_creditmemo_save_after`)

### Configurazione Admin
- `etc/adminhtml/system.xml` (UI form)
- `etc/config.xml` (default values)
- `Helper/Data.php` (getter methods)

---

## 🔧 File da Modificare per Custom

### Cambiare Endpoint
📄 **File**: `etc/config.xml` o configurazione admin
```xml
<endpoint_url>https://nuovo-endpoint.com/track</endpoint_url>
```

### Aggiungere Campi Custom al Tracking Vendite
📄 **File**: `Block/OrderSuccess.php` (metodo `getOrderDataJson()`)
```php
$orderData = [
    // ... campi esistenti ...
    'custom_field' => $order->getCustomAttribute(),
];
```

### Modificare Logica Cookie
📄 **File**: `view/frontend/web/js/konverty-pixel.js`
```javascript
function setCookie(name, value, days) {
    // Modifica logica qui
}
```

### Aggiungere Nuovo Observer
1. 📄 Crea: `Observer/MioObserver.php`
2. 📄 Registra in: `etc/events.xml`
```xml
<event name="nome_evento">
    <observer name="konverty_mio_observer" 
              instance="Konverty\AffiliateTracker\Observer\MioObserver"/>
</event>
```

### Modificare Template Pixel
📄 **File**: `view/frontend/templates/pixel-config.phtml`
```php
<?php if ($block->isEnabled()): ?>
    <script>
        // Custom JS qui
    </script>
<?php endif; ?>
```

---

## 📦 File Deployment

### Production Deploy
```bash
# File da deployare
app/code/Konverty/AffiliateTracker/**/*

# Escludi (già in .gitignore)
var/
generated/
pub/static/
```

### Backup Essenziali
```bash
# Backup prima di installare
cp -r app/code/Konverty app/code/Konverty.backup
mysqldump magento_db > magento_db_backup.sql
```

---

## 🧪 File per Testing

### Test Manuali
- `TESTING.md` - Guida completa
- `view/frontend/web/js/konverty-pixel.js` (debug mode)
- `Helper/Data.php::log()` - Logging

### Log Files
```bash
var/log/system.log          # Log applicazione
var/log/exception.log       # Eccezioni PHP
var/log/debug.log           # Debug Magento
```

### Test Endpoint
```bash
# Test con curl
curl -X POST https://admin.konverty.com/trackShopify.jsp \
  -H "Content-Type: application/json" \
  -d @test-payload.json
```

---

## 📈 Metriche Codice

| Metrica | Valore |
|---------|--------|
| File PHP | 8 |
| Classi PHP | 6 |
| Observer | 4 |
| Layout XML | 2 |
| Config XML | 4 |
| Template PHTML | 2 |
| JavaScript | 1 (~250 lines) |
| Linee codice totali | ~1,200 |
| Complessità ciclomatica | Bassa (< 10 per metodo) |
| Copertura test | N/A (da implementare) |

---

## 🔒 File con Logica Sensibile

### Sicurezza da Verificare

1. **`Helper/Data.php::sendWebhook()`**
   - Validazione URL endpoint
   - Timeout configurabile
   - Error handling

2. **`view/frontend/web/js/konverty-pixel.js`**
   - Cookie SameSite=Lax
   - No dati sensibili in cookie
   - Sanitizzazione input

3. **`Block/OrderSuccess.php::getOrderDataJson()`**
   - Escape JSON output
   - No esposizione password/token
   - Validazione email

---

## 📚 Documentazione

| File | Scopo | Target |
|------|-------|--------|
| `README.md` | Documentazione completa | Utente finale |
| `INSTALL.md` | Guida installazione | Sysadmin |
| `TESTING.md` | Guida testing | QA/Developer |
| `CHANGELOG.md` | Log versioni | Tutti |
| `PROJECT_STRUCTURE.md` | Struttura progetto | Developer |

---

## 🚀 Quick Access

### Configurazione
```bash
# Admin path
Stores → Configuration → Konverty → Affiliate Tracker
```

### Log Debugging
```bash
tail -f var/log/system.log | grep Konverty
```

### Cache Clear
```bash
php bin/magento cache:flush
```

### Recompile
```bash
php bin/magento setup:di:compile
```

---

**Struttura ottimizzata per:**
- ✅ Manutenibilità
- ✅ Scalabilità
- ✅ Testing
- ✅ Standard Magento 2
- ✅ Best practices PHP/JS


