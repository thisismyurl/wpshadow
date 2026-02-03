# 100 Mobile Experience Diagnostic Tests

**Purpose:** Comprehensive test suite to identify all causes of poor mobile experience on WordPress sites.  
**Date:** February 2, 2026  
**Status:** Planning Phase  

**Existing Coverage:** ~42 mobile-related diagnostics already implemented  
**Gap Analysis:** This document identifies 100 testable, repeatable, accurate diagnostics to ensure complete coverage

---

## Principles for Mobile Diagnostics

All mobile experience diagnostics must be:

1. **Testable** - Can be validated programmatically or with known test patterns
2. **Repeatable** - Same inputs produce same results consistently
3. **Accurate** - Low false positive rate, clear detection criteria
4. **Actionable** - Finding includes specific remediation steps
5. **Measurable** - Impact quantifiable (page speed, usability metrics)

---

## Category 1: Viewport & Layout Configuration (10 tests)

### 1.1 Viewport Meta Tag Detection
**Test:** Check for `<meta name="viewport">` in HTML `<head>`  
**Expected:** `width=device-width, initial-scale=1`  
**Finding:** Missing viewport causes desktop rendering on mobile (320px width displayed at ~980px)  
**Impact:** 🔴 Critical - Google mobile-first indexing penalizes  
**Status:** ✅ Existing - `class-diagnostic-viewport-meta-tag-not-configured.php`

### 1.2 Viewport Configuration Validation
**Test:** Parse viewport content attribute for proper values  
**Expected:** 
- `width=device-width` (NOT fixed pixel width)
- `initial-scale=1.0` (NOT < 1.0 or > 1.0)
- No `maximum-scale=1` (prevents zoom accessibility)
- No `user-scalable=no` (WCAG violation)  
**Finding:** Incorrect viewport prevents proper responsive rendering  
**Impact:** 🟡 High  
**Status:** ✅ Existing - Validated in multiple diagnostics

### 1.3 Horizontal Scroll Detection
**Test:** Check CSS for fixed-width elements exceeding viewport  
**Method:** 
- Scan `style.css` for `width: [value]px` where value > 480
- Check for `overflow-x: scroll` on `body` or container elements
- Detect `min-width` values forcing horizontal scroll  
**Expected:** No horizontal scroll on 375px viewport  
**Finding:** Forces pinch-zoom and horizontal panning (poor UX)  
**Impact:** 🟡 High  
**Status:** 🔴 Gap - Needs implementation

### 1.4 Fixed Positioning Issues
**Test:** Detect `position: fixed` elements that block content on mobile  
**Method:**
- Scan for `position: fixed` in stylesheets
- Check element heights (headers/footers > 80px problematic)
- Validate `z-index` stacking (overlapping content)  
**Expected:** Fixed elements < 15% of viewport height  
**Finding:** Fixed headers/footers reduce usable screen space  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 1.5 Responsive Breakpoint Coverage
**Test:** Validate CSS media queries cover key mobile breakpoints  
**Method:**
- Check for `@media (max-width: 480px)` - Small phones
- Check for `@media (max-width: 768px)` - Tablets portrait
- Check for `@media (max-width: 1024px)` - Tablets landscape  
**Expected:** At least 3 mobile breakpoints defined  
**Finding:** Missing breakpoints cause layout issues on specific devices  
**Impact:** 🟡 Medium  
**Status:** 🟢 Partial - `class-diagnostic-theme-responsive-design-check.php`

### 1.6 Mobile-First vs Desktop-First Detection
**Test:** Analyze CSS cascade to determine design approach  
**Method:**
- Check if base styles assume mobile (then expand) or desktop (then contract)
- Look for `min-width` (mobile-first) vs `max-width` (desktop-first) media queries  
**Expected:** Mobile-first approach (faster mobile load)  
**Finding:** Desktop-first loads unnecessary CSS for mobile users  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 1.7 Fluid Layout Validation
**Test:** Check for flexible units (%, em, rem, vw) vs fixed pixels  
**Method:**
- Scan CSS for `width: [value]%` or `max-width: 100%`
- Detect `box-sizing: border-box` (prevents overflow)
- Check for `min-width` / `max-width` constraints  
**Expected:** 80%+ layout elements use flexible units  
**Finding:** Fixed-width elements break on narrow screens  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 1.8 Safe Area Insets (Notch Support)
**Test:** Check for CSS safe-area-inset variables for notched devices  
**Method:**
- Look for `padding-top: env(safe-area-inset-top)`
- Check `padding-bottom: env(safe-area-inset-bottom)`
- Validate `viewport-fit=cover` in viewport meta  
**Expected:** Safe areas defined for iPhone X+, Android notched devices  
**Finding:** Content hidden behind notch/navigation bars  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 1.9 Landscape Orientation Support
**Test:** Validate layout works in landscape mode (not just portrait)  
**Method:**
- Check for `@media (orientation: landscape)` rules
- Test navigation accessibility in landscape
- Verify content doesn't get cut off  
**Expected:** Functional layout in both orientations  
**Finding:** Landscape mode breaks navigation or hides content  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 1.10 Print Stylesheet Mobile Compatibility
**Test:** Check if print styles work on mobile browsers  
**Method:**
- Validate `@media print` rules exist
- Check for mobile-specific print adjustments  
**Expected:** Clean mobile print output  
**Finding:** Mobile users can't print pages effectively  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

---

## Category 2: Touch Interaction & Usability (15 tests)

### 2.1 Tap Target Size Validation
**Test:** Measure interactive element dimensions  
**Method:**
- Scan for `<button>`, `<a>`, `<input>` elements
- Calculate rendered size (width × height)
- Check CSS `min-width` and `min-height`  
**Expected:** Minimum 44×44px (Apple HIG) or 48×48px (Material Design)  
**Finding:** Small tap targets cause mis-taps (frustration, errors)  
**Impact:** 🔴 Critical - WCAG 2.5.5 requirement  
**Status:** 🟢 Partial - Mentioned in mobile checks

### 2.2 Tap Target Spacing
**Test:** Measure distance between adjacent interactive elements  
**Method:**
- Calculate center-to-center distance between buttons/links
- Check for `margin` or `padding` separation  
**Expected:** Minimum 8px spacing (WCAG 2.5.8)  
**Finding:** Cramped targets cause accidental activation  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 2.3 Touch Gesture Support
**Test:** Validate common gestures work (swipe, pinch, long-press)  
**Method:**
- Check for JavaScript touch event listeners
- Validate gesture libraries (Hammer.js, etc.)
- Test media gallery swipe navigation  
**Expected:** Native gestures supported where appropriate  
**Finding:** Users can't navigate carousels/galleries with swipe  
**Impact:** 🟡 Medium  
**Status:** ✅ Existing - `class-diagnostic-touch-gesture-support.php`

### 2.4 Hover-Dependent Functionality
**Test:** Detect CSS `:hover` states with no touch alternative  
**Method:**
- Scan CSS for `:hover` selectors
- Check if equivalent `:active` or `:focus` styles exist
- Detect hover-only dropdown menus  
**Expected:** All hover interactions have touch equivalents  
**Finding:** Menus/tooltips inaccessible on touch devices  
**Impact:** 🔴 Critical  
**Status:** 🔴 Gap

### 2.5 Double-Tap Zoom Delay
**Test:** Detect 300ms click delay on mobile browsers  
**Method:**
- Check for `touch-action: manipulation` CSS
- Look for FastClick library or native solution
- Validate viewport width=device-width (eliminates delay in modern browsers)  
**Expected:** No 300ms delay  
**Finding:** Sluggish interface feels unresponsive  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 2.6 Scroll Performance
**Test:** Check for smooth scroll implementation  
**Method:**
- Look for `-webkit-overflow-scrolling: touch`
- Check for JavaScript scroll event throttling
- Detect heavy scroll event listeners  
**Expected:** Smooth 60fps scrolling  
**Finding:** Janky scrolling hurts user experience  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 2.7 Form Input Focus Zoom
**Test:** Validate input fields don't trigger unwanted zoom on focus  
**Method:**
- Check if input `font-size` is ≥16px
- iOS zooms on inputs <16px to improve readability  
**Expected:** 16px minimum font size on form fields  
**Finding:** Automatic zoom disrupts form completion  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 2.8 Touch Event Conflicts
**Test:** Detect click + touch event handlers causing double-fire  
**Method:**
- Scan JavaScript for both `click` and `touchstart`/`touchend` listeners
- Check for `preventDefault()` usage  
**Expected:** Single event fires per interaction  
**Finding:** Buttons trigger twice, forms submit multiple times  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 2.9 Long Press Detection
**Test:** Validate context menus work on touch devices  
**Method:**
- Check for `contextmenu` event listeners
- Look for `touchstart` + timer pattern for long-press  
**Expected:** Long-press triggers context actions  
**Finding:** Touch users can't access right-click features  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 2.10 Pull-to-Refresh Conflicts
**Test:** Detect custom scroll implementations that break pull-to-refresh  
**Method:**
- Check for `overscroll-behavior` CSS
- Look for JavaScript that prevents default touch behavior  
**Expected:** Native pull-to-refresh works unless intentionally disabled  
**Finding:** Users can't refresh page with standard gesture  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 2.11 Pointer Event Support
**Test:** Check for modern pointer events vs touch/mouse events  
**Method:**
- Scan for `pointerdown`, `pointermove`, `pointerup` listeners
- Validate unified pointer handling (mouse/touch/pen)  
**Expected:** Pointer events used for cross-device compatibility  
**Finding:** Inconsistent behavior across input methods  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 2.12 Touch Feedback Visual Indicators
**Test:** Validate visual feedback on touch (highlight, ripple effect)  
**Method:**
- Check for `:active` CSS states
- Look for Material Design ripple effects
- Validate `user-select: none` on interactive elements  
**Expected:** Clear visual feedback on all touch interactions  
**Finding:** Users unsure if tap registered  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 2.13 Accidental Touch Prevention
**Test:** Check for touch rejection on edge swipes  
**Method:**
- Validate `touch-action: pan-y` on vertical scrollers
- Check for edge detection on back swipe  
**Expected:** Accidental edge touches ignored  
**Finding:** Users trigger unintended navigation  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 2.14 Multi-Touch Gesture Support
**Test:** Validate pinch-zoom, two-finger scroll work correctly  
**Method:**
- Check if viewport allows zoom (no `user-scalable=no`)
- Validate `touch-action` doesn't prevent gestures  
**Expected:** Standard multi-touch gestures work  
**Finding:** Accessibility issue (WCAG 1.4.4 requires zoom)  
**Impact:** 🔴 Critical  
**Status:** 🟢 Partial - Viewport checks

### 2.15 Touch Latency Measurement
**Test:** Calculate time between touch and visual response  
**Method:**
- Measure JavaScript execution time on touch events
- Check for render-blocking during interaction  
**Expected:** <100ms response time  
**Finding:** Sluggish interface feels broken  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

---

## Category 3: Performance & Speed (20 tests)

### 3.1 Mobile Page Weight
**Test:** Calculate total page size (HTML + CSS + JS + images)  
**Method:**
- Sum all resource sizes served to mobile user agent
- Separate above-fold vs below-fold resources  
**Expected:** <1MB for initial load, <3MB total  
**Finding:** Large pages consume mobile data plans  
**Impact:** 🔴 Critical  
**Status:** 🔴 Gap

### 3.2 Mobile-Specific Image Sizes
**Test:** Validate images served match device resolution  
**Method:**
- Check for `srcset` attribute with multiple sizes
- Verify `sizes` attribute for responsive sizing
- Test if 2x Retina images only sent to Retina devices  
**Expected:** Right-sized images per device  
**Finding:** 4K images sent to 375px screens waste 80% bandwidth  
**Impact:** 🔴 Critical  
**Status:** ✅ Existing - `class-diagnostic-image-resizing-misconfigured.php`

### 3.3 Mobile JavaScript Bundle Size
**Test:** Measure JS sent to mobile devices  
**Method:**
- Calculate total JS size for mobile user agent
- Check for mobile-specific JS exclusions  
**Expected:** <300KB JavaScript (compressed)  
**Finding:** Desktop scripts sent to mobile waste bandwidth  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.4 Mobile CSS Bundle Size
**Test:** Measure CSS sent to mobile devices  
**Method:**
- Calculate total CSS size for mobile user agent
- Check for conditional CSS loading  
**Expected:** <100KB CSS (compressed)  
**Finding:** Unused desktop styles slow mobile parsing  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.5 Render-Blocking Resources on Mobile
**Test:** Identify scripts/styles blocking first paint  
**Method:**
- Check for `async` or `defer` on scripts
- Look for critical CSS inlining
- Detect blocking external resources  
**Expected:** No render-blocking resources above fold  
**Finding:** Blank white screen for 2-5 seconds  
**Impact:** 🔴 Critical  
**Status:** 🟢 Partial - Performance diagnostics exist

### 3.6 Mobile Font Loading Strategy
**Test:** Validate web font loading doesn't block rendering  
**Method:**
- Check for `font-display: swap` or `font-display: optional`
- Look for FOIT (Flash of Invisible Text) duration
- Validate system font fallbacks  
**Expected:** Text visible immediately with fallback font  
**Finding:** Invisible text for 3+ seconds (FOIT)  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.7 Mobile LCP (Largest Contentful Paint)
**Test:** Measure time to largest visible element  
**Method:**
- Use Chrome User Experience Report API
- Calculate LCP from resource timing
- Check if hero image optimized  
**Expected:** <2.5s on 4G  
**Finding:** Slow LCP hurts Core Web Vitals score  
**Impact:** 🔴 Critical - Google ranking factor  
**Status:** 🔴 Gap

### 3.8 Mobile FID (First Input Delay)
**Test:** Measure time from first tap to browser response  
**Method:**
- Check for long JavaScript tasks blocking main thread
- Validate input handlers are fast  
**Expected:** <100ms  
**Finding:** Unresponsive to user interaction  
**Impact:** 🔴 Critical - Google ranking factor  
**Status:** 🔴 Gap

### 3.9 Mobile CLS (Cumulative Layout Shift)
**Test:** Calculate layout shifts during page load  
**Method:**
- Check for width/height attributes on images
- Validate ad slot reservations
- Look for dynamic content insertion  
**Expected:** <0.1 CLS score  
**Finding:** Content jumps cause mis-taps  
**Impact:** 🔴 Critical - Google ranking factor  
**Status:** 🔴 Gap

### 3.10 Mobile Time to Interactive (TTI)
**Test:** Measure when page becomes fully interactive  
**Method:**
- Calculate JavaScript parse + execution time
- Check for long tasks blocking main thread  
**Expected:** <5s on 4G  
**Finding:** Page looks ready but doesn't respond  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.11 Mobile Server Response Time
**Test:** Measure TTFB (Time to First Byte) on mobile networks  
**Method:**
- Check server response time via Resource Timing API
- Test on 3G/4G emulation  
**Expected:** <600ms  
**Finding:** Slow backend delays all mobile users  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.12 Mobile Connection Type Detection
**Test:** Check if site adapts to network speed  
**Method:**
- Look for Network Information API usage
- Check for `Save-Data` header support
- Validate adaptive loading based on connection  
**Expected:** Site adapts to slow connections  
**Finding:** 3G users get same experience as fiber users  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 3.13 Mobile Cache Strategy
**Test:** Validate service worker and cache headers for mobile  
**Method:**
- Check for service worker registration
- Validate `Cache-Control` headers
- Test offline fallback  
**Expected:** Repeat visits use cache effectively  
**Finding:** Every visit re-downloads everything  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 3.14 Mobile Image Format Optimization
**Test:** Check for modern formats (WebP, AVIF) served to mobile  
**Method:**
- Validate `<picture>` with format fallbacks
- Check for WebP support detection  
**Expected:** WebP/AVIF served to supporting browsers  
**Finding:** PNG/JPEG 30% larger than WebP  
**Impact:** 🟡 High  
**Status:** ✅ Existing - `class-diagnostic-webp-conversion-support-missing.php`

### 3.15 Mobile Video Optimization
**Test:** Validate videos optimized for mobile bandwidth  
**Method:**
- Check for multiple bitrate sources
- Validate poster images for videos
- Test autoplay disabled on cellular  
**Expected:** Adaptive bitrate streaming  
**Finding:** HD videos auto-play on 3G connections  
**Impact:** 🟡 High  
**Status:** 🟢 Partial - Video diagnostics exist

### 3.16 Mobile Third-Party Script Impact
**Test:** Measure performance impact of third-party scripts on mobile  
**Method:**
- Calculate total third-party JS size
- Check for async loading
- Validate script timeouts  
**Expected:** <200KB third-party scripts  
**Finding:** Analytics/ads slow mobile by 3-5 seconds  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.17 Mobile AMP Availability
**Test:** Check if AMP version available for mobile users  
**Method:**
- Look for `<link rel="amphtml">` in pages
- Validate AMP markup if present  
**Expected:** AMP available for content pages  
**Finding:** Missing fast mobile alternative  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 3.18 Mobile Resource Hints
**Test:** Validate preconnect/prefetch for mobile performance  
**Method:**
- Check for `<link rel="preconnect">` for critical origins
- Look for `<link rel="dns-prefetch">`
- Validate resource prioritization  
**Expected:** Preconnect to CDN, fonts, APIs  
**Finding:** Delayed connections add 200-500ms per domain  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 3.19 Mobile Critical CSS Inlining
**Test:** Check if above-fold CSS is inlined  
**Method:**
- Measure `<style>` block in `<head>`
- Validate critical CSS size (<14KB)  
**Expected:** Critical styles inlined, rest async loaded  
**Finding:** Render-blocking external stylesheet delays paint  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 3.20 Mobile Lazy Loading Implementation
**Test:** Validate images/iframes lazy load below fold  
**Method:**
- Check for `loading="lazy"` attribute
- Look for Intersection Observer usage
- Validate eager loading on first 2-3 images  
**Expected:** Below-fold resources deferred  
**Finding:** All images load immediately (waste bandwidth)  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

---

## Category 4: Typography & Readability (8 tests)

### 4.1 Mobile Font Size - Body Text
**Test:** Validate minimum font size for readability  
**Method:**
- Check `body` font-size in CSS
- Calculate at 375px viewport width  
**Expected:** ≥16px (Chrome won't zoom)  
**Finding:** Small text forces pinch-zoom to read  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 4.2 Mobile Font Size - Headings
**Test:** Validate heading hierarchy on small screens  
**Method:**
- Check `h1`-`h6` font sizes at mobile breakpoint
- Ensure sufficient contrast between levels  
**Expected:** H1 ≥24px, clear hierarchy  
**Finding:** Headings too similar or too small  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 4.3 Mobile Line Height
**Test:** Validate line spacing for mobile readability  
**Method:**
- Check `line-height` on body text
- Calculate comfort zone (1.4-1.6)  
**Expected:** 1.5 line-height minimum  
**Finding:** Cramped text hard to read on small screens  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 4.4 Mobile Line Length
**Test:** Measure characters per line at mobile widths  
**Method:**
- Calculate text container width
- Estimate characters per line (avg 5 chars/word + spaces)  
**Expected:** 45-75 characters per line  
**Finding:** Too long (hard to track) or too short (choppy)  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 4.5 Mobile Text Contrast Ratio
**Test:** Validate text contrast on mobile (outdoor readability)  
**Method:**
- Calculate contrast ratio (WCAG formula)
- Check for thin fonts (harder to read outdoors)  
**Expected:** 4.5:1 for normal text, 3:1 for large text  
**Finding:** Low contrast illegible in sunlight  
**Impact:** 🔴 Critical - WCAG 1.4.3  
**Status:** 🟢 Partial - Accessibility checks

### 4.6 Mobile Font Loading Strategy
**Test:** Check web font performance on mobile  
**Method:**
- Count number of font families loaded
- Calculate total font file size  
**Expected:** ≤2 font families, <100KB total  
**Finding:** Excessive fonts slow load by 1-3 seconds  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 4.7 Mobile Text Selection
**Test:** Validate text is selectable for copy/paste  
**Method:**
- Check for `user-select: none` on text content
- Validate selection highlighting visible  
**Expected:** All content text selectable  
**Finding:** Users can't copy addresses, phone numbers  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 4.8 Mobile Text Zoom Capability
**Test:** Ensure text scales when user zooms  
**Method:**
- Check for CSS that prevents zoom
- Test if text reflows at 200% zoom (WCAG 1.4.4)  
**Expected:** Text readable at 200% zoom without horizontal scroll  
**Finding:** Accessibility violation for low-vision users  
**Impact:** 🔴 Critical - WCAG requirement  
**Status:** 🔴 Gap

---

## Category 5: Navigation & Menus (10 tests)

### 5.1 Mobile Menu Existence
**Test:** Validate mobile-friendly navigation exists  
**Method:**
- Check for hamburger menu icon
- Look for `@media` query hiding desktop nav
- Validate mobile menu implementation  
**Expected:** Dedicated mobile menu at <768px  
**Finding:** Desktop nav cramped/unusable on mobile  
**Impact:** 🔴 Critical  
**Status:** 🟢 Partial - `class-diagnostic-menu-mobile-responsiveness.php`

### 5.2 Mobile Menu Tap Target Size
**Test:** Measure menu item dimensions  
**Method:**
- Check mobile menu `<li>` or `<a>` height
- Validate padding provides 44px+ tap area  
**Expected:** 44px minimum height per menu item  
**Finding:** Difficult to tap correct menu item  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 5.3 Mobile Menu Keyboard Navigation
**Test:** Validate keyboard access to mobile menu  
**Method:**
- Check if menu toggle is keyboard accessible
- Test Tab navigation through menu items  
**Expected:** Full keyboard navigation  
**Finding:** Screen reader users can't navigate  
**Impact:** 🔴 Critical - WCAG 2.1.1  
**Status:** 🔴 Gap

### 5.4 Mobile Submenu Interaction
**Test:** Validate submenu disclosure on touch devices  
**Method:**
- Check if submenus use click/tap (not hover)
- Test nested submenu accessibility  
**Expected:** Tap to expand submenus  
**Finding:** Hover-only submenus inaccessible  
**Impact:** 🔴 Critical  
**Status:** 🔴 Gap

### 5.5 Mobile Menu Performance
**Test:** Measure menu toggle animation performance  
**Method:**
- Check for CSS transitions vs JavaScript animations
- Validate 60fps during open/close  
**Expected:** Smooth animation <300ms  
**Finding:** Janky animation feels broken  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 5.6 Mobile Menu Overlay Accessibility
**Test:** Validate menu overlay behavior  
**Method:**
- Check if overlay traps focus (modal pattern)
- Test Escape key closes menu  
**Expected:** Focus management, ESC to close  
**Finding:** Can't exit menu without closing  
**Impact:** 🟡 High - WCAG 2.4.3  
**Status:** 🔴 Gap

### 5.7 Mobile Sticky Header
**Test:** Validate sticky navigation doesn't block content  
**Method:**
- Check sticky header height percentage
- Test content visibility behind sticky element  
**Expected:** <10% viewport height  
**Finding:** Sticky header blocks 30%+ of screen  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 5.8 Mobile Search Functionality
**Test:** Validate search accessible on mobile  
**Method:**
- Check for mobile search icon/input
- Test search results page mobile optimization  
**Expected:** Prominent search access  
**Finding:** Can't find search on mobile  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 5.9 Mobile Skip Link
**Test:** Check for "Skip to content" link on mobile  
**Method:**
- Look for skip link in mobile menu
- Validate keyboard activation  
**Expected:** Skip link bypasses navigation  
**Finding:** Screen reader users must tab through all nav  
**Impact:** 🟡 High - WCAG 2.4.1  
**Status:** 🔴 Gap

### 5.10 Mobile Breadcrumb Usability
**Test:** Validate breadcrumb navigation on small screens  
**Method:**
- Check if breadcrumbs collapse/truncate on mobile
- Test tap target size on breadcrumb links  
**Expected:** Readable breadcrumbs with adequate spacing  
**Finding:** Breadcrumbs overflow or overlap  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

---

## Category 6: Forms & Input (12 tests)

### 6.1 Mobile Input Field Size
**Test:** Validate form inputs are large enough  
**Method:**
- Check input height in CSS
- Validate padding provides comfortable tap target  
**Expected:** 44px minimum height  
**Finding:** Difficult to tap into input fields  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.2 Mobile Input Type Attributes
**Test:** Check for mobile-optimized input types  
**Method:**
- Validate `type="email"` for email fields
- Check `type="tel"` for phone numbers
- Ensure `type="number"` for numeric inputs  
**Expected:** Correct virtual keyboard for each field  
**Finding:** Wrong keyboard type slows form completion  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.3 Mobile Input Autocomplete
**Test:** Validate autocomplete attributes for autofill  
**Method:**
- Check for `autocomplete="name"`, `autocomplete="email"`, etc.
- Test with browser autofill  
**Expected:** Fields auto-populate from saved data  
**Finding:** Users must manually type everything  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.4 Mobile Form Label Association
**Test:** Validate labels properly associated with inputs  
**Method:**
- Check for `<label for="input-id">` pattern
- Ensure tapping label focuses input  
**Expected:** Larger tap area via label association  
**Finding:** Can only tap tiny input box  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.5 Mobile Input Focus Styling
**Test:** Check visual feedback when input receives focus  
**Method:**
- Validate `:focus` styles on inputs
- Ensure focus indicator visible  
**Expected:** Clear focus ring/highlight  
**Finding:** User unsure which field is active  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 6.6 Mobile Input Validation
**Test:** Validate inline error messages visible on mobile  
**Method:**
- Check error message positioning
- Ensure errors don't overlap inputs  
**Expected:** Error visible without scrolling  
**Finding:** Error messages hidden or truncated  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.7 Mobile Form Submit Button
**Test:** Validate submit button visible and accessible  
**Method:**
- Check button size (minimum 44×44px)
- Ensure button doesn't scroll off viewport  
**Expected:** Fixed/sticky submit button or clearly visible  
**Finding:** Must scroll to find submit button  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.8 Mobile Multi-Step Form Navigation
**Test:** Validate progress indicators on multi-step forms  
**Method:**
- Check for step counter or progress bar
- Test back/next button accessibility  
**Expected:** Clear progress indication  
**Finding:** Users lost in multi-step process  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 6.9 Mobile File Upload Interface
**Test:** Validate file picker accessible on mobile  
**Method:**
- Check for camera capture attributes
- Test gallery selection works  
**Expected:** Camera + gallery options  
**Finding:** Can't upload photos from mobile  
**Impact:** 🟡 High  
**Status:** ✅ Existing - `class-diagnostic-mobile-upload-compatibility.php`

### 6.10 Mobile Dropdown Accessibility
**Test:** Validate `<select>` dropdowns work on mobile  
**Method:**
- Check if native select works
- Test custom dropdowns for touch support  
**Expected:** Native mobile picker UI  
**Finding:** Custom dropdowns don't work on mobile  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 6.11 Mobile Date/Time Pickers
**Test:** Validate date inputs use native mobile pickers  
**Method:**
- Check for `type="date"`, `type="time"`
- Test custom pickers for touch support  
**Expected:** Native mobile date/time picker  
**Finding:** Typing dates manually error-prone  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 6.12 Mobile Form Field Grouping
**Test:** Check fieldset/legend for form organization  
**Method:**
- Validate `<fieldset>` used for related fields
- Check for clear section headers  
**Expected:** Logical field grouping  
**Finding:** Long forms feel overwhelming  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

---

## Category 7: Media & Images (8 tests)

### 7.1 Mobile Image Lazy Loading
**Test:** Validate images load efficiently on mobile  
**Method:**
- Check for `loading="lazy"` attribute
- Test Intersection Observer implementation  
**Expected:** Below-fold images deferred  
**Finding:** All images load immediately (slow)  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 7.2 Mobile Responsive Images
**Test:** Check srcset implementation for different screen densities  
**Method:**
- Validate `srcset` with 1x, 2x, 3x options
- Check `sizes` attribute for breakpoints  
**Expected:** Right image size per device  
**Finding:** Oversized images waste bandwidth  
**Impact:** 🔴 Critical  
**Status:** ✅ Existing - Image diagnostics

### 7.3 Mobile Video Playback
**Test:** Validate videos play inline on mobile  
**Method:**
- Check for `playsinline` attribute
- Test autoplay behavior  
**Expected:** Inline playback (no fullscreen popup)  
**Finding:** Videos force fullscreen (disruptive)  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 7.4 Mobile Image Gallery Navigation
**Test:** Check gallery swipe functionality  
**Method:**
- Validate touch events on lightbox
- Test pinch-to-zoom in gallery  
**Expected:** Swipe between images  
**Finding:** Must use tiny arrow buttons  
**Impact:** 🟡 Medium  
**Status:** 🟢 Partial - Touch gesture checks

### 7.5 Mobile Image Alt Text
**Test:** Ensure images have descriptive alt text  
**Method:**
- Check for `alt` attribute presence
- Validate alt text quality (not just filename)  
**Expected:** Descriptive alt text  
**Finding:** Screen readers can't describe images  
**Impact:** 🔴 Critical - WCAG 1.1.1  
**Status:** 🟢 Partial - Accessibility checks

### 7.6 Mobile Background Images
**Test:** Validate background images optimized for mobile  
**Method:**
- Check for mobile-specific background sizes
- Look for CSS media queries for backgrounds  
**Expected:** Smaller backgrounds on mobile  
**Finding:** 4K hero images slow mobile load  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 7.7 Mobile Icon Rendering
**Test:** Check SVG vs icon font vs PNG icons on mobile  
**Method:**
- Validate icons are crisp at all sizes
- Check icon file sizes  
**Expected:** SVG icons or optimized icon fonts  
**Finding:** Blurry or oversized icon files  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 7.8 Mobile Thumbnail Loading
**Test:** Validate thumbnail sizes appropriate for mobile  
**Method:**
- Check thumbnail dimensions in srcset
- Test if thumbnails progressive/blurred placeholder  
**Expected:** <50KB thumbnails  
**Finding:** Full-size images used as thumbnails  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

---

## Category 8: Accessibility on Mobile (10 tests)

### 8.1 Mobile Screen Reader Compatibility
**Test:** Validate ARIA labels work on mobile screen readers  
**Method:**
- Test with VoiceOver (iOS) or TalkBack (Android)
- Check landmark regions  
**Expected:** Logical reading order, clear labels  
**Finding:** Screen reader navigation confusing  
**Impact:** 🔴 Critical - WCAG 4.1.2  
**Status:** 🔴 Gap

### 8.2 Mobile Focus Management
**Test:** Validate focus order on touch + keyboard devices  
**Method:**
- Test tab order with keyboard
- Check focus isn't trapped in modals  
**Expected:** Logical focus progression  
**Finding:** Focus jumps unpredictably  
**Impact:** 🔴 Critical - WCAG 2.4.3  
**Status:** 🔴 Gap

### 8.3 Mobile Color Contrast in Dark Mode
**Test:** Check contrast ratios in dark mode  
**Method:**
- Test with OS-level dark mode enabled
- Validate `prefers-color-scheme` media query  
**Expected:** 4.5:1 contrast maintained  
**Finding:** Dark mode illegible  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 8.4 Mobile Text Resize Without Zoom
**Test:** Validate text scales with OS-level text size  
**Method:**
- Test with iOS Dynamic Type or Android font scaling
- Check for fixed pixel sizes  
**Expected:** Text respects user preferences  
**Finding:** Users with vision issues can't read content  
**Impact:** 🔴 Critical - WCAG 1.4.4  
**Status:** 🔴 Gap

### 8.5 Mobile Reduce Motion Preference
**Test:** Check for `prefers-reduced-motion` support  
**Method:**
- Test with OS animation settings reduced
- Validate animations can be disabled  
**Expected:** Minimal/no animations when requested  
**Finding:** Vestibular issues triggered by motion  
**Impact:** 🔴 Critical - WCAG 2.3.3  
**Status:** 🔴 Gap

### 8.6 Mobile Touch Accommodation
**Test:** Validate support for assistive touch tools  
**Method:**
- Test with Switch Control (iOS) or Switch Access (Android)
- Check for sufficient tap targets  
**Expected:** All interactive elements accessible  
**Finding:** Motor impaired users can't navigate  
**Impact:** 🔴 Critical  
**Status:** 🔴 Gap

### 8.7 Mobile Audio/Video Captions
**Test:** Check for closed captions on mobile videos  
**Method:**
- Validate `<track>` elements present
- Test caption display on mobile  
**Expected:** Captions available and readable  
**Finding:** Deaf users can't access video content  
**Impact:** 🔴 Critical - WCAG 1.2.2  
**Status:** 🔴 Gap

### 8.8 Mobile Orientation Lock Detection
**Test:** Check if content accessible in both orientations  
**Method:**
- Test with orientation locked (accessibility feature)
- Validate content doesn't force rotation  
**Expected:** Works in user's preferred orientation  
**Finding:** Some users can't rotate device  
**Impact:** 🟡 High - WCAG 1.3.4  
**Status:** 🔴 Gap

### 8.9 Mobile Haptic Feedback
**Test:** Validate appropriate haptic feedback on interactions  
**Method:**
- Check for vibration API usage
- Test feedback on form errors  
**Expected:** Haptic cues for important actions  
**Finding:** No tactile feedback for blind users  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

### 8.10 Mobile Voice Control Compatibility
**Test:** Test with Siri/Google Assistant voice commands  
**Method:**
- Check if form fields have proper labels
- Validate voice navigation works  
**Expected:** Can complete tasks with voice  
**Finding:** Voice control users can't navigate  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

---

## Category 9: Content & Layout (7 tests)

### 9.1 Mobile Content Prioritization
**Test:** Validate most important content appears first  
**Method:**
- Check source order vs visual order
- Ensure sidebars don't precede main content  
**Expected:** Main content first in DOM  
**Finding:** Users must scroll past ads/sidebars  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 9.2 Mobile Ad Placement
**Test:** Check ads don't block content on mobile  
**Method:**
- Validate ad sizes appropriate for mobile
- Check for interstitial ad timing  
**Expected:** Ads don't obscure content  
**Finding:** Can't read article behind ads  
**Impact:** 🟡 High - Google penalizes intrusive ads  
**Status:** 🔴 Gap

### 9.3 Mobile Table Responsiveness
**Test:** Validate tables work on narrow screens  
**Method:**
- Check for horizontal scroll or table transformation
- Test if tables collapse to cards on mobile  
**Expected:** Tables readable without horizontal scroll  
**Finding:** Tables overflow viewport  
**Impact:** 🟡 High  
**Status:** 🔴 Gap

### 9.4 Mobile Popup Timing
**Test:** Validate popups don't appear too early  
**Method:**
- Check popup trigger delay (should be >5 seconds)
- Ensure popups have large close buttons  
**Expected:** User can read content before popup  
**Finding:** Immediate popups block content  
**Impact:** 🟡 High - Google penalizes early popups  
**Status:** 🔴 Gap

### 9.5 Mobile Footer Accessibility
**Test:** Check if footer links usable on mobile  
**Method:**
- Validate footer link sizes (44px+ tap target)
- Check for mobile footer simplification  
**Expected:** Readable footer with adequate spacing  
**Finding:** Tiny links impossible to tap  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 9.6 Mobile Sidebar Handling
**Test:** Validate sidebar placement on mobile  
**Method:**
- Check if sidebar moves below content
- Ensure sidebar doesn't disappear entirely  
**Expected:** Sidebar accessible after main content  
**Finding:** Important links lost on mobile  
**Impact:** 🟡 Medium  
**Status:** 🔴 Gap

### 9.7 Mobile Reading Mode Compatibility
**Test:** Check if content works in browser reading mode  
**Method:**
- Test with Safari Reader, Firefox Reader
- Validate semantic HTML structure  
**Expected:** Clean extraction to reading mode  
**Finding:** Reading mode breaks layout  
**Impact:** 🟢 Low  
**Status:** 🔴 Gap

---

## Summary Statistics

| Category | Total Tests | Existing | Partial | Gaps |
|----------|------------|----------|---------|------|
| 1. Viewport & Layout | 10 | 2 | 1 | 7 |
| 2. Touch & Usability | 15 | 1 | 1 | 13 |
| 3. Performance & Speed | 20 | 2 | 1 | 17 |
| 4. Typography | 8 | 0 | 1 | 7 |
| 5. Navigation & Menus | 10 | 0 | 1 | 9 |
| 6. Forms & Input | 12 | 1 | 0 | 11 |
| 7. Media & Images | 8 | 1 | 1 | 6 |
| 8. Accessibility | 10 | 0 | 0 | 10 |
| 9. Content & Layout | 7 | 0 | 0 | 7 |
| **TOTAL** | **100** | **7** | **6** | **87** |

**Coverage Analysis:**
- ✅ **Existing:** 7 tests (7%) - Already implemented diagnostics
- 🟢 **Partial:** 6 tests (6%) - Partially covered by existing diagnostics
- 🔴 **Gaps:** 87 tests (87%) - Need new diagnostic implementation

---

## Priority Implementation Roadmap

### Phase 1: Critical Gaps (🔴 Critical Impact) - 15 diagnostics
**Business Impact:** Direct Google ranking factors, WCAG compliance, basic usability

1. Mobile Page Weight Detection
2. Mobile LCP (Largest Contentful Paint)
3. Mobile FID (First Input Delay)
4. Mobile CLS (Cumulative Layout Shift)
5. Tap Target Size Validation
6. Hover-Dependent Functionality Detection
7. Mobile Font Size - Body Text
8. Mobile Text Contrast Ratio
9. Mobile Menu Existence Check
10. Mobile Submenu Interaction
11. Multi-Touch Gesture Support (Zoom)
12. Mobile Screen Reader Compatibility
13. Mobile Focus Management
14. Mobile Text Resize Without Zoom
15. Mobile Reduce Motion Preference

### Phase 2: High Impact (🟡 High) - 35 diagnostics
**Business Impact:** User experience, conversion optimization, accessibility

Phase 2 covers all remaining "High" impact tests including:
- Performance optimizations (JS/CSS bundle size, render-blocking)
- Form usability (input types, validation, labels)
- Navigation improvements (menu performance, search)
- Content accessibility (tables, ads, popups)

### Phase 3: Medium Impact (🟡 Medium) - 35 diagnostics
**Business Impact:** Polish, edge cases, advanced features

Phase 3 includes "Medium" priority items that improve overall experience but aren't blocking critical functionality.

### Phase 4: Low Impact (🟢 Low) - 15 diagnostics
**Business Impact:** Nice-to-have features, specialized use cases

Phase 4 covers specialized scenarios and advanced features that affect smaller user segments.

---

## Testing Methodology

Each diagnostic must follow this pattern:

```php
/**
 * Example Mobile Diagnostic
 *
 * Brief description of what this checks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.YDDD.HHMM
 */
class Diagnostic_Mobile_Example extends Diagnostic_Base {
    protected static $slug = 'mobile-example';
    protected static $title = 'Mobile Example Check';
    protected static $description = 'Checks X for mobile devices';
    protected static $family = 'performance';

    public static function check() {
        // 1. DETECT: Programmatically test condition
        $test_result = self::perform_test();
        
        // 2. MEASURE: Quantify the issue
        $impact_metric = self::calculate_impact($test_result);
        
        // 3. VALIDATE: Ensure accurate detection
        if (self::is_false_positive($test_result)) {
            return null;
        }
        
        // 4. RETURN: Structured finding with remediation
        if ($issue_detected) {
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => 'Specific issue detected: X',
                'severity'    => 'high',
                'threat_level' => 65,
                'auto_fixable' => true/false,
                'current_value' => $measured_value,
                'recommended_value' => $optimal_value,
                'user_impact' => 'What users experience',
                'fix_steps'   => array('Step 1', 'Step 2'),
                'kb_link'     => 'https://wpshadow.com/kb/...',
            );
        }
        
        return null; // No issue
    }
}
```

---

## Next Steps

1. **Review & Prioritize:** Validate this list with team, adjust priorities
2. **Create Issues:** Generate GitHub issues for Phase 1 diagnostics
3. **Implement:** Build diagnostics following existing patterns
4. **Test:** Validate on real mobile devices (not just emulators)
5. **Document:** Update this document with implementation status

---

**Document Version:** 1.0  
**Last Updated:** February 2, 2026  
**Next Review:** After Phase 1 completion
