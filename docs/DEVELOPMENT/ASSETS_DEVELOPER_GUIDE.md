/**
 * WPShadow Consolidated Assets - Developer Guide
 * 
 * Quick reference for using the new external CSS and JavaScript assets
 * instead of inline styles and scripts.
 */

/**
 * ============================================================================
 * ASSET LOCATIONS
 * ============================================================================
 */

CSS Files:
- /assets/css/admin-pages.css       - Common admin page styles
- /assets/css/reports.css           - Report builder/renderer styles
- /assets/css/guardian.css          - Guardian dashboard styles
- /assets/css/design-system.css     - Design system utilities (pre-existing)

JavaScript Files:
- /assets/js/admin-pages.js         - Common admin page functionality
- /assets/js/reports.js             - Report builder/renderer functionality
- /assets/js/guardian.js            - Guardian dashboard functionality


/**
 * ============================================================================
 * GLOBALLY AVAILABLE MODULES
 * ============================================================================
 */

// All pages
window.WPShadowAdmin = {
    init(),                        // Initialize admin functionality
    initModals(),                  // Set up modal handlers
    initFormHandlers(),            // Set up form AJAX handlers
    initAjaxHandlers(),            // Set up generic AJAX buttons
    initToggles(),                 // Set up toggle switches
    openModal(modalId),            // Open a modal by ID
    closeModal(modal),             // Close a modal
    handleFormSubmit(form),        // Submit form via AJAX
    showNotice(type, message),     // Show admin notice (success/error)
    showSpinner(container),        // Show loading spinner
    removeSpinner(spinner),        // Remove loading spinner
    formatDate(date),              // Format date to locale string
    formatNumber(num),             // Format number with thousands separator
};

// Report pages
window.WPShadowReportBuilder = {
    init(),                        // Initialize report builder
    initPresetButtons(),           // Set up preset date buttons
    initDatePicker(),              // Set up date range validation
    initReportTypeSelection(),     // Set up report type selector
    initFormSubmission(),          // Set up form submission
    applyDatePreset(preset),       // Apply date preset values
    submitReportForm(form),        // Submit report form
    validateForm(form),            // Validate required fields
};

window.WPShadowReportDisplay = {
    init(),                        // Initialize report display
    initExportButtons(),           // Set up export buttons
    initEmailReport(),             // Set up email functionality
    initPrintButton(),             // Set up print button
    initShareOptions(),            // Set up social sharing
    exportReport(format),          // Export in specified format
    sendReportEmail(form),         // Send report via email
    shareReport(platform, url, title), // Share to social platform
    getReportData(),               // Extract report data from page
};

// Guardian pages
window.WPShadowGuardian = {
    init(),                        // Initialize Guardian dashboard
    initToggleSwitch(),            // Set up Guardian toggle
    initScanControls(),            // Set up scan buttons
    initIssueActions(),            // Set up issue action buttons
    initAutoRefresh(),             // Set up auto-refresh
    toggleGuardian(enabled, elem), // Toggle Guardian on/off
    handleScanAction(action, btn), // Handle scan actions
    monitorScanProgress(),         // Monitor scan in real-time
    handleIssueAction(action, id, link), // Handle issue actions
    pollIssueStatus(id, card, link, text), // Poll issue status after fix
};


/**
 * ============================================================================
 * COMMON CSS CLASS NAMES
 * ============================================================================
 */

// Page Layout
.wps-page-container             // Main page wrapper
.wps-page-header                // Page header section
.wps-admin-card-container       // Card wrapper with shadow
.wps-report-container           // Report content area
.wps-report-header              // Report title/meta area

// Forms
.wps-form-inline                // Inline form layout
.wps-form-row                   // Form row wrapper
.wps-form-row-2col              // 2-column form row
.wps-form-group-label           // Form label styling
.wps-checkbox-group             // Checkbox list wrapper
.wps-checkbox-item              // Single checkbox item
.wps-preset-buttons             // Preset button container
.wps-preset-btn                 // Individual preset button
.wps-preset-btn.selected        // Active preset button

// Modals
.wps-modal                      // Modal backdrop container
.wps-modal.active               // Active modal (visible)
.wps-modal-content              // Modal content box
.wps-modal-close                // Close button

// Cards & Display
.wps-report-card                // Content card with border
.wps-report-card.info           // Info card variant
.wps-report-card.success        // Success card variant
.wps-report-card.warning        // Warning card variant
.wps-report-card.error          // Error card variant
.wps-data-card                  // Data visualization card
.wps-recommendation-box         // Recommendation box
.wps-recommendation-box.priority-high   // High priority recommendation
.wps-recommendation-box.priority-medium // Medium priority
.wps-recommendation-box.priority-low    // Low priority

// Tables
.wps-report-table               // Table styling
.wps-report-table thead         // Table header
.wps-report-table th            // Table header cell
.wps-report-table td            // Table data cell

// Buttons & Status
.wps-btn                        // Button base class
.wps-btn-primary                // Primary button
.wps-btn-secondary              // Secondary button
.wps-status-badge               // Status badge
.wps-status-badge-text          // Badge text

// Guardian Specific
.wps-guardian-dashboard         // Guardian page wrapper
.wps-guardian-header            // Guardian header
.wps-guardian-status            // Status display
.wps-guardian-status-badge      // Status badge (active/inactive)
.wps-guardian-status-badge.active  // Active status
.wps-guardian-status-badge.inactive // Inactive status
.wps-guardian-issue-card        // Issue card
.wps-guardian-issue-card.critical   // Critical issue
.wps-guardian-issue-card.high       // High priority issue
.wps-toggle-switch              // Toggle switch container

// Utilities
.wps-icon-text                  // Icon + text layout
.wps-icon-large                 // Large icon (32px)
.wps-icon-medium                // Medium icon (24px)
.wps-icon-small                 // Small icon (18px)
.wps-loading-message            // Loading message container
.wps-back-button                // Back navigation button
.wps-filter-badge               // Filter/tag badge


/**
 * ============================================================================
 * LOCALIZED DATA AVAILABLE
 * ============================================================================
 */

// On all WPShadow pages (wpshadowAdmin object)
wpshadowAdmin.ajaxUrl           // AJAX endpoint URL
wpshadowAdmin.nonce             // Security nonce for AJAX
wpshadowAdmin.locale            // User locale (en_US, etc.)
wpshadowAdmin.i18n              // Localized strings
  .saving                       // "Saving..."
  .saved                        // "Saved successfully!"
  .error                        // "An error occurred..."
  .confirmDelete                // "Are you sure you want to delete this?"

// On report pages (wpshadowReportBuilder, wpshadowReportDisplay objects)
wpshadowReportBuilder.ajaxUrl   // AJAX endpoint
wpshadowReportBuilder.nonce     // Report generation nonce
wpshadowReportBuilder.i18n      // Report-specific strings
  .generating                   // "Generating report..."
  .reportGenerated              // "Report generated successfully!"
  .fillAllFields                // "Please fill all required fields"
  .invalidDateRange             // "End date must be after start date"

// On Guardian page (wpshadowGuardian object)
wpshadowGuardian.ajaxUrl        // AJAX endpoint
wpshadowGuardian.nonce          // Guardian nonce
wpshadowGuardian.refreshInterval // Auto-refresh interval (120000 = 2 min)
wpshadowGuardian.i18n           // Guardian-specific strings
  .active                       // "Active"
  .inactive                     // "Inactive"
  .fixing                       // "Fixing..."


/**
 * ============================================================================
 * COMMON DATA ATTRIBUTES
 * ============================================================================
 */

// Modal Triggers
data-modal-trigger="modal-id"   // Opens modal with this ID

// AJAX Actions
data-action="action_name"       // Performs this AJAX action
data-action="name"              // AJAX action name
data-confirm="message"          // Show confirmation before action
data-id="value"                 // ID for the action
data-refresh="true"             // Reload page after success

// Form Submission
data-ajax-form                  // Mark form for AJAX submission
data-value="initial"            // Initial value for field edit

// Report Pages
data-preset="preset_name"       // Date preset (last_7_days, etc.)
data-export-format="pdf"        // Export format (pdf, csv, etc.)
data-report-title              // Title of report
data-report-generated          // Report generation timestamp

// Guardian
data-scan-action="run"         // Scan action (run, stop, reset)
data-issue-action="fix"        // Issue action (fix, ignore, detail)
data-issue-type="type_name"    // Type of issue for counting

// Tool Pages
data-section-type="type"       // Section type identifier


/**
 * ============================================================================
 * COMMON USAGE EXAMPLES
 * ============================================================================
 */

// Open a modal programmatically
WPShadowAdmin.openModal('my-modal-id');

// Close current modal
WPShadowAdmin.closeModal($('.wps-modal'));

// Show success notification
WPShadowAdmin.showNotice('success', 'Changes saved!');

// Show error notification
WPShadowAdmin.showNotice('error', 'Something went wrong.');

// Apply date preset (Report pages)
WPShadowReportBuilder.applyDatePreset('last_30_days');

// Export report (Report pages)
WPShadowReportDisplay.exportReport('pdf');

// Toggle Guardian on/off
WPShadowGuardian.toggleGuardian(true, toggleElement);

// Monitor scan progress
WPShadowGuardian.monitorScanProgress();


/**
 * ============================================================================
 * MIGRATION CHECKLIST (For removing inline code)
 * ============================================================================
 */

When removing inline <script> blocks:
1. ☐ Check if functionality already exists in exported modules
2. ☐ If not, add to appropriate external JS file
3. ☐ Register function in Asset Manager
4. ☐ Call from PHP: wp_enqueue_script()
5. ☐ Localize data if needed: wp_localize_script()
6. ☐ Test functionality thoroughly
7. ☐ Remove inline <script> block from HTML

When consolidating inline <style> blocks:
1. ☐ Check if styles already exist in CSS files
2. ☐ If not, add to appropriate external CSS file
3. ☐ Use class names from CSS classes reference above
4. ☐ Register in Asset Manager: wp_enqueue_style()
5. ☐ Replace inline style attributes with classes
6. ☐ Test responsive design on mobile
7. ☐ Remove inline <style> block from HTML


/**
 * ============================================================================
 * PERFORMANCE CONSIDERATIONS
 * ============================================================================
 */

- Assets are only loaded on WPShadow admin pages
- Guardian assets only load on guardian page
- Report assets only load on report pages
- CSS is minified in production
- JavaScript dependencies tracked via wp_enqueue_script deps
- Nonces regenerated for security
- Localized strings translated based on user locale
