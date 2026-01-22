# Documentation Consolidation Plan

**Goal:** Create the definitive guide structure where docs answer 95%+ of plugin questions without redundancy.

## Current State Analysis

**Total Markdown Files:** 105 (active docs)
- Phase Summaries: 16 files
- Diagnostics: 16 files
- KB & Content: 12 files
- Strategy & Planning: 5 files
- Architecture & Code: 8 files
- UI & Features: 7 files
- Workflows: 4 files
- GitHub & Issues: 3 files
- Guardian: 1 file
- Meta Documentation: 7 files
- Other: 33 files

**Problems Identified:**
1. **Phase documentation sprawl:** 16 phase files + 3 .txt summaries = redundant completion reports
2. **Diagnostic duplication:** Multiple overlapping diagnostic expansion/summary docs
3. **Performance fragmentation:** 5 separate performance impact docs
4. **KB duplication:** 2 KB writing guides, multiple article samples
5. **Missing cross-links:** New persona stubs not integrated into main docs

## Consolidation Actions

### Archive Redundant Phase Summaries
Move to `docs/archive/phases/`:
- PHASE_3_IMPLEMENTATION_COMPLETE.md → covered by ROADMAP.md
- PHASE_4_COMPLETE_SUMMARY.md → covered by ROADMAP.md
- PHASE_4_IMPLEMENTATION_COMPLETE.md → covered by ROADMAP.md  
- PHASE_5_IMPLEMENTATION_COMPLETE.md → covered by ROADMAP.md
- PHASE_7_8_FOUNDATION_COMPLETE.md → covered by GUARDIAN_CORE_COMPLETION.md
- PHASE_7_8_INTEGRATION_COMPLETE.md → covered by GUARDIAN_CORE_COMPLETION.md
- PHASE_7_8_FINAL_SUMMARY.md → covered by GUARDIAN_CORE_COMPLETION.md
- All .txt completion summaries → outdated

**Keep Active:**
- PHASE_4_QUICK_WINS_IMPLEMENTATION.md (actionable tasks)
- PHASE_5_6_IMPLEMENTATION_PLAN.md (future roadmap)
- PHASE_7_8_IMPLEMENTATION_PLAN.md (Guardian roadmap)
- PHASE_*_QUICK_REFERENCE.md (quick refs are useful)

### Consolidate Diagnostic Documentation
Create single **DIAGNOSTICS_GUIDE.md** merging:
- DIAGNOSTIC_EXPANSION_EXECUTIVE_SUMMARY.md
- DIAGNOSTIC_EXPANSION_INDEX.md
- SESSION_SUMMARY_100_DIAGNOSTIC_STUBS.md
- QUICK_WIN_DIAGNOSTIC_EXPANSION.md

Keep separate:
- FEATURE_MATRIX_DIAGNOSTICS.md (complete reference)
- PERSONA_DIAGNOSTIC_COVERAGE.md (new stubs)
- DIAGNOSTIC_TEMPLATE.md (dev template)
- COMPETITIVE_DIAGNOSTIC_BREAKDOWN.md (competitive analysis)

### Consolidate Performance Documentation
Create single **PERFORMANCE_GUIDE.md** merging:
- PERFORMANCE_IMPACT_START_HERE.md
- PERFORMANCE_IMPACT_QUICK_REFERENCE.md
- PERFORMANCE_IMPACT_SYSTEM_SUMMARY.md
- PERFORMANCE_IMPACT_FILE_INDEX.md
- SCHEDULER_PERFORMANCE_INTEGRATION.md

Archive:
- PERFORMANCE_DIAGNOSTICS_50_ADDITIONAL.md → covered by PERSONA_DIAGNOSTIC_COVERAGE.md
- PERFORMANCE_DIAGNOSTICS_100_NEW.md → covered by PERSONA_DIAGNOSTIC_COVERAGE.md

### Consolidate KB Documentation
Create single **KB_GUIDE.md** merging:
- KB_WRITING_GUIDE.md
- KB_ARTICLE_WRITING_GUIDE.md (duplicate)
- KB_CONTENT_STRATEGY_SUMMARY.md
- KB_PIPELINE_AND_FEATURE_IDEAS.md

Keep separate:
- KB_ARTICLE_MAP.md (structure reference)
- KB_SEO_GAMIFICATION_ARCHITECTURE.md (technical architecture)

Archive sample articles:
- KB_ARTICLE_36_ACTIVATE_PLUGIN.md
- KB_ARTICLE_36_IMPLEMENTATION_NOTES.md
- KB_ARTICLE_PLUGIN_ACTIVATION_REWRITE.md
- KB_ARTICLE_SAMPLE_DELETE_ITEM.md

### Update Core Strategic Documents

**ROADMAP.md updates:**
- Reflect 57 live + 95 stub diagnostics
- Update phase completion status
- Cross-link to PERSONA_DIAGNOSTIC_COVERAGE.md
- Remove outdated task lists (completed phases)

**TECHNICAL_STATUS.md updates:**
- Already updated with stub count
- Add cross-link to consolidation docs

**README.md updates:**
- Add "Documentation Guide" section
- Link to key consolidated docs

### Create New Master Guides

**DIAGNOSTICS_GUIDE.md:**
- Overview: 57 live, 95 stubs, philosophy alignment
- Implementation status by persona
- Expansion roadmap
- Links to matrix + persona coverage

**PERFORMANCE_GUIDE.md:**
- Performance impact system overview
- Scheduler integration
- Diagnostic-specific performance tracking
- Quick reference for performance work

**KB_GUIDE.md:**
- Writing standards (consolidated)
- SEO & gamification strategy
- Content pipeline
- Training integration

## Result: Essential Documentation Set

### Tier 1: Strategic (Read First)
1. README.md
2. PRODUCT_PHILOSOPHY.md
3. ROADMAP.md
4. TECHNICAL_STATUS.md

### Tier 2: Architecture & Implementation
5. ARCHITECTURE.md
6. SYSTEM_OVERVIEW.md
7. CODING_STANDARDS.md
8. FILE_STRUCTURE_GUIDE.md

### Tier 3: Feature Guides
9. DIAGNOSTICS_GUIDE.md (new consolidation)
10. FEATURE_MATRIX_DIAGNOSTICS.md
11. PERSONA_DIAGNOSTIC_COVERAGE.md
12. FEATURE_MATRIX_TREATMENTS.md
13. WORKFLOW_BUILDER.md
14. PERFORMANCE_GUIDE.md (new consolidation)
15. KB_GUIDE.md (new consolidation)

### Tier 4: UI & Features
16. DASHBOARD_LAYOUT_GUIDE.md
17. KANBAN_UI_GUIDE.md
18. KPI_METRICS_QUICK_REFERENCE.md

### Tier 5: Development & Operations
19. TESTING_SETUP.md
20. DEPLOYMENT.md
21. COMPLETE_SETUP_GUIDE.md

### Tier 6: Reference Materials
22. GITHUB_ISSUES_ALIGNMENT.md
23. DOCUMENTATION_INDEX.md
24. Quick reference docs (as needed)

**Total Active Docs:** ~30 (down from 105)
**Archived:** ~75 files
**Cross-linking:** Every doc links to related tier 1-3 docs

## Success Criteria

✅ Single source of truth for each topic
✅ No duplicate information across files
✅ Clear navigation path (tier system)
✅ 95%+ questions answerable from tier 1-3 docs
✅ All persona stubs cross-referenced
✅ Outdated content archived with clear labeling
