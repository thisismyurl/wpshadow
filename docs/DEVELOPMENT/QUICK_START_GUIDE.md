# WPShadow Assets - Quick Start Guide

---

## ✅ Core Values Embedded

**Commandment #3 - Register, Don't Pay:** Free tier is generous with a simple registration process.

**Commandment #6 - Drive to Free Training:** Quick start guide makes learning and contribution accessible to all.

**Accessibility Pillar 🎓 - Learning Inclusive:** Structured steps, code examples, and clear explanations support developers of all skill levels.

Learn more: [PHILOSOPHY/VISION.md](../../PHILOSOPHY/VISION.md) | [PHILOSOPHY/ACCESSIBILITY.md](../../PHILOSOPHY/ACCESSIBILITY.md)

---

## 🎯 For Developers Using These Assets

### Getting Started in 5 Minutes

#### Step 1: Understand What's Available
- **admin-pages.js/css** → Used on ALL WPShadow pages
- **reports.js/css** → Used on report pages
- **guardian.js/css** → Used on Guardian dashboard

#### Step 2: Access the API
```javascript
// On any WPShadow page
WPShadowAdmin.openModal('my-modal');
WPShadowAdmin.showNotice('success', 'Done!');

// On report pages
WPShadowReportBuilder.applyDatePreset('last_30_days');
WPShadowReportDisplay.exportReport('pdf');

// On Guardian page
WPShadowGuardian.toggleGuardian(true, element);
```

#### Step 3: Use CSS Classes
```html
<!-- Modal -->
<div id="my-modal" class="wps-modal">
  <div class="wps-modal-content">
    <button class="wps-modal-close">&times;</button>
    <!-- Content -->
  </div>
</div>

<!-- Card -->
<div class="wps-admin-card-container">
  Content here
</div>

<!-- Form -->
<form class="wps-form-inline">
  <input class="wps-form-group-label" />
  <button class="wps-btn wps-btn-primary">Submit</button>
</form>
```

---

## 📚 Quick Reference

### Common Tasks

**Open a Modal**
```javascript
WPShadowAdmin.openModal('modal-id');
```

**Close a Modal**
```javascript
WPShadowAdmin.closeModal($('#modal-id'));
```

**Show Success Message**
```javascript
WPShadowAdmin.showNotice('success', 'Saved!');
```

**Show Error Message**
```javascript
WPShadowAdmin.showNotice('error', 'Error occurred!');
```

**Get Report Date Range**
```javascript
const startDate = $('input[name="date_from"]').val();
const endDate = $('input[name="date_to"]').val();
```

**Export Report**
```javascript
WPShadowReportDisplay.exportReport('pdf');
```

**Toggle Guardian**
```javascript
WPShadowGuardian.toggleGuardian(true, toggleElement);
```

---

## 🎨 Common CSS Classes

| Class | Purpose |
|-------|---------|
| `.wps-page-container` | Page wrapper |
| `.wps-admin-card-container` | Card with shadow |
| `.wps-modal` | Modal backdrop |
| `.wps-modal.active` | Visible modal |
| `.wps-form-inline` | Horizontal form |
| `.wps-btn` | Button base |
| `.wps-btn-primary` | Primary button |
| `.wps-status-badge` | Status indicator |
| `.wps-report-card` | Report content card |
| `.wps-guardian-issue-card` | Issue card |

---

## 🔌 Data Attributes

```html
<!-- Open modal on click -->
<button data-modal-trigger="modal-id">Open</button>

<!-- AJAX action button -->
<button data-action="my_action">Action</button>

<!-- Confirmation dialog -->
<button data-action="delete" data-confirm="Are you sure?">Delete</button>

<!-- Date preset -->
<button class="wps-preset-btn" data-preset="last_30_days">Last 30 Days</button>

<!-- Export format -->
<button data-export-format="pdf">Export PDF</button>

<!-- Scan action -->
<button data-scan-action="run">Start Scan</button>
```

---

## 🧪 Testing Each Module

### Test Admin Pages (All Pages)
1. Click any `data-modal-trigger` button → Modal opens
2. Fill form, click submit → AJAX request fires
3. Error appears → Shows notice automatically
4. Success → Auto-dismissing notice appears

### Test Report Pages
1. Click preset button → Dates auto-populate
2. Click "Generate Report" → Progress shows
3. Click "Export PDF" → Download starts
4. All dates validate correctly

### Test Guardian Page
1. Click toggle switch → Guardian toggles on/off
2. Click "Run Scan" → Progress bar updates
3. Progress reaches 100% → Page refreshes
4. Can fix individual issues

---

## 📖 Documentation Quick Links

| Document | Use When |
|----------|----------|
| ASSETS_DEVELOPER_GUIDE.md | Need API reference |
| ARCHITECTURE_OVERVIEW.md | Want system design |
| IMPLEMENTATION_STATUS.md | Looking for examples |
| CONSOLIDATION_EXECUTIVE_SUMMARY.md | Need overview |
| DELIVERABLES_LIST.md | Want complete list |

---

## 🐛 Troubleshooting

### Modal Not Opening?
```javascript
// Check if modal exists
console.log($('#modal-id').length); // Should be 1

// Check if JS is loaded
console.log(window.WPShadowAdmin); // Should exist
```

### AJAX Not Working?
```javascript
// Check nonce
console.log(wpshadowAdmin.nonce);

// Check AJAX URL
console.log(wpshadowAdmin.ajaxUrl);

// Test AJAX directly
$.ajax({
  url: wpshadowAdmin.ajaxUrl,
  data: { action: 'test', nonce: wpshadowAdmin.nonce }
});
```

### Styles Not Applying?
```javascript
// Check if CSS is loaded
console.log($('link[href*="admin-pages.css"]').length); // Should be > 0

// Inspect element
// Right-click element → Inspect → Check applied styles
```

---

## 💡 Pro Tips

1. **Use Inspector Devtools**
   - Right-click → Inspect
   - Check "Styles" tab for applied CSS
   - Use "Console" to test JavaScript

2. **Test in Browser Console**
   ```javascript
   WPShadowAdmin.showNotice('success', 'Test message');
   ```

3. **Check Localized Data**
   ```javascript
   console.table(wpshadowAdmin.i18n);
   console.table(wpshadowReportBuilder.i18n);
   ```

4. **Monitor AJAX Calls**
   - Open DevTools Network tab
   - Look for requests to `admin-ajax.php`
   - Check response for success/error

---

## 🚀 Adding New Features

To add a new feature using these assets:

1. **Identify which module**: admin, reports, or guardian
2. **Add HTML with data attributes**: `data-modal-trigger`, `data-action`, etc.
3. **Use existing CSS classes**: `.wps-modal`, `.wps-btn`, etc.
4. **Call module functions**: `WPShadowAdmin.openModal()`, etc.
5. **Test in browser**: Open DevTools and verify

Example:
```html
<!-- HTML -->
<button data-modal-trigger="my-new-modal">Click Me</button>
<div id="my-new-modal" class="wps-modal">
  <div class="wps-modal-content">
    <button class="wps-modal-close">&times;</button>
    <h2>My Modal</h2>
  </div>
</div>

<!-- JavaScript (runs automatically) -->
<!-- Just works! No additional code needed -->
```

---

## 📞 Getting Help

1. **Check the console** for error messages
2. **Read the relevant guide** (ASSETS_DEVELOPER_GUIDE.md)
3. **Look at code examples** in IMPLEMENTATION_STATUS.md
4. **Review module API** in ARCHITECTURE_OVERVIEW.md

---

## ✅ Checklist Before Launch

- [ ] All modals open and close
- [ ] Forms submit via AJAX
- [ ] Date presets work
- [ ] Reports generate
- [ ] Export works
- [ ] Email works
- [ ] Guardian scan works
- [ ] Mobile responsive
- [ ] No console errors
- [ ] Tested on Chrome, Firefox, Safari

---

## 🎓 Learning Path

### Level 1: Basics (5 min)
- Understand which asset file does what
- Learn 3 main modules: WPShadowAdmin, WPShadowReportBuilder, WPShadowGuardian

### Level 2: Common Tasks (15 min)
- Opening/closing modals
- Showing notifications
- Using date presets
- Exporting reports

### Level 3: Advanced (30 min)
- Understanding dependency graph
- How AJAX security works
- CSS specificity and overrides
- Adding custom functionality

### Level 4: Expert (1+ hour)
- Deep dive into each module
- Understanding lifecycle
- Performance optimization
- Custom extensions

---

## 🎯 Next Steps

1. **For QA Testing**: Use Testing Checklist in IMPLEMENTATION_STATUS.md
2. **For Developers**: Read ASSETS_DEVELOPER_GUIDE.md
3. **For Architects**: Review ARCHITECTURE_OVERVIEW.md
4. **For Management**: See CONSOLIDATION_EXECUTIVE_SUMMARY.md

---

**Ready to use? Start with clicking a modal button to verify everything works!**

Location of assets: `/assets/css/` and `/assets/js/`
Location of docs: `/docs/`
