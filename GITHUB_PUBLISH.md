# 🚀 Pubblicazione su GitHub e Installazione via Composer

Guida completa per pubblicare il plugin Magento 2 su GitHub e permettere installazione con Composer.

---

## 📋 PREREQUISITI

- [ ] Account GitHub (https://github.com)
- [ ] Git installato localmente
- [ ] Repository configurato

---

## 🎯 STEP 1: CREA REPOSITORY GITHUB

### 1. Vai su GitHub

```
https://github.com/new
```

### 2. Configura Repository

**Nome repository:** `magento2-affiliate-tracker`

**Descrizione:** 
```
Konverty Affiliate Tracking Pixel for Magento 2 - Track visits and sales with affiliate parameters
```

**Visibilità:**
- ✅ **Public** (se vuoi distribuirlo pubblicamente)
- ⚠️ **Private** (se solo per clienti specifici - vedi nota sotto)

**NON inizializzare con:**
- [ ] README
- [ ] .gitignore  
- [ ] License

(li abbiamo già nel progetto)

---

## 🎯 STEP 2: PUBBLICA IL CODICE

### 1. Inizializza Git (se non già fatto)

```bash
cd C:\GitHub\magento-plugin
git init
```

### 2. Aggiungi Remote Origin

```bash
# Sostituisci 'konverty' con il tuo username GitHub
git remote add origin https://github.com/konverty/magento2-affiliate-tracker.git
```

### 3. Commit Iniziale

```bash
git add .
git commit -m "Initial release v1.0.0 - Konverty Affiliate Tracker for Magento 2"
```

### 4. Push su GitHub

```bash
git branch -M main
git push -u origin main
```

### 5. Crea Tag Versione

```bash
git tag v1.0.0
git push origin v1.0.0
```

---

## 🎯 STEP 3: INSTALLAZIONE PER CLIENTI

### Metodo A: Repository Pubblico (più semplice)

**Cliente esegue:**

```bash
cd /path/to/magento

# Aggiungi repository al composer.json
composer config repositories.konverty-tracker vcs https://github.com/konverty/magento2-affiliate-tracker

# Installa plugin
composer require konverty/magento2-affiliate-tracker:^1.0

# Abilita modulo
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

### Metodo B: Repository Privato (con access token)

**1. Crea GitHub Personal Access Token**
```
GitHub → Settings → Developer settings → Personal access tokens → Generate new token
Permessi: repo (full control)
```

**2. Cliente configura accesso**
```bash
# Aggiungi credentials
composer config --global --auth github-oauth.github.com YOUR_GITHUB_TOKEN

# Poi installa normalmente
composer config repositories.konverty-tracker vcs https://github.com/konverty/magento2-affiliate-tracker
composer require konverty/magento2-affiliate-tracker:^1.0
```

### Metodo C: Packagist (pubblico)

**1. Pubblica su Packagist.org**
```
1. Vai su: https://packagist.org/
2. Login con GitHub
3. Click "Submit"
4. Inserisci: https://github.com/konverty/magento2-affiliate-tracker
5. Submit package
```

**2. Cliente installa (più semplice!)**
```bash
composer require konverty/magento2-affiliate-tracker
php bin/magento setup:upgrade
```

---

## 🎯 STEP 4: AGGIORNAMENTI

### Rilasciare Nuova Versione

```bash
# Fai modifiche al codice
git add .
git commit -m "Fix: Validazione affiliate_id aggiunta"

# Aggiorna version in composer.json
# version: "1.0.1"

# Crea tag
git tag v1.0.1
git push origin main
git push origin v1.0.1
```

### Cliente Aggiorna

```bash
composer update konverty/magento2-affiliate-tracker
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## 📦 FILE COMPOSER.JSON DEL CLIENTE

Il cliente avrà questo nel suo `composer.json`:

```json
{
  "require": {
    "konverty/magento2-affiliate-tracker": "^1.0"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/konverty/magento2-affiliate-tracker"
    }
  ]
}
```

**Oppure (se su Packagist):**
```json
{
  "require": {
    "konverty/magento2-affiliate-tracker": "^1.0"
  }
}
```

---

## 🔧 COMANDI COMPOSER UTILI

### Per Te (maintainer)

```bash
# Valida composer.json
composer validate app/code/Konverty/AffiliateTracker/composer.json

# Crea package per test locale
cd app/code/Konverty/AffiliateTracker
composer archive --format=zip
```

### Per Cliente

```bash
# Lista moduli installati
composer show | grep konverty

# Info dettagliate
composer show konverty/magento2-affiliate-tracker

# Rimuovi modulo
composer remove konverty/magento2-affiliate-tracker
php bin/magento module:disable Konverty_AffiliateTracker
php bin/magento setup:upgrade
```

---

## 🎯 DISTRIBUZIONE ALTERNATIVA: ZIP RELEASE

Se preferisci NON usare Git/Composer:

### 1. Crea ZIP da GitHub

```bash
# Locale
cd C:\GitHub\magento-plugin
# Comprimi cartella app/code/Konverty in ZIP

# Oppure su GitHub:
# Releases → Create a new release → Upload ZIP
```

### 2. Cliente Installa Manualmente

```bash
# Scarica ZIP da GitHub Releases
# Estrai in app/code/
unzip konverty-affiliate-tracker-v1.0.0.zip -d app/code/

# Abilita
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
```

---

## 🏪 MAGENTO MARKETPLACE (opzionale - futuro)

### Step per Pubblicare

**1. Registrazione**
```
https://commercemarketplace.adobe.com/partner/register
```

**2. Documentazione Richiesta**
- [ ] User Guide (PDF)
- [ ] Installation Guide
- [ ] Release Notes
- [ ] Screenshots (4-5)
- [ ] Video demo (opzionale)

**3. Technical Requirements**
- [ ] Code review passed (Adobe verifica codice)
- [ ] Magento Coding Standards
- [ ] Security scan passed
- [ ] Functional test passed

**4. Submission**
```
Marketplace Portal → Extensions → Add New Extension → Upload ZIP
```

**5. Review Process**
- ⏱️ **Tempo:** 2-4 settimane
- 📧 **Comunicazione:** via email
- ✅ **Approval:** Pubblicato su marketplace

**6. Cliente Installa**
```
Magento Admin → System → Web Setup Wizard → Extension Manager
→ Trova "Konverty Affiliate Tracker" → Install
```

### Vantaggi Marketplace
- ✅ Visibilità ufficiale
- ✅ Credibilità (reviewed by Adobe)
- ✅ Update automatici
- ✅ Rating e reviews
- ✅ Monetizzazione possibile

### Svantaggi
- ⏱️ Review lunga
- 📋 Documentazione extensive
- 💰 Fee su vendite (se a pagamento): 80% a te, 20% ad Adobe

---

## 📊 CONFRONTO METODI

| Metodo | Setup | Cliente Installa | Aggiornamenti | Visibilità | Costo |
|--------|-------|------------------|---------------|------------|-------|
| **GitHub Public** | 5 min | 2 comandi | `composer update` | Media | Gratis |
| **GitHub Private** | 10 min | 3 comandi (+ token) | `composer update` | Privata | Gratis |
| **Packagist** | 15 min | 1 comando | `composer update` | Alta | Gratis |
| **Marketplace** | 2-4 sett | Click admin | Automatico | Altissima | 20% fee |
| **ZIP Manual** | 5 min | Manuale | Manuale | Bassa | Gratis |

---

## ✅ RACCOMANDAZIONE FINALE

### **PER INIZIARE SUBITO:**
→ **GitHub Public + Composer** (Setup: 10 minuti)

### **PER CRESCERE:**
→ **Packagist** (dopo GitHub, aggiungere 5 minuti)

### **PER PROFESSIONALIZZARE:**
→ **Magento Marketplace** (quando hai clienti e feedback)

---

## 🚀 QUICK START (10 minuti)

```bash
# 1. Crea repo GitHub (web)
# https://github.com/new → magento2-affiliate-tracker

# 2. Push codice
cd C:\GitHub\magento-plugin
git init
git add .
git commit -m "Initial release v1.0.0"
git remote add origin https://github.com/TUO_USERNAME/magento2-affiliate-tracker.git
git push -u origin main
git tag v1.0.0
git push origin v1.0.0

# 3. FATTO! Condividi con clienti:
# composer config repositories.konverty vcs https://github.com/TUO_USERNAME/magento2-affiliate-tracker
# composer require konverty/magento2-affiliate-tracker
```

---

## 📝 README.md per GitHub

Crea un bel README per GitHub con badge:

```markdown
# Konverty Affiliate Tracker for Magento 2

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![Magento](https://img.shields.io/badge/magento-2.3%20%7C%202.4-orange)
![License](https://img.shields.io/badge/license-proprietary-red)

Track affiliate visits and sales on your Magento 2 store with Konverty.

## Installation

```bash
composer require konverty/magento2-affiliate-tracker
php bin/magento module:enable Konverty_AffiliateTracker
php bin/magento setup:upgrade
```

## Configuration

Stores → Configuration → Konverty → Affiliate Tracker

## Support

- 📧 Email: info@konverty.com
- 🐛 Issues: https://github.com/konverty/magento2-affiliate-tracker/issues
- 📖 Docs: [README.md](README.md)

## License

Proprietary - Copyright © 2025 Konverty
```

---

**Domande?** Fammi sapere se vuoi che prepari altri file o se hai dubbi! 🚀

