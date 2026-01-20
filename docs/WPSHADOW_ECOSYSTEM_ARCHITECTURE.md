# WPShadow Modular Ecosystem Architecture

**Date:** January 2026  
**Status:** Active - 7 repositories

## Repository Hierarchy

### Tier 1: Core Plugin
```
wpshadow/
├─ Purpose: Base detection, diagnostics, safety features
├─ Type: Standalone WordPress plugin
├─ Role: Detection engine + fixing foundation
└─ All other repos can integrate or extend
```

**What it does:**
- Detects WordPress health issues (security, performance, conflicts)
- Auto-fixes safe issues
- Provides activity logging and health dashboard
- Foundation for all other modules

---

### Tier 2: Pro Ecosystem - Foundation Systems

#### wpshadow-pro-vault/
```
Purpose: Secure storage and encryption foundation
Type: Standalone WordPress plugin (can be used without wpshadow)
Dependencies: None
Extends: Provides storage backend for media and backup systems
```

**What it does:**
- Secure original file storage
- Encryption for sensitive files
- Media file offload to cloud
- Journaling for audit trails

**Modules that use this:**
- wpshadow-pro-wpadmin-media (can store versions here)

---

#### wpshadow-pro-integration/
```
Purpose: Third-party design tool integrations
Type: Standalone WordPress plugin
Dependencies: None (optional integration enhancement)
Extends: Allows WPShadow content to work with external tools
```

**What it does:**
- Canva integration
- Adobe Express integration
- Figma integration
- Allows users to work with design tools while maintaining WPShadow quality

---
### Tier 1: FREE Base Plugin
```
wpshadow/ (FREE - except SaaS features)
├─ Purpose: Base detection, diagnostics, safety features
├─ License: Free (open source)
├─ Type: Standalone WordPress plugin
├─ Role: Detection engine + fixing foundation for all users
└─ Revenue: Free tier + optional SaaS features (dashboard hosting, etc.)
```

**What it does:**
- Detects WordPress health issues (security, performance, conflicts)
- Auto-fixes safe issues
- Provides activity logging and health dashboard
- Foundation for all other modules
- Works completely standalone - no dependencies on paid modules

**Who uses it:**
- Individual site owners (free)
- Agencies (free)
- Enterprises (free, with optional SaaS)

---

### Tier 2: PRO Wrapper/Loader Plugin

#### wpshadow-pro/
```
Purpose: Pro features wrapper + module loader
License: Paid (requires active subscription)
Type: WordPress plugin that requires wpshadow/ as dependency
Dependencies: REQUIRES wpshadow/ (base plugin)
Role: Extends wpshadow with paid features + loads paid modules
```

**What it does:**
- Requires wpshadow/ to be installed and activated
- Adds premium features to wpshadow (advanced diagnostics, priority support, etc.)
- Acts as the loader/manager for pro modules:
  - wpshadow-pro-vault
  - wpshadow-pro-integration
  - wpshadow-pro-wpadmin-media
- Provides unified interface for all pro functionality

**Who uses it:**
- Paying customers only
- Requires active wpshadow/ installation

---

### Tier 3: PRO Modules (loaded by wpshadow-pro)

Each of these is loaded/managed by `wpshadow-pro/`. They cannot function without wpshadow-pro being active.

#### wpshadow-pro-vault/
```
Purpose: Secure backup, encryption, and offsite storage
Type: Pro Module (loads within wpshadow-pro)
Dependencies: Requires wpshadow-pro (which requires wpshadow)
Role: Backup and storage infrastructure
```

**What it does:**
- Full WordPress backups (database + files)
- Encryption for backups
- Offsite cloud storage
- Secure original file storage
- Media file offload to cloud
- Journaling for audit trails
- One-click restore capabilities

**Access:**
- Only available if wpshadow-pro is subscribed
- Loaded and managed by wpshadow-pro

---

#### wpshadow-pro-integration/
```
Purpose: Third-party website and tool integrations
Type: Pro Module (loads within wpshadow-pro)
Dependencies: Requires wpshadow-pro (which requires wpshadow)
Role: Integration and compatibility enhancements
```

**What it does:**
- Canva integration
- Adobe Express integration
- Figma integration
- Improves compatibility with various website builders
- Allows WPShadow content to work seamlessly with external tools

**Access:**
- Only available if wpshadow-pro is subscribed
- Loaded and managed by wpshadow-pro

---

#### wpshadow-pro-wpadmin-media/
```
Purpose: WordPress media library improvements
Type: Pro Module (loads within wpshadow-pro)
Dependencies: Requires wpshadow-pro (which requires wpshadow)
Role: Media management and optimization
```

**What it does:**
- Unified media management interface
- Shared media optimization logic
- Transcoding framework
- Smart image/video/document handling
- Shared storage and retrieval

**Sub-features:**
- Image enhancement (filters, overlays, social optimization)
- Video management (editing, chapters, streaming)
- Document management (preview, search, versioning)

**Access:**
- Only available if wpshadow-pro is subscribed
- Loaded and managed by wpshadow-pro

---

## Architecture Patterns

### Dependency Model
```
BASE TIER (FREE):
wpshadow/ (standalone, free)
  ├─ Completely functional
  ├─ No dependencies
  └─ Free tier + optional SaaS features

PRO TIER (PAID):
wpshadow-pro/ (requires wpshadow/)
  └─ Loads pro modules:
      ├─ wpshadow-pro-vault (backup & storage)
      ├─ wpshadow-pro-integration (integrations)
      └─ wpshadow-pro-wpadmin-media (media improvements)
         └─ Contains features:
             - Image enhancement
             - Video management
             - Document management
```

### Upgrade Path
```
FREE TIER:
  └─ wpshadow/
     └─ Full WordPress health + diagnostics
        (no pro modules available)

PRO TIER (All-in-one):
  └─ wpshadow/ (free base)
     + wpshadow-pro/ (paid wrapper)
        ├─ wpshadow-pro-vault
        ├─ wpshadow-pro-integration
        └─ wpshadow-pro-wpadmin-media

SPECIALIST TIERS (Future):
  └─ wpshadow/ (free base)
     + wpshadow-pro-specialist-[area]/ (focused pro variant)
```

### Revenue Model
```
FREE:
  └─ wpshadow/ usage = revenue from SaaS features (optional)

PRO:
  └─ wpshadow-pro/ subscription = revenue from all modules
  └─ Premium support add-on
  └─ SaaS features add-on
```

## Summary Table

| Repository | Type | Purpose | Role |
|-----------|------|---------|------|
| **wpshadow** | Core | Detection + fixing + diagnostics | Foundation |
| **wpshadow-pro-vault** | Pro Module | Secure storage + encryption | Independent |
| **wpshadow-pro-integration** | Pro Module | Third-party tool integrations | Independent |
| **wpshadow-pro-wpadmin-media** | Pro Hub | Media management framework | Parent |
| **wpshadow-pro-wpadmin-media-image** | Pro Module | Image enhancement | Child (media) |
| **wpshadow-pro-wpadmin-media-video** | Pro Module | Video management | Child (media) |
| **wpshadow-pro-wpadmin-media-document** | Pro Module | Document management | Child (media) |

---

## Next Steps

1. **Create core experience issues** in `wpshadow/` for universal UX improvements
2. **Create module-specific issues** in specialized repos for module-unique features
3. **Link related issues** across repos for visibility
4. **Plan specialist variants** (login, agency, content) once core experience is complete
