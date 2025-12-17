# Konverty Affiliate Tracker for Magento 2

Plugin Magento 2 per il tracciamento delle affiliazioni tramite il sistema Konverty.

## 📋 Descrizione

Questo plugin permette di:
- **Tracciare le visite** con parametri di affiliazione (es. `?affiliate_id=ABC123`)
- **Tracciare le vendite** complete con attribuzione affiliato
- **Inviare webhook** automatici su cambio stato ordine (spedito, annullato, rimborsato)
- **Gestire cookie** di affiliazione con durata configurabile (default 60 giorni)

Il plugin si integra con il sistema backend Konverty (`admin.konverty.com`) riutilizzando gli endpoint esistenti di Shopify.

---

## 🚀 Installazione

### Metodo 1: Composer (Raccomandato) ⭐

**⚠️ IMPORTANTE - Credenziali Magento Marketplace:**

L'installazione via Composer potrebbe richiedere le credenziali di accesso al Magento Marketplace (`repo.magento.com`). Questo accade perché Composer verifica le dipendenze del progetto Magento principale.

- **Se hai le credenziali:** Configurale una volta (vedi [INSTALL_COMPOSER.md](INSTALL_COMPOSER.md#-configurazione-credenziali-magento-se-richiesto)) e procedi con l'installazione
- **Se NON hai le credenziali:** Usa l'[installazione manuale](#metodo-2-installazione-manuale) invece - è più semplice e non richiede credenziali

**Installazione via Composer:**

```bash
cd /path/to/magento

# Aggiungi repository
composer config repositories.konverty-tracker vcs https://github.com/pamasoftcom/magento2-affiliate-tracker

# Installa plugin
composer require konverty/magento2-affiliate-tracker

# Abilita
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

**Guida completa:** [INSTALL_COMPOSER.md](INSTALL_COMPOSER.md)

---

### Metodo 2: Installazione Manuale

**Per chi non usa Composer:**

1. **Copia i file** nella directory Magento:
```bash
cp -r app/code/Konverty /path/to/magento/app/code/
```

2. **Abilita il modulo**:
```bash
cd /path/to/magento
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

**Guida completa:** [INSTALL.md](INSTALL.md)

---

## ⚙️ Configurazione

### 1. Accesso Configurazione Admin

Vai su: **Stores → Configuration → Konverty → Affiliate Tracker**

### 2. Impostazioni Generali

| Campo | Descrizione | Valore Default |
|-------|-------------|----------------|
| **Enable Tracking** | Abilita/disabilita il tracking | `No` |
| **Tracking Endpoint URL** | URL endpoint Konverty per tracking | `https://admin.konverty.com/trackShopify.jsp` |
| **Webhook Endpoint URL** | URL per aggiornamenti stato ordine | `https://admin.konverty.com/webhookShopify.jsp` |
| **Cookie Lifetime (days)** | Durata cookie affiliazione | `60` |
| **Debug Mode** | Abilita logging dettagliato | `No` |

### 3. Impostazioni Avanzate

| Campo | Descrizione | Valore Default |
|-------|-------------|----------------|
| **Track Visits** | Traccia visite con parametri affiliazione | `Yes` |
| **Track Sales** | Traccia vendite completate | `Yes` |
| **Send Status Webhooks** | Invia webhook cambio stato ordine | `Yes` |
| **Cookie Prefix** | Prefisso per cookie affiliazione | `aff_` |

### 4. Salva Configurazione

Dopo aver configurato, clicca su **Save Config** e pulisci la cache:

```bash
php bin/magento cache:flush
```

---

## 📊 Funzionamento

### 1. Tracciamento Visite

Quando un utente visita il sito con parametri URL:

```
https://tuosito.com/prodotto?affiliate_id=ABC123&campaign=summer2025
```

Il pixel JavaScript:
1. **Cattura** tutti i parametri URL
2. **Salva** cookie con prefisso `aff_`:
   - `aff_affiliate_id=ABC123`
   - `aff_campaign=summer2025`
3. **Invia POST** a `trackShopify.jsp` con:
```json
{
  "type": "visit",
  "platform": "magento",
  "shop": "tuosito.com",
  "timestamp": "2025-10-25T10:30:00.000Z",
  "params": {
    "affiliate_id": "ABC123",
    "campaign": "summer2025"
  },
  "url": "https://tuosito.com/prodotto?affiliate_id=ABC123&campaign=summer2025"
}
```

### 2. Tracciamento Vendite

Quando l'utente completa un acquisto sulla **Success Page**:

1. Il pixel JavaScript **legge i cookie** di affiliazione salvati
2. **Recupera i dati dell'ordine** dal checkout
3. **Invia POST** a `trackShopify.jsp` con:
```json
{
  "type": "sale",
  "platform": "magento",
  "shop": "tuosito.com",
  "timestamp": "2025-10-25T11:45:00.000Z",
  "order_id": "000000123",
  "total_price": 150.00,
  "subtotal_price": 120.00,
  "currency": "EUR",
  "discount_codes": ["SUMMER10"],
  "customer": {
    "email": "cliente@example.com",
    "phone": "+39123456789",
    "name": "Mario Rossi"
  },
  "line_items": [
    {
      "product_id": "456",
      "variant_id": "456",
      "title": "Prodotto Esempio",
      "sku": "PROD-001",
      "quantity": 2,
      "price": 60.00
    }
  ],
  "params": {
    "affiliate_id": "ABC123",
    "campaign": "summer2025"
  },
  "payment_info": [
    {
      "gateway": "stripe",
      "amount": 150.00
    }
  ]
}
```

4. **Elimina i cookie** di affiliazione dopo il tracking

### 3. Webhook Cambio Stato Ordine

Gli **Observer Magento** inviano automaticamente webhook quando:

#### Ordine Spedito (Fulfilled)
```json
{
  "id": "1234",
  "order_id": "000000123",
  "status": "fulfilled",
  "fulfillment_status": "fulfilled",
  "created_at": "2025-10-26T14:00:00+00:00",
  "tracking_numbers": ["ABC123456789"],
  "platform": "magento"
}
```
→ Aggiorna DB: `id_status = 1`

#### Ordine Annullato (Cancelled)
```json
{
  "id": "000000123",
  "order_id": "000000123",
  "status": "cancelled",
  "financial_status": "voided",
  "cancelled_at": "2025-10-27T09:30:00+00:00",
  "platform": "magento"
}
```
→ Aggiorna DB: `id_status = 4`

#### Ordine Rimborsato (Refunded)
```json
{
  "id": "5678",
  "order_id": "000000123",
  "status": "refunded",
  "financial_status": "refunded",
  "created_at": "2025-10-28T16:45:00+00:00",
  "refund_amount": 150.00,
  "platform": "magento"
}
```
→ Aggiorna DB: `id_status = 17`

---

## 🗄️ Database

Il plugin utilizza le stesse tabelle del sistema Shopify esistente:

- **`affiliate_visits`** - Visite con parametri affiliazione
- **`affiliate_sales`** - Vendite completate
- **`sale_line_items`** - Prodotti venduti
- **`webhook_sent`** - Log webhook inviati ai publisher

Il campo `platform` (valore: `"magento"`) permette di distinguere le vendite da Magento rispetto a Shopify.

---

## 🧪 Testing

### Test 1: Tracciamento Visita

1. Visita il sito con parametri:
```
https://tuosito.com/?affiliate_id=TEST001&utm_source=facebook
```

2. Apri **Developer Console** (F12) → Tab **Network**
3. Cerca richieste a `trackShopify.jsp` con `type: "visit"`
4. Verifica cookie `aff_affiliate_id` e `aff_utm_source` in **Application → Cookies**

### Test 2: Tracciamento Vendita

1. Con i cookie di affiliazione attivi, completa un ordine
2. Sulla **Success Page**, controlla **Network** per richieste a `trackShopify.jsp` con `type: "sale"`
3. Verifica che i cookie `aff_*` siano stati eliminati dopo il tracking

### Test 3: Webhook Cambio Stato

1. Da admin Magento, crea una **Shipment** per un ordine
2. Controlla i log:
```bash
tail -f var/log/system.log | grep Konverty
```
3. Verifica che il webhook sia stato inviato a `webhookShopify.jsp`

### Test 4: Debug Mode

1. Abilita **Debug Mode** in configurazione
2. Esegui azioni di tracciamento
3. Controlla log dettagliati:
```bash
tail -f var/log/system.log
```

---

## 🔧 Troubleshooting

### Il pixel non traccia le visite

**Soluzioni:**
- Verifica che **Enable Tracking** sia impostato su `Yes`
- Verifica che **Track Visits** sia abilitato
- Controlla che il JavaScript sia caricato: `view-source` → cerca `konverty-pixel.js`
- Apri Console JavaScript per errori
- Controlla CORS: l'endpoint deve accettare richieste cross-origin

### Le vendite non vengono tracciate

**Soluzioni:**
- Verifica che **Track Sales** sia abilitato
- Controlla che i cookie `aff_*` esistano al momento dell'acquisto
- Verifica che il blocco `OrderSuccess` sia presente nella Success Page
- Controlla log JavaScript per errori nel payload

### I webhook non vengono inviati

**Soluzioni:**
- Verifica che **Send Status Webhooks** sia abilitato
- Controlla **Webhook Endpoint URL** sia corretto
- Abilita **Debug Mode** e controlla i log
- Verifica che il server possa raggiungere l'endpoint (nessun firewall)
- Testa manualmente l'endpoint con `curl`

### Cookie non vengono salvati

**Soluzioni:**
- Verifica che il dominio non blocchi cookie di terze parti
- Controlla `SameSite=Lax` nella configurazione browser
- Usa HTTPS (alcuni browser bloccano cookie su HTTP)
- Controlla `Cookie Prefix` in configurazione

---

## 🛠️ Sviluppo e Personalizzazione

### Modificare il Lifetime dei Cookie

Admin: **Stores → Configuration → Konverty → Affiliate Tracker → Cookie Lifetime**

O modificare direttamente in `app/code/Konverty/AffiliateTracker/etc/config.xml`:
```xml
<cookie_lifetime>90</cookie_lifetime>
```

### Aggiungere Campi Custom al Tracking

Modificare `app/code/Konverty/AffiliateTracker/Block/OrderSuccess.php` nel metodo `getOrderDataJson()`:

```php
$orderData = [
    // ... campi esistenti ...
    'custom_field' => $order->getCustomField(),
];
```

### Aggiungere Nuovi Observer

1. Definire l'evento in `etc/events.xml`:
```xml
<event name="nome_evento">
    <observer name="konverty_custom_observer" 
              instance="Konverty\AffiliateTracker\Observer\CustomObserver"/>
</event>
```

2. Creare il file `Observer/CustomObserver.php`

### Modificare Endpoint

Admin: **Stores → Configuration → Konverty → Affiliate Tracker**

O modificare `etc/config.xml`:
```xml
<endpoint_url>https://nuovo-endpoint.com/track.jsp</endpoint_url>
```

---

## 📝 Requisiti Sistema

- **Magento**: 2.3.x, 2.4.x
- **PHP**: 7.4+
- **MySQL**: 5.7+ / MariaDB 10.2+
- **Estensioni PHP**: curl, json
- **HTTPS**: Raccomandato per cookie sicuri

---

## 📄 Licenza

Proprietaria - Copyright (c) 2025 Konverty

---

## 🆘 Supporto

Per assistenza:
- **Email**: info@konverty.com
- **Documentazione Backend**: Vedere documentazione `trackShopify.jsp` e `webhookShopify.jsp`

---

## 📦 Struttura File Plugin

```
app/code/Konverty/AffiliateTracker/
├── registration.php
├── composer.json
├── etc/
│   ├── module.xml
│   ├── config.xml
│   ├── events.xml
│   ├── acl.xml
│   └── adminhtml/
│       └── system.xml
├── Block/
│   ├── Pixel.php
│   └── OrderSuccess.php
├── Helper/
│   └── Data.php
├── Observer/
│   ├── OrderCompleteObserver.php
│   ├── OrderCancelObserver.php
│   ├── CreditmemoSaveObserver.php
│   └── OrderShipmentObserver.php
├── view/
│   └── frontend/
│       ├── layout/
│       │   ├── default.xml
│       │   └── checkout_onepage_success.xml
│       ├── templates/
│       │   ├── pixel-config.phtml
│       │   └── order-success.phtml
│       └── web/
│           └── js/
│               └── konverty-pixel.js
└── README.md
```

---

## 🔄 Versioni

### v1.0.0 - 2025-10-25
- ✅ Tracciamento visite con parametri affiliazione
- ✅ Tracciamento vendite complete
- ✅ Webhook cambio stato ordine (fulfilled, cancelled, refunded)
- ✅ Configurazione admin completa
- ✅ Debug mode per logging dettagliato
- ✅ Compatibilità con sistema Konverty esistente (Shopify)

---

## 🎯 Prossimi Sviluppi

- [ ] Dashboard statistiche affiliate in admin Magento
- [ ] Report vendite per affiliato
- [ ] Supporto multi-store avanzato
- [ ] API REST per query esterne
- [ ] Widget per visualizzare link affiliazione

---

**Konverty Affiliate Tracker** - Tracciamento affiliazioni professionale per Magento 2

---

## 📦 Pixel per Shopify (repo separata)

Il pixel Shopify con istruzioni dedicate è ora in una repo separata:
- Cartella locale: `../konverty-shopify-pixel`
- File JS: `konverty-pixel-shopify.js`
- Doc rapida: `README.md`

Quando sarà pubblicato su GitHub, sostituisci il link con l'URL del nuovo repository.


