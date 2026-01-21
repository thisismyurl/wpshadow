# Phase 5 & 6 Implementation Plan

## Phase 5: Knowledge Base & Training Integration (📚)
**Focus:** Educate users with every diagnostic/treatment. Create searchable KB system.

### Phase 5 Tasks (4-5 days)

#### Task 1: KB Article Generator System (8 hours)
Create framework to auto-generate KB articles from diagnostics/treatments:
- `class-kb-article-generator.php` - Template engine
- `class-kb-library.php` - Article storage & search
- `class-kb-formatter.php` - Markdown → HTML conversion

**Files to create:**
```
/includes/knowledge-base/
├── class-kb-article-generator.php
├── class-kb-library.php
├── class-kb-formatter.php
└── templates/
    ├── diagnostic-template.php
    ├── treatment-template.php
    └── training-template.php
```

**Schema:** Each article includes:
- What it is (simple explanation)
- Why it matters (business impact)
- Fix yourself (DIY instructions)
- Learn more (training links)
- Related articles (linkage)

#### Task 2: Training Course Integration (6 hours)
Embed free training courses directly in plugin:
- `class-training-provider.php` - Course API integration
- `class-training-progress.php` - User progress tracking
- `class-training-video-library.php` - Video repository

**Features:**
- 2-5 minute videos per topic
- Certificates (free, motivational)
- Progress badges in dashboard
- "Learn more about this" links after fixes

#### Task 3: Contextual Help System (6 hours)
Integrate KB articles into every diagnostic/treatment:
- Update `class-diagnostic-registry.php` to include KB link metadata
- Update `class-treatment-registry.php` to include KB link metadata
- Add "Learn more" button to dashboard UI

**Implementation:**
```php
// In each diagnostic/treatment class:
protected $kb_article_id = 'security-headers';
protected $training_video_id = 'why-security-headers-matter';
```

#### Task 4: Search & Discovery (4 hours)
Full-text search across KB articles:
- `class-kb-search.php` - Indexing and search
- AJAX endpoint: `wpshadow_search_kb`
- UI: Dashboard search widget

#### Task 5: Helpful Neighbor Messaging (6 hours)
Post-fix education messages:
- `class-helpful-messages.php` - Message templates
- Integration with `Finding_Status_Manager`
- Add message on treatment application

---

## Phase 6: Privacy & Consent Excellence (🔒)
**Focus:** Transparent data handling. First-run privacy consent flow.

### Phase 6 Tasks (3-4 days)

#### Task 1: Privacy Policy Manager (6 hours)
Create privacy policy system:
- `class-privacy-policy-manager.php` - Policy storage
- `class-privacy-disclosure.php` - Service disclosure
- `class-consent-tracker.php` - Consent history

**Features:**
- Version tracking
- Notify admins on changes
- Export consent history
- Third-party service disclosure

#### Task 2: First-Run Consent Flow (8 hours)
Onboarding privacy consent:
- `class-first-run-consent.php` - Consent UI
- `class-consent-preferences.php` - User consent preferences
- Store in `user_meta` and `option` with versioning

**Consent Options:**
1. ✅ Basic usage data (anonymous, no IP, no user data)
2. ✅ Error reporting (stack traces only)
3. ❌ Optional: Anonymized telemetry (opt-in)

#### Task 3: Telemetry System (OPTIONAL) (4 hours)
If user consents to telemetry:
- `class-telemetry-collector.php` - Collect anonymous metrics
- `class-telemetry-sender.php` - Send to server (encrypted)
- Dashboard: "See your impact" visualization

**What we collect (if opted in):**
- Diagnostics ran (names only, no data)
- Treatments applied (names only, no data)
- Time saved (aggregate)
- Issues prevented (aggregate)

#### Task 4: Privacy UI Settings (4 hours)
Privacy settings page:
- View/change consent preferences
- View collected data
- Export data (GDPR-compliant)
- Delete all data

---

## Implementation Timeline

### Week 1 (Phase 5: KB & Training)
- Mon-Wed: Tasks 1-2 (KB Generator, Training Integration)
- Thu-Fri: Tasks 3-5 (Contextual Help, Search, Messaging)

### Week 2 (Phase 6: Privacy & Consent)
- Mon-Tue: Tasks 1-2 (Privacy Manager, Consent Flow)
- Wed-Thu: Task 3 (Telemetry - optional)
- Fri: Task 4 (Privacy Settings UI)

---

## Priority Order

### Phase 5 (MUST HAVE)
1. ✅ Task 1: KB Article Generator - Creates foundation
2. ✅ Task 3: Contextual Help System - Links KB to UI
3. ✅ Task 4: Search & Discovery - Makes KB findable

### Phase 5 (NICE TO HAVE)
4. Task 2: Training Integration - Motivational content
5. Task 5: Helpful Messages - Post-fix education

### Phase 6 (MUST HAVE)
1. ✅ Task 1: Privacy Policy Manager - Required for WP plugins
2. ✅ Task 2: First-Run Consent - Essential for data collection
3. ✅ Task 4: Privacy Settings UI - User control

### Phase 6 (OPTIONAL)
4. Task 3: Telemetry System - Nice-to-have metrics

---

## Success Metrics

### Phase 5 Complete When:
- ✅ 57 KB articles auto-generated from diagnostics
- ✅ 44 KB articles auto-generated from treatments
- ✅ Search finds articles in <100ms
- ✅ Users see "Learn more" links on every diagnostic
- ✅ Training videos embed successfully
- ✅ Post-fix messages display contextually

### Phase 6 Complete When:
- ✅ Privacy policy displays in settings
- ✅ First-run consent flow works on activation
- ✅ Users can view/change consent in settings
- ✅ Privacy settings page fully functional
- ✅ No data collected without explicit consent
- ✅ Export/delete functions work

---

## File Structure After Phases 5 & 6

```
includes/
├── knowledge-base/              [NEW - Phase 5]
│   ├── class-kb-article-generator.php
│   ├── class-kb-library.php
│   ├── class-kb-formatter.php
│   ├── class-kb-search.php
│   ├── class-training-provider.php
│   ├── class-training-progress.php
│   ├── class-helpful-messages.php
│   ├── templates/
│   │   ├── diagnostic-template.php
│   │   ├── treatment-template.php
│   │   └── training-template.php
│   └── data/
│       └── articles.json
│
├── privacy/                     [NEW - Phase 6]
│   ├── class-privacy-policy-manager.php
│   ├── class-privacy-disclosure.php
│   ├── class-consent-tracker.php
│   ├── class-consent-preferences.php
│   ├── class-first-run-consent.php
│   ├── class-telemetry-collector.php
│   ├── class-telemetry-sender.php
│   └── templates/
│       ├── consent-flow.php
│       ├── privacy-settings.php
│       └── policy-viewer.php
│
└── views/
    ├── knowledge-base/          [NEW - Phase 5]
    │   ├── kb-search.php
    │   ├── kb-article.php
    │   ├── training-library.php
    │   └── helpful-tips.php
    │
    └── privacy/                 [NEW - Phase 6]
        ├── first-run-consent.php
        ├── privacy-settings.php
        ├── privacy-policy.php
        └── data-export.php
```

---

## Dependencies & Integration Points

### Phase 5 Depends On:
- ✅ Diagnostic_Registry (already exists)
- ✅ Treatment_Registry (already exists)
- New: KB article metadata in registry

### Phase 6 Depends On:
- New: User_Preferences_Manager (Phase 4 ✅)
- New: Privacy settings page

### Both Phases Need:
- AJAX endpoints for search, consent, etc.
- Bootstrap integration in wpshadow.php
- Admin menu updates

---

## Let's Start with Phase 5, Task 1!

Ready to begin KB Article Generator system?
