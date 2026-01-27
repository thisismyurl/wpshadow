# CORE Documentation

Foundation and architecture documentation for WPShadow developers and contributors.

## 📚 Contents

| File | Purpose |
|------|---------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | System architecture, design patterns, and plugin structure |
| [CODING_STANDARDS.md](CODING_STANDARDS.md) | WordPress coding standards, security practices, and best practices |
| [SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md) | Technical overview of core systems |
| [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) | Plugin directory structure and organization |
| [HOOKS_REFERENCE.md](HOOKS_REFERENCE.md) | Complete reference of all hooks, filters, and actions |
| [WP_CLI_REFERENCE.md](WP_CLI_REFERENCE.md) | WP-CLI command reference and usage |

## 🎯 Start Here

**New to WPShadow codebase?**
1. Read: [ARCHITECTURE.md](ARCHITECTURE.md) - Understand the system design
2. Review: [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) - Know where things are
3. Study: [CODING_STANDARDS.md](CODING_STANDARDS.md) - Understand our patterns
4. Reference: [HOOKS_REFERENCE.md](HOOKS_REFERENCE.md) - Find extension points

**Contributing code?**
1. Must read: [CODING_STANDARDS.md](CODING_STANDARDS.md)
2. Review: [ARCHITECTURE.md](ARCHITECTURE.md) (design patterns)
3. Check: [HOOKS_REFERENCE.md](HOOKS_REFERENCE.md) (where to hook in)

**Building an extension?**
1. Start: [ARCHITECTURE.md](ARCHITECTURE.md) - Understand base classes
2. Learn: [HOOKS_REFERENCE.md](HOOKS_REFERENCE.md) - Extension points
3. Study: Code examples in `/examples/`

## 🌟 Core Values

These documents reflect our commitment to:
- ✅ **Commandment #10:** Beyond Pure (security and privacy in architecture)
- ✅ **Commandment #8:** Inspire Confidence (trustworthy design patterns)
- ✅ **Pillar 1:** 🌍 Accessibility First (built-in from start)

Learn more: [PHILOSOPHY/VISION.md](../PHILOSOPHY/VISION.md)

## 🔍 By Purpose

### Understanding the System
- [ARCHITECTURE.md](ARCHITECTURE.md) - How it's organized
- [SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md) - What it does
- [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) - Where things live

### Development Standards
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - How to code
- [HOOKS_REFERENCE.md](HOOKS_REFERENCE.md) - Where to extend

### Integration & Automation
- [WP_CLI_REFERENCE.md](WP_CLI_REFERENCE.md) - Command line tools

## 📖 Key Concepts

### Base Classes
All features in WPShadow extend from base classes defined in this architecture:
- `Diagnostic_Base` - For health checks
- `Treatment_Base` - For auto-fixes
- `AJAX_Handler_Base` - For AJAX endpoints

See [ARCHITECTURE.md](ARCHITECTURE.md) for full details.

### Namespace Convention
- Core: `WPShadow\Core\`
- Features: `WPShadow\Diagnostics\`, `WPShadow\Treatments\`, etc.
- Pro modules: `WPShadow\Pro\{ModuleName}\`

See [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) for complete structure.

### Security First
Every line of code follows security best practices:
- SQL injection prevention with prepared statements
- Output escaping for all user-facing content
- Nonce verification for all forms
- Capability checks on all admin actions

See [CODING_STANDARDS.md](CODING_STANDARDS.md) for requirements.

## 🚀 Quick Links

- **Plugin main file:** [wpshadow.php](../../wpshadow.php)
- **Core classes:** [includes/core/](../../includes/core/)
- **Contributing:** See [DEVELOPMENT/](../DEVELOPMENT/) folder
- **Testing:** See [TESTING/](../TESTING/) folder
- **Design system:** See [DESIGN/](../DESIGN/) folder

## 📚 Related Documentation

- **For features:** [FEATURES/](../FEATURES/)
- **For testing:** [TESTING/](../TESTING/)
- **For deployment:** [DEPLOYMENT/](../DEPLOYMENT/)
- **For design:** [DESIGN/](../DESIGN/)
- **For values:** [PHILOSOPHY/](../PHILOSOPHY/)

---

**Last Updated:** January 27, 2026  
**Audience:** Developers, Contributors, Extension Builders
