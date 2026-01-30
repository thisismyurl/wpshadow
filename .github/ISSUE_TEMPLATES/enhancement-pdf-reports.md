# [Enhancement] PDF Report Export for Client-Facing Reports

**Labels:** `enhancement`, `reporting`, `pdf`, `agency`
**Assignee:** TBD
**Milestone:** v1.4

## Current State
Report generation infrastructure exists:
- ✅ CSV export (`export-csv-handler.php`)
- ✅ Site DNA reports (`class-site-dna-handler.php`)
- ✅ Customization audit reports (`generate-customization-audit-handler.php`)
- ✅ Report download handler (`download-report-handler.php`)

However, **PDF export is missing** - a critical feature for agencies presenting to clients.

## Problem Statement
**Agency Use Case**: "I need to show my client a professional-looking report that proves the site is healthy and secure. CSV files and HTML pages aren't client-friendly."

**Current Workaround**: Users must manually screenshot dashboard or print HTML to PDF (poor quality).

## Proposed Solution
Add PDF export capability using **TCPDF** (stable, reliable) or **DomPDF** (better CSS support).

### Features
1. **Executive Summary** - 1-page overview with health score, critical issues, fixes applied
2. **Detailed Findings** - Full diagnostic results with severity levels
3. **Activity Timeline** - Last 30 days of treatments applied
4. **Branding** - Site logo, custom colors (hooks for white-label in Pro)
5. **Export Options** - "Quick Report" vs "Comprehensive Report"

## Implementation Checklist

### Core Infrastructure
- [ ] Choose PDF library (recommend: **TCPDF** for stability)
- [ ] Add library via Composer: `composer require tecnickcom/tcpdf`
- [ ] Create `includes/reporting/class-pdf-generator.php`
- [ ] Create PDF templates in `includes/views/reports/pdf/`
- [ ] Add AJAX handler: `generate-pdf-report-handler.php`

### PDF Templates
- [ ] **template-executive-summary.php** - 1-page overview
- [ ] **template-detailed-findings.php** - Full diagnostic results
- [ ] **template-activity-log.php** - Timeline of fixes
- [ ] **template-header.php** - Consistent header with branding
- [ ] **template-footer.php** - Page numbers, generation timestamp

### UI Integration
- [ ] Add "Export PDF" button to Dashboard widget
- [ ] Add "Export PDF" to Guardian page (detailed findings)
- [ ] Add "Export PDF" to Activity History page
- [ ] Show progress spinner during generation
- [ ] Auto-download PDF when ready

## Technical Considerations

### Library Choice
| Feature | TCPDF | DomPDF |
|---------|-------|--------|
| PHP 8.1 Support | ✅ Yes | ✅ Yes |
| Memory Usage | Low | Higher |
| CSS Support | Limited | Better |
| Stability | Excellent | Good |
| Maintenance | Active | Active |

**Recommendation**: **TCPDF** for reliability

### Performance
- PDF generation can be slow (3-5 seconds for detailed reports)
- Show progress indicator in UI
- Consider background processing for large reports
- Cache PDFs for 1 hour (transient with unique key)

### Security
- ✅ Nonce verification required
- ✅ Capability check (any logged-in user can view own site)
- ✅ Sanitize all inputs
- ✅ Escape all outputs in templates
- ✅ Limit report to findings user has permission to see

## Philosophy Alignment
✅ **Philosophy #3**: Free (PDF generation uses open-source library)
✅ **Philosophy #4**: Advice not sales (reports show value, not upsell prompts)
✅ **Philosophy #9**: Show value (visual proof of work done)

## User Stories
1. **Agency Owner**: "I need to show my client what I did this month"
2. **Site Owner**: "I want to print a report for my records"
3. **Corporate User**: "I need compliance documentation for my security audit"

## Success Metrics
- PDF generation success rate (target: >99%)
- Average generation time (target: <5 seconds)
- User adoption (target: 15% of users export PDF monthly)
- Support tickets reduced ("How do I show my client?" → 0)

## Future Enhancements (Pro)
- **White-label branding** - Custom logo, colors, company name
- **Scheduled email delivery** - "Send me PDF every Monday"
- **Client portal** - Shareable link for clients to view reports
- **Report templates** - Different layouts (minimal, detailed, executive)
- **Multi-site reports** - Network-wide health summary

## Related Files
- `includes/admin/ajax/download-report-handler.php` - Existing download handler
- `includes/admin/ajax/generate-report-handler.php` - Report generation infrastructure
- `includes/admin/ajax/export-csv-handler.php` - CSV export (reference implementation)
- `includes/reporting/class-kpi-advanced-features.php` - KPI data source

## References
- TCPDF: https://tcpdf.org/
- DomPDF: https://github.com/dompdf/dompdf
