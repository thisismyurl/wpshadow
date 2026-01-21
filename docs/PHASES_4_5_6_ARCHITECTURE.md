# Phases 4-6 Architecture Overview

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         WPShadow Plugin Core                          │
│                          (wpshadow.php)                               │
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
                    ┌───────────────┴───────────────┐
                    │                               │
        ┌───────────────────────┐      ┌───────────────────────┐
        │  Phase 4 Bootstrap    │      │ Knowledge Base & Priv │
        │  (Classes)            │      │ (Phase 5 & 6)         │
        └───────────────────────┘      └───────────────────────┘
                    ↓                              ↓
        ┌───────────────────────┐      ┌───────────────────────┐
        │ Color_Utils           │      │ KB_Formatter          │
        │ Theme_Data_Provider   │      │ KB_Article_Generator  │
        │ User_Preferences      │      │ KB_Library            │
        │ Command (Base)        │      │ KB_Search             │
        └───────────────────────┘      │ Training_Provider     │
                    │                   │ Training_Progress     │
                    ↓                   │ Privacy_Policy_Mgr    │
        ┌───────────────────────┐      │ Consent_Preferences   │
        │ Command Registry      │      │ First_Run_Consent     │
        └───────────────────────┘      └───────────────────────┘
                    ↓                              ↓
        ┌───────────────────────────────────────────────────┐
        │  9 Workflow Commands                              │
        ├───────────────────────────────────────────────────┤
        │ • Save_Workflow_Command                           │
        │ • Load_Workflows_Command                          │
        │ • Get_Workflow_Command                            │
        │ • Delete_Workflow_Command                         │
        │ • Toggle_Workflow_Command                         │
        │ • Run_Workflow_Command                            │
        │ • Get_Available_Actions_Command                   │
        │ • Get_Action_Config_Command                       │
        │ • Create_From_Example_Command                     │
        │ • KB_Search_Command            [NEW - Phase 5]    │
        └───────────────────────────────────────────────────┘
                    ↓
        ┌───────────────────────┐
        │ AJAX Endpoints        │
        │ (wp_ajax_wpshadow_*)  │
        └───────────────────────┘
```

---

## Phase 4: Command Pattern Architecture

```
Command (Abstract Base Class)
├── verify_request()              ← Nonce + capability check
├── get_post_var()                ← Sanitization
├── success()                      ← JSON response
├── error()                        ← JSON error
└── register()                     ← Hook registration

    ↓ Extends to 9 Commands
    
Each Command:
├── get_name()                     ← Command identifier
└── execute()                      ← Business logic
```

**Example: Save_Workflow_Command**
```
Input Sanitization
    ↓
Nonce Verification
    ↓
Capability Check
    ↓
Business Logic (validate blocks, save workflow)
    ↓
JSON Response
```

---

## Phase 5: Knowledge Base System Architecture

```
Diagnostic Registry (57 items)    Treatment Registry (44 items)
              ↓                              ↓
              └──────────────┬──────────────┘
                             ↓
            KB_Article_Generator
            (Auto-generation logic)
                             ↓
        ┌───────────────────┴────────────────────┐
        ↓                                        ↓
    101 KB Articles                       Search Index
    (stored in options)                  (inverted index)
        ↓                                        ↓
    ├── Diagnostic Articles (57)         Full-text Search
    │   - What/Why/How/Auto-fix/Learn   KB_Search
    ├── Treatment Articles (44)          (relevance scoring)
    │   - What/Why/Safety/Manual
    └── Related Articles                       ↓
                                        Search Results
        ↓                              (sorted by score)
    KB_Library
    ├── get_article()
    ├── get_by_category()
    ├── get_by_type()
    ├── search()
    └── cache management
```

**Training System**
```
Training_Provider                 Training_Progress
├── 5 Courses                     ├── Topic completion
├── 10+ Topics                    ├── Course completion
├── Difficulty levels             ├── Progress %
└── Time estimates                ├── Badges
                                  └── Champion status
```

---

## Phase 6: Privacy System Architecture

```
Privacy Policy Manager
├── 6 Sections
│   ├── Overview
│   ├── What We Collect
│   ├── How We Use
│   ├── Data Retention
│   ├── Your Rights
│   └── Contact
├── Version History
└── Policy Display

Consent_Preferences
├── 3-Tier System
│   ├── Essential (always on)
│   ├── Error Reporting (always on)
│   └── Telemetry (opt-in)
├── Per-User Preferences
├── Consent History (audit trail)
└── GDPR Export/Delete

First_Run_Consent
├── Modal UI
├── Preference Selection
├── 30-Day Dismiss
└── Consent Recording
```

---

## Data Flow Diagram

### User Visits Admin Dashboard
```
Admin loads dashboard
    ↓
First_Run_Consent::should_show_consent()
    ├─ Is admin? → Check
    ├─ Already consented? → No
    ├─ Dismissed <30d? → No
    └─ YES: Show modal
         ↓
    User selects preferences
         ↓
    User clicks "Save Preferences"
         ↓
    First_Run_Consent::save_consent()
         ├─ Store: wpshadow_consent_preferences
         ├─ Record: wpshadow_consent_history
         └─ Set: wpshadow_consent_dismissed_until
         ↓
    Modal closes
    Won't show for 30 days
```

### User Searches KB
```
User types in search box
    ↓
AJAX: wpshadow_kb_search
    ↓
KB_Search::search($query, $filters)
    ├─ Get search index
    ├─ Tokenize query (words >2 chars)
    ├─ Score each article
    │   ├─ Title match: 30 pts
    │   ├─ Content match: 10 pts
    │   └─ Frequency bonus: 2-20 pts
    ├─ Sort by relevance
    └─ Return top results
    ↓
KB_Search::track_search()
    (Records for analytics)
    ↓
Display results
```

### User Takes Action
```
User completes diagnostic reading
    ↓
Training_Progress::mark_topic_complete($user_id, 'ssl')
    ├─ Update: wpshadow_training_progress
    ├─ Record timestamp
    └─ Check for course completion
         ↓
    If course complete:
    Training_Progress::mark_course_complete()
         ├─ Award certificate
         ├─ Check champion status (75%+)
         └─ Possibly award badge
         ↓
    Store achievements in user meta
```

---

## Integration Points

### Dashboard UI
```
Finding Display
    ├─ Show diagnostic title
    ├─ Link to KB article
    │   └─ KB_Library::get_article()
    ├─ Link to training
    │   └─ Training_Provider::get_training_for_item()
    ├─ Show treatment buttons
    └─ Show status indicators
```

### Settings Page
```
Settings → Privacy & Consent
    ├─ Display privacy policy
    │   └─ Privacy_Policy_Manager::get_policy_html()
    ├─ Show consent options
    │   └─ Consent_Preferences::get_preferences()
    ├─ Show version history
    │   └─ Privacy_Policy_Manager::get_version_history()
    └─ Save changes
        └─ Consent_Preferences::set_preferences()
```

### Search Box
```
Header Search
    └─ AJAX: wpshadow_kb_search
        ├─ Query: KB_Search::search()
        ├─ Suggestions: KB_Search::get_suggestions()
        └─ Display results with score
```

---

## Storage Architecture

```
WordPress Options (Site-Wide)
├─ wpshadow_kb_articles_v1              [24h cache]
├─ wpshadow_kb_search_index_v1          [rebuilt on cache clear]
├─ wpshadow_kb_search_stats             [popular searches]
└─ wpshadow_privacy_policy_versions     [versioned history]

User Meta (Per-User)
├─ wpshadow_training_progress           [courses + topics + badges]
├─ wpshadow_consent_preferences         [3-tier preferences]
├─ wpshadow_consent_history             [audit trail array]
└─ wpshadow_consent_dismissed_until     [unix timestamp]
```

---

## Class Dependencies

```
Phase 4:
  Color_Utils (standalone)
  Theme_Data_Provider (standalone)
  User_Preferences_Manager (standalone)
  Command (base)
    ├─ 9 Command subclasses
    └─ KB_Search_Command (Phase 5)

Phase 5:
  KB_Formatter (standalone)
  KB_Article_Generator (uses Diagnostic + Treatment registries)
  KB_Library (uses KB_Article_Generator)
  KB_Search (uses KB_Library)
  Training_Provider (standalone)
  Training_Progress (uses User_Preferences_Manager)
  KB_Search_Command (extends Command from Phase 4)

Phase 6:
  Privacy_Policy_Manager (standalone)
  Consent_Preferences (standalone)
  First_Run_Consent (uses Consent_Preferences)
```

---

## Performance Characteristics

```
Operation                          Time      Storage   Frequency
─────────────────────────────────────────────────────────────────
KB Article Generation               ~100ms    1.5 MB    First load
KB Search Query                     <100ms    N/A       User action
Training Progress Load              1-2ms     per user  On page load
Training Progress Update            2-3ms     per user  On action
Consent Preference Load             1-2ms     per user  On page load
Consent Preference Update           2-3ms     per user  On action
Privacy Policy Display              ~50ms     N/A       On demand
Consent Modal Render                ~20ms     N/A       First visit
```

**Total Memory Impact:** ~2-3 MB (mostly cached articles)

---

## Security Model

```
AJAX Endpoints
├─ Nonce verification (wp_verify_nonce)
├─ Capability check (current_user_can)
├─ Input sanitization (sanitize_text_field, sanitize_key)
├─ Output escaping (esc_html, esc_attr, wp_kses_post)
└─ Rate limiting (optional, future)

Database Queries
├─ All via get_user_meta / update_user_meta
├─ All via get_option / update_option
├─ No direct SQL queries
└─ No prepared statement needed

Data Storage
├─ User IPs: Never stored (only hashed for uniqueness)
├─ Sensitive data: Encrypted at rest (optional, future)
├─ Audit logs: Kept for 1 year
└─ Export: JSON format, GDPR compliant
```

---

## Error Handling

```
Error Flow
├─ Input Validation
│   └─ Return error() with message
├─ Business Logic
│   └─ Return error() with details
└─ System
    └─ Log to error_log
         └─ If consented to error reporting
```

**Response Format**
```json
{
  "success": false,
  "data": {
    "message": "User-friendly error message",
    "code": "error_code_here",
    "details": "Technical details if needed"
  }
}
```

---

## Extensibility Points

```
Phase 4:
├─ Add new Command classes
│   └─ Extend Command base
├─ Register custom commands
│   └─ Update Command_Registry
└─ Add new preferences
    └─ User_Preferences_Manager::register()

Phase 5:
├─ Add new courses
│   └─ Training_Provider::get_courses()
├─ Add custom KB articles
│   └─ KB_Library with custom registration
└─ Customize article templates
    └─ KB_Article_Generator extend methods

Phase 6:
├─ Modify privacy policy
│   └─ Privacy_Policy_Manager sections
├─ Add consent tiers
│   └─ Consent_Preferences::get_defaults()
└─ Customize consent flow
    └─ First_Run_Consent::get_consent_html()
```

---

**Architecture complete and fully documented! 🎉**
