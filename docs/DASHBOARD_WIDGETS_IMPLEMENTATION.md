# Dashboard Widgets Implementation Plan

## Overview
This document outlines the implementation of comprehensive WordPress dashboard widgets for the TIMU Suite, providing at-a-glance status information for the suite, vault, modules, licensing, and performance metrics.

## Implementation Strategy

### Phase 1: Core Widget Infrastructure
The implementation extends the existing `WPS_Dashboard_Widgets` class found in `includes/class-wps-dashboard-widgets.php` and creates a new complementary class `WPS_WP_Dashboard_Widgets` specifically for WordPress admin dashboard widgets.

### Phase 2: Widget Implementations

#### Widget 1: TIMU Suite Status
**File**: `includes/class-wps-wp-dashboard-widgets.php`
**Registration**: Uses `wp_add_dashboard_widget()` hook on `wp_dashboard_setup` action
**Features**:
- Suite Health Score (0-100) with visual ring chart (SVG-based)
- Quick Stats Grid (4 columns): Active Modules, Vault Files, Vault Size, License Status
- Recent Activity Feed (last 5 actions from `WPS_Activity_Logger`)
- Action buttons: "View Full Dashboard", "Run Diagnostics"
- Color coding: Green (90-100), Yellow (70-89), Red (< 70)

**Data Sources**:
- `WPS_Module_Registry::get_catalog_with_status()` for module stats
- `WPS_Activity_Logger::get_events()` for recent activity
- Vault stats from `wp_upload_dir()` + `WPS_vault_dirname` option
- Health score calculation based on vault status, encryption, license validity, module health

#### Widget 2: Vault Status & Alerts
**Features**:
- Animated circular progress bar using Chart.js (doughnut chart)
- Integrity status with last verification timestamp
- Files verified count with failed checks highlighted
- Storage breakdown bar chart by file type (Images, Videos, Documents, Other)
- Alert section for: capacity warnings, failed integrity checks, encryption key location warnings
- Actions: "Manage Vault", "View Failed Files", "Verify Now"

**Data Sources**:
- Vault directory scanning for size/file count
- `wps_vault_last_verification`, `wps_vault_verified_files`, `wps_vault_failed_checks` options
- File type analysis for storage breakdown

#### Widget 3: License & Module Manager
**Features**:
- License status badge with color coding
- License details: Type, Registered To, Valid Until, Days Remaining, Sites Activated
- Module list (top 5) with status indicators (✓ Active, ⚪ Not Installed, ⚠ Update Available)
- Quick actions per module: Settings, Deactivate, Update
- Global actions: "Install New Module", "Check for Updates", "Module Marketplace"

**Data Sources**:
- `WPS_Module_Registry::get_catalog_with_status()`
- License information (currently using placeholder data, ready for future license system integration)

####Widget 4: Performance Insights
**Features**:
- Optimization stats for last 7 days: Images optimized, Space saved, Avg processing time
- Line chart showing optimization activity over time using Chart.js
- Performance impact metrics: Page load improvement, Bandwidth saved, CO₂ emissions reduced
- Actions: "View Full Report", "Export Data"

**Data Sources**:
- Performance metrics from `WPS_Performance_Monitor` class (if available)
- Generated sample data with caching (5-minute transient cache)
- Future integration point for real performance tracking

#### Widget 5: Quick Actions
**Features**:
- Large icon-based action buttons (6 actions):
  - 🔒 Verify Vault Integrity
  - 📊 Run Health Check  
  - 🔄 Sync Modules
  - 🎯 Optimize Media
  - ⚙️ Suite Settings (link)
  - 📋 View Logs (link)
- Loading states with spinner
- Toast notification feedback
- ARIA live region for accessibility

**Functionality**:
- AJAX handlers for each action
- Progressive enhancement (works without JS)
- Keyboard accessible

### Phase 3: Technical Features

#### Caching Strategy
- Transient caching for expensive queries (5-minute default)
- Cache keys:
  - `wps_suite_status_cache` - Suite status data
  - `wps_vault_status_cache` - Vault status data
  - `wps_performance_data_cache` - Performance metrics
- Manual cache invalidation on relevant actions (e.g., module activation, vault verification)

#### AJAX Refresh
- Auto-refresh every 60 seconds (configurable via `wps_dashboard_refresh_rate` option)
- Manual refresh available on all widgets
- Nonce verification for security
- Error handling with user feedback

#### Responsive Design
- CSS Grid for flexible layouts
- Mobile-friendly collapse/expand
- Adapts to WordPress admin color schemes
- Dashboard widgets use native WordPress drag-and-drop API

#### Performance Optimization
- Lazy loading of Chart.js (only on dashboard page)
- Debounced AJAX requests
- Minimal DOM manipulations
- Progressive enhancement

#### Accessibility (WCAG 2.1 AA Compliance)
- ARIA labels on all interactive elements
- Keyboard navigation support
- Screen reader announcements for dynamic updates
- Color contrast ratios meet AA standards
- Focus indicators on all focusable elements
- Semantic HTML structure

### Phase 4: Assets

#### CSS File: `assets/css/wps-dashboard-widgets.css`
Styles for:
- Ring chart SVG animations
- Stats grid layouts
- Activity feed styling
- Alert badges and colors
- Action button grid
- Responsive breakpoints
- Dark mode support (using CSS custom properties)

#### JavaScript File: `assets/js/wps-dashboard-widgets.js`
Features:
- AJAX refresh functionality
- Chart.js initialization
- Action button click handlers
- Toast notification system
- Loading state management
- Auto-refresh timer with visibility API
- Error handling and retry logic

### Phase 5: Integration Points

#### Main Plugin File (`wp-support-thisismyurl.php`)
Add initialization:
```php
// Initialize WordPress Dashboard Widgets
if ( class_exists( '\\WPS\\CoreSupport\\WPS_WP_Dashboard_Widgets' ) ) {
    \WPS\CoreSupport\WPS_WP_Dashboard_Widgets::init();
}
```

#### Settings Integration
Widget settings stored in `wps_dashboard_widget_settings` option:
- `suite_status` (boolean) - Enable/disable Suite Status widget
- `vault_status` (boolean) - Enable/disable Vault Status widget  
- `license_modules` (boolean) - Enable/disable License & Modules widget
- `performance_insights` (boolean) - Enable/disable Performance Insights widget
- `quick_actions` (boolean) - Enable/disable Quick Actions widget

Settings UI accessible via Settings > Dashboard Widgets (future enhancement).

#### Screen Options Integration
Widgets automatically integrate with WordPress Screen Options:
- Show/hide individual widgets
- Drag-and-drop reordering
- Column layout (1-4 columns)
- Native WordPress meta box API

### Phase 6: Testing Strategy

#### Manual Testing Checklist
- [ ] All widgets render correctly on fresh install
- [ ] Widgets display correct data when modules are activated/deactivated
- [ ] AJAX refresh works without errors
- [ ] Charts render correctly (requires Chart.js)
- [ ] Action buttons execute correctly with proper feedback
- [ ] Widgets are draggable and reorderable
- [ ] Screen Options show/hide works
- [ ] Responsive design works on mobile/tablet
- [ ] Keyboard navigation works throughout
- [ ] Screen readers announce changes correctly
- [ ] Performance is acceptable (< 100ms widget render)
- [ ] Caching reduces database queries
- [ ] No JavaScript errors in console
- [ ] Widgets work with WordPress color schemes
- [ ] Dark mode support (if theme provides)

#### Browser Compatibility
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Android)

### Phase 7: Documentation

#### User Documentation
Create user-facing documentation in `docs/` directory:
- Widget overview and features
- How to configure widgets
- How to interpret health scores and metrics
- Troubleshooting common issues

#### Developer Documentation  
Inline PHPDoc comments for all classes and methods:
- Widget registration process
- Data source documentation
- Cache invalidation hooks
- Extension points for custom widgets

### Phase 8: Future Enhancements

#### Planned Features (Post-M04)
- [ ] Widget configuration UI for each widget
- [ ] Custom refresh rate per widget
- [ ] Export widget data (CSV, JSON)
- [ ] Widget templates for customization
- [ ] Real-time updates using WebSockets
- [ ] Historical data tracking and trends
- [ ] Alerting system with email notifications
- [ ] Integration with external monitoring services
- [ ] Multi-site network widgets (network admin)
- [ ] Role-based widget visibility
- [ ] Widget presets (Admin, Editor, Contributor)

## Implementation Files

### Required New Files
1. `includes/class-wps-wp-dashboard-widgets.php` - Main widget class (1200+ lines)
2. `assets/css/wps-dashboard-widgets.css` - Widget styles (500+ lines)
3. `assets/js/wps-dashboard-widgets.js` - Widget JavaScript (300+ lines)

### Modified Files
1. `wp-support-thisismyurl.php` - Add widget initialization (2 lines)

### Dependencies
- Chart.js 4.4.0 (CDN: https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js)
- jQuery (already available in WordPress)
- WordPress Dashboard API

## Security Considerations

- All AJAX requests use nonce verification
- Data sanitization on all user inputs
- Proper escaping of all outputs
- Capability checks before displaying sensitive data
- No direct file access
- SQL injection prevention (using WordPress APIs)
- XSS prevention (using `esc_*` functions)

## Performance Considerations

- Transient caching (5 minutes) for expensive queries
- Minimal database queries per widget render
- Chart.js loaded only on dashboard page
- Debounced AJAX requests
- Lazy loading of widget content
- No blocking operations on page load

## Accessibility Considerations

- ARIA labels on all SVG charts
- Live regions for dynamic updates
- Keyboard navigation support
- Screen reader announcements
- High contrast mode support
- Focus management
- Semantic HTML
- Skip links where appropriate

## Conclusion

This implementation provides a comprehensive dashboard widget system that integrates seamlessly with WordPress core, leverages existing TIMU Suite data sources, and provides a solid foundation for future enhancements. The widgets are designed to be performant, accessible, and maintainable while providing maximum value to site administrators.
