# Documentation Directory

This directory contains development roadmaps, architectural decisions, and planning documents for the WP Support plugin and its ecosystem.

## Contents

### Module Planning Documents

#### [MAINTENANCE_MODULE_ROADMAP.md](MAINTENANCE_MODULE_ROADMAP.md)
Comprehensive roadmap for the planned Maintenance Support module (deferred to post-M02).

**Status:** Deferred  
**Type:** Spoke Module  
**Target:** Post-M02  

This document outlines:
- Decision rationale for deferring to separate module
- Complete feature set (daily/weekly/monthly tasks)
- Technical architecture (WP-Cron, batch processing)
- Development phases and timeline
- Integration points with core features
- Success criteria and testing plan

## Module Development Status

| Module | Type | Status | Priority | Documentation |
|--------|------|--------|----------|---------------|
| Maintenance Support | Spoke | Planned | Post-M02 | [Roadmap](MAINTENANCE_MODULE_ROADMAP.md) |

## Adding New Documents

When creating new planning or roadmap documents:

1. Use clear, descriptive filenames (e.g., `MODULE_NAME_ROADMAP.md`)
2. Include decision date and decision maker
3. Document rationale and architecture
4. Specify integration points with core
5. Define success criteria
6. Update this README with a link

## Related Directories

- **`/modules/`** - Module catalog and installed modules
- **`/includes/`** - Core plugin functionality
- **`/docs/`** - Planning and architecture documents (this directory)

## External Documentation

- **User Guide:** [DASHBOARD_USER_GUIDE.md](../DASHBOARD_USER_GUIDE.md)
- **Privacy:** [VAULT_PRIVACY_ERASER.md](../VAULT_PRIVACY_ERASER.md)
- **Changelog:** [CHANGELOG.md](../CHANGELOG.md)
- **README:** [README.md](../README.md)

---

**Last Updated:** January 13, 2026
