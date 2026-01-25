# CANON Accessibility Testing Guide

**CANON** = **C**omprehensive **A**ccessibility **N**orms & **O**perationally **N**ecessary

This guide ensures WPShadow meets the highest accessibility standards across all three CANON pillars.

---

## 🌍 Pillar 1: Accessibility First

### Automated Testing Tools

#### 1. **axe DevTools** (Browser Extension)
```bash
# Install for Chrome/Firefox
# Run automated accessibility audit
# Fix all Critical and Serious issues
# Document Moderate issues for manual review
```

#### 2. **WAVE** (Web Accessibility Evaluation Tool)
```bash
# Visit: https://wave.webaim.org/
# Enter your localhost URL
# Review all errors and alerts
# Verify contrast ratios
```

#### 3. **Lighthouse** (Chrome DevTools)
```bash
# Open Chrome DevTools
# Navigate to Lighthouse tab
# Run Accessibility audit
# Aim for 100 score
```

### Manual Testing Checklist

#### Keyboard Navigation
- [ ] Tab through all interactive elements
- [ ] Shift+Tab works in reverse
- [ ] Enter/Space activates buttons/links
- [ ] Escape closes modals/dropdowns
- [ ] Arrow keys navigate within components
- [ ] Focus never gets trapped
- [ ] Skip navigation links present
- [ ] Focus indicators clearly visible

#### Screen Reader Testing
```bash
# NVDA (Windows - Free)
# Install: https://www.nvaccess.org/download/
# Test all pages and forms

# JAWS (Windows - Commercial)
# VoiceOver (macOS - Built-in)
# Press Cmd+F5 to enable

# Orca (Linux - Free)
```

**Screen Reader Checklist:**
- [ ] All images have descriptive alt text
- [ ] Form fields have proper labels
- [ ] Error messages are announced
- [ ] Dynamic content changes announced
- [ ] ARIA landmarks present
- [ ] Heading hierarchy is logical (h1 → h2 → h3)
- [ ] Links have descriptive text (no "click here")

#### Color & Contrast
- [ ] Text contrast ratio ≥ 4.5:1 (normal text)
- [ ] Text contrast ratio ≥ 3:1 (large text 18pt+)
- [ ] UI component contrast ≥ 3:1
- [ ] Color is not the only indicator
- [ ] Links distinguishable without color
- [ ] Focus indicators meet contrast requirements

#### Visual Testing
- [ ] Content readable at 200% zoom
- [ ] No horizontal scrolling at 320px width
- [ ] Text spacing adjustable
- [ ] Content reflows on mobile
- [ ] Images scale appropriately
- [ ] Touch targets ≥ 44x44 pixels (mobile)

---

## 🎓 Pillar 2: Learning Inclusive

### Content Accessibility

#### Readability
```bash
# Use Hemingway Editor
# Aim for Grade 8 reading level or below
# Short sentences (15-20 words)
# Active voice preferred
```

**Checklist:**
- [ ] Plain language used (no unnecessary jargon)
- [ ] Technical terms defined on first use
- [ ] Instructions are clear and sequential
- [ ] Examples provided for complex concepts
- [ ] Visual aids support text content

#### Documentation Testing
- [ ] Beginner can complete tasks from docs
- [ ] Multiple formats available (text, video, interactive)
- [ ] Search functionality works well
- [ ] Code examples are copy-pasteable
- [ ] Screenshots have descriptive captions

#### Error Messages
- [ ] State what went wrong
- [ ] Explain why it's a problem
- [ ] Suggest how to fix it
- [ ] Provide links to relevant help
- [ ] Written in friendly tone

---

## 🌐 Pillar 3: Culturally Respectful

### Internationalization (i18n) Testing

#### Code Checklist
- [ ] All user-facing strings use `__()` or `_e()`
- [ ] Text domain is 'wpshadow'
- [ ] No hardcoded strings in templates
- [ ] Numbers formatted with `number_format_i18n()`
- [ ] Dates formatted with `date_i18n()`
- [ ] Currency uses appropriate formatting

#### RTL (Right-to-Left) Testing
```bash
# Install RTL Tester plugin
# Switch site language to Arabic/Hebrew
# Verify layout mirrors correctly
```

**RTL Checklist:**
- [ ] Layout flips correctly
- [ ] Icons positioned appropriately
- [ ] Text alignment correct
- [ ] Margins/padding mirror correctly
- [ ] No hardcoded left/right CSS

#### Cultural Sensitivity
- [ ] No culturally specific idioms
- [ ] Date formats don't assume MM/DD/YYYY
- [ ] Time zones handled correctly
- [ ] Name fields flexible (no assumptions)
- [ ] Address formats flexible
- [ ] Phone number formats flexible
- [ ] Gender fields inclusive (or not required)

---

## 🧪 Testing Workflow

### Pre-Commit Checklist
```bash
# Run automated tests
npm run test:a11y

# Manual keyboard test (2 minutes)
# Manual screen reader test (5 minutes)
# Check color contrast
# Verify responsive behavior
```

### Pre-Release Checklist
```bash
# Full accessibility audit
# User testing with assistive technology users
# RTL language testing
# Documentation review
# Third-party accessibility audit (for major releases)
```

---

## 🛠️ Tools & Resources

### Browser Extensions
- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE](https://wave.webaim.org/extension/)
- [Accessibility Insights](https://accessibilityinsights.io/)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)

### Color Contrast Checkers
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Colour Contrast Analyser](https://www.tpgi.com/color-contrast-checker/)

### Screen Readers
- [NVDA](https://www.nvaccess.org/) (Windows - Free)
- [JAWS](https://www.freedomscientific.com/products/software/jaws/) (Windows)
- VoiceOver (macOS/iOS - Built-in)
- [Orca](https://help.gnome.org/users/orca/stable/) (Linux)

### Learning Resources
- [W3C WAI Tutorials](https://www.w3.org/WAI/tutorials/)
- [WebAIM Articles](https://webaim.org/articles/)
- [A11y Project](https://www.a11yproject.com/)
- [Deque University](https://dequeuniversity.com/)

### WordPress Specific
- [Theme Handbook - Accessibility](https://developer.wordpress.org/themes/functionality/accessibility/)
- [Plugin Handbook - Accessibility](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/#accessibility)

---

## 📊 Accessibility Score Target

| Component | Target | Measurement |
|-----------|--------|-------------|
| Lighthouse Accessibility | 100 | Chrome DevTools |
| WAVE Errors | 0 | WAVE Tool |
| Keyboard Navigation | 100% functional | Manual testing |
| Screen Reader | Fully usable | Manual testing |
| Color Contrast | 100% passing | Automated + Manual |
| WCAG 2.1 AA | Full compliance | Third-party audit |

---

## 🚨 Common Accessibility Issues to Avoid

### ❌ Don't:
- Use `<div>` or `<span>` as buttons (use `<button>`)
- Rely solely on color to convey information
- Use placeholder as label
- Auto-play audio/video
- Set font size in pixels
- Use only icons without text labels
- Create keyboard traps
- Use low contrast colors
- Skip heading levels (h1 → h3)

### ✅ Do:
- Use semantic HTML elements
- Provide text alternatives for non-text content
- Ensure sufficient color contrast
- Support keyboard navigation
- Use ARIA attributes appropriately
- Test with real assistive technologies
- Include accessibility in your definition of done
- Consider accessibility from the start, not as an afterthought

---

**Remember:** Accessibility is not a feature—it's a fundamental requirement. Every user deserves equal access to WPShadow's functionality.

For questions or support: accessibility@wpshadow.com
