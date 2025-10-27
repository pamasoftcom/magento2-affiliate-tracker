# 🚀 Guida Rapida Installazione - Konverty Affiliate Tracker

## Installazione Plugin Magento

### 1. Copia File

```bash
cd /path/to/your/magento
cp -r /path/to/magento-plugin/app/code/Konverty ./app/code/
```

### 2. Abilita Modulo

```bash
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

### 3. Configura da Admin

1. Login admin Magento
2. Vai a: **Stores → Configuration → Konverty → Affiliate Tracker**
3. Imposta:
   - **Enable Tracking**: `Yes`
   - **Tracking Endpoint URL**: `https://admin.konverty.com/trackShopify.jsp`
   - **Webhook Endpoint URL**: `https://admin.konverty.com/webhookShopify.jsp`
   - **Cookie Lifetime**: `60` giorni
4. Salva configurazione

### 4. Test Rapido

**Test Visita:**
```
https://tuosito.com/?affiliate_id=TEST001
```
Verifica cookie `aff_affiliate_id` nel browser (DevTools → Application → Cookies)

**Test Vendita:**
- Con cookie attivo, completa un ordine
- Sulla success page, apri DevTools → Network
- Cerca POST a `trackShopify.jsp` con `type: "sale"`

### 5. Abilitare Debug (opzionale)

```bash
# In configurazione admin, abilita Debug Mode
# Poi monitora i log:
tail -f var/log/system.log | grep Konverty
```

---

## Verifica Installazione

```bash
# Verifica che il modulo sia installato
php bin/magento module:status Konverty_AffiliateTracker

# Output atteso:
# List of enabled modules:
# Konverty_AffiliateTracker
```

---

## Risoluzione Problemi Comuni

### Errore: "Module not found"
```bash
# Verifica percorso file
ls -la app/code/Konverty/AffiliateTracker/registration.php

# Se presente, ricompila
php bin/magento setup:upgrade
```

### JavaScript non caricato
```bash
# Deploy static content
php bin/magento setup:static-content:deploy -f it_IT en_US
php bin/magento cache:flush
```

### Webhook non inviati
```bash
# Test connessione endpoint
curl -X POST https://admin.konverty.com/webhookShopify.jsp \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Abilita debug mode e controlla log
tail -f var/log/system.log
```

---

## Disinstallazione

```bash
php bin/magento module:disable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento cache:flush
rm -rf app/code/Konverty
```

---

Per documentazione completa, vedi [README.md](README.md)


