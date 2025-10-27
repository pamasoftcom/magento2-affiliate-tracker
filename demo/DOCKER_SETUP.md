# 🐳 Setup Magento 2 con Docker - Guida Rapida

Setup completo Magento 2 + Plugin Konverty in **15 minuti**.

---

## 📋 Prerequisiti

- **Docker Desktop** installato
  - Windows: https://docs.docker.com/desktop/install/windows-install/
  - Mac: https://docs.docker.com/desktop/install/mac-install/
  - Linux: https://docs.docker.com/desktop/install/linux-install/
- **10 GB** spazio disco libero
- **8 GB RAM** disponibili

---

## 🚀 Setup Rapido (Consigliato)

### Step 1: Clone Repository Docker Magento

```bash
# Clone il miglior stack Docker per Magento
git clone https://github.com/markshust/docker-magento
cd docker-magento

# (Opzionale) Scegli la versione Magento
# Versioni disponibili: 2.4.6, 2.4.5, 2.4.4
bin/download 2.4.6 community
```

### Step 2: Setup Magento

```bash
# Setup automatico (5-10 minuti)
bin/setup magento.test

# Output atteso:
# ✓ Downloading Magento...
# ✓ Creating Docker containers...
# ✓ Installing Magento...
# ✓ Deploying sample data...
# ✓ Setup complete!
```

### Step 3: Installa Plugin Konverty

```bash
# Copia plugin nel container
cp -r ../magento-plugin/app/code/Konverty src/app/code/

# Abilita modulo
bin/magento module:enable Konverty_AffiliateTracker

# Upgrade e compile
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f it_IT en_US
bin/magento cache:flush
```

### Step 4: Accedi a Magento

**Frontend:**
```
https://magento.test
```

**Admin Panel:**
```
URL: https://magento.test/admin
Username: admin
Password: admin123
```

### Step 5: Configura Konverty

1. Login admin: `https://magento.test/admin`
2. Vai a: **Stores → Configuration → Konverty → Affiliate Tracker**
3. Imposta:
   - Enable Tracking: `Yes`
   - Tracking Endpoint URL: `https://admin.konverty.com/trackShopify.jsp`
   - Webhook Endpoint URL: `https://admin.konverty.com/webhookShopify.jsp`
   - Debug Mode: `Yes`
4. Save Config

### Step 6: Test

```bash
# Test visita con affiliate
https://magento.test/?affiliate_id=DOCKER001

# Monitora log
bin/magento tail
# oppure
docker-compose logs -f phpfpm | grep Konverty
```

---

## 🔧 Comandi Utili Docker

```bash
# Avvia container
bin/start

# Ferma container
bin/stop

# Restart container
bin/restart

# Shell nel container PHP
bin/bash

# Esegui comando Magento
bin/magento <comando>

# Tail log Magento
bin/magento tail

# Accedi MySQL
bin/mysql

# Pulisci cache
bin/magento cache:flush

# Reindexing
bin/magento indexer:reindex

# Deploy static content
bin/magento setup:static-content:deploy -f
```

---

## 📂 Struttura Directory

```
docker-magento/
├── bin/                  # Script helper
├── compose/              # Docker Compose files
├── src/                  # Codice Magento
│   └── app/
│       └── code/
│           └── Konverty/ # ← IL TUO PLUGIN QUI
└── docker-compose.yml
```

---

## 🐛 Troubleshooting

### Problema: "Port 80 already in use"

```bash
# Ferma altri servizi su porta 80/443
# Windows - XAMPP/IIS
net stop w3svc

# Mac - Apache
sudo apachectl stop

# Linux
sudo systemctl stop apache2
```

### Problema: Container non si avvia

```bash
# Pulisci tutto e ricomincia
bin/stop
docker-compose down -v
bin/start
```

### Problema: "Permission denied"

```bash
# Windows - Esegui PowerShell come Amministratore
# Mac/Linux
sudo chown -R $(whoami) src/
```

### Problema: Magento lento

```bash
# Aumenta RAM a Docker Desktop
# Settings → Resources → Memory: 8GB minimo

# Disabilita alcuni moduli non necessari
bin/magento module:disable Magento_TwoFactorAuth
bin/magento cache:flush
```

---

## 🔄 Update Plugin

```bash
# 1. Copia nuova versione
cp -r ../magento-plugin/app/code/Konverty src/app/code/ -f

# 2. Upgrade
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

---

## 🗑️ Disinstallazione

```bash
# Rimuovi plugin
rm -rf src/app/code/Konverty
bin/magento module:disable Konverty_AffiliateTracker
bin/magento setup:upgrade
bin/magento cache:flush

# Rimuovi tutto Docker
cd docker-magento
bin/stop
docker-compose down -v
cd ..
rm -rf docker-magento
```

---

## 📊 Risorse Sistema

**Minimo:**
- CPU: 2 core
- RAM: 4 GB
- Disco: 5 GB

**Raccomandato:**
- CPU: 4 core
- RAM: 8 GB
- Disco: 10 GB

---

## 🌐 Alternative Docker

### Opzione A: Warden (Multi-Environment)

```bash
# Installa Warden
brew install davidalger/warden/warden
warden svc up

# Setup Magento
mkdir magento2 && cd magento2
warden env-init magento2 magento2.test
warden env up
warden env exec composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition /tmp/magento-tmp
warden env exec rsync -a /tmp/magento-tmp/ /var/www/html/
warden env exec bin/magento setup:install
```

### Opzione B: Bitnami Magento

```bash
# Quick start con Bitnami
docker run -d -p 80:8080 -p 443:8443 \
  --name magento \
  bitnami/magento:latest

# Credenziali default
# User: user
# Password: bitnami
```

---

## 📚 Documentazione Utile

- **Docker Magento Official**: https://github.com/markshust/docker-magento
- **Magento DevDocs**: https://devdocs.magento.com/
- **Docker Desktop**: https://docs.docker.com/desktop/

---

## ✅ Checklist Setup

- [ ] Docker Desktop installato e running
- [ ] Repository clonato
- [ ] Magento scaricato e installato
- [ ] Plugin Konverty copiato in `src/app/code/`
- [ ] Modulo abilitato
- [ ] Setup upgrade eseguito
- [ ] Frontend accessibile su `https://magento.test`
- [ ] Admin accessibile su `https://magento.test/admin`
- [ ] Configurazione Konverty completata
- [ ] Test pixel funzionante

---

## 🎯 Prossimi Step

Dopo il setup:
1. Leggi **TESTING.md** per test completi
2. Prova test rapido: `https://magento.test/?affiliate_id=TEST001`
3. Completa un ordine di test
4. Verifica webhook con shipment

**Tempo totale stimato: 15-20 minuti** ⏱️


