# Hostinger VPS Migration Guide - GatePilot Platform

This document outlines the absolute requirements and mandatory changes when moving the GatePilot project from a local XAMPP environment to a **Hostinger VPS**.

## 1. Environment Configuration (`config.php`)
Your first mission is to update the core constants to reflect the production VPS environment.

```php
// Location: c:/xampp/htdocs/gatepilot/config.php

// 1. SWITCH TO PRODUCTION MODE (Mandatory)
define('ENVIRONMENT', 'PRODUCTION');

// 2. VPS DATABASE ACCESS (Master)
// On a VPS, 'localhost' usually works, but check Hostinger's provided DB IP
define('DB_HOST', 'localhost');
define('DB_USER', 'your_vps_db_root_or_admin'); 
define('DB_PASS', 'your_secure_password');
define('DB_NAME', 'gp_admin'); // The master registry database
```

## 2. Master Database Infrastructure
Before the application can onboard any tenants, the **Master Registry** must be initialized on the VPS.

1.  **Create `gp_admin`**: Use phpMyAdmin or `mysql` CLI to create the `gp_admin` database.
2.  **Bootstrap Admin**: Manual entry of the first "Master Tenant" (slug: `admin`) in the `tenants` table is required to access the central control panel.
3.  **Permissions**: Ensure the `DB_USER` has global `CREATE DATABASE` permissions so `createTenant()` can automate new customer setups.

## 3. Server-Side Prerequisites (VPS)
Run these commands on your VPS terminal (Ubuntu/Debian) to ensure the PHP environment matches development:

```bash
# 1. Install missing PHP extensions
sudo apt update
sudo apt install php-mysqli php-gd php-curl php-mbstring php-xml php-zip -y

# 2. Set ownership for the web server (usually www-data)
sudo chown -R www-data:www-data /path/to/gatepilot/

# 3. Secure but writable folder for photo uploads
sudo chmod -R 775 /path/to/gatepilot/uploads/
```

## 4. Multi-Tenant Provisioning Workflow
Wait, the logic in `functions.php:createTenant()` is already production-ready. On a VPS:
*   The system will automatically prefix customer databases with your account prefix (e.g., `u875321134_gp_slug`).
*   It will clone the `database/schema.sql` into every new database automatically.

## 5. Security & SSL Setup
1.  **SSL Certificate**: Install a free SSL via Hostinger's hPanel. All tenant subdomains MUST use `https://`.
2.  **Htaccess**: Ensure `AllowOverride All` is active in your Apache configuration so that `.htaccess` can properly manage routing and folder protection.
3.  **PHP Version**: Lock the VPS to **PHP 8.1 or 8.2** for stability.

## 6. Deployment Checklist
- [ ] Export your local `gp_admin` table and import it to the VPS.
- [ ] SFTP upload all files or `git clone` to the web root.
- [ ] Update `config.php` with the new VPS credentials.
- [ ] Verify the `uploads/` folder permissions (must show `drwxrwxr-x`).
- [ ] Test the "New Tenant" creation flow to confirm the script can remotely create MySQL databases.
- [ ] Redirect domains/subdomains to the VPS IP address.

---
**GatePilot Dev Team**  
*Last Updated: 2026-03-29*
