# Changelog

Tutte le modifiche importanti a questo progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.0.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/lang/it/).

---

## [1.0.0] - 2025-10-25

### ✨ Aggiunto

- **Tracking Pixel JavaScript**
  - Cattura automatica parametri URL di affiliazione
  - Salvataggio cookie con prefisso configurabile (`aff_`)
  - Durata cookie configurabile (default 60 giorni)
  - Tracciamento visite con invio a endpoint Konverty
  - Tracciamento vendite su checkout success page
  - Eliminazione automatica cookie dopo conversione

- **Configurazione Admin Magento**
  - Pannello configurazione completo in Stores → Configuration
  - Abilita/disabilita tracking globale
  - Configurazione endpoint tracking e webhook
  - Impostazioni cookie (lifetime, prefisso)
  - Debug mode per logging dettagliato
  - Controlli granulari (track visits, track sales, send webhooks)

- **Observer Eventi Ordine**
  - `OrderCompleteObserver`: Log ordini completati
  - `OrderCancelObserver`: Webhook su ordine annullato (status 4)
  - `OrderShipmentObserver`: Webhook su spedizione (status 1 - fulfilled)
  - `CreditmemoSaveObserver`: Webhook su rimborso (status 17 - refunded)

- **Helper e Utility**
  - Helper Data per gestione configurazioni
  - Metodo `sendWebhook()` per invio webhook con cURL
  - Logging centralizzato con supporto debug mode
  - Gestione errori e retry logic

- **Block e Template**
  - `Pixel` Block per configurazione JavaScript
  - `OrderSuccess` Block per dati ordine
  - Template `pixel-config.phtml` per inject configurazione
  - Template `order-success.phtml` per tracking vendite

- **Layout XML**
  - `default.xml`: Inject pixel su tutte le pagine frontend
  - `checkout_onepage_success.xml`: Tracking specifico success page

- **Documentazione**
  - README.md completo con guida utente
  - INSTALL.md per installazione rapida
  - Esempi JSON payload per visite, vendite e webhook
  - Sezione troubleshooting dettagliata

### 🔧 Caratteristiche Tecniche

- **Compatibilità**: Magento 2.3.x, 2.4.x
- **PHP**: 7.4+
- **Endpoint**: Riutilizzo `trackShopify.jsp` e `webhookShopify.jsp` esistenti
- **Platform**: Campo `platform: "magento"` per distinguere da Shopify
- **Database**: Uso tabelle esistenti (`affiliate_visits`, `affiliate_sales`, ecc.)
- **Cookie**: `SameSite=Lax` per compatibilità cross-browser
- **API**: Invio dati con POST JSON + fallback image beacon

### 📊 Flusso Tracking

1. **Visita**: Utente arriva con `?affiliate_id=XXX` → Cookie salvati → POST a tracking endpoint
2. **Vendita**: Checkout completato → Lettura cookie → POST dati ordine → Eliminazione cookie
3. **Webhook**: Cambio stato ordine → POST a webhook endpoint → Update DB status

### 🛡️ Sicurezza

- Validazione URL endpoint in configurazione
- Sanitizzazione input JSON
- Timeout configurabili su chiamate HTTP (10s)
- No esposizione dati sensibili in cookie
- Supporto HTTPS per cookie sicuri

---

## [Unreleased]

### 🎯 Pianificato per Future Release

- Dashboard admin per statistiche affiliate
- Report vendite per affiliato
- Export CSV/Excel delle conversioni
- API REST per query esterne
- Widget frontend per link affiliazione
- Supporto multi-currency avanzato
- Integrazione Google Analytics
- A/B testing link affiliazione

---

## Note sulla Versione

### Come Aggiornare

```bash
# Backup database e file
php bin/magento maintenance:enable

# Update file plugin
cp -r /path/to/new/version/app/code/Konverty ./app/code/

# Esegui upgrade
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush

php bin/magento maintenance:disable
```

### Breaking Changes

Nessun breaking change in questa versione iniziale.

### Deprecazioni

Nessuna deprecazione in questa versione.

---

**Legenda:**
- ✨ Aggiunto: Nuove funzionalità
- 🔧 Modificato: Modifiche a funzionalità esistenti
- 🐛 Corretto: Bug fix
- 🗑️ Rimosso: Funzionalità rimosse
- 🛡️ Sicurezza: Fix di sicurezza
- 📚 Documentazione: Aggiornamenti documentazione


