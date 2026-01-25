# WPShadow.com Complete Setup Guide

**Date:** January 21, 2026  
**Purpose:** Full development environment for WPShadow plugin + WPShadow.com site

---

## 🏗️ Architecture Overview

This setup provides everything needed to develop both the WPShadow plugin AND the wpshadow.com website simultaneously.

### Components

1. **Main Site (Port 8080)** - WPShadow.com Development
   - WPShadow plugin (core)
   - WPShadow Site plugin (CPTs, integrations)
   - Theme: theme-wpshadow
   - MailPoet (newsletters)
   - Sensei LMS (Academy)

2. **Test Site (Port 9000)** - Plugin Testing
   - WPShadow plugin (core)
   - WPShadow Site plugin (for testing)
   - Default theme (Twenty Twenty-Four)
   - Isolated database

3. **Supporting Services**
   - phpMyAdmin (Port 8081) - Database management
   - MailHog (Port 8025) - Email testing

### Directory Structure

```
/workspaces/
├── wpshadow/                    # Core plugin repository
│   ├── dev-tools/
│   │   ├── docker-compose.yml   # Multi-service orchestration (for dev)
│   │   └── wp-content/          # Test WordPress content
│   ├── Makefile                 # 30+ convenience commands
│   └── wpshadow.php             # Main plugin file
├── theme-wpshadow/              # Site theme
│   ├── style.css
│   ├── functions.php
│   └── templates/
└── wpshadow-site-plugin/        # Site-specific plugin (NEW)
    ├── wpshadow-site.php        # Main plugin file
    ├── includes/
    │   ├── class-post-types.php      # Products, KB, Stories, Changelog
    │   ├── class-taxonomies.php      # Categories and tags
    │   ├── class-meta-boxes.php      # Custom fields
    │   ├── class-mailpoet-integration.php
    │   ├── class-sensei-integration.php
    │   └── class-rest-api.php        # Custom endpoints
    └── README.md
```

---

## 🚀 Quick Start

### Option 1: One-Command Setup (Recommended)

```bash
cd /workspaces/wpshadow
./setup-docker.sh
```

This will:
- Validate Docker installation
- Create theme directory if needed
- Start all 6 containers
- Display access URLs

### Option 2: Using Make

```bash
cd /workspaces/wpshadow
make setup
```

### Option 3: Manual docker-compose

```bash
cd /workspaces/wpshadow/dev-tools
docker-compose up -d
```

---

## 📋 Initial Configuration

### 1. Complete WordPress Setups

**Main Site (wpshadow.com):**
1. Open http://localhost:8080
2. Choose language: English
3. Site Title: **WPShadow**
4. Username: **admin**
5. Password: **(generate strong password)**
6. Email: **admin@wpshadow.local**
7. Click "Install WordPress"

**Test Site:**
1. Open http://localhost:9000
2. Choose language: English
3. Site Title: **WPShadow Test**
4. Username: **testadmin**
5. Password: **(different from main site)**
6. Email: **test@wpshadow.local**
7. Click "Install WordPress"

### 2. Activate Plugins & Theme

**Main Site:**
```bash
make activate
```

Or manually:
```bash
docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow
docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow
docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow-site
```

**Test Site:**
```bash
make activate-test
```

### 3. Install MailPoet & Sensei

**MailPoet (Free):**
```bash
make wp-site CMD="plugin install mailpoet --activate"
```

**Sensei LMS (Free):**
```bash
make wp-site CMD="plugin install sensei-lms --activate"
```

---

## 🎨 Site Plugin Features

The **WPShadow Site Plugin** provides all custom functionality for wpshadow.com:

### Custom Post Types

1. **Products** (`wpshadow_product`)
   - Guardian, Vault, Academy, Pro
   - Custom fields: pricing, features, CTA URL/text
   - Menu position: 20
   - Icon: 🛒 (dashicons-cart)

2. **KB Articles** (`wpshadow_kb`)
   - Knowledge base documentation
   - Custom fields: difficulty, related product, read time
   - Hierarchical (parent/child articles)
   - Menu position: 21
   - Icon: 📖 (dashicons-book)

3. **Success Stories** (`wpshadow_story`)
   - User testimonials & case studies
   - Custom fields: author name, role, website, featured flag
   - Menu position: 22
   - Icon: 🏆 (dashicons-awards)

4. **Changelog** (`wpshadow_changelog`)
   - Version updates & feature announcements
   - Menu position: 23
   - Icon: 📋 (dashicons-list-view)

### Custom Taxonomies

- **Product Categories** (hierarchical)
- **KB Categories** (hierarchical)
- **KB Tags** (non-hierarchical)
- **Story Categories** (hierarchical)

### Plugin Integrations

**MailPoet Integration:**
- Newsletter signup forms
- Custom interest fields (security, performance, maintenance, news)
- Auto-subscribe on registration
- Targeted campaigns

**Sensei Integration:**
- WPShadow Academy branding
- Course completion badges
- Gamification points (100 pts per course)
- Progress tracking for dashboard
- Custom course metadata

### REST API Endpoints

- `GET /wp-json/wpshadow/v1/products/featured` - Featured products
- `GET /wp-json/wpshadow/v1/kb/{difficulty}` - KB by difficulty (beginner/intermediate/advanced)
- `GET /wp-json/wpshadow/v1/stories/featured` - Featured success stories
- `GET /wp-json/wpshadow/v1/search?s={query}` - Search all content

---

## 🔧 Development Workflow

### Plugin Development (WPShadow Core)

1. **Edit files** in `/workspaces/wpshadow/`
2. **Changes appear** instantly in both sites
3. **Test main site:** http://localhost:8080/wp-admin
4. **Test test site:** http://localhost:9000/wp-admin
5. **Check logs:** `make logs-site` or `make logs-test`

### Site Plugin Development

1. **Edit files** in `/workspaces/wpshadow-site-plugin/includes/`
2. **Changes appear** instantly (no rebuild)
3. **Test CPTs:** Main site > Products/KB/Stories/Changelog
4. **Test REST API:** 
   ```bash
   curl http://localhost:8080/wp-json/wpshadow/v1/products/featured
   ```

### Theme Development

1. **Edit files** in `/workspaces/theme-wpshadow/`
2. **Refresh browser** to see changes
3. **Use DevTools** for CSS/JS debugging
4. **Check errors:** View source, browser console

### Database Management

**Visual (phpMyAdmin):**
- URL: http://localhost:8081
- Server: `db-site` or `db-test`
- Username: `wordpress`
- Password: `wordpress`

**CLI:**
```bash
make db-site    # MySQL CLI for main database
make db-test    # MySQL CLI for test database
```

**Backup/Restore:**
```bash
make backup-site          # Creates timestamped backup
make import-site FILE=backup.sql
```

### Email Testing

All WordPress emails are caught by MailHog:

1. Trigger email (password reset, new user, etc.)
2. Open http://localhost:8025
3. View email in MailHog UI

**Test command:**
```bash
docker-compose exec wordpress-site wp --allow-root eval "wp_mail('test@example.com', 'Test Email', 'This is a test.');"
```

---

## 📦 Creating Content

### Create a Product (Guardian, Vault, etc.)

1. Main site > **Products > Add New**
2. Title: **WPShadow Guardian**
3. Content: Full product description
4. Featured Image: Product logo
5. **Product Details** meta box:
   - Pricing: `$5-$60 credits, Pro $19/month`
   - Features:
     ```
     AI-powered security recommendations
     Token-based pricing (5-60 credits)
     Real-time threat detection
     Automated response system
     ```
   - CTA URL: `https://guardian.wpshadow.com`
   - CTA Text: `Try Guardian Free`
6. Assign **Product Category**
7. Click **Publish**

### Create a KB Article

1. Main site > **Knowledge Base > Add New**
2. Title: **How to Fix SSL Certificate Errors**
3. Content: Step-by-step guide with screenshots
4. Featured Image: Relevant illustration
5. **Article Details** meta box:
   - Difficulty: `Beginner`
   - Related Product: Select "WPShadow Core"
   - Read Time: `5` minutes
6. Assign **KB Category**: Security
7. Add **KB Tags**: ssl, security, https
8. Click **Publish**

### Create a Success Story

1. Main site > **Success Stories > Add New**
2. Title: **How Agency X Secured 100+ Client Sites**
3. Content: Full testimonial/case study
4. Featured Image: Client logo or headshot
5. **Story Details** meta box:
   - Author Name: `John Smith`
   - Author Role: `CTO, Agency X`
   - Author Website: `https://agencyx.com`
   - ☑️ **Featured Story** (for homepage)
6. Assign **Story Category**: Agency Success
7. Click **Publish**

### Create a Changelog Entry

1. Main site > **Changelog > Add New**
2. Title: **Version 1.2601.2112 - January 2026**
3. Content:
   ```markdown
   ## Added
   - 5 new security diagnostics
   - Kanban board for finding management
   - KPI tracking dashboard

   ## Improved
   - Treatment execution speed (30% faster)
   - Memory usage optimization
   
   ## Fixed
   - SSL detection on some hosting providers
   - Multisite compatibility issues
   ```
4. Click **Publish**

---

## 🎓 Sensei LMS Setup (Academy)

### Install & Configure Sensei

```bash
make wp-site CMD="plugin install sensei-lms --activate"
```

### Create Your First Course

1. Main site > **Courses > Add New**
2. Title: **WordPress Security Fundamentals**
3. Content: Course overview and what students will learn
4. **Course Settings:**
   - Prerequisite: None
   - Featured Course: ☑️
   - Course Category: Security
5. **Add Lessons:**
   - Click "Add Lesson"
   - Create lessons like:
     - Lesson 1: Understanding WordPress Security
     - Lesson 2: SSL Certificates Explained
     - Lesson 3: Common Security Vulnerabilities
     - Lesson 4: Using WPShadow for Security Audits
6. **Add Quizzes:**
   - Create quizzes for each lesson
   - Set passing grade (e.g., 70%)
7. Click **Publish**

### Course Completion Gamification

The site plugin automatically:
- Awards 100 points on course completion
- Fires `wpshadow_course_completed` action
- Displays points in completion message

---

## 📧 MailPoet Setup (Newsletters)

### Install & Configure MailPoet

```bash
make wp-site CMD="plugin install mailpoet --activate"
```

### Create Newsletter List

1. Main site > **MailPoet > Lists**
2. Click **New List**
3. Name: **WPShadow Newsletter**
4. Description: WordPress management tips & updates
5. Click **Save**
6. Note the **List ID** (e.g., 1)

### Configure Site Plugin

```bash
# Set default list ID for auto-subscribe
make wp-site CMD="option update wpshadow_mailpoet_list_id 1"
```

### Create Newsletter

1. Main site > **MailPoet > Emails**
2. Click **Create New Email**
3. Choose **Newsletter**
4. Select template
5. Customize content:
   - Header: WPShadow logo
   - Featured article from KB
   - Recent changelog entry
   - Tips section
   - Footer with unsubscribe
6. Click **Send** or **Schedule**

### Custom Subscriber Fields

The site plugin adds **Interest Checkboxes:**
- ☐ Security Tips
- ☐ Performance Optimization
- ☐ Maintenance Best Practices
- ☐ WPShadow News & Updates

Use these for targeted campaigns.

---

## 🛠️ Common Tasks

### View All Running Containers

```bash
make status
# Or: docker-compose ps
```

### View Logs

```bash
make logs           # All services
make logs-site      # Main site only
make logs-test      # Test site only
make logs-db-site   # Main database
```

### Restart Services

```bash
make restart        # All services
make restart-site   # Main site only
make restart-test   # Test site only
```

### Shell Access

```bash
make shell-site     # Bash in main site container
make shell-test     # Bash in test site container
```

### WP-CLI Commands

```bash
# Main site
make wp-site CMD="plugin list"
make wp-site CMD="theme list"
make wp-site CMD="user list"

# Test site
make wp-test CMD="plugin list"
make wp-test CMD="option get siteurl"
```

### Database Operations

```bash
# Export
make backup-site     # Creates wpshadow-site-YYYYMMDD-HHMMSS.sql
make backup-test

# Import
make import-site FILE=backup.sql
make import-test FILE=backup.sql

# MySQL CLI
make db-site
# mysql> SHOW TABLES;
# mysql> SELECT * FROM wp_posts WHERE post_type='wpshadow_product';
# mysql> exit
```

### Reset Environment

```bash
# Reset single site (keeps volumes)
make reset-site
# Confirmation prompt appears

# Reset test site
make reset-test

# Nuclear option (deletes everything)
make clean
# ⚠️ This deletes ALL databases and files!
```

---

## 🐛 Troubleshooting

### Port Already in Use

**Error:** "Port 8080 is already in use"

**Solution:**
```bash
# Find process using port
lsof -i :8080

# Kill process
kill -9 <PID>

# Or change port in docker-compose.yml
# 8080:80 → 8082:80
```

### Theme Not Found

**Error:** "Theme not found after activation"

**Solution:**
```bash
# Check theme directory exists
ls -la /workspaces/theme-wpshadow/

# If missing, recreate
mkdir -p /workspaces/theme-wpshadow
# Then copy theme files or clone repo

# Restart to remount
make restart-site
```

### Plugin Not Showing

**Error:** "Site plugin not visible in WordPress admin"

**Solution:**
```bash
# Check plugin directory
make shell-site
ls -la /var/www/html/wp-content/plugins/

# Should see:
# wpshadow/
# wpshadow-site/

# If missing, check docker-compose.yml volume mount
# Restart container
make restart-site
```

### Database Connection Error

**Error:** "Error establishing database connection"

**Solution:**
```bash
# Check database container
docker-compose ps db-site

# If unhealthy, view logs
make logs-db-site

# Common fix: Wait for healthcheck
sleep 10
make restart-site
```

### MailPoet Not Found

**Error:** "Class '\MailPoet\API\API' not found"

**Solution:**
```bash
# Install MailPoet
make wp-site CMD="plugin install mailpoet --activate"

# Verify
make wp-site CMD="plugin list | grep mailpoet"
```

### Sensei Not Found

**Error:** "Class 'Sensei_Main' not found"

**Solution:**
```bash
# Install Sensei
make wp-site CMD="plugin install sensei-lms --activate"

# Verify
make wp-site CMD="plugin list | grep sensei"
```

---

## 📊 Access URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| **Main Site** | http://localhost:8080 | admin / (your password) |
| **Main Site Admin** | http://localhost:8080/wp-admin | |
| **Test Site** | http://localhost:9000 | testadmin / (your password) |
| **Test Site Admin** | http://localhost:9000/wp-admin | |
| **phpMyAdmin** | http://localhost:8081 | wordpress / wordpress |
| **MailHog** | http://localhost:8025 | (no auth) |

---

## 🎯 Next Steps

1. ✅ **Complete WordPress setups** (main + test)
2. ✅ **Activate plugins** (`make activate` and `make activate-test`)
3. ✅ **Install MailPoet & Sensei**
4. ✅ **Create first product** (Guardian, Vault, Academy, or Pro)
5. ✅ **Create first KB article** (link to from product)
6. ✅ **Create first course** (Sensei, for Academy)
7. ✅ **Design homepage** (theme-wpshadow/index.php)
8. ✅ **Test REST API endpoints**
9. ✅ **Setup newsletter list** (MailPoet)
10. ✅ **Create success story** (build social proof)

---

## 📚 Documentation Files

- **docker-compose.README.md** - Comprehensive Docker guide (500+ lines)
- **DOCKER-QUICKREF.txt** - Quick command reference
- **Makefile** - 30+ convenience commands with `make help`
- **wpshadow-site-plugin/README.md** - Site plugin documentation
- **docs/PRODUCT_ECOSYSTEM.md** - Complete product family architecture
- **docs/PRODUCT_PHILOSOPHY.md** - 11 commandments

---

## 🔒 Philosophy Compliance

This setup embodies WPShadow's 11 commandments:

✅ **Free Forever** - All local development tools free  
✅ **Educational** - KB articles, courses, documentation  
✅ **Privacy-First** - MailHog catches emails, no external sending  
✅ **Show Value** - Track course completions, points, KPIs  
✅ **Helpful Neighbor** - Complete setup automation  
✅ **Inspire Confidence** - Clear documentation, safety prompts  
✅ **Talk-Worthy** - Best-in-class dev environment

---

**Questions?** Check documentation or open an issue at https://github.com/thisismyurl/wpshadow

**Ready to build?** 🚀 Start with `./setup-docker.sh`
