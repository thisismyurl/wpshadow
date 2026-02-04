# Content Review Wizard - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          WPSHADOW CONTENT REVIEW SYSTEM                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                               │
│  ┌──────────────────┐            ┌──────────────────┐                       │
│  │   WordPress      │            │    WPShadow      │                       │
│  │   Edit Post      │            │   Diagnostics    │                       │
│  │   Screen         │            │   Registry       │                       │
│  └────────┬─────────┘            └────────┬─────────┘                       │
│           │                               │                                  │
│           │ User clicks                   │ Get all                          │
│           │ "Review Content"              │ diagnostics                      │
│           │                               │                                  │
│           └───────────────┬───────────────┘                                  │
│                           │                                                   │
│                           ▼                                                   │
│        ┌──────────────────────────────────┐                                  │
│        │ Content_Review_Manager           │                                  │
│        │  (Core Orchestrator)             │                                  │
│        │                                  │                                  │
│        │ • fetch_diagnostics()            │                                  │
│        │ • get_user_preferences()         │                                  │
│        │ • manage_tips()                  │                                  │
│        │ • manage_skips()                 │                                  │
│        │ • get_kb_articles()              │                                  │
│        │ • get_training_courses()         │                                  │
│        └────────┬────────────────────┬───┘                                   │
│                 │                    │                                        │
│     ┌───────────┴──────┬────────────┴──────┬──────────┐                      │
│     │                  │                   │          │                      │
│     ▼                  ▼                   ▼          ▼                      │
│  ┌──────┐         ┌──────────┐      ┌─────────┐  ┌──────────┐               │
│  │AJAX  │         │User      │      │Cloud    │  │Report    │               │
│  │Handle│         │Prefs     │      │Service  │  │System    │               │
│  │ors   │         │Meta      │      │API      │  │          │               │
│  └──────┘         └──────────┘      └─────────┘  └──────────┘               │
│     │                                                     │                   │
│     └─────────────────┬──────────────────────────────────┘                   │
│                       │                                                       │
│                       ▼                                                       │
│        ┌──────────────────────────────────┐                                  │
│        │      Frontend UI                 │                                  │
│        │                                  │                                  │
│        │ • content-review-wizard.js       │                                  │
│        │ • content-review-report.js       │                                  │
│        │ • content-review-wizard.css      │                                  │
│        │ • content-review-report.css      │                                  │
│        └──────────────────────────────────┘                                  │
│                       │                                                       │
│           ┌───────────┴───────────┐                                          │
│           │                       │                                          │
│           ▼                       ▼                                          │
│    ┌─────────────┐        ┌─────────────┐                                   │
│    │ Wizard      │        │ Report      │                                   │
│    │ Modal       │        │ Page        │                                   │
│    │             │        │             │                                   │
│    │ • Steps     │        │ • Filters   │                                   │
│    │ • Issues    │        │ • Table     │                                   │
│    │ • AI Sugg   │        │ • Details   │                                   │
│    │ • KB Links  │        │ • Actions   │                                   │
│    └─────────────┘        └─────────────┘                                   │
│                                                                               │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER ACTION FLOWS                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  FLOW 1: Pre-Publish Review                                     │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  1. User clicks "Review Content"                                │
│         ↓                                                        │
│  2. JS sends AJAX request                                       │
│         ↓                                                        │
│  3. Content_Review_Get_Data_Handler processes                   │
│         ↓                                                        │
│  4. Manager fetches:                                            │
│     • All diagnostics for post                                  │
│     • KB articles for each diagnostic                           │
│     • Training courses for families                             │
│     • User preferences                                          │
│     • Cloud registration status                                 │
│         ↓                                                        │
│  5. Data sent back to JS                                        │
│         ↓                                                        │
│  6. Wizard modal renders with steps                             │
│         ↓                                                        │
│  7. User navigates through issues                               │
│         ↓                                                        │
│  8. Optional actions:                                           │
│     • Hide tip → AJAX → Preference saved                        │
│     • Skip diagnostic → AJAX → Preference saved                 │
│     • Get AI suggestion → AJAX → Cloud API → Result displayed   │
│     • Generate report → AJAX → Report data returned             │
│                                                                  │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  FLOW 2: Formal Report                                          │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  1. User goes to Reports → Content Quality Report               │
│         ↓                                                        │
│  2. Sets filters (type, severity, search)                       │
│         ↓                                                        │
│  3. Clicks "Generate Report"                                    │
│         ↓                                                        │
│  4. Content_Review_Generate_Report_Handler processes            │
│         ↓                                                        │
│  5. Manager loads matching posts                                │
│         ↓                                                        │
│  6. For each post:                                              │
│     • Run diagnostics                                           │
│     • Count issues by severity                                  │
│     • Return summary                                            │
│         ↓                                                        │
│  7. Data sent to report page                                    │
│         ↓                                                        │
│  8. Report JS renders table:                                    │
│     • Post title, type, status                                  │
│     • Issue counts (critical, high, medium, low)                │
│     • Severity-based highlighting                               │
│         ↓                                                        │
│  9. User clicks post detail:                                    │
│     • Show severity breakdown chart                             │
│     • Show edit and review buttons                              │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Component Interaction Diagram

```
┌────────────────────────────────────────────────────────────────────┐
│                    COMPONENT INTERACTIONS                          │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│                   Content_Review_Manager                          │
│                   (Singleton)                                     │
│                                                                    │
│     ┌──────────────────────────────────────────────────────┐      │
│     │ • Manages metabox display                            │      │
│     │ • Coordinates diagnostics fetching                   │      │
│     │ • Handles user preferences (user_meta)              │      │
│     │ • Enqueues JS/CSS assets                             │      │
│     └──────────────────────────────────────────────────────┘      │
│            │           │              │              │            │
│            ▼           ▼              ▼              ▼            │
│      ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│      │Diagnostic│ │WordPress │ │KB/Training│ │Cloud API│         │
│      │Registry  │ │Options   │ │Filters   │ │Connector│         │
│      │          │ │(user meta)│ │(Hooks)   │ │          │         │
│      └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                                                                    │
│      ─────────────────────────────────────────────────────────   │
│                                                                    │
│            AJAX Handlers Layer                                    │
│                                                                    │
│      ┌──────────────────────────────────────────────────┐         │
│      │ • Get_Data_Handler                               │         │
│      │ • Hide_Tip_Handler                               │         │
│      │ • Skip_Diagnostic_Handler                        │         │
│      │ • AI_Improvement_Handler                         │         │
│      │ • Generate_Report_Handler                        │         │
│      └──────────────────────────────────────────────────┘         │
│                    │                                              │
│      All handlers extend AJAX_Handler_Base                        │
│      Automatic security:                                          │
│        • Nonce verification                                       │
│        • Capability checks                                        │
│        • Input sanitization                                       │
│                                                                    │
│      ─────────────────────────────────────────────────────────   │
│                                                                    │
│            Frontend Layer                                         │
│                                                                    │
│      ┌─────────────────────┬─────────────────────────┐           │
│      │                     │                         │           │
│      ▼                     ▼                         ▼           │
│   Wizard Modal         Report Page         Assets/Config         │
│                                                                    │
│   • ContentReview      • ContentReview    • CSS Styling          │
│     Wizard Class         Report Class     • JS Event Handlers    │
│   • Multi-step UI      • Filtering        • Localized Data       │
│   • AI modals          • Detail view      • Nonce & URLs         │
│   • User actions       • Navigation                              │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

## User Preference Storage Schema

```
WordPress User Meta:
┌─────────────────────────────────────────────────────┐
│ Meta Key: wpshadow_review_preferences               │
│ Meta Value:                                         │
│ {                                                   │
│   "hide_tips": [                                    │
│     "content-missing-alt-text",                     │
│     "keyword-stuffing",                             │
│     ...                                             │
│   ],                                                │
│   "skip_diagnostics": [                             │
│     "seo-title-tags",                               │
│     "meta-descriptions",                            │
│     ...                                             │
│   ],                                                │
│   "show_ai_tips": true|false,                       │
│   "show_kb_links": true|false                       │
│ }                                                   │
└─────────────────────────────────────────────────────┘
```

## Diagnostic Family Workflow

```
┌──────────────────────────────────────────────────────┐
│ Available Diagnostic Families (Checked by Wizard)   │
├──────────────────────────────────────────────────────┤
│                                                      │
│  1. CONTENT                                          │
│     ├─ Missing featured image                        │
│     ├─ Missing excerpt                               │
│     ├─ Content too short                             │
│     ├─ Post revision bloat                           │
│     └─ ... (more content diagnostics)               │
│                                                      │
│  2. SEO                                              │
│     ├─ Missing meta description                      │
│     ├─ Keyword not in title                          │
│     ├─ Internal link weak                            │
│     ├─ No H1 tag                                     │
│     └─ ... (15+ SEO diagnostics)                    │
│                                                      │
│  3. ACCESSIBILITY                                    │
│     ├─ Missing alt text                              │
│     ├─ Heading hierarchy broken                      │
│     ├─ Links not descriptive                         │
│     └─ ... (WCAG compliance checks)                 │
│                                                      │
│  4. READABILITY                                      │
│     ├─ Long paragraphs                               │
│     ├─ Long sentences                                │
│     ├─ Complex vocabulary                            │
│     └─ ... (readability metrics)                    │
│                                                      │
│  5. CODE-QUALITY                                     │
│     ├─ Inline styles                                 │
│     ├─ Deprecated markup                             │
│     └─ ... (performance & standards)                │
│                                                      │
└──────────────────────────────────────────────────────┘

Each family becomes a wizard step ↓

Step structure:
  ┌──────────────────────────────────────┐
  │ [Family Name] Review                 │
  │                                      │
  │ Issue 1 (severity badge)             │
  │  Description and impact              │
  │  KB Article Link                     │
  │  [✨ Get AI Suggestion]              │
  │  [Hide this tip] [Skip in future]    │
  │                                      │
  │ Issue 2 (severity badge)             │
  │  ...                                 │
  │                                      │
  └──────────────────────────────────────┘
```

## Severity Level System

```
┌────────────────────────────────────────────────┐
│ SEVERITY LEVELS & IMPACT                       │
├────────────────────────────────────────────────┤
│                                                │
│ 🔴 CRITICAL (50+ point impact)                │
│    └─ Major issue affecting all users         │
│       • Broken page structure                 │
│       • Security vulnerability                │
│       • Complete accessibility failure         │
│       • No page ranking possible               │
│                                                │
│ 🟠 HIGH (30-49 point impact)                  │
│    └─ Significant issue                       │
│       • Missing critical metadata              │
│       • Major accessibility barrier            │
│       • Poor readability (< 50% readability)  │
│       • Impacts SEO ranking significantly      │
│                                                │
│ 🔵 MEDIUM (10-29 point impact)                │
│    └─ Moderate issue                          │
│       • Missing optional metadata              │
│       • Moderate accessibility issues          │
│       • Could improve readability              │
│       • May impact SEO ranking                 │
│                                                │
│ 🟦 LOW (1-9 point impact)                     │
│    └─ Minor improvement                       │
│       • Nice-to-have optimizations             │
│       • Minor accessibility concern            │
│       • Small readability boost                │
│       • Minimal SEO impact                     │
│                                                │
└────────────────────────────────────────────────┘
```

## Feature Availability by User State

```
┌────────────────────────────────────────────────┐
│ FEATURE AVAILABILITY MATRIX                    │
├─────────────────────────────────────────────────┤
│                                                │
│ Not Registered with Cloud:                     │
│  ✓ Review wizard available                     │
│  ✓ Pre-publish suggestions work                │
│  ✓ KB articles visible                         │
│  ✓ Training visible                            │
│  ✓ User preferences work                       │
│  ✓ Report generation works                     │
│  ✗ AI suggestions NOT available               │
│  ✗ Cloud improvement button hidden            │
│                                                │
│ Registered with Cloud:                         │
│  ✓ All above features                          │
│  ✓ "Get AI Suggestion" buttons visible        │
│  ✓ Cloud API requests work                     │
│  ✓ AI modal displays suggestions               │
│  ✓ AI suggestions per-aspect                   │
│  ✓ Toggle AI tips preference                   │
│                                                │
└────────────────────────────────────────────────┘
```

---

This diagram shows the complete architecture, data flows, and component interactions of the Content Review System. All components work together seamlessly to provide users with a comprehensive content review experience.
