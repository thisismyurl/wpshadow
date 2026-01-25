# WP Shadow Development Environment

> **Welcome, fellow developer!** 👋  
> This environment embodies our philosophy: be a **Helpful Neighbor**, make learning **ridiculously good**, and provide tools that **inspire confidence**.

---

## 🚀 Quick Start (5 minutes)

**First time here?** Don't worry - we've got you covered!

1. **Open in your environment**  
   GitHub Codespaces or VS Code with Dev Containers

2. **Wait for setup** (3-5 minutes)  
   Our scripts install everything you need automatically  
   💡 *Learn why:* [Understanding Dev Containers](https://docs.wpshadow.com/dev-environment/what-is-devcontainer)

3. **Access WordPress**  
   http://localhost:8080  
   Default credentials will show in the terminal

4. **Check your dashboard**  
   ```bash
   composer kpi
   ```
   See your progress and track your development journey!

📚 **New to development?** Check our [🌱 Beginner Learning Path](LEARNING_RESOURCES.md#beginner-path)

---

## 🏡 The WPShadow Philosophy

Everything in this environment reflects our **11 Commandments** and **3 CANON Pillars**:

### Why This Matters
- **Educational First** - Every error teaches, every success guides
- **Free & Accessible** - No artificial limitations, no paywalls
- **Quality Worth Sharing** - Tools so good you'll want to tell others
- **Track Your Growth** - See concrete evidence of your progress

💡 *Learn more:* [The Complete Philosophy](https://docs.wpshadow.com/philosophy/overview)

---

## 🌐 Available Services

| Service | URL | Credentials |
|---------|-----|-------------|
| **WordPress** | http://localhost:8080 | *(shown in terminal)* |
| **phpMyAdmin** | http://localhost:8081 | `wordpress` / `wordpress` |
| **MySQL** | localhost:3306 | `wordpress` / `wordpress` |

---

## 🛠️ Development Tools

### Track Your Progress

```bash
# See your development KPIs anytime
composer kpi
```

**What you'll track:**
- Tests run and passed
- Code quality checks
- Commits made
- Time saved by automation
- Achievement level

💡 *Philosophy: Commandment #9 - Everything Has a KPI*

### WP-CLI (WordPress Command Line)

**Why use it:** Automate WordPress tasks that would take minutes in the GUI

```bash
# List plugins
wp plugin list

# Create a test post
wp post create --post_title="Test" --post_status=publish

# Database query
wp db query "SHOW TABLES"

# Export database
wp db export backup.sql
```

📚 **Learn more:** [WP-CLI Fundamentals](https://docs.wpshadow.com/tools/wp-cli/basics) *(15 min)*

### PHPCS (Coding Standards)

**Why use it:** Maintain WordPress coding standards automatically. It's like having an expert review every line.

```bash
# Check coding standards (tracked automatically)
composer phpcs

# Auto-fix coding standards
composer phpcbf

# Check specific file
phpcs includes/core/class-plugin.php

# List installed standards
phpcs -i
```

**💡 Helpful Tip:** PHPCS saves you ~5 minutes per check vs. manual review!

📚 **Learn more:** [PHPCS Basics](https://docs.wpshadow.com/tools/phpcs/getting-started) *(10 min)*

### PHPUnit (Testing)

**Why use it:** Catch bugs before users do. Automated testing builds confidence.

```bash
# Run tests (tracked automatically)
composer test

# Run specific test file
phpunit tests/test-example.php

# Run with coverage report
phpunit --coverage-html coverage/
```

**💡 Helpful Tip:** Each test run saves you ~10 minutes of manual testing!

📚 **Learn more:** [Writing Your First Test](https://docs.wpshadow.com/testing/first-test) *(20 min)*

### PHPStan (Static Analysis)

**Why use it:** Find potential bugs without running code. It's like a spell-checker for logic errors.

```bash
# Run PHPStan
composer phpstan

# Analyze specific directory
phpstan analyse includes/

# Check at different strictness level
phpstan analyse --level=6
```

📚 **Learn more:** [PHPStan Introduction](https://docs.wpshadow.com/tools/phpstan/intro) *(15 min)*

---

## 📚 Learning Paths

Choose your path based on experience:

### 🌱 Beginner (10 hours)
**You're new to WordPress development**

Start here → [Beginner Learning Path](LEARNING_RESOURCES.md#beginner-path)

**What you'll learn:**
- Environment basics
- Making your first change
- Code quality fundamentals
- Running tests

### 🌿 Intermediate (20 hours)
**You're comfortable with basics**

Start here → [Intermediate Learning Path](LEARNING_RESOURCES.md#intermediate-path)

**What you'll learn:**
- Writing tests
- Security best practices
- Performance optimization
- Architecture patterns

### 🌳 Advanced (40+ hours)
**You're ready to master plugin architecture**

Start here → [Advanced Learning Path](LEARNING_RESOURCES.md#advanced-path)

**What you'll learn:**
- Dependency injection
- Test-driven development
- Advanced performance tuning
- Custom tooling

---

## 💼 Daily Workflow Examples

### Starting Your Day

```bash
# 1. Check for updates
git pull

# 2. See your progress
composer kpi

# 3. Make sure everything works
composer test
```

### Before Committing

```bash
# 1. Check coding standards
composer phpcs

# 2. Run tests
composer test

# 3. Static analysis
composer phpstan

# 4. Commit (Git hooks run automatically)
git add .
git commit -m "feat: add new feature"
```

💡 *Philosophy: Our Git hooks celebrate your work, not criticize it!*

### Debugging a Problem

```bash
# 1. Check WordPress logs
wp log list

# 2. Enable Xdebug
echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini

# 3. Use WP-CLI for quick tests
wp eval 'var_dump(your_function());'

# 4. Check database directly
wp db query "SELECT * FROM wp_options WHERE option_name LIKE '%your_plugin%'"
```

📚 **Need help?** See [Troubleshooting Guide](LEARNING_RESOURCES.md#troubleshooting)

---

## 🐛 Debugging Tools

### Xdebug

**Why use it:** Step through code line by line, inspect variables in real-time

```bash
# Enable Xdebug debugging
echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini

# Enable profiling for performance analysis
echo "xdebug.mode=profile" >> /usr/local/etc/php/conf.d/xdebug.ini
```

📚 **Learn more:** [Profiling with Xdebug](https://docs.wpshadow.com/performance/profiling) *(30 min)*

### WordPress Debug Mode

Already enabled in this environment! Check `wp-config.php` for:
- `WP_DEBUG` - Shows PHP errors
- `WP_DEBUG_LOG` - Saves errors to file
- `SCRIPT_DEBUG` - Uses unminified JS/CSS

---

## ✅ WordPress.org Submission Checklist

Ready to publish? Make sure you've covered:

- [ ] All code passes `composer phpcs`
- [ ] All code passes `composer phpstan`
- [ ] PHP 8.1+ compatibility verified
- [ ] All strings internationalized (`wpshadow` text domain)
- [ ] `.distignore` excludes dev files
- [ ] `readme.txt` follows WordPress.org format
- [ ] Screenshots added and documented
- [ ] Tested on WordPress 6.4+
- [ ] Security review completed
- [ ] Accessibility checked (WCAG 2.1 AA)

📚 **Learn more:** [Publishing to WordPress.org](https://docs.wpshadow.com/publishing/wordpress-org)

---

## 🆘 Troubleshooting

### Common Issues

**Container won't start?**
```bash
# Check Docker is running
docker ps

# Rebuild container
# In VS Code: F1 → "Dev Containers: Rebuild Container"
```
📚 [Container Troubleshooting](https://docs.wpshadow.com/troubleshooting/container-start)

**PHPCS not finding standards?**
```bash
# Reconfigure paths
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```
📚 [PHPCS Troubleshooting](https://docs.wpshadow.com/troubleshooting/phpcs-standards)

**Tests not running?**
```bash
# Check PHPUnit is installed
phpunit --version

# Reinstall dependencies
composer install
```
📚 [PHPUnit Troubleshooting](https://docs.wpshadow.com/troubleshooting/phpunit)

### Get Help

**You're not alone!** We're here to help:

- 💬 **Forum:** https://forum.wpshadow.com
- 📅 **Office Hours:** Tuesdays 2pm UTC (Free!)
- 📚 **Knowledge Base:** https://docs.wpshadow.com
- 🐛 **GitHub Issues:** Report bugs or request features

---

## 🎓 Educational Features

This environment is designed to teach, not just work:

### Helpful Error Messages
When something goes wrong, you get:
- **What happened** - The technical error
- **Why it matters** - The impact and context
- **How to fix it** - Clear next steps
- **Learn more** - KB articles for deeper understanding

### Progress Tracking
Your KPI dashboard shows:
- Concrete metrics of your work
- Time saved by automation
- Achievement levels
- Motivational milestones

### Integrated Learning
Every script includes:
- Comments explaining "why" not just "what"
- Links to relevant KB articles
- Time estimates for learning
- Beginner-friendly explanations

💡 *Philosophy: Commandment #1 - Helpful Neighbor Experience*

---

## 🔐 Privacy & Security

> **Commandment #10: Beyond Pure** - Privacy as brand value

- Your KPIs are stored **locally only** (`.devcontainer/.dev-kpis.json`)
- No tracking, no telemetry, no phone-home
- Your code stays on your machine
- All tools respect your privacy

---

## 🚀 VS Code Tips

### Useful Commands

- `F1` → "WordPress: Activate Plugin"
- `F1` → "PHP CS Fixer: Fix this file"  
- `F1` → "PHPUnit: Run Test" (on test file)
- `F1` → "Git: View Commit History"

### Recommended Extensions

Already installed for you:
- PHP Intelephense (code intelligence)
- PHP Debug (Xdebug integration)
- phpcs (inline code standards)
- WordPress Snippets (code snippets)

---

## 📈 Next Steps

### Explore Further

1. **📖 Read** [Complete Learning Resources](LEARNING_RESOURCES.md)
2. **🎯 Choose** Your learning path
3. **💻 Practice** Make your first change
4. **📊 Track** Check your KPIs
5. **🎉 Celebrate** Share your progress!

### Contribute Back

Found this helpful? Help others:
- Share on social media (Commandment #11: Talk-About-Worthy!)
- Answer questions in the forum
- Contribute to documentation
- Submit improvements

---

## 🏆 Your Journey Starts Here

Remember: **Every expert was once a beginner**. This environment is designed to support you at every step.

Questions? Check `composer kpi` to see how far you've already come!

---

*Built with ❤️ by the WPShadow community*  
*Following our 11 Commandments and 3 CANON Pillars*
