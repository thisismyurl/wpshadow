# TIMU Suite Milestone Structure

**Last Updated:** January 8, 2026

This document defines the unified milestone structure enforced across all TIMU Suite plugins. All repositories follow this standard to ensure consistent versioning, feature planning, and release coordination.

## Milestone Philosophy

- **Phased Delivery**: Features organized by development phase (M01–M05), with M99 reserved for long-term vision
- **Suite-Wide Consistency**: All plugins track the same milestones for synchronized releases
- **Dependency Clarity**: Earlier milestones build foundation; later milestones depend on earlier ones
- **Community Friendly**: Clear phases help external contributors understand roadmap

## Unified Milestone Structure

### **M01 — Foundations** (Due: 2026-01-31)
**Status:** In Progress

Establishes core infrastructure, admin UI, module discovery, catalog system, updates API, and baseline compliance (security, accessibility, i18n).

**Scope Across Suite:**
- **Core:** Admin menu, modules dashboard, catalog, licensing, baseline compliance
- **Image Support:** Basic UI, format registration, preview of features
- **License Support:** License data model, admin UI, activation hooks
- **Vault Support:** (Optional) Vault directory setup, encryption key generation UI
- **Media Support:** (Optional) Media module scope definition, architecture

**Key Deliverables:**
- [ ] Production-ready admin interface
- [ ] Module discovery & display
- [ ] Basic licensing & registration
- [ ] WCAG 2.2 AA accessibility baseline
- [ ] i18n infrastructure
- [ ] Security baselines (SVE Protocol, nonces, capabilities)

**Acceptance Criteria:**
- All repositories have working admin dashboards
- Module installation/activation works
- PHPCS/PHPStan pass at 100%
- No critical security vulnerabilities

---

### **M02 — Core Features** (Due: 2026-02-29)
**Status:** Planned

Implements primary feature set for each plugin (Vault for Core, Image processing for Image Support, etc.). Builds on M01 foundation.

**Scope Across Suite:**
- **Core:** Vault (encryption, on-upload capture, integrity verification, rollback, journaling)
- **Image Support:** Image processing engines (Imagick/GD fallback), format conversion, compression presets, EXIF handling
- **License Support:** License verification, activation/deactivation, webhook integration, REST API
- **Vault Support:** (If separate) Full vault implementation (moved from Core)
- **Media Support:** Media library integration, attachment hooks

**Key Deliverables:**
- [ ] Vault fully functional (Core/Vault Support)
- [ ] Image optimization pipeline (Image Support)
- [ ] License verification hooks (License Support)
- [ ] Media library integration (Media Support)
- [ ] WP-CLI commands for all major operations
- [ ] Background queue processing

**Acceptance Criteria:**
- Feature set is production-ready
- Background jobs process without errors
- All critical paths tested
- Multisite support verified

---

### **M03 — Diagnostics & Bootstrap** (Due: 2026-03-31)
**Status:** Planned

Adds health diagnostics, environment checks, safe mode isolation, configuration tools, support bundles. Improves observability and troubleshooting.

**Scope Across Suite:**
- **Core:** Suite health checks, environment diagnostics, safe mode toggle, config export/import, support bundle generator
- **Image Support:** Image processing health checks, engine availability diagnostics
- **License Support:** License validation diagnostics, webhook health verification
- **Vault Support:** Vault integrity checks, encryption key validation
- **Media Support:** Media library health status

**Key Deliverables:**
- [ ] Site Health integration (WordPress admin dashboard)
- [ ] Health check UI in each plugin dashboard
- [ ] Safe mode toggle for debugging
- [ ] Config export/import for migration
- [ ] Support bundle generator (one-click diagnostic package)
- [ ] Clear troubleshooting documentation

**Acceptance Criteria:**
- All plugins integrate with WordPress Site Health
- Support bundle captures all relevant diagnostics
- Troubleshooting documentation is comprehensive
- No false positives in health checks

---

### **M04 — Multisite & UX** (Due: 2026-04-15)
**Status:** Planned

Delivers multisite governance, network admin policies, feature discovery UX, privacy UI, bulk operations, and mobile responsiveness.

**Scope Across Suite:**
- **Core:** Network policies, plugin list hiding (role-sensitive), network governance UI
- **Image Support:** Multisite image processing policies, network-wide format support
- **License Support:** Network license broadcasting, site-level overrides
- **Vault Support:** Network vault policies, shared storage configuration
- **Media Support:** Multisite media library governance

**Key Deliverables:**
- [ ] Network admin policies for all features
- [ ] Per-site overrides (where allowed)
- [ ] Feature search & discovery UX
- [ ] Privacy UI (data retention, consent, export/erase)
- [ ] Bulk operations (rehydrate, verify, optimize)
- [ ] Mobile-responsive admin interface
- [ ] Dashboard pagination & filtering

**Acceptance Criteria:**
- Multisite admins can enforce global policies
- Site admins can override (where allowed)
- Feature discovery is intuitive
- All interfaces responsive at 320px+
- Privacy workflows comply with GDPR/CCPA

---

### **M05 — Templates & Dependencies** (Due: 2026-04-30)
**Status:** Planned

Standardizes format add-on template, WordPress plugin dependencies, versioning schemes, package signatures, privacy templates.

**Scope Across Suite:**
- **Core:** Versioning standard, dependency specification, privacy template
- **Format Plugins (AVIF, WebP, HEIC, etc.):** Standardized template, dependency declaration, signature validation
- **Image Support:** Base format plugin template, reference implementation
- **License Support:** License verification in dependency resolution
- **Vault Support:** Dependency on Core vault features

**Key Deliverables:**
- [ ] Format add-on template (reusable boilerplate)
- [ ] WP 6.5+ Plugin Dependencies support
- [ ] Versioning scheme: `v1.YYmmdd.HHMM` (hierarchical, per-plugin)
- [ ] Package signatures & checksums
- [ ] Privacy exporter/eraser template for add-ons
- [ ] Contributing guidelines aligned across all repos
- [ ] Automated versioning & changelog generation

**Acceptance Criteria:**
- New format plugins use template with minimal customization
- Dependencies enforce correctly via WordPress
- Version numbering is consistent and sortable
- All packages are cryptographically signed
- Privacy compliance across all add-ons

---

### **M06 — Privacy & Compliance** (Due: 2026-04-30)
**Status:** Planned | Cross-cutting (parallel to M02–M05)

GDPR/CCPA compliance, data export/erase workflows, audit logging, consent management. Distributed across earlier milestones but consolidated here.

**Scope Across Suite:**
- **Core:** Privacy hooks, audit logging, data retention policies
- **Image Support:** Image metadata export/erase
- **License Support:** License registration data handling
- **Vault Support:** Original file erasure, personal data purge
- **Media Support:** Media attachment privacy

**Key Deliverables:**
- [ ] WordPress Tools → Export/Erase Personal Data integration
- [ ] Audit trail for all privacy operations
- [ ] Data retention policy engine
- [ ] GDPR Article 17 (right to be forgotten) compliance
- [ ] CCPA compliance features
- [ ] Privacy policy generator

**Acceptance Criteria:**
- Data export returns all user personal data
- Data erase purges all traces (where applicable)
- Audit trail is immutable and comprehensive
- Legal review completed

---

### **M99 — Vision & Future** (Ongoing)
**Status:** Backlog

Long-term enhancements, nice-to-have features, community feedback, and strategic improvements not blocking core releases.

**Examples:**
- Gutenberg blocks for TIMU suite status
- REST API endpoints for headless integration
- Advanced UX polish (micro-interactions, animations)
- Machine learning features (AI alt-text assistant, smart focus detection)
- Cloud provider integration (S3, Azure, GCS offloading)
- License bundling & cross-sell features
- Affiliate system
- GraphQL API
- Customer success tooling

**Scope:** No hard due date; features moved up to M02–M06 as capacity allows.

---

## Milestone Rules & Enforcement

### 1. **All Repositories Use Same Milestones**

Every TIMU Suite plugin repository must implement the exact milestone structure above:

- **Repositories in scope:**
  - `core-support-thisismyurl`
  - `image-support-thisismyurl`
  - `media-support-thisismyurl`
  - `license-support-thisismyurl`
  - `vault-support-thisismyurl`
  - All format spokes (AVIF, WebP, HEIC, RAW, SVG, TIFF, BMP, GIF)

- **Milestone naming:** Exact match (M01, M02, M03, M04, M05, M06, M99)
- **Due dates:** Synchronized across all repos
- **Descriptions:** Reference this document; tailor repo-specific scope

### 2. **Issue Assignment Rules**

Every issue must be assigned to exactly one milestone:

- **When creating an issue:**
  1. Determine which phase the feature belongs to (M01–M05, M06, or M99)
  2. Assign to appropriate milestone immediately
  3. Link to blocking/dependent issues (if any)
  4. Use labels: `core`, `enhancement`, `bug`, `priority` (P1/P2/P3)

- **Exceptions:**
  - Bugs discovered during current milestone → assign to current milestone
  - Bugs in old releases → assign to M99 (backlog)
  - Unplanned work → discuss; assign to next available milestone

### 3. **Cross-Repo Dependency Tracking**

Since plugins have dependencies (Image depends on Core, Vault depends on Media or Core), ensure:

- **Core milestones are locked first** (M01 completes before M02 can start)
- **Child plugins track Core milestones** (Image Support M02 starts after Core M01 is done)
- **Blockers are documented** (e.g., "Image Support M02 blocked by Core M01")

### 4. **Release Coordination**

Milestones drive release cycles:

| Milestone | Release Date | Version | Artifacts |
|-----------|---|---|---|
| M01 | 2026-01-31 | v1.2601.0131 (date-based) | Core 1.0.0, Image 1.0.0, License 1.0.0 |
| M02 | 2026-02-29 | v1.2602.0229 | Core 1.1.0, Image 1.1.0, Vault 1.0.0 |
| M03 | 2026-03-31 | v1.2603.0331 | Core 1.2.0, Image 1.2.0, etc. |
| M04 | 2026-04-15 | v1.2604.0415 | Core 1.3.0, all plugins 1.3.0 |
| M05 | 2026-04-30 | v1.2604.0430 | Core 1.4.0, format plugins released |

**Version Scheme:** `v{major}.{YYmm00}.{HHMM}` (hierarchical, sortable, date-traceable)

### 5. **Milestone Review Cadence**

Every Friday:
- Review milestone progress
- Adjust due dates if needed (add/remove issues, not slippage)
- Flag blockers or dependencies
- Update milestone descriptions with current status

Every month (end of month):
- Close completed milestone
- Publish release notes
- Move incomplete issues to next milestone or M99

### 6. **Scope Creep Prevention**

- **No issue added to a milestone within 2 weeks of its due date** (avoid last-minute scope creep)
- **M99 is the "holding area"** — move new requests there; promote to M02+ during planning cycles
- **"Nice-to-have" features go to M99** first; only promote if explicitly committed

---

## Repository-Specific Milestone Scope

### **core-support-thisismyurl**
The hub and foundational layer. Milestones establish:
- Admin infrastructure
- Module discovery
- Licensing
- Vault infrastructure
- Diagnostics
- Multisite governance

### **image-support-thisismyurl**
Image format processing spoke. Milestones establish:
- Image optimization engines
- Format support (WebP, AVIF, etc.)
- EXIF handling
- Responsive srcset generation
- Integration with Vault (originals)

### **media-support-thisismyurl**
Media library hub. Milestones establish:
- Media library integration
- Attachment hook infrastructure
- Upload interception
- Media management UI

### **license-support-thisismyurl**
Licensing & validation. Milestones establish:
- License data model
- Registration & verification
- Activation/deactivation
- REST API & webhooks
- Analytics dashboard (M99)

### **vault-support-thisismyurl**
Secure original storage (may move from Core to separate plugin). Milestones establish:
- Vault directory & encryption
- On-upload capture
- Rollback engine
- Journaling & audit
- Cloud offload providers (M99)

---

## Milestone Communication

### GitHub Issues
- Always include milestone link in issue description
- Use milestone-specific labels (M01, M02, etc.) alongside milestones
- Link blocking/dependent issues

### Changelog
- Organize CHANGELOG.md by milestone
- Example:
  ```
  ## [1.1.0] — M02 (2026-02-29)
  ### Added
  - Vault encryption engine
  - Rollback functionality
  - WP-CLI vault commands
  ```

### Release Notes
- Title: "M02 — Core Features Release"
- Highlight new capabilities by plugin
- Link to migration guide if applicable

### Documentation
- README.md references current milestone
- Roadmap section links to GitHub milestones
- Compatibility matrix updated per release

---

## Escalation & Changes

### If a milestone is at risk:

1. **Identify blockers** — What issues are stuck?
2. **Reduce scope** — Move lower-priority issues to M99
3. **Add resources** — Request additional help
4. **Communicate** — Update GitHub issue #30 (Roadmap) with revised timeline

### If a new critical issue appears:

1. **Assess severity** (P1/P2/P3)
2. **If P1 blocking current milestone** — add to current milestone
3. **If P1 but not blocking** — add to next milestone with "high priority" label
4. **If P2/P3** — add to M99 for next planning cycle

### If a milestone's scope seems wrong:

1. **Post issue** labeled `planning` with detailed rationale
2. **Discuss in team** (async or sync meeting)
3. **Propose alternative** (move issues, adjust due date, split milestone)
4. **Update this document** once consensus is reached

---

## Enforcement via CI/CD

**Optional:** Add GitHub Actions workflow to enforce:
- [ ] Every issue has a milestone
- [ ] Milestone names match approved list
- [ ] Issue descriptions link to milestone context
- [ ] PR titles reference milestone or issue number

Example workflow (GitHub Actions):

```yaml
name: Milestone Enforcement
on: [issues, pull_request]
jobs:
  check:
    runs-on: ubuntu-latest
    steps:
      - name: Require Milestone
        if: github.event.action == 'opened'
        run: |
          if [ -z "${{ github.event.issue.milestone }}" ]; then
            echo "❌ Every issue must have a milestone."
            exit 1
          fi
      - name: Validate Milestone Name
        run: |
          milestone="${{ github.event.issue.milestone.title }}"
          if [[ ! $milestone =~ ^M0[1-6]|M99$ ]]; then
            echo "❌ Milestone must be M01–M06 or M99."
            exit 1
          fi
```

---

## Questions & Updates

For questions or proposed changes to this structure:
1. Open an issue labeled `planning` + `roadmap`
2. Reference this document with specific section
3. Propose alternative (with rationale)
4. Await consensus before implementing

Last review: January 8, 2026
Next review: January 31, 2026 (post-M01)
