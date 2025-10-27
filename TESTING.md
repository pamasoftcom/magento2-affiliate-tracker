# 🧪 Testing Guide - Konverty Affiliate Tracker

Guida completa per testare tutte le funzionalità del plugin.

---

## 📋 Checklist Pre-Test

- [ ] Plugin installato e abilitato
- [ ] Configurazione completata in admin
- [ ] Debug mode abilitato
- [ ] Console browser aperta (F12)
- [ ] Terminal aperto per monitorare log

---

## 1️⃣ Test Tracciamento Visite

### Setup
```bash
# Monitora log in tempo reale
tail -f var/log/system.log | grep Konverty
```

### Test A: Visita con Affiliate ID

**URL da visitare:**
```
https://tuosito.com/?affiliate_id=TEST001
```

**Verifica:**
1. ✅ Cookie `aff_affiliate_id=TEST001` presente
   - DevTools → Application → Cookies → tuosito.com
   - Scadenza: +60 giorni
   
2. ✅ POST inviato a `trackShopify.jsp`
   - DevTools → Network → Filtra: `trackShopify`
   - Payload:
   ```json
   {
     "type": "visit",
     "platform": "magento",
     "shop": "tuosito.com",
     "params": {
       "affiliate_id": "TEST001"
     }
   }
   ```

3. ✅ Log nel database `affiliate_visits`
   ```sql
   SELECT * FROM affiliate_visits 
   WHERE affiliate_id = 'TEST001' 
   ORDER BY created_at DESC LIMIT 1;
   ```

### Test B: Visita con Parametri Multipli

**URL da visitare:**
```
https://tuosito.com/?affiliate_id=TEST002&utm_source=facebook&utm_campaign=summer2025&utm_medium=cpc
```

**Verifica:**
- ✅ 4 cookie salvati:
  - `aff_affiliate_id=TEST002`
  - `aff_utm_source=facebook`
  - `aff_utm_campaign=summer2025`
  - `aff_utm_medium=cpc`

- ✅ Tutti i parametri nel payload `params`

### Test C: Visita senza Parametri

**URL da visitare:**
```
https://tuosito.com/
```

**Verifica:**
- ✅ Nessun POST inviato (no parametri = no tracking visit)
- ✅ Console log: "No URL parameters, skipping visit tracking"

---

## 2️⃣ Test Tracciamento Vendite

### Setup
```bash
# Assicurati di avere cookie affiliate attivi prima di ordinare
# Usa Test 1A o 1B per creare i cookie
```

### Test A: Ordine Completo con Affiliazione

**Steps:**
1. Visita con affiliate: `https://tuosito.com/?affiliate_id=SALE001`
2. Verifica cookie `aff_affiliate_id` presente
3. Aggiungi prodotto al carrello
4. Completa checkout
5. Arriva alla Success Page

**Verifica Success Page:**

1. ✅ POST inviato a `trackShopify.jsp` con `type: "sale"`
   - DevTools → Network → `trackShopify.jsp`
   
2. ✅ Payload contiene:
   ```json
   {
     "type": "sale",
     "platform": "magento",
     "order_id": "000000123",
     "total_price": 150.00,
     "subtotal_price": 120.00,
     "currency": "EUR",
     "customer": {
       "email": "test@example.com",
       "name": "Test User"
     },
     "line_items": [
       {
         "product_id": "456",
         "title": "Prodotto Test",
         "sku": "TEST-001",
         "quantity": 1,
         "price": 120.00
       }
     ],
     "params": {
       "affiliate_id": "SALE001"
     }
   }
   ```

3. ✅ Cookie `aff_*` eliminati dopo 1 secondo
   - Ricontrolla Application → Cookies
   - Devono essere spariti

4. ✅ Record in database `affiliate_sales`
   ```sql
   SELECT * FROM affiliate_sales 
   WHERE order_id = '000000123';
   ```

5. ✅ Record in database `sale_line_items`
   ```sql
   SELECT * FROM sale_line_items 
   WHERE sale_id = (SELECT id FROM affiliate_sales WHERE order_id = '000000123');
   ```

6. ✅ Payout calcolati correttamente
   - `payout_pub` = subtotal * 0.15 (arrotondato)
   - `payout_net` = subtotal * 0.05 (arrotondato)

### Test B: Ordine senza Affiliazione

**Steps:**
1. Cancella tutti i cookie
2. Visita sito senza parametri: `https://tuosito.com/`
3. Completa ordine

**Verifica:**
- ✅ Nessun POST `type: "sale"` inviato
- ✅ Console log: "No affiliate cookies found, skipping sale tracking"
- ✅ Nessun record in `affiliate_sales`

---

## 3️⃣ Test Webhook Ordine Spedito

### Test A: Creazione Shipment

**Steps (Admin Magento):**
1. Vai a Sales → Orders
2. Apri un ordine con status "Processing"
3. Click "Ship"
4. Crea shipment con tracking number "TEST123456"
5. Submit Shipment

**Verifica:**

1. ✅ Log Magento:
   ```bash
   tail -f var/log/system.log | grep "Shipment created"
   # Output: Shipment created for order: 000000123
   ```

2. ✅ Webhook inviato a `webhookShopify.jsp`
   ```json
   {
     "id": "5678",
     "order_id": "000000123",
     "status": "fulfilled",
     "fulfillment_status": "fulfilled",
     "created_at": "2025-10-25T14:00:00+00:00",
     "tracking_numbers": ["TEST123456"],
     "platform": "magento"
   }
   ```

3. ✅ Database aggiornato:
   ```sql
   SELECT id_status FROM affiliate_sales WHERE order_id = '000000123';
   -- Dovrebbe essere: 1 (fulfilled)
   ```

---

## 4️⃣ Test Webhook Ordine Annullato

### Test A: Cancellazione Ordine

**Steps (Admin Magento):**
1. Sales → Orders
2. Apri ordine
3. Click "Cancel"
4. Conferma cancellazione

**Verifica:**

1. ✅ Log Magento:
   ```bash
   # Output: Order cancelled: 000000123
   ```

2. ✅ Webhook inviato:
   ```json
   {
     "id": "000000123",
     "order_id": "000000123",
     "status": "cancelled",
     "financial_status": "voided",
     "cancelled_at": "2025-10-25T09:30:00+00:00",
     "platform": "magento"
   }
   ```

3. ✅ Database aggiornato:
   ```sql
   SELECT id_status FROM affiliate_sales WHERE order_id = '000000123';
   -- Dovrebbe essere: 4 (cancelled)
   ```

---

## 5️⃣ Test Webhook Ordine Rimborsato

### Test A: Creazione Credit Memo

**Steps (Admin Magento):**
1. Sales → Orders
2. Apri ordine fatturato
3. Click "Credit Memo"
4. Imposta quantità da rimborsare
5. Seleziona "Refund" o "Refund Offline"
6. Submit Credit Memo

**Verifica:**

1. ✅ Log Magento:
   ```bash
   # Output: Credit memo created for order: 000000123
   ```

2. ✅ Webhook inviato:
   ```json
   {
     "id": "9012",
     "order_id": "000000123",
     "status": "refunded",
     "financial_status": "refunded",
     "created_at": "2025-10-25T16:45:00+00:00",
     "refund_amount": 150.00,
     "platform": "magento"
   }
   ```

3. ✅ Database aggiornato:
   ```sql
   SELECT id_status FROM affiliate_sales WHERE order_id = '000000123';
   -- Dovrebbe essere: 17 (refunded)
   ```

---

## 6️⃣ Test Configurazioni

### Test A: Disabilita Tracking

**Steps:**
1. Admin: Stores → Configuration → Konverty
2. Set "Enable Tracking" = No
3. Save Config
4. Flush cache

**Verifica:**
- ✅ Nessun JavaScript caricato (view-source: nessun `konverty-pixel.js`)
- ✅ Nessun tracking visit/sale
- ✅ Nessun webhook inviato

### Test B: Cambia Cookie Lifetime

**Steps:**
1. Set "Cookie Lifetime" = 30 giorni
2. Save Config
3. Visita con `?affiliate_id=EXPIRE001`

**Verifica:**
- ✅ Cookie `aff_affiliate_id` scade tra 30 giorni
  - DevTools → Application → Cookies → Expires column

### Test C: Debug Mode

**Steps:**
1. Set "Debug Mode" = Yes
2. Save Config
3. Esegui qualsiasi azione di tracking

**Verifica:**
- ✅ Log dettagliati in `var/log/system.log`:
  ```
  [Konverty Affiliate Tracker] Visit tracked
  [Konverty Affiliate Tracker] Affiliate params found: {"affiliate_id":"TEST"}
  [Konverty Affiliate Tracker] Sending webhook to: https://...
  ```

---

## 7️⃣ Test Compatibilità Browser

### Browsers da testare:

| Browser | Versione | Cookie | Fetch API | Pass/Fail |
|---------|----------|--------|-----------|-----------|
| Chrome  | Latest   | ✅     | ✅        | ✅        |
| Firefox | Latest   | ✅     | ✅        | ✅        |
| Safari  | Latest   | ✅     | ✅        | ✅        |
| Edge    | Latest   | ✅     | ✅        | ✅        |
| Mobile Safari | iOS 14+ | ✅ | ✅      | ✅        |
| Chrome Mobile | Android | ✅ | ✅      | ✅        |

### Cookie Restrictions Test:

**Third-party cookie blocked:**
- Safari: Preferences → Privacy → Block all cookies
- Verifica: Cookie first-party `aff_*` devono funzionare comunque (stesso dominio)

---

## 8️⃣ Test Performance

### Test A: Page Load Impact

**Tools:**
- Google PageSpeed Insights
- GTmetrix
- Lighthouse

**Verifica:**
- ✅ JavaScript pixel < 5KB
- ✅ Async loading (no blocking)
- ✅ No impatto significativo su FCP/LCP

### Test B: Webhook Timeout

**Steps:**
1. Configura endpoint fasullo non raggiungibile
2. Crea shipment
3. Monitora tempo risposta

**Verifica:**
- ✅ Timeout dopo 10 secondi (configurato in Helper)
- ✅ Magento non bloccato durante attesa
- ✅ Errore loggato, no crash

---

## 🛠️ Comandi Utili per Testing

```bash
# Pulisci cache
php bin/magento cache:flush

# Recompila (dopo modifiche codice)
php bin/magento setup:di:compile

# Monitora log in tempo reale
tail -f var/log/system.log | grep Konverty

# Monitora log errori
tail -f var/log/exception.log

# Query test database
mysql -u root -p magento_db

# Test endpoint manualmente
curl -X POST https://admin.konverty.com/trackShopify.jsp \
  -H "Content-Type: application/json" \
  -d '{
    "type": "visit",
    "platform": "magento",
    "shop": "test.com",
    "params": {"affiliate_id": "CURL001"}
  }'

# Pulisci cookie da command line (per reset test)
# Chrome: Settings → Privacy → Clear browsing data → Cookies
```

---

## 📊 Test Report Template

```
# Test Report - [DATA]

## Ambiente
- Magento: 2.4.x
- PHP: 7.4.x
- Browser: Chrome 100+
- Plugin Version: 1.0.0

## Test Eseguiti
- [x] Tracciamento Visite
- [x] Tracciamento Vendite
- [x] Webhook Shipment
- [x] Webhook Cancellation
- [x] Webhook Refund
- [ ] Performance Test

## Risultati
| Test | Status | Note |
|------|--------|------|
| Visit Tracking | ✅ Pass | Cookie salvati correttamente |
| Sale Tracking | ✅ Pass | Payload completo, cookie eliminati |
| Webhook Shipment | ✅ Pass | Status aggiornato a 1 |
| Webhook Cancel | ⚠️ Warning | Ritardo 2s nell'invio |
| Webhook Refund | ✅ Pass | - |

## Problemi Riscontrati
- Nessuno

## Raccomandazioni
- Tutto OK per produzione
```

---

## ❓ FAQ Testing

**Q: Il POST non arriva all'endpoint. Cosa verifico?**
- CORS configurato correttamente sul server?
- Firewall blocca richieste outbound?
- Endpoint raggiungibile? Testa con `curl`

**Q: Cookie non salvati in Safari**
- Disabilita "Prevent cross-site tracking" per test
- Verifica dominio corretto (no www vs non-www mismatch)

**Q: Webhook non inviato**
- Debug mode abilitato?
- Controlla `var/log/system.log` per errori
- Verifica configurazione endpoint in admin

**Q: Sale tracking duplicato**
- Refresh della success page può retriggare tracking
- Mitigazione: lato server deve dedupare per `order_id`

---

**Happy Testing!** 🎉


