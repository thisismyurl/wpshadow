#!/usr/bin/env python3
"""
Create 100 Core Values Diagnostic Issues

Generates GitHub issues for diagnostics that help customers test if THEIR sites
embody the 12 Commandments and 5 CANON Pillars.

These are NOT plugin self-checks - they're tests customers run on their sites.
"""

import subprocess
import json

# 12 Commandments + 5 CANON Pillars diagnostics
diagnostics = [
    # ═══════════════════════════════════════════════════════════════
    # PILLAR #1: 🌍 ACCESSIBILITY FIRST (26 diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "Videos Missing Captions or Transcripts",
        "body": """**Diagnostic:** Check if embedded videos have captions/transcripts available

**Why This Matters (Commandment #1: Helpful Neighbor):**
About 5% of the world's population is deaf or hard of hearing. Videos without captions exclude them completely—like hosting a party and not letting some guests in the door.

**User Impact:**
- Deaf/hard of hearing visitors can't understand video content
- Users in noisy environments (cafes, public transit) can't watch
- SEO suffers (search engines can't index spoken content)
- Non-native speakers struggle to understand audio

**What We Check:**
- `<video>` tags have `<track kind="captions">` elements
- YouTube embeds have `cc_load_policy=1` parameter
- Vimeo embeds link to transcript
- Audio content has text alternative

**Expected Result:**
"Your videos are accessible to everyone. Captions help deaf visitors, noisy environments, and search engines."

**If Issue Found:**
"Your site has 12 videos without captions. This excludes deaf and hard-of-hearing visitors (like having a conversation in a crowded room—some people can't hear). Here's how to add captions: [KB link]"

**Auto-Fixable:** No (requires human transcription)
**Severity:** Medium (accessibility barrier for 5% of population)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #1 (Helpful Neighbor - help everyone)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-1"]
    },
    {
        "title": "Image Alt Text is Generic or Missing",
        "body": """**Diagnostic:** Check if images have meaningful, descriptive alt text (not just "image" or filename)

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen readers read alt text aloud to blind users. Generic text like "IMG_1234.jpg" or "image" is useless—like showing someone a photo but refusing to describe what's in it.

**User Impact:**
- Blind users get no information about images
- Context and meaning is lost
- SEO suffers (search engines can't understand images)
- Images with text become inaccessible

**What We Check:**
- Images have `alt` attribute
- Alt text is descriptive (>10 characters, not filename pattern)
- Alt text isn't generic ("image", "photo", "picture", "graphic")
- Decorative images use `alt=""`

**Expected Result:**
"Your images are accessible. Descriptive alt text helps blind visitors and search engines understand your content."

**If Issue Found:**
"Found 47 images with generic alt text like 'image' or 'IMG_1234'. This is like showing someone a book with all the words blurred—screen reader users can't understand your content. Here's how to write helpful alt text: [KB link]"

**Auto-Fixable:** No (requires human judgment)
**Severity:** High (blocks blind users from understanding content)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },
    {
        "title": "Focus Indicators Not Visible",
        "body": """**Diagnostic:** Check if keyboard focus indicators are visible (not removed with `outline: none`)

**Why This Matters (Pillar 🌍: Accessibility First):**
Keyboard-only users (motor disabilities, power users) navigate by tabbing. No visible focus = they're lost—like walking through a dark room with no flashlight.

**User Impact:**
- Keyboard users can't see where they are
- Tab navigation becomes guessing game
- Forms are nearly impossible to complete
- ~16% of users have motor disabilities

**What We Check:**
- CSS doesn't have `outline: none` without custom focus styles
- `:focus` pseudo-class has visible styling
- Focus indicator has 3:1 contrast ratio
- Focus isn't hidden by `opacity: 0` or similar

**Expected Result:**
"Your site is keyboard-accessible. Clear focus indicators help keyboard users navigate confidently."

**If Issue Found:**
"Your site hides keyboard focus indicators (outline: none). This is like asking someone to find a needle in a haystack blindfolded—keyboard users can't tell where they are. Here's how to add visible focus styles: [KB link]"

**Auto-Fixable:** Partial (can detect issue, but design fix requires human)
**Severity:** High (blocks 16% of users from navigating)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #8 (Inspire Confidence - users know where they are)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-8", "critical"]
    },
    {
        "title": "Form Error Messages Not Associated with Fields",
        "body": """**Diagnostic:** Check if error messages use `aria-describedby` to link to form fields

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen readers can't guess which error goes with which field. Without proper association, it's like getting a list of problems but no clue where they are.

**User Impact:**
- Blind users hear errors but don't know which field failed
- Fixing forms becomes trial and error
- Frustration leads to abandonment
- Form completion rates drop

**What We Check:**
- Error messages have IDs
- Form fields use `aria-describedby` pointing to error ID
- Error messages use `role="alert"` for live updates
- Visual and programmatic association match

**Expected Result:**
"Your form errors are accessible. Screen readers announce which field has a problem and why."

**If Issue Found:**
"Your contact form shows errors, but screen readers can't tell which field is wrong. This is like getting a test back with a grade but no marks showing what you missed. Here's how to link errors to fields: [KB link]"

**Auto-Fixable:** No (requires template modification)
**Severity:** Medium (creates accessibility barrier in forms)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "forms"]
    },
    {
        "title": "Modals Don't Trap Focus",
        "body": """**Diagnostic:** Check if modal dialogs properly trap keyboard focus

**Why This Matters (Pillar 🌍: Accessibility First):**
When a modal opens, keyboard users need focus trapped inside. Without it, they can tab to hidden content behind the modal—like trying to have a conversation while someone keeps changing the subject.

**User Impact:**
- Keyboard users tab into hidden background content
- Can't reliably close or interact with modal
- Confusing and frustrating experience
- May get stuck unable to dismiss modal

**What We Check:**
- Modal sets focus to first interactive element on open
- Tab key cycles within modal only
- Escape key closes modal
- Focus returns to trigger element on close
- Background content has `aria-hidden="true"`

**Expected Result:**
"Your modals are keyboard-accessible. Focus stays inside until modal is closed."

**If Issue Found:**
"Your popup modals let keyboard focus escape to background content. This is like trying to fill out a form while someone keeps sliding it away—users can't complete the task. Here's how to trap focus: [KB link]"

**Auto-Fixable:** No (requires JavaScript modification)
**Severity:** Medium (creates confusion for keyboard users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #8 (Inspire Confidence - predictable behavior)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-8"]
    },
    {
        "title": "Landmark Regions Not Labeled",
        "body": """**Diagnostic:** Check if ARIA landmark regions (`<nav>`, `<main>`, `<aside>`) have descriptive labels

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen reader users navigate by landmarks (like chapter headings). Unlabeled landmarks are like a book where every chapter is called "Chapter"—useless for navigation.

**User Impact:**
- Screen reader users can't distinguish between navigation regions
- "Navigation 1", "Navigation 2" gives no context
- Slower page navigation
- Harder to find specific content

**What We Check:**
- Multiple `<nav>` elements have `aria-label` or `aria-labelledby`
- `<aside>` regions are labeled (e.g., "Related Posts", "Author Bio")
- Landmark labels are unique and descriptive
- Redundant landmarks are avoided

**Expected Result:**
"Your page landmarks are clearly labeled. Screen reader users can jump directly to 'Main Menu', 'Footer Links', 'Sidebar'."

**If Issue Found:**
"Your site has 3 navigation regions all called 'navigation'. This is like a building with three doors all labeled 'Door'—visitors can't tell which is which. Here's how to label landmarks: [KB link]"

**Auto-Fixable:** No (requires semantic labeling decisions)
**Severity:** Low (convenience issue, not blocker)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Text Contrast Below WCAG AA Standard",
        "body": """**Diagnostic:** Check if text has minimum 4.5:1 contrast ratio (3:1 for large text)

**Why This Matters (Pillar 🌍: Accessibility First):**
Low contrast text is hard to read for everyone, but especially users with vision impairments, older users, and anyone in bright sunlight. It's like trying to read gray text on a light gray background.

**User Impact:**
- 8% of men have color blindness
- Older users have reduced contrast sensitivity
- Mobile users in sunlight can't read screen
- Headaches and eye strain

**What We Check:**
- Body text: 4.5:1 contrast minimum
- Headings 18pt+: 3:1 contrast minimum
- UI elements: 3:1 contrast minimum
- Links have sufficient contrast and non-color indicator

**Expected Result:**
"Your text is readable for everyone. Strong contrast works in all lighting conditions and for all vision abilities."

**If Issue Found:**
"Your site uses #999 gray text on white background (2.8:1 contrast). This is like reading a faded photocopy—hard for everyone, impossible for users with vision impairments. Here's how to improve contrast: [KB link]"

**Auto-Fixable:** Partial (can suggest colors, but design choice remains)
**Severity:** High (affects readability for large population)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },
    {
        "title": "Skip to Content Link Missing or Broken",
        "body": """**Diagnostic:** Check if first focusable element is a "Skip to Content" link that works

**Why This Matters (Pillar 🌍: Accessibility First):**
Keyboard users have to tab through 50+ navigation links on every page load. A skip link lets them jump straight to content—like having an express elevator instead of taking stairs.

**User Impact:**
- Keyboard users waste time tabbing through navigation
- Screen reader users hear entire menu on every page
- Frustrating repeat navigation
- Slower site exploration

**What We Check:**
- Page has skip link as first focusable element
- Link is visible on focus (can be visually hidden until tabbed)
- Link points to valid `id` (usually `#main-content`)
- Target element exists and receives focus

**Expected Result:**
"Your site has a working skip link. Keyboard users can jump directly to content without tabbing through navigation."

**If Issue Found:**
"Your site has no skip to content link. Keyboard users must tab through 67 menu items on every page—like forcing someone to read the table of contents before each chapter. Here's how to add one: [KB link]"

**Auto-Fixable:** Yes (can inject skip link)
**Severity:** Medium (convenience issue for keyboard users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "auto-fixable"]
    },
    {
        "title": "Buttons and Links Not Distinguishable",
        "body": """**Diagnostic:** Check if buttons (`<button>`) and links (`<a>`) are semantically correct and visually distinct

**Why This Matters (Pillar 🌍: Accessibility First):**
Buttons do things (submit, open, toggle). Links go places. Using them wrong confuses screen readers and keyboard users—like labeling a door "Push" when you need to pull.

**User Impact:**
- Screen reader users expect different behaviors
- Keyboard shortcuts don't work as expected
- Form submission becomes confusing
- Cognitive load increases

**What We Check:**
- Actions use `<button>` (submit form, open modal, toggle)
- Navigation uses `<a href>` (go to URL)
- No `<a>` with `href="#"` and JavaScript click handlers
- Buttons look like buttons, links look like links

**Expected Result:**
"Your interactive elements are semantically correct. Buttons perform actions, links navigate to pages."

**If Issue Found:**
"Your 'Submit' uses a link instead of a button. This confuses screen readers (they expect links to go somewhere, not submit forms). It's like putting a doorknob on a light switch. Here's how to use correct elements: [KB link]"

**Auto-Fixable:** No (requires template changes)
**Severity:** Medium (creates confusion for assistive tech)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Audio Plays Automatically Without Control",
        "body": """**Diagnostic:** Check if audio/video plays automatically without user control

**Why This Matters (Pillar 🌍: Accessibility First + Commandment #1: Helpful Neighbor):**
Auto-playing audio is jarring and disruptive. For screen reader users, it competes with their assistive tech—like trying to have two conversations at once.

**User Impact:**
- Screen reader output drowned out by audio
- Startles users in quiet environments
- Drains mobile data
- WCAG 2.1 violation

**What We Check:**
- `<video>` and `<audio>` don't have `autoplay` attribute
- Or if autoplay, must be muted by default
- Embedded content doesn't auto-play
- Pause/stop control is within 3 seconds

**Expected Result:**
"Your site respects users' audio preferences. No auto-playing sound that disrupts screen readers or quiet environments."

**If Issue Found:**
"Your homepage video auto-plays with sound. This disrupts screen readers (imagine trying to listen to GPS while someone blasts music) and startles visitors. Here's how to disable auto-play: [KB link]"

**Auto-Fixable:** Yes (can remove autoplay attribute)
**Severity:** Medium (accessibility + user experience issue)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #1 (Helpful Neighbor - respectful)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-1", "auto-fixable"]
    },
    {
        "title": "Form Fields Missing Autocomplete Attributes",
        "body": """**Diagnostic:** Check if common form fields have `autocomplete` attributes for autofill

**Why This Matters (Pillar 🌍: Accessibility First + Commandment #8: Inspire Confidence):**
Autocomplete helps everyone but especially users with cognitive disabilities, motor impairments, and mobile users. It's like having pre-filled forms at the doctor's office instead of writing everything by hand each visit.

**User Impact:**
- Users with dyslexia struggle to type accurately
- Motor impairments make typing difficult
- Mobile typing is slow and error-prone
- Repeat customers waste time re-entering info

**What We Check:**
- Name fields use `autocomplete="name"`
- Email uses `autocomplete="email"`
- Address fields use appropriate values
- Credit card fields use `cc-number`, `cc-exp`, etc.
- Phone uses `autocomplete="tel"`

**Expected Result:**
"Your forms support autofill. Browsers can help users complete forms faster and more accurately."

**If Issue Found:**
"Your checkout form doesn't use autocomplete attributes. Users must manually type name, address, and credit card on every purchase—like filling out the same form at every store visit instead of using a loyalty card. Here's how to add autocomplete: [KB link]"

**Auto-Fixable:** Partial (can suggest attributes, but field mapping needs verification)
**Severity:** Low (convenience feature, not blocker)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #8 (Inspire Confidence - reduce errors)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-8", "forms"]
    },
    {
        "title": "Time Limits Without User Control",
        "body": """**Diagnostic:** Check if timed content (sessions, quizzes, checkouts) allows users to extend time

**Why This Matters (Pillar 🌍: Accessibility First):**
Users with cognitive disabilities, motor impairments, or screen readers need more time. Hard time limits without warning are discriminatory—like giving someone a test but taking it away mid-answer.

**User Impact:**
- Screen reader users need extra time to navigate
- Motor impairments slow down interaction
- Cognitive disabilities require processing time
- Session timeouts lose shopping carts

**What We Check:**
- Timed content warns before expiring (at least 20 seconds)
- Users can extend time or disable timeout
- Sessions don't expire during active use
- No time limits on essential tasks

**Expected Result:**
"Your time limits are flexible. Users get warnings and can extend time as needed."

**If Issue Found:**
"Your checkout times out after 10 minutes with no warning. Users lose their cart without chance to extend time—like a parking meter that boots your car without warning. Here's how to add timeout warnings: [KB link]"

**Auto-Fixable:** No (requires logic changes)
**Severity:** Medium (creates barriers for disabled users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Tables Missing Header Associations",
        "body": """**Diagnostic:** Check if data tables use `<th>`, `scope`, or `headers` for screen readers

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen readers announce cell contents but need headers for context. Without proper markup, it's like reading a spreadsheet where column names were deleted—numbers without meaning.

**User Impact:**
- Screen reader users hear data without context
- Can't understand table relationships
- Pricing tables become gibberish
- Comparison tables are useless

**What We Check:**
- Tables use `<th>` for headers
- Headers have `scope="col"` or `scope="row"`
- Complex tables use `headers` and `id` association
- Layout tables use `role="presentation"` (or better: CSS)

**Expected Result:**
"Your data tables are accessible. Screen readers announce headers with each cell so users understand relationships."

**If Issue Found:**
"Your pricing table uses all `<td>` elements—no headers. Screen readers just announce numbers without context: '19.99, 29.99, 49.99' (what are these prices for?). Here's how to add table headers: [KB link]"

**Auto-Fixable:** Partial (can suggest headers, but semantic meaning needs human verification)
**Severity:** Medium (makes tables unusable for screen reader users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Icon Buttons Missing Accessible Names",
        "body": """**Diagnostic:** Check if icon-only buttons have `aria-label` or accessible text

**Why This Matters (Pillar 🌍: Accessibility First):**
Icons aren't universal. A magnifying glass might mean "search" or "zoom". Screen readers need text labels—like a button labeled just "→" instead of "Next Page".

**User Impact:**
- Screen readers announce "button" with no purpose
- Icon-only navigation is gibberish
- Social media buttons are mystery links
- Users can't predict button behavior

**What We Check:**
- Icon buttons have `aria-label` with descriptive text
- Or use visually-hidden `<span>` with text
- Or have `title` attribute (last resort)
- SVG icons use `aria-hidden="true"` (icon is decorative)

**Expected Result:**
"Your icon buttons are labeled. Screen readers announce 'Search', 'Share on Twitter', 'Close Menu' instead of just 'button'."

**If Issue Found:**
"Your site has 23 icon-only buttons with no labels. Screen readers just say 'button' (imagine 23 doors with no signs—which one do you open?). Here's how to label icon buttons: [KB link]"

**Auto-Fixable:** No (requires human understanding of icon meaning)
**Severity:** High (makes navigation impossible for screen reader users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },
    {
        "title": "Error Messages Not Announced to Screen Readers",
        "body": """**Diagnostic:** Check if dynamic error messages use `role="alert"` or `aria-live` regions

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen readers don't automatically detect visual changes. Errors that appear without announcement go unnoticed—like someone yelling at you with the sound off.

**User Impact:**
- Blind users submit forms repeatedly, not knowing they failed
- No feedback when validation fails
- Can't fix errors they don't know exist
- Form abandonment increases

**What We Check:**
- Error containers use `role="alert"` for immediate announcement
- Or use `aria-live="assertive"` for critical errors
- Success messages use `aria-live="polite"`
- Status updates are programmatically announced

**Expected Result:**
"Your error messages are announced to screen readers immediately when they appear."

**If Issue Found:**
"Your contact form shows red error text, but screen readers never hear it. Users keep submitting and don't know why it's failing—like knocking on a door and not hearing 'go away'. Here's how to announce errors: [KB link]"

**Auto-Fixable:** Partial (can add aria-live, but needs testing)
**Severity:** High (prevents blind users from fixing errors)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #8 (Inspire Confidence - clear feedback)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-8", "critical"]
    },
    {
        "title": "Color Used as Only Visual Indicator",
        "body": """**Diagnostic:** Check if important information relies solely on color (no icon, text, or pattern)

**Why This Matters (Pillar 🌍: Accessibility First):**
8% of men are colorblind. Red/green distinctions are invisible to them—like a traffic light with no position difference, just color.

**User Impact:**
- Colorblind users miss critical information
- Required form fields not distinguishable
- Error states not visible
- Charts and graphs meaningless

**What We Check:**
- Required fields use asterisk + color
- Error states use icon + color
- Success/warning uses text + color
- Charts use patterns + color
- Links distinguished by underline, not just color

**Expected Result:**
"Your site doesn't rely on color alone. Icons, text, patterns, and underlines provide information to colorblind users."

**If Issue Found:**
"Your form marks required fields with red color only (no asterisk or text). Colorblind users can't tell which fields are required—like giving directions using only left/right with no landmarks. Here's how to add non-color indicators: [KB link]"

**Auto-Fixable:** No (requires design decisions)
**Severity:** Medium (affects 8% of male users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Page Language Not Declared",
        "body": """**Diagnostic:** Check if HTML has `lang` attribute declaring primary language

**Why This Matters (Pillar 🌍: Accessibility First + Pillar 🌐: Culturally Respectful):**
Screen readers need to know what language to speak. Without `lang`, they guess wrong—like someone reading Spanish text with English pronunciation rules (gibberish).

**User Impact:**
- Screen readers use wrong pronunciation
- Braille displays show incorrect output
- Translation tools can't detect language
- Search engines confused about content

**What We Check:**
- `<html>` tag has `lang` attribute
- Value is valid BCP 47 language tag
- Multi-language content uses `lang` on sections
- Changes in language are marked up

**Expected Result:**
"Your pages declare their language. Screen readers pronounce correctly, and translation tools work properly."

**If Issue Found:**
"Your pages don't declare language. Screen readers will mispronounce words, and translators will fail—like trying to read French using English phonics rules. Here's how to add lang attribute: [KB link]"

**Auto-Fixable:** Yes (can detect language and add attribute)
**Severity:** High (makes screen readers speak gibberish)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First + 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "pillar-culturally-respectful", "auto-fixable", "critical"]
    },
    {
        "title": "Headings Not in Logical Order",
        "body": """**Diagnostic:** Check if heading levels follow proper hierarchy (H1 → H2 → H3, no skipping)

**Why This Matters (Pillar 🌍: Accessibility First):**
Screen reader users navigate by headings (like a table of contents). Skipped levels break navigation—like a book outline that goes 1, 2, 5, 2, 7 randomly.

**User Impact:**
- Screen readers can't build proper page outline
- Users miss content sections
- Navigation keyboard shortcuts don't work well
- Document structure unclear

**What We Check:**
- Page has exactly one H1 (usually page title)
- Heading levels increment by 1 (H2 after H1, H3 after H2)
- No skipping from H2 to H4
- Headings reflect content hierarchy

**Expected Result:**
"Your headings follow logical order. Screen reader users can navigate your page structure like a well-organized book."

**If Issue Found:**
"Your page skips from H2 to H5 in the sidebar. This breaks screen reader navigation—like a book chapter outline that goes '1, 1.1, 1.6, 2, 2.13' randomly. Here's how to fix heading order: [KB link]"

**Auto-Fixable:** No (requires structural understanding)
**Severity:** Medium (hinders screen reader navigation)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "Images of Text Used Instead of Real Text",
        "body": """**Diagnostic:** Check if text content is embedded in images instead of using HTML text

**Why This Matters (Pillar 🌍: Accessibility First):**
Text in images can't be resized, translated, or read by screen readers. It's like scanning a document instead of typing it—looks the same but functions worse.

**User Impact:**
- Blind users can't read text in images
- Low vision users can't enlarge text
- Translation tools can't translate
- Text becomes pixelated when zoomed

**What We Check:**
- Headings aren't images (use HTML `<h1>`, `<h2>`)
- Body text isn't in images (use CSS for styling)
- Buttons aren't image-based (use styled `<button>`)
- Logos acceptable (but need descriptive alt text)

**Expected Result:**
"Your text is real HTML text. Users can resize, translate, and screen readers can read it."

**If Issue Found:**
"Your pricing page uses image files for price text. Screen readers can't read prices, users can't enlarge text, and translators fail—like a menu written in permanent marker instead of printed text. Here's how to use real text: [KB link]"

**Auto-Fixable:** No (requires manual conversion)
**Severity:** High (makes text inaccessible)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },
    {
        "title": "Carousel Without Pause Control",
        "body": """**Diagnostic:** Check if auto-rotating carousels have visible pause/stop button

**Why This Matters (Pillar 🌍: Accessibility First):**
Auto-rotating content causes problems: users with motor disabilities can't click moving targets, dyslexic users can't finish reading, and screen reader users get interrupted mid-announcement.

**User Impact:**
- Users can't click calls-to-action before slide changes
- Reading interrupted mid-sentence
- Screen readers cut off mid-announcement
- Causes motion sickness for some users

**What We Check:**
- Carousels have visible pause button
- Pause persists (not temporary)
- Keyboard accessible pause control
- Or carousel stops on hover/focus
- Or rotation speed > 5 seconds per slide

**Expected Result:**
"Your carousels have pause controls. Users can stop auto-rotation and read at their own pace."

**If Issue Found:**
"Your homepage carousel auto-rotates every 3 seconds with no pause button. Users can't finish reading or click buttons before content disappears—like trying to read a book while someone keeps turning pages. Here's how to add pause control: [KB link]"

**Auto-Fixable:** Partial (can slow rotation or add pause, but needs design integration)
**Severity:** Medium (affects users with disabilities + general UX)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility"]
    },
    {
        "title": "PDF Files Not Accessible",
        "body": """**Diagnostic:** Check if downloadable PDFs are tagged for accessibility

**Why This Matters (Pillar 🌍: Accessibility First):**
Untagged PDFs are unusable for screen readers—like giving someone a book but every page is an image instead of text.

**User Impact:**
- Screen readers can't read PDF content
- No logical reading order
- Forms in PDFs are inaccessible
- Alt text missing from images

**What We Check:**
- PDFs have document structure tags
- Reading order is defined
- Form fields are labeled
- Images have alt text
- Or HTML alternative is provided

**Expected Result:**
"Your PDFs are accessible, or you provide accessible HTML alternatives."

**If Issue Found:**
"Your site offers 12 PDF downloads, but none are tagged for accessibility. Screen readers see gibberish or can't read them at all—like publishing a book where every page is a photo of text instead of real text. Here's how to create accessible PDFs: [KB link]"

**Auto-Fixable:** No (requires PDF remediation or HTML alternative)
**Severity:** High (locks blind users out of content)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },
    {
        "title": "Touch Targets Below 44×44 Pixels",
        "body": """**Diagnostic:** Check if interactive elements meet minimum 44×44px touch target size

**Why This Matters (Pillar 🌍: Accessibility First):**
Small tap targets are hard to hit for everyone, but especially users with motor impairments, tremors, or large fingers. It's like trying to thread a needle while wearing winter gloves.

**User Impact:**
- Users tap wrong element accidentally
- Frustration with small mobile buttons
- People with Parkinson's can't tap accurately
- Increases errors and rage-quits

**What We Check:**
- Buttons, links, form controls are at least 44×44px
- Or have 44×44px hit area (with padding)
- Adequate spacing between tap targets
- Mobile-specific issues more critical

**Expected Result:**
"Your interactive elements are easy to tap. Large touch targets work for users with motor impairments and mobile users with large fingers."

**If Issue Found:**
"Your mobile menu has 28×28px close button. Users with motor impairments or tremors struggle to tap it accurately—like trying to hit a tiny bullseye. Here's how to enlarge touch targets: [KB link]"

**Auto-Fixable:** Partial (can suggest CSS, but layout changes needed)
**Severity:** Medium (affects mobile users + motor disabilities)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "mobile"]
    },
    {
        "title": "CAPTCHA Without Accessible Alternative",
        "body": """**Diagnostic:** Check if CAPTCHA has audio alternative or accessible fallback

**Why This Matters (Pillar 🌍: Accessibility First):**
Visual-only CAPTCHAs lock out blind users completely. It's like having a security guard who only lets people in if they pass a vision test.

**User Impact:**
- Blind users literally cannot submit forms
- Audio CAPTCHA often too distorted
- No way to contact site or make purchase
- Discriminatory and possibly illegal (ADA)

**What We Check:**
- CAPTCHA has audio alternative
- Or uses accessible methods (reCAPTCHA v3, hCaptcha)
- Or offers human verification option
- Audio quality is usable

**Expected Result:**
"Your CAPTCHAs have accessible alternatives. Blind users can prove they're human without seeing images."

**If Issue Found:**
"Your contact form uses visual-only CAPTCHA with no audio option. Blind users literally cannot contact you—like putting a lock on your door that only works if you can see colors. Here's how to make CAPTCHAs accessible: [KB link]"

**Auto-Fixable:** No (requires CAPTCHA replacement)
**Severity:** Critical (complete blocker for blind users)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical", "blocker"]
    },
    {
        "title": "Visible Focus Moves During Keyboard Navigation",
        "body": """**Diagnostic:** Check if dynamic content changes don't unexpectedly move keyboard focus

**Why This Matters (Pillar 🌍: Accessibility First + Commandment #8: Inspire Confidence):**
Focus jumping randomly disorients keyboard users. It's like reading a book where someone keeps flipping to random pages—you lose your place and context.

**User Impact:**
- Keyboard users lose navigation position
- Tab order becomes unpredictable
- Confusion and frustration
- Have to re-navigate from top

**What We Check:**
- AJAX updates don't steal focus
- DOM changes preserve focus position
- Sort/filter doesn't reset focus to top
- Tab order remains logical after updates

**Expected Result:**
"Your dynamic content respects keyboard focus. Users maintain their position when content updates."

**If Issue Found:**
"Your search results update and reset focus to top of page. Keyboard users lose their position with every filter—like reading a list where someone keeps sending you back to the beginning. Here's how to preserve focus: [KB link]"

**Auto-Fixable:** No (requires JavaScript modification)
**Severity:** Medium (creates disorientation)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First
**Commandment:** #8 (Inspire Confidence - predictable)""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "commandment-8"]
    },
    {
        "title": "Redundant ARIA Labels on Native Elements",
        "body": """**Diagnostic:** Check if native HTML elements have unnecessary ARIA that conflicts

**Why This Matters (Pillar 🌍: Accessibility First):**
ARIA should enhance, not replace native semantics. Redundant ARIA creates noise for screen readers—like having two narrators talking over each other.

**User Impact:**
- Screen readers announce twice: "button, button"
- Confusing duplicated information
- Increased cognitive load
- Sometimes overwrites native behavior incorrectly

**What We Check:**
- `<button>` doesn't have redundant `role="button"`
- Native elements use implicit roles
- ARIA only used when native HTML insufficient
- No conflicting ARIA attributes

**Expected Result:**
"Your ARIA is strategic. Used only where native HTML can't provide semantics."

**If Issue Found:**
"Your buttons use redundant `role='button'` on `<button>` elements. Screen readers announce 'button button'—like saying 'this is a button, button' twice. Here's when to use ARIA: [KB link]"

**Auto-Fixable:** Yes (can remove redundant ARIA)
**Severity:** Low (creates noise, not blocker)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "auto-fixable"]
    },
    {
        "title": "Infinite Scroll Without Keyboard Bypass",
        "body": """**Diagnostic:** Check if infinite scroll has option to load all or jump to footer

**Why This Matters (Pillar 🌍: Accessibility First):**
Infinite scroll traps keyboard users. They can never reach footer links—like a hallway where new rooms appear faster than you can walk.

**User Impact:**
- Keyboard users can't reach footer content
- Screen readers stuck loading new content
- Footer links (privacy policy, contact) unreachable
- Tab navigation becomes impossible

**What We Check:**
- Infinite scroll has "Load All" button
- Or has "Skip to Footer" link
- Or footer moves below infinite content
- Or keyboard bypass mechanism exists

**Expected Result:**
"Your infinite scroll doesn't trap keyboard users. Footer and bottom content is reachable."

**If Issue Found:**
"Your blog uses infinite scroll with no bypass. Keyboard users can never reach your footer contact links—new posts load faster than they can tab. Like trying to reach the end of a treadmill. Here's how to add bypass: [KB link]"

**Auto-Fixable:** No (requires design decision)
**Severity:** High (makes footer content unreachable)
**Family:** accessibility
**CANON Pillar:** 🌍 Accessibility First""",
        "labels": ["diagnostic", "accessibility", "pillar-accessibility", "critical"]
    },

    # ═══════════════════════════════════════════════════════════════
    # PILLAR #2: 🎓 LEARNING INCLUSIVE (16 diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "Help Text Explains What, Not Why",
        "body": """**Diagnostic:** Check if form help text explains *why* fields matter, not just what to enter

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
"Enter your email" tells users what to do. "We'll send order updates here" tells them why it matters. Context helps everyone but especially users with cognitive disabilities.

**User Impact:**
- Users don't understand purpose of fields
- Higher abandonment when purpose unclear
- Anxiety about how data will be used
- Missing context for decision-making

**What We Check:**
- Form fields have help text explaining purpose
- Password requirements explain why (security)
- Email fields explain what emails they'll receive
- Help text goes beyond restating label

**Expected Result:**
"Your forms explain why information is needed. Users understand purpose and make informed decisions."

**If Issue Found:**
"Your checkout asks for phone number with help text 'Enter your phone'. Users don't know why—spam calls? Delivery updates? This creates anxiety. Help text should say 'We'll text you when order ships'. Here's how to write helpful help text: [KB link]"

**Auto-Fixable:** No (requires human understanding of purpose)
**Severity:** Low (UX improvement, not blocker)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - explain clearly)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1", "forms"]
    },
    {
        "title": "Complex Features Missing Tooltips",
        "body": """**Diagnostic:** Check if advanced settings or unfamiliar terms have tooltip explanations

**Why This Matters (Pillar 🎓: Learning Inclusive):**
Power features intimidate non-technical users. Tooltips provide just-in-time learning—like having a coach who whispers advice when you need it.

**User Impact:**
- Users afraid to explore advanced features
- Settings left at defaults because confusing
- Support requests for things explained in tooltips
- Missed opportunities to use valuable features

**What We Check:**
- Technical terms have `?` icon with tooltip
- Advanced settings have explanatory tooltips
- Tooltips appear on hover and keyboard focus
- Mobile has alternative (tap or expand)

**Expected Result:**
"Your advanced features have tooltips. Users can learn in context without leaving the page."

**If Issue Found:**
"Your settings page uses term 'CDN' with no explanation. Non-technical users don't know what this is or why they'd enable it. A tooltip could explain: 'Content Delivery Network—speeds up your site by serving images from servers near visitors'. Here's how to add tooltips: [KB link]"

**Auto-Fixable:** No (requires writing explanations)
**Severity:** Low (convenience feature)
**Family:** design
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive"]
    },
    {
        "title": "Onboarding Flow Missing for New Users",
        "body": """**Diagnostic:** Check if first-time users get guided tour or setup wizard

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
Overwhelming users with everything at once causes abandonment. Onboarding provides scaffolded learning—like a GPS that gives directions one turn at a time, not the entire route at once.

**User Impact:**
- New users feel overwhelmed
- Don't discover key features
- Higher abandonment rate
- Slower time-to-value

**What We Check:**
- First visit triggers welcome tour or setup wizard
- Tour highlights key features progressively
- Users can skip or dismiss
- Tour persists across sessions if not completed

**Expected Result:**
"Your site onboards new users. Guided tour introduces features progressively so users aren't overwhelmed."

**If Issue Found:**
"New users land on your dashboard with 50+ options and no guidance. This is overwhelming—like handing someone car keys without explaining how to drive. A setup wizard could guide them through essential first steps. Here's how to add onboarding: [KB link]"

**Auto-Fixable:** No (requires design and implementation)
**Severity:** Low (improves user success but not critical)
**Family:** workflows
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - guide users)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1"]
    },
    {
        "title": "Documentation Links Generic Not Contextual",
        "body": """**Diagnostic:** Check if help links go to specific relevant pages, not generic help center

**Why This Matters (Pillar 🎓: Learning Inclusive):**
"Click here for help" that lands on generic FAQ is useless. Contextual links directly to relevant article reduce cognitive load—like a dictionary that opens to the exact word instead of page 1.

**User Impact:**
- Users waste time searching help docs
- Frustration with generic help pages
- Give up before finding answer
- Increased support tickets

**What We Check:**
- Help links point to specific relevant articles
- Not just homepage of help center
- Links labeled with what they explain
- Context-aware help suggestions

**Expected Result:**
"Your help links are contextual. Users jump directly to answers for their current task."

**If Issue Found:**
"Your payment settings page has 'Help' link that goes to generic support homepage. Users must search for payment help themselves. Link should go directly to payment setup article. Here's how to add contextual help links: [KB link]"

**Auto-Fixable:** No (requires mapping help content)
**Severity:** Low (convenience improvement)
**Family:** content
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #5 (Drive to KB - contextual links)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-5"]
    },
    {
        "title": "Error Messages Don't Suggest Solutions",
        "body": """**Diagnostic:** Check if error messages include how to fix, not just what went wrong

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
"Invalid email" doesn't help users fix it. "Email must include @ symbol" teaches them. Educational errors reduce repeat mistakes—like a spell checker that explains why, not just marks wrong.

**User Impact:**
- Users don't know how to fix errors
- Trial-and-error frustration
- Form abandonment increases
- Repeat errors

**What We Check:**
- Errors explain what's wrong AND how to fix
- Positive framing ("Try this" not "You failed")
- Examples of correct format
- Link to help if complex

**Expected Result:**
"Your error messages teach users. They explain what's wrong and how to fix it."

**If Issue Found:**
"Your contact form says 'Invalid input' with no explanation of what's invalid or how to fix it. This is frustrating—like a teacher marking answers wrong without explaining why. Errors should guide users: 'Phone must be 10 digits (no dashes)'. Here's how to write helpful errors: [KB link]"

**Auto-Fixable:** No (requires writing helpful messages)
**Severity:** Medium (affects user success with forms)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - teach, don't scold)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1", "forms"]
    },
    {
        "title": "Progress Indicators Missing on Multi-Step Forms",
        "body": """**Diagnostic:** Check if multi-step processes show progress (Step 2 of 5)

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #8: Inspire Confidence):**
Users need to know how far they've come and how much remains. Progress indicators reduce anxiety—like a GPS showing "5 minutes remaining" instead of just turning randomly.

**User Impact:**
- Users don't know how long process takes
- Anxiety about length increases abandonment
- ADHD users need progress markers
- Especially critical for checkout flows

**What We Check:**
- Multi-step forms show "Step X of Y"
- Visual progress bar or breadcrumb
- Each step clearly labeled
- Users can see upcoming steps

**Expected Result:**
"Your multi-step processes show progress. Users know exactly where they are and what's remaining."

**If Issue Found:**
"Your checkout has 6 steps but no progress indicator. Users don't know if they're halfway done or just starting—creates anxiety and abandonment. A progress bar showing 'Step 2 of 6: Shipping' builds confidence. Here's how to add progress indicators: [KB link]"

**Auto-Fixable:** Partial (can detect multi-step, but UI addition needed)
**Severity:** Medium (affects conversion, especially ADHD users)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #8 (Inspire Confidence - show progress)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-8", "forms"]
    },
    {
        "title": "Technical Jargon Used Without Explanation",
        "body": """**Diagnostic:** Check if technical terms appear without definitions or links

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
Unexplained jargon excludes non-technical users. It's like a doctor using medical terms without explaining—makes you feel dumb and helpless.

**User Impact:**
- Users feel intimidated and stupid
- Can't make informed decisions
- Avoid features they don't understand
- Disproportionately affects non-native speakers

**What We Check:**
- Technical terms have tooltip, glossary link, or inline definition
- Acronyms spelled out on first use
- Industry jargon explained in human terms
- Reading level appropriate (8th grade target)

**Expected Result:**
"Your content explains technical terms. Non-technical users can understand without feeling excluded."

**If Issue Found:**
"Your settings page uses terms like 'REST API', 'JWT tokens', 'DNS propagation' without explanation. This intimidates non-developers—like a mechanic using technical jargon without explaining. Add tooltips or glossary links. Here's how to explain technical terms: [KB link]"

**Auto-Fixable:** Partial (can detect jargon, but explanations need writing)
**Severity:** Medium (creates barriers for non-technical users)
**Family:** content
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - explain clearly)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1"]
    },
    {
        "title": "Example Use Cases Not Shown",
        "body": """**Diagnostic:** Check if features show real-world examples, not just abstract descriptions

**Why This Matters (Pillar 🎓: Learning Inclusive):**
Abstract descriptions don't help visual learners. Examples make features concrete—like a cookbook showing photos of finished dish, not just ingredients list.

**User Impact:**
- Users don't understand feature value
- Can't visualize how it applies to them
- Slower adoption of useful features
- More support questions

**What We Check:**
- Feature descriptions include "Example:" section
- Real-world use cases provided
- Screenshots or demos available
- "Why you'd use this" explained

**Expected Result:**
"Your features include examples. Users see how features apply to real situations."

**If Issue Found:**
"Your automation feature says 'Create workflows with conditional logic' but no example of what that means. Users can't visualize use. Add example: 'Example: Automatically email customers 3 days before subscription renews'. Here's how to write examples: [KB link]"

**Auto-Fixable:** No (requires writing examples)
**Severity:** Low (helps understanding but not critical)
**Family:** content
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive"]
    },
    {
        "title": "No Visual Feedback During Long Operations",
        "body": """**Diagnostic:** Check if long-running operations show progress or status

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #8: Inspire Confidence):**
Users need to know something's happening, not frozen. Progress feedback reduces anxiety—like elevators that show floor numbers vs ones that just go silent.

**User Impact:**
- Users think page froze, refresh and restart
- Anxiety during wait times
- ADHD users especially need reassurance
- Increased perceived wait time

**What We Check:**
- Form submissions show spinner or "Processing..."
- File uploads show progress bar
- Long operations show status updates
- No operations appear frozen

**Expected Result:**
"Your operations provide feedback. Users know something's happening and roughly how long it'll take."

**If Issue Found:**
"Your image upload goes silent for 30+ seconds—users think it froze and close the window. A progress bar showing 'Uploading... 45%' would prevent this. Here's how to add progress feedback: [KB link]"

**Auto-Fixable:** No (requires backend progress tracking)
**Severity:** Medium (causes confusion and abandoned operations)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #8 (Inspire Confidence - show status)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-8", "forms"]
    },
    {
        "title": "Settings Don't Explain Impact of Changes",
        "body": """**Diagnostic:** Check if settings show what happens when toggled (before-after preview)

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #8: Inspire Confidence):**
Users fear changing settings they don't understand. Showing impact builds confidence—like paint samples showing color on wall before committing.

**User Impact:**
- Users afraid to experiment
- Settings left at defaults
- Support tickets asking "what does this do?"
- Missed optimization opportunities

**What We Check:**
- Settings show before/after or describe effect
- "This will..." explanations present
- Preview available where applicable
- Undo/revert option mentioned

**Expected Result:**
"Your settings explain their impact. Users understand what changes before making them."

**If Issue Found:**
"Your 'Enable CDN' toggle has no explanation of what happens. Users don't know if this breaks their site or speeds it up. Add: 'This will serve images from servers near visitors, speeding up page load by 30-50%'. Here's how to explain settings: [KB link]"

**Auto-Fixable:** No (requires writing impact descriptions)
**Severity:** Low (prevents feature adoption)
**Family:** settings
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #8 (Inspire Confidence - explain changes)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-8"]
    },
    {
        "title": "Confirmation Messages Too Generic",
        "body": """**Diagnostic:** Check if success messages explain what happened, not just "Success"

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #8: Inspire Confidence):**
"Saved successfully" doesn't teach. "Settings saved. Visitors now see new homepage layout" reinforces cause-effect—like getting a receipt that itemizes what you bought.

**User Impact:**
- Users unsure what actually happened
- Can't verify desired outcome occurred
- Learning opportunity missed
- Less confidence in system

**What We Check:**
- Success messages are specific
- Explain what changed/happened
- Mention visible effects
- Not just "Success" or generic message

**Expected Result:**
"Your confirmations teach users. They explain specifically what happened and what to expect."

**If Issue Found:**
"Your form shows 'Saved' with no detail. Users don't know what was saved or what happens next. Better: 'Email preferences saved. You'll receive weekly newsletters starting next Monday.' Here's how to write specific confirmations: [KB link]"

**Auto-Fixable:** No (requires context-specific messages)
**Severity:** Low (improves understanding)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #8 (Inspire Confidence - confirm clearly)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-8"]
    },
    {
        "title": "Empty States Don't Guide Next Action",
        "body": """**Diagnostic:** Check if empty states show helpful message and call-to-action

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
Empty dashboards leave users confused. Good empty states guide them to first action—like a recipe that says "First, gather ingredients" instead of blank page.

**User Impact:**
- New users don't know what to do first
- Blank dashboards feel broken
- Higher abandonment on first session
- Missed onboarding opportunity

**What We Check:**
- Empty lists show helpful message + action button
- Messages explain what will appear here
- Clear call-to-action to add first item
- Not just blank or "No items found"

**Expected Result:**
"Your empty states are helpful. They guide users to take their first action."

**If Issue Found:**
"Your dashboard shows blank 'Products' section with just '0 products'. Better empty state: 'You haven't added products yet. Add your first product to start selling.' + [Add Product] button. Here's how to design empty states: [KB link]"

**Auto-Fixable:** No (requires messaging and CTA design)
**Severity:** Medium (affects new user experience)
**Family:** design
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - guide users)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1"]
    },
    {
        "title": "No In-Context Help for Complex Workflows",
        "body": """**Diagnostic:** Check if complex multi-step processes have inline help or wizard

**Why This Matters (Pillar 🎓: Learning Inclusive):**
Complex workflows need scaffolding. In-context help prevents overwhelm—like IKEA instructions with step-by-step diagrams vs. dumping all pieces on floor.

**User Impact:**
- Users abandon complex processes
- Call support instead of self-serving
- Fear of doing it wrong
- Slower time-to-value

**What We Check:**
- Setup wizards for complex tasks
- Inline tips at each step
- "Need help?" expandable sections
- Video walkthroughs embedded where relevant

**Expected Result:**
"Your complex workflows have in-context help. Users get guidance exactly when needed."

**If Issue Found:**
"Your import process has 8 steps with technical requirements but no inline help. Users must open separate help docs, losing context. Add collapsible 'How to do this step' sections at each stage. Here's how to add in-context help: [KB link]"

**Auto-Fixable:** No (requires help content creation)
**Severity:** Medium (affects completion of complex tasks)
**Family:** workflows
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive"]
    },
    {
        "title": "Buttons Don't Explain What Happens Next",
        "body": """**Diagnostic:** Check if button labels describe outcome, not just action

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #8: Inspire Confidence):**
"Submit" doesn't tell users what happens. "Send Message" or "Create Account" sets expectations—like elevator buttons that say "Lobby" instead of just "1".

**User Impact:**
- Users unsure of button outcome
- Hesitation before clicking
- Accidentally do wrong action
- Increases anxiety

**What We Check:**
- Button labels are specific and outcome-focused
- Not generic: "Submit", "OK", "Continue"
- Describe what will happen: "Send Email", "Delete Post"
- Action verbs + noun

**Expected Result:**
"Your buttons clearly describe outcomes. Users know what happens when they click."

**If Issue Found:**
"Your payment form has generic 'Submit' button. Users unsure if this charges card, saves info, or continues to next step. Change to 'Complete Purchase ($49.99)' so users know exactly what clicking does. Here's how to write clear button labels: [KB link]"

**Auto-Fixable:** No (requires understanding of context)
**Severity:** Low (improves clarity)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #8 (Inspire Confidence - clear actions)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-8"]
    },
    {
        "title": "Advanced Features Not Progressive Disclosed",
        "body": """**Diagnostic:** Check if advanced options are hidden until needed (not overwhelming beginners)

**Why This Matters (Pillar 🎓: Learning Inclusive):**
Showing all options at once overwhelms beginners. Progressive disclosure reveals complexity gradually—like a camera with "Auto" mode that hides manual controls until you're ready.

**User Impact:**
- Beginners feel overwhelmed
- Power users frustrated by simplified interface
- Abandonment due to complexity
- Cognitive overload

**What We Check:**
- "Advanced Options" sections collapsed by default
- "Show More" reveals additional settings
- Simple/Advanced mode toggle
- Default to simple, expand on demand

**Expected Result:**
"Your interfaces progressively disclose complexity. Beginners see simple options, advanced users can expand."

**If Issue Found:**
"Your settings page shows all 47 options at once. Beginners see walls of intimidating checkboxes. Use collapsible 'Advanced Settings' section to hide power-user options until needed. Here's how to implement progressive disclosure: [KB link]"

**Auto-Fixable:** No (requires UI restructuring)
**Severity:** Low (affects user experience)
**Family:** settings
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive"]
    },
    {
        "title": "Field-Level Validation Doesn't Teach",
        "body": """**Diagnostic:** Check if real-time validation explains rules, not just shows red/green

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #1: Helpful Neighbor):**
Red X doesn't teach. "Password must have 8+ characters, 1 number, 1 symbol" does—like a coach who explains what's wrong, not just says "try again".

**User Impact:**
- Users guess at requirements
- Password creation frustration
- Trial-and-error increases abandonment
- Learning opportunity lost

**What We Check:**
- Real-time validation shows requirements
- Explains what's needed, not just "invalid"
- Shows progress: "Need 1 more number"
- Positive reinforcement when correct

**Expected Result:**
"Your forms teach users in real-time. Validation explains requirements as users type."

**If Issue Found:**
"Your password field just shows red X when weak—users don't know what's required. Show checklist: '✗ At least 8 characters ✓ 1 uppercase ✗ 1 number ✗ 1 symbol' that updates as they type. Here's how to implement teaching validation: [KB link]"

**Auto-Fixable:** No (requires validation UI changes)
**Severity:** Medium (affects form completion)
**Family:** forms
**CANON Pillar:** 🎓 Learning Inclusive
**Commandment:** #1 (Helpful Neighbor - teach actively)""",
        "labels": ["diagnostic", "learning", "pillar-learning-inclusive", "commandment-1", "forms"]
    },

    # ═══════════════════════════════════════════════════════════════
    # PILLAR #3: 🌐 CULTURALLY RESPECTFUL (14 diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "CSS Uses Fixed Directional Properties Not Logical",
        "body": """**Diagnostic:** Check if CSS uses `margin-left`/`margin-right` instead of logical properties

**Why This Matters (Pillar 🌐: Culturally Respectful):**
422 million people speak RTL languages (Arabic, Hebrew, Urdu). Using `margin-left` breaks layouts in RTL—like designing a book where page numbers are always on the left, even when reading back-to-front.

**User Impact:**
- RTL users see broken, backwards layouts
- Navigation menus appear reversed
- Text alignment wrong
- Excluding 5% of world's population

**What We Check:**
- CSS uses `margin-inline-start` not `margin-left`
- Uses `padding-inline-end` not `padding-right`
- Uses `inset-inline-start` not `left`
- Float uses logical `inline-start`

**Expected Result:**
"Your CSS uses logical properties. Layouts work correctly in both LTR and RTL languages."

**If Issue Found:**
"Your CSS uses 247 instances of `margin-left` instead of `margin-inline-start`. This breaks layouts for Arabic, Hebrew, and Urdu readers—422 million people see backwards design. Here's how to use logical properties: [KB link]"

**Auto-Fixable:** Partial (can detect, suggest fixes, but testing needed)
**Severity:** High (excludes 5% of global population)
**Family:** design
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "critical"]
    },
    {
        "title": "Dates Hardcoded in US Format",
        "body": """**Diagnostic:** Check if dates use hardcoded format (MM/DD/YYYY) instead of localized functions

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Different regions use different date formats. US uses MM/DD/YYYY, most of world uses DD/MM/YYYY, Asia uses YYYY/MM/DD. Hardcoding creates confusion—like writing checks in foreign currency.

**User Impact:**
- Non-US users misread dates (is 06/03 June 3rd or March 6th?)
- Confusion on time-sensitive actions
- International customers miss deadlines
- Unprofessional appearance

**What We Check:**
- Uses WordPress `date_i18n()` function
- Not hardcoded `date('m/d/Y')`
- Respects user's locale settings
- Calendar widgets adapt to region

**Expected Result:**
"Your dates use localized formatting. Users see dates in their familiar format."

**If Issue Found:**
"Your site hardcodes dates as '03/15/2026' (US format). Europeans read this as March 15th when you meant 15th March, or vice versa. Use WordPress `date_i18n( get_option( 'date_format' ) )` to respect user locale. Here's how to localize dates: [KB link]"

**Auto-Fixable:** Partial (can detect hardcoded dates, but conversion needs testing)
**Severity:** Medium (creates confusion for international users)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Numbers Hardcoded Without Localization",
        "body": """**Diagnostic:** Check if numbers use `number_format_i18n()` instead of hardcoded separators

**Why This Matters (Pillar 🌐: Culturally Respectful):**
US uses 1,000.50 (comma thousands, dot decimal). Europe uses 1.000,50 (dot thousands, comma decimal). Hardcoding confuses international users—like using feet/inches in metric countries.

**User Impact:**
- European users read prices wrong
- 1.000 looks like 1,000 or 1.0?
- Confusion on financial transactions
- Appears unprofessional

**What We Check:**
- Uses WordPress `number_format_i18n()` function
- Currency formatted with region rules
- Large numbers respect locale separators
- Decimal separators adapt to region

**Expected Result:**
"Your numbers use localized formatting. Users see familiar number formats."

**If Issue Found:**
"Your prices show $1,234.56 hardcoded. European users expect €1.234,56 format. Use `number_format_i18n()` and respect locale. Here's how to localize numbers: [KB link]"

**Auto-Fixable:** Partial (can detect, but currency conversion complex)
**Severity:** Medium (creates confusion on financial info)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Content Uses English Idioms",
        "body": """**Diagnostic:** Check if content uses culture-specific idioms or expressions

**Why This Matters (Pillar 🌐: Culturally Respectful + Commandment #1: Helpful Neighbor):**
Idioms don't translate. "Break a leg" to wish luck makes no sense globally. "Piece of cake" for easy task is confusing. Clear, literal language helps everyone—especially non-native speakers and translation tools.

**User Impact:**
- Non-native speakers confused
- Translation tools produce gibberish
- Appears culturally insensitive
- Excludes international audience

**What We Check:**
- Content avoids idioms: "piece of cake", "break a leg"
- No sports metaphors: "hit it out of the park", "touchdown"
- No cultural references: "Thanksgiving shopping"
- Uses clear, literal language

**Expected Result:**
"Your content uses clear, universal language. Non-native speakers and translators understand easily."

**If Issue Found:**
"Your onboarding says 'This feature is a piece of cake!' Non-native speakers don't understand. Use literal: 'This feature is very easy to use.' Here's list of idioms to avoid: [KB link]"

**Auto-Fixable:** Partial (can detect common idioms, but context-dependent)
**Severity:** Low (improves comprehension)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful
**Commandment:** #1 (Helpful Neighbor - clear language)""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "commandment-1"]
    },
    {
        "title": "Forms Don't Support Non-Latin Characters in Names",
        "body": """**Diagnostic:** Check if name fields accept Unicode characters (Chinese, Arabic, Cyrillic, etc.)

**Why This Matters (Pillar 🌐: Culturally Respectful):**
2+ billion people have names with non-Latin characters. Rejecting them is discriminatory—like a form that says "Your name isn't valid because it's not English".

**User Impact:**
- Chinese, Arabic, Russian, etc. users can't enter real names
- Forced to use inaccurate romanization
- Feels discriminatory and unwelcoming
- Can't complete transactions

**What We Check:**
- Name fields accept Unicode (not just [a-zA-Z])
- Accepts diacritics: José, François, Müller
- Accepts non-Latin: 张伟, محمد, Владимир
- No arbitrary character limits

**Expected Result:**
"Your forms respect all names. Users can enter names in their native scripts."

**If Issue Found:**
"Your name field rejects non-Latin characters. Users named 张伟 or محمد can't complete your form—you're literally telling them their names aren't valid. Remove regex restrictions. Here's how to support Unicode names: [KB link]"

**Auto-Fixable:** Yes (can remove restrictive validation)
**Severity:** High (discriminatory, blocks transactions)
**Family:** forms
**CANON Pillar:** 🌐 Culturally Respectful
**Commandment:** #1 (Helpful Neighbor - inclusive)""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "commandment-1", "critical", "auto-fixable"]
    },
    {
        "title": "Currency Symbols Hardcoded as Dollar Signs",
        "body": """**Diagnostic:** Check if prices use hardcoded $ instead of user's currency

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Showing dollars to European users is confusing and unprofessional. It's like a store pricing everything in foreign currency without conversion.

**User Impact:**
- International users confused by pricing
- Must mentally convert currency
- Appears US-centric and unprofessional
- Reduces international sales

**What We Check:**
- Currency symbols respect user locale
- Or clearly state currency (USD, EUR, GBP)
- Prices convert to local currency
- Or offer currency selector

**Expected Result:**
"Your prices adapt to user's region, or clearly state which currency is used."

**If Issue Found:**
"Your ecommerce shows '$49' to all users globally. Europeans see dollars and don't know if this is converted or USD-only. Use currency converter or clearly label 'US $49'. Here's how to handle currency: [KB link]"

**Auto-Fixable:** No (requires business decision on currency handling)
**Severity:** Medium (confuses international customers)
**Family:** ecommerce
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "ecommerce"]
    },
    {
        "title": "Time Zones Assumed or Not Displayed",
        "body": """**Diagnostic:** Check if times include timezone or respect user's timezone

**Why This Matters (Pillar 🌐: Culturally Respectful):**
"Event starts at 2 PM" is useless for global audience. Is that user's 2 PM or server's 2 PM? It's like scheduling a meeting without saying which country's time.

**User Impact:**
- Users miss events due to timezone confusion
- Support tickets asking "what timezone?"
- International customers get wrong expectations
- Delivery windows unclear

**What We Check:**
- Times include timezone: "2 PM EST" or "14:00 UTC"
- Or convert to user's timezone
- Server time clearly labeled
- JavaScript shows local time

**Expected Result:**
"Your times are timezone-aware. Users know exactly when events happen in their local time."

**If Issue Found:**
"Your webinar registration shows 'Starts at 3:00 PM' with no timezone. Global registrants don't know if this is their time or yours. Add timezone or convert to user's local time. Here's how to display timezones: [KB link]"

**Auto-Fixable:** Partial (can add timezone labels, conversion needs more work)
**Severity:** Medium (causes confusion and missed events)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Address Forms Assume US Format",
        "body": """**Diagnostic:** Check if address forms support international address formats

**Why This Matters (Pillar 🌐: Culturally Respectful):**
US addresses use State + ZIP code. UK uses postcode. Japan uses prefecture. Forcing US format blocks international customers—like requiring everyone to write addresses in English format.

**User Impact:**
- International users can't enter addresses correctly
- Forced workarounds with wrong data
- Can't complete purchases
- Form abandonment

**What We Check:**
- Address fields adapt to selected country
- State/Province optional (not all countries have)
- Postal code format flexible (UK: SW1A 1AA, Canada: K1A 0B1)
- City/State order varies by country

**Expected Result:**
"Your address forms adapt to selected country. International customers can enter addresses naturally."

**If Issue Found:**
"Your checkout requires US-style 'State' and '5-digit ZIP'. UK customers don't have states or US ZIP codes—they can't checkout. Make fields adapt to country selection. Here's how to support international addresses: [KB link]"

**Auto-Fixable:** No (requires form restructuring)
**Severity:** High (blocks international sales)
**Family:** forms
**CANON Pillar:** 🌐 Culturally Respectful
**Commandment:** #1 (Helpful Neighbor - accommodate everyone)""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "commandment-1", "critical", "forms"]
    },
    {
        "title": "Phone Number Validation Too Restrictive",
        "body": """**Diagnostic:** Check if phone fields accept international formats

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Phone formats vary globally: US (555-1234), UK (+44 20 7946 0958), France (01 23 45 67 89). Requiring US format blocks international users—like a form that only accepts US passports.

**User Impact:**
- International users can't enter phones correctly
- Forced to fake US format
- Can't complete registrations
- No way to receive calls/SMS

**What We Check:**
- Phone validation accepts + prefix
- Allows varying length (not hardcoded 10 digits)
- Accepts international formats
- Or uses libphonenumber library

**Expected Result:**
"Your phone fields accept international formats. Global users can enter numbers naturally."

**If Issue Found:**
"Your phone field requires exactly 10 digits. International numbers vary: UK (11 digits), France (9 digits), Germany (10-11). Users outside US can't register. Allow flexible phone formats. Here's how to validate international phones: [KB link]"

**Auto-Fixable:** Yes (can remove restrictive regex)
**Severity:** High (blocks international registrations)
**Family:** forms
**CANON Pillar:** 🌐 Culturally Respectful
**Commandment:** #1 (Helpful Neighbor - inclusive)""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "commandment-1", "critical", "auto-fixable", "forms"]
    },
    {
        "title": "Images Contain Culture-Specific Assumptions",
        "body": """**Diagnostic:** Check if imagery is globally appropriate (not US/Western-centric)

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Using only Western imagery (white professionals, US landmarks, Western holidays) feels exclusionary to global audience. It's like a restaurant with only one country's food—makes others feel unwelcome.

**User Impact:**
- Non-Western users feel excluded
- Appears culturally insensitive
- Reduces trust with international audience
- Can be offensive (e.g., hand gestures differ)

**What We Check:**
- Stock photos show diverse people
- Not all Western/white faces
- Avoid US-centric landmarks (Statue of Liberty, etc.)
- Holiday references inclusive
- Hand gestures appropriate globally

**Expected Result:**
"Your imagery is globally inclusive. Diverse people and universal symbols used."

**If Issue Found:**
"Your team page shows only white faces in Western business attire. Global visitors may feel this service isn't for them. Include diverse, international representation. Here's guide to inclusive imagery: [KB link]"

**Auto-Fixable:** No (requires manual image selection)
**Severity:** Low (cultural sensitivity issue)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Default Language Not Based on Browser",
        "body": """**Diagnostic:** Check if site detects browser language and offers translation

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Visitors have language preferences set in browser. Ignoring them forces English on everyone—like a tour guide who only speaks English and expects everyone to understand.

**User Impact:**
- Non-English speakers see content they can't read
- Must manually find language selector (if exists)
- Frustration and bounce
- Reduced international engagement

**What We Check:**
- Site detects `Accept-Language` header
- Offers appropriate language version
- Or shows language selector prominently
- Content available in multiple languages

**Expected Result:**
"Your site respects browser language preferences. Non-English speakers see content in their language."

**If Issue Found:**
"Your site shows English to all visitors, regardless of browser language setting. Spanish-speaking visitors with `Accept-Language: es` should see Spanish version automatically. Here's how to implement language detection: [KB link]"

**Auto-Fixable:** No (requires translation infrastructure)
**Severity:** Medium (affects international usability)
**Family:** internationalization
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Religious Assumptions in Content or Scheduling",
        "body": """**Diagnostic:** Check if content or features assume specific religion/calendar

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Assuming Christian calendar (Sundays off, Christmas references) excludes billions. It's like scheduling meetings only on Friday afternoons—fine for some, impossible for others.

**User Impact:**
- Non-Christian users feel excluded
- "Weekend" means different days in some countries
- Holiday references confusing/offensive
- Business hours assumptions wrong

**What We Check:**
- No assumed "weekend" days (Saturday-Sunday)
- Holiday references inclusive or avoided
- Calendar supports multiple systems
- No religious assumptions in examples

**Expected Result:**
"Your content and features don't assume specific religion or calendar system."

**If Issue Found:**
"Your scheduling tool labels Saturday-Sunday as 'Weekend' and defaults to unavailable. In Middle East, weekend is Friday-Saturday. In Israel, Friday-Saturday. Use 'Days Off' with flexible selection instead of assumed weekends. Here's how to handle international calendars: [KB link]"

**Auto-Fixable:** No (requires design changes)
**Severity:** Low (cultural sensitivity)
**Family:** workflows
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Unit Measurements Hardcoded as Imperial",
        "body": """**Diagnostic:** Check if measurements use imperial (feet, pounds) without metric option

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Only 3 countries use imperial measurements (US, Liberia, Myanmar). 95% of world uses metric. Showing only pounds/feet excludes billions—like publishing recipes in outdated measurements.

**User Impact:**
- Non-US users must convert mentally
- Shipping weight confusing
- Product dimensions unclear
- Appears US-centric

**What We Check:**
- Measurements include metric equivalent
- Or offer unit toggle (imperial/metric)
- Respect user's locale for units
- Weight, distance, temperature inclusive

**Expected Result:**
"Your measurements accommodate global users. Metric options available or conversions shown."

**If Issue Found:**
"Your product specs show only '5 feet, 20 pounds'. 95% of world users must convert to meters/kilograms. Show both: '5 ft (1.5 m), 20 lbs (9 kg)' or add unit toggle. Here's how to support metric: [KB link]"

**Auto-Fixable:** Partial (can add conversions, but formatting needs design)
**Severity:** Low (convenience issue)
**Family:** content
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },
    {
        "title": "Translation Files Not Loading or Incomplete",
        "body": """**Diagnostic:** Check if translations actually load and cover all strings

**Why This Matters (Pillar 🌐: Culturally Respectful):**
Having translation files but not loading them is worse than no translations—like a restaurant with menu in 5 languages but only English menus available.

**User Impact:**
- Visitors see English mixed with their language
- Broken user experience
- Appears half-finished
- Users question quality

**What We Check:**
- Translation files exist for declared languages
- `load_textdomain()` called properly
- All user-facing strings in translation files
- No hardcoded English text
- Pluralization handled correctly

**Expected Result:**
"Your translations load completely. Users see fully localized experience."

**If Issue Found:**
"Your site declares Spanish support, but 40% of strings show English. Translation file incomplete or not loading. All user-facing text must be translatable with proper text domain. Here's how to fix translations: [KB link]"

**Auto-Fixable:** Partial (can detect missing strings, but translation needs humans)
**Severity:** Medium (breaks localized experience)
**Family:** internationalization
**CANON Pillar:** 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful"]
    },

    # ═══════════════════════════════════════════════════════════════
    # PILLAR #4: 🛡️ SAFE BY DEFAULT (20 diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "Destructive Actions Missing Confirmation Dialogs",
        "body": """**Diagnostic:** Check if delete/clear/reset actions require confirmation before executing

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #8: Inspire Confidence):**
One accidental click shouldn't destroy data. Confirmation dialogs prevent disasters—like a car that asks "Are you sure?" before you reverse into a wall.

**User Impact:**
- Accidental deletions cause data loss
- No way to undo after confirmation skip
- Users afraid to explore interface
- Support tickets for lost data

**What We Check:**
- Delete buttons require confirmation modal
- Bulk actions show "Are you sure?" dialog
- Confirmation explains what will be deleted
- Irreversible actions have strong warnings

**Expected Result:**
"Your destructive actions require confirmation. Users can't accidentally delete data with one click."

**If Issue Found:**
"Your 'Delete All' button has no confirmation—one accidental click wipes data. Add confirmation: 'Delete all 47 posts? This cannot be undone.' with Cancel + Confirm buttons. Here's how to add confirmations: [KB link]"

**Auto-Fixable:** No (requires UI modal implementation)
**Severity:** Critical (data loss prevention)
**Family:** design
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #8 (Inspire Confidence - prevent accidents)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-8", "critical"]
    },
    {
        "title": "No Undo or Rollback for Major Changes",
        "body": """**Diagnostic:** Check if major configuration changes have undo/rollback capability

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #8: Inspire Confidence):**
Users need confidence to experiment. Undo removes fear of mistakes—like Word's undo button vs typewriter where errors are permanent.

**User Impact:**
- Users afraid to try new settings
- One mistake requires support ticket
- Can't explore without risk
- Reduces feature adoption

**What We Check:**
- Settings changes have "Restore Previous" option
- Database operations are logged for rollback
- File modifications create backups
- "Undo" or "Revert" clearly visible

**Expected Result:**
"Your major changes can be undone. Users can experiment confidently knowing they can restore previous state."

**If Issue Found:**
"Your theme customizer applies changes permanently with no undo. Users who break their design must restore backup manually. Add 'Restore Previous Theme Settings' button. Here's how to implement undo: [KB link]"

**Auto-Fixable:** No (requires state management)
**Severity:** High (prevents confident exploration)
**Family:** workflows
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #8 (Inspire Confidence - safe experimentation)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-8", "critical"]
    },
    {
        "title": "Changes Applied Without Preview First",
        "body": """**Diagnostic:** Check if visual/layout changes show preview before applying

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #8: Inspire Confidence):**
Users need to see impact before committing. Preview prevents regret—like trying on clothes before buying, not buying then seeing if they fit.

**User Impact:**
- Changes look wrong only after applied
- Must undo and try again (trial and error)
- Reduces confidence in system
- Time wasted on wrong choices

**What We Check:**
- Theme/layout changes show preview
- Email templates have preview mode
- CSS/design changes viewable before save
- "Preview" button prominently placed

**Expected Result:**
"Your changes show previews. Users see impact before committing."

**If Issue Found:**
"Your color scheme settings apply immediately with no preview. Users must save, view site, decide it looks bad, come back to change. Add live preview panel. Here's how to implement preview: [KB link]"

**Auto-Fixable:** No (requires preview infrastructure)
**Severity:** Medium (improves decision-making)
**Family:** design
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #8 (Inspire Confidence - informed choices)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-8"]
    },
    {
        "title": "Password Fields Without Strength Meter",
        "body": """**Diagnostic:** Check if password creation shows strength feedback

**Why This Matters (Pillar 🛡️: Safe by Default):**
Users don't inherently know what makes passwords strong. Strength meters educate in real-time—like a personal trainer showing form, not just saying "lift correctly".

**User Impact:**
- Users create weak passwords unknowingly
- Account compromise risk
- No guidance on improvement
- Security breach vulnerability

**What We Check:**
- Password fields show strength meter
- Meter updates as user types
- Explains what makes it weak/strong
- Suggests improvements

**Expected Result:**
"Your password fields educate users. Strength meters guide toward secure passwords."

**If Issue Found:**
"Your registration password field has no strength indicator. Users create 'password123' with no warning. Add strength meter showing: 'Weak - Add numbers and symbols'. Here's how to implement password strength: [KB link]"

**Auto-Fixable:** No (requires UI implementation)
**Severity:** High (security risk)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical"]
    },
    {
        "title": "File Upload Missing Type Validation",
        "body": """**Diagnostic:** Check if file uploads validate file types server-side

**Why This Matters (Pillar 🛡️: Safe by Default):**
Accepting any file type is a security risk. Validation prevents malicious uploads—like a nightclub checking IDs, not trusting everyone to be 21+.

**User Impact:**
- Malware can be uploaded
- System compromise risk
- Legal liability if hosting illegal content
- Server resource abuse

**What We Check:**
- Upload validates MIME type server-side
- Extension whitelist (not blacklist)
- File size limits enforced
- Magic number validation (not just extension)

**Expected Result:**
"Your file uploads validate types securely. Only allowed file types can be uploaded."

**If Issue Found:**
"Your image upload accepts any file without server-side validation. Users could upload malware disguised as images (just rename virus.exe to virus.jpg). Implement server-side MIME type validation. Here's how to secure file uploads: [KB link]"

**Auto-Fixable:** No (requires server-side code)
**Severity:** Critical (security vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "Database Queries Not Using Prepared Statements",
        "body": """**Diagnostic:** Check if custom queries use `$wpdb->prepare()` instead of string concatenation

**Why This Matters (Pillar 🛡️: Safe by Default):**
SQL injection is the #1 web vulnerability. Prepared statements prevent it—like a guard checking IDs instead of letting anyone through who says "I'm authorized".

**User Impact:**
- SQL injection vulnerability
- Data theft or destruction risk
- Site compromise
- Customer data breach liability

**What We Check:**
- Custom queries use `$wpdb->prepare()`
- No direct string concatenation in SQL
- Placeholders (%s, %d, %f) used correctly
- User input never directly in queries

**Expected Result:**
"Your database queries use prepared statements. SQL injection attacks are prevented."

**If Issue Found:**
"Your plugin queries database with: `$wpdb->query( 'SELECT * FROM table WHERE id = ' . $_GET['id'] )`. This is a critical SQL injection vulnerability. Use `$wpdb->prepare()` with placeholders. Here's how to secure database queries: [KB link]"

**Auto-Fixable:** No (requires code refactoring)
**Severity:** Critical (SQL injection vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "User Input Not Sanitized Before Saving",
        "body": """**Diagnostic:** Check if form inputs are sanitized with WordPress functions before storing

**Why This Matters (Pillar 🛡️: Safe by Default):**
Raw user input can contain malicious code. Sanitization prevents XSS attacks—like washing vegetables before cooking, not trusting they're clean.

**User Impact:**
- XSS attacks possible
- Script injection risk
- User data compromised
- Site defacement

**What We Check:**
- Text inputs use `sanitize_text_field()`
- Emails use `sanitize_email()`
- URLs use `esc_url_raw()`
- Textareas use `sanitize_textarea_field()`
- Keys use `sanitize_key()`

**Expected Result:**
"Your form inputs are sanitized before storage. XSS attacks are prevented."

**If Issue Found:**
"Your contact form saves `$_POST['message']` directly to database without sanitization. Attackers can inject scripts: `<script>stealCookies()</script>`. Use `sanitize_textarea_field( wp_unslash( $_POST['message'] ) )`. Here's how to sanitize input: [KB link]"

**Auto-Fixable:** No (requires code modification)
**Severity:** Critical (XSS vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "Output Not Escaped for Display Context",
        "body": """**Diagnostic:** Check if user-generated content is escaped with context-appropriate functions

**Why This Matters (Pillar 🛡️: Safe by Default):**
Unescaped output allows stored XSS attacks. Context-appropriate escaping prevents them—like different locks for different doors (front door vs bathroom).

**User Impact:**
- Stored XSS vulnerability
- User accounts compromised
- Malicious script execution
- Data theft

**What We Check:**
- HTML content uses `esc_html()`
- Attributes use `esc_attr()`
- URLs use `esc_url()`
- JavaScript strings use `esc_js()`
- HTML blocks use `wp_kses_post()`

**Expected Result:**
"Your output is properly escaped. XSS attacks through displayed content are prevented."

**If Issue Found:**
"Your comment display shows user comments with: `echo $comment;` (no escaping). Attackers can inject: `<img src=x onerror=alert('XSS')>`. Use `echo esc_html( $comment );`. Here's how to escape output: [KB link]"

**Auto-Fixable:** No (requires context analysis)
**Severity:** Critical (XSS vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "Forms Missing CSRF Protection (Nonces)",
        "body": """**Diagnostic:** Check if state-changing forms have WordPress nonce verification

**Why This Matters (Pillar 🛡️: Safe by Default):**
Forms without CSRF protection allow attack sites to perform actions as logged-in users—like someone forging your signature on checks while you're logged into your bank.

**User Impact:**
- CSRF attack vulnerability
- Unauthorized actions performed
- Data manipulation by attackers
- Account compromise

**What We Check:**
- Forms include nonce field
- Form processing verifies nonce
- AJAX uses `check_ajax_referer()`
- Admin forms use `wp_verify_nonce()`

**Expected Result:**
"Your forms have CSRF protection. Attackers can't trick browsers into performing actions."

**If Issue Found:**
"Your settings form has no nonce verification. Attacker could create malicious page that submits form when visited while you're logged in. Add `wp_create_nonce()` and `wp_verify_nonce()`. Here's how to implement CSRF protection: [KB link]"

**Auto-Fixable:** No (requires form modification)
**Severity:** Critical (CSRF vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "Admin Actions Missing Capability Checks",
        "body": """**Diagnostic:** Check if privileged actions verify user has required capability

**Why This Matters (Pillar 🛡️: Safe by Default):**
Actions without capability checks allow privilege escalation—like having admin buttons visible but not checking if user should actually use them.

**User Impact:**
- Privilege escalation vulnerability
- Lower-role users perform admin actions
- Data modification by unauthorized users
- Security breach

**What We Check:**
- Admin actions check `current_user_can()`
- Capabilities match action (manage_options, edit_posts, etc.)
- AJAX handlers verify capabilities
- Both nonce AND capability checked

**Expected Result:**
"Your admin actions verify user capabilities. Privilege escalation is prevented."

**If Issue Found:**
"Your delete function checks nonce but not capabilities. Subscriber-role users can delete posts. Add: `if ( ! current_user_can( 'delete_posts' ) ) { wp_die(); }`. Here's how to check capabilities: [KB link]"

**Auto-Fixable:** No (requires permission analysis)
**Severity:** Critical (privilege escalation)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical", "vulnerability"]
    },
    {
        "title": "External Links Open in Same Tab (Security Risk)",
        "body": """**Diagnostic:** Check if external links use `target=\"_blank\" rel=\"noopener noreferrer\"`

**Why This Matters (Pillar 🛡️: Safe by Default):**
External links opening in same tab without noopener allows tabnabbing attacks—like letting strangers into your house when you step outside.

**User Impact:**
- Tabnabbing vulnerability
- Malicious sites can redirect your page
- Phishing attacks possible
- User confusion

**What We Check:**
- External links have `target="_blank"`
- Must include `rel="noopener noreferrer"`
- Or links open in same tab (user choice)
- No mix of both behaviors

**Expected Result:**
"Your external links are secure. Tabnabbing attacks are prevented with proper rel attributes."

**If Issue Found:**
"Your site has external links with `target='_blank'` but missing `rel='noopener noreferrer'`. External sites can manipulate your tab with `window.opener`. Always include both attributes together. Here's why noopener matters: [KB link]"

**Auto-Fixable:** Yes (can add rel attribute)
**Severity:** Medium (security vulnerability)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "auto-fixable"]
    },
    {
        "title": "Sensitive Data Exposed in HTML or JavaScript",
        "body": """**Diagnostic:** Check if API keys, secrets, or PII appear in client-side code

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #10: Beyond Pure):**
Client-side code is public. Exposing secrets is like writing passwords on sticky notes—everyone can see them.

**User Impact:**
- API keys stolen and abused
- Service bills increase
- Privacy violation (PII exposed)
- Credential compromise

**What We Check:**
- No API keys in JavaScript
- No authentication tokens in HTML comments
- No PII in data attributes
- No database credentials in source

**Expected Result:**
"Your client-side code doesn't expose secrets. Sensitive data stays server-side."

**If Issue Found:**
"Your JavaScript contains: `const apiKey = 'sk_live_abc123'`. This key is public to anyone viewing source. Attackers can use it to access your services. Move keys to server-side. Here's how to secure API keys: [KB link]"

**Auto-Fixable:** No (requires code refactoring)
**Severity:** Critical (credential exposure)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #10 (Beyond Pure - protect privacy)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-10", "security", "critical", "vulnerability"]
    },
    {
        "title": "No Rate Limiting on Forms or API Endpoints",
        "body": """**Diagnostic:** Check if submission endpoints have rate limiting to prevent abuse

**Why This Matters (Pillar 🛡️: Safe by Default):**
Unlimited submissions enable spam, brute force, and DOS attacks—like a bank with no limit on wrong PIN attempts.

**User Impact:**
- Spam flood your inbox
- Brute force password attempts
- Resource exhaustion
- Service degradation for real users

**What We Check:**
- Forms have rate limiting (X submissions per hour)
- Login has attempt limits
- API endpoints have throttling
- IP-based or user-based limits

**Expected Result:**
"Your submission points have rate limiting. Abuse attacks are mitigated."

**If Issue Found:**
"Your contact form has no rate limit. Bots can submit 1000+ times per minute flooding your inbox. Implement rate limiting: max 5 submissions per hour per IP. Here's how to add rate limiting: [KB link]"

**Auto-Fixable:** No (requires backend logic)
**Severity:** High (enables abuse)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical"]
    },
    {
        "title": "Backup System Not Tested for Restore",
        "body": """**Diagnostic:** Check if site has working backup AND has tested successful restore

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #8: Inspire Confidence):**
Untested backups are useless. You only discover they don't work when disaster strikes—like a fire extinguisher you've never checked.

**User Impact:**
- False sense of security
- Data loss despite "backup"
- Cannot recover from disasters
- Business continuity failure

**What We Check:**
- Backup system is active
- Backups run on schedule
- Restore has been tested (not just assumed)
- Restore documentation exists
- Backup verification happens automatically

**Expected Result:**
"Your backups are tested. You've confirmed you can actually restore from them."

**If Issue Found:**
"Your site has automatic backups but no record of testing restore. Backups often fail silently. Schedule restore test: download backup and verify you can recover your site. Here's how to test backups: [KB link]"

**Auto-Fixable:** No (requires manual testing)
**Severity:** High (false security)
**Family:** reliability
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #8 (Inspire Confidence - proven reliability)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-8", "reliability", "critical"]
    },
    {
        "title": "Error Messages Expose System Information",
        "body": """**Diagnostic:** Check if error messages reveal database structure, file paths, or versions

**Why This Matters (Pillar 🛡️: Safe by Default):**
Detailed errors help attackers map your system—like a burglar getting blueprints of your house.

**User Impact:**
- Information disclosure vulnerability
- Easier for attackers to find weaknesses
- Reveals technology stack
- Database schema exposed

**What We Check:**
- Errors don't show SQL queries
- File paths not revealed to users
- Version numbers hidden
- Stack traces not displayed
- Generic errors shown to users

**Expected Result:**
"Your error messages are user-friendly without exposing system details."

**If Issue Found:**
"Your forms show database errors: 'MySQL error in query SELECT * FROM wp_users WHERE...'. This reveals table structure to attackers. Show generic 'Something went wrong, please try again' to users, log details server-side. Here's how to secure errors: [KB link]"

**Auto-Fixable:** No (requires error handling code)
**Severity:** Medium (information disclosure)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security"]
    },
    {
        "title": "Session Timeout Too Long or Nonexistent",
        "body": """**Diagnostic:** Check if user sessions expire after reasonable inactivity period

**Why This Matters (Pillar 🛡️: Safe by Default):**
Indefinite sessions allow hijacking and unauthorized access—like leaving your front door unlocked when you leave for vacation.

**User Impact:**
- Session hijacking vulnerability
- Shared computer security risk
- Unauthorized account access
- Compliance violations (PCI, HIPAA)

**What We Check:**
- Sessions expire after inactivity (15-30 minutes typical)
- Sensitive actions re-verify identity
- "Remember me" has separate long-lived token
- Timeout configurable for high-security

**Expected Result:**
"Your sessions timeout appropriately. Inactive users are logged out for security."

**If Issue Found:**
"Your sessions never expire. Users on public computers remain logged in forever, allowing next person to access account. Set session timeout to 30 minutes of inactivity. Here's how to configure sessions: [KB link]"

**Auto-Fixable:** No (requires session configuration)
**Severity:** High (unauthorized access risk)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical"]
    },
    {
        "title": "Passwords Transmitted or Stored Insecurely",
        "body": """**Diagnostic:** Check if passwords are hashed (not encrypted or plaintext) and HTTPS is enforced

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #10: Beyond Pure):**
Plaintext or encrypted passwords can be stolen. Hashing is one-way—like a shredder vs a safe (you can't un-shred, but you can crack a safe).

**User Impact:**
- Password theft if database breached
- Credential stuffing attacks
- Privacy violation
- Legal liability (PCI, GDPR)

**What We Check:**
- Passwords hashed with bcrypt/Argon2 (not MD5/SHA1)
- Never stored as plaintext
- Never encrypted (should be hashed)
- Login forms use HTTPS
- Password reset uses HTTPS

**Expected Result:**
"Your passwords are properly hashed and transmitted securely. Breach doesn't expose credentials."

**If Issue Found:**
"Your user passwords are stored encrypted (not hashed). If encryption key is compromised, all passwords are exposed. Use `wp_hash_password()` which uses bcrypt. Also, enforce HTTPS on login pages. Here's how to secure passwords: [KB link]"

**Auto-Fixable:** No (requires migration + infrastructure)
**Severity:** Critical (credential theft)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #10 (Beyond Pure - privacy)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-10", "security", "critical", "vulnerability"]
    },
    {
        "title": "Third-Party Scripts Loaded from Unverified Sources",
        "body": """**Diagnostic:** Check if external scripts use Subresource Integrity (SRI) hashes

**Why This Matters (Pillar 🛡️: Safe by Default + Commandment #10: Beyond Pure):**
CDN-loaded scripts can be modified if CDN is compromised. SRI verifies integrity—like checking a wax seal on a letter to ensure it wasn't tampered with.

**User Impact:**
- Supply chain attack vulnerability
- Malicious script injection
- User data theft
- Site compromise

**What We Check:**
- External `<script>` tags have `integrity` attribute
- `crossorigin="anonymous"` is set
- Hash matches expected file
- Scripts from trusted CDNs only

**Expected Result:**
"Your external scripts use SRI. File tampering is detected and blocked."

**If Issue Found:**
"Your site loads jQuery from CDN without SRI hash. If CDN is compromised, malicious code runs on your site. Add: `integrity='sha384-...' crossorigin='anonymous'`. Here's how to implement SRI: [KB link]"

**Auto-Fixable:** Yes (can generate and add SRI hashes)
**Severity:** High (supply chain security)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default
**Commandment:** #10 (Beyond Pure - trust verification)""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "commandment-10", "security", "auto-fixable", "critical"]
    },
    {
        "title": "User Roles Have Excessive Permissions",
        "body": """**Diagnostic:** Check if user roles follow principle of least privilege

**Why This Matters (Pillar 🛡️: Safe by Default):**
Excessive permissions enable compromised accounts to do more damage—like giving every employee the master key instead of just their office.

**User Impact:**
- Privilege escalation risk
- Accidental or intentional damage
- Data breach scope increases
- Compliance violations

**What We Check:**
- Editor role doesn't have install_plugins
- Author role doesn't have delete_others_posts
- Custom roles have minimal needed permissions
- No users with unnecessary admin access

**Expected Result:**
"Your user roles follow least privilege. Users have minimum permissions needed."

**If Issue Found:**
"Your 'Editor' role has 'manage_options' capability (admin-level). Compromised editor account can change any setting, install plugins, delete everything. Remove excessive capabilities. Here's how to audit roles: [KB link]"

**Auto-Fixable:** No (requires permission audit)
**Severity:** High (security risk)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical"]
    },
    {
        "title": "No Security Headers Implemented",
        "body": """**Diagnostic:** Check if site uses security headers (CSP, X-Frame-Options, etc.)

**Why This Matters (Pillar 🛡️: Safe by Default):**
Security headers provide defense-in-depth protection—like multiple locks on a door instead of just one.

**User Impact:**
- Clickjacking vulnerability
- XSS attacks easier
- MIME-sniffing exploits
- Referrer leakage

**What We Check:**
- X-Frame-Options: DENY or SAMEORIGIN
- X-Content-Type-Options: nosniff
- Referrer-Policy: no-referrer-when-downgrade
- Content-Security-Policy (at least basic)
- Permissions-Policy (formerly Feature-Policy)

**Expected Result:**
"Your site uses security headers. Multiple layers of defense against attacks."

**If Issue Found:**
"Your site has no X-Frame-Options header. Site can be embedded in iframe on malicious sites (clickjacking attack). Add security headers via plugin or server config. Here's how to implement security headers: [KB link]"

**Auto-Fixable:** Partial (can suggest headers, but server config needed)
**Severity:** High (defense-in-depth)
**Family:** security
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "safety", "pillar-safe-by-default", "security", "critical"]
    },

    # ═══════════════════════════════════════════════════════════════
    # PILLAR #5: ⚙️ MURPHY'S LAW (15 diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "Long Forms Don't Auto-Save Draft Progress",
        "body": """**Diagnostic:** Check if forms with 5+ fields implement localStorage auto-save

**Why This Matters (Pillar ⚙️: Murphy's Law + Commandment #8: Inspire Confidence):**
Browsers crash. Users accidentally close tabs. Lost form data causes rage—like writing a long email and losing it all when computer freezes.

**User Impact:**
- Lost work causes frustration and abandonment
- Users must re-enter everything
- Trust in site decreases
- Especially painful on mobile

**What We Check:**
- Long forms (5+ fields) auto-save to localStorage
- Auto-save every 5-10 seconds
- On page load, offer to restore unsaved data
- User can dismiss auto-saved data

**Expected Result:**
"Your long forms auto-save progress. Browser crashes don't lose user's work."

**If Issue Found:**
"Your job application form has 15 fields but no auto-save. If browser crashes, users lose 10 minutes of work and abandon application. Implement localStorage auto-save every 5 seconds. Here's how to add auto-save: [KB link]"

**Auto-Fixable:** No (requires JavaScript implementation)
**Severity:** High (causes abandonment)
**Family:** forms
**CANON Pillar:** ⚙️ Murphy's Law
**Commandment:** #8 (Inspire Confidence - protect user work)""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "commandment-8", "critical", "forms"]
    },
    {
        "title": "AJAX Failures Show Technical Errors Not User-Friendly Messages",
        "body": """**Diagnostic:** Check if AJAX errors display helpful messages, not raw error text

**Why This Matters (Pillar ⚙️: Murphy's Law + Commandment #1: Helpful Neighbor):**
Network fails. Servers timeout. Users need to know what happened and what to do—not see "500 Internal Server Error" gibberish.

**User Impact:**
- Technical errors confuse users
- No guidance on what to do next
- Appears broken/unprofessional
- Increases support tickets

**What We Check:**
- AJAX error handlers exist
- User sees friendly message: "Couldn't connect. Try again?"
- Technical error logged, not shown
- Retry button or guidance provided

**Expected Result:**
"Your AJAX failures show helpful messages. Users know what happened and how to proceed."

**If Issue Found:**
"Your form AJAX shows raw error: 'XMLHttpRequest failed: net::ERR_CONNECTION_REFUSED'. Users don't understand this. Show: 'Couldn't save your changes. Please check your internet connection and try again.' Here's how to handle AJAX errors: [KB link]"

**Auto-Fixable:** No (requires error handling code)
**Severity:** Medium (user experience + trust)
**Family:** workflows
**CANON Pillar:** ⚙️ Murphy's Law
**Commandment:** #1 (Helpful Neighbor - clear communication)""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "commandment-1"]
    },
    {
        "title": "Long Operations Have No Progress Indicator",
        "body": """**Diagnostic:** Check if operations >3 seconds show progress bar or spinner

**Why This Matters (Pillar ⚙️: Murphy's Law + Commandment #8: Inspire Confidence):**
Users need to know something's happening, not frozen. Silent operations cause anxiety and premature cancellation—like an elevator that doesn't show floor numbers.

**User Impact:**
- Users think page froze
- Refresh mid-operation, causing corruption
- Anxiety during wait
- ADHD users especially affected

**What We Check:**
- Form submissions show spinner
- File uploads show progress bar with %
- Long operations show "Processing..." status
- Time estimates if possible

**Expected Result:**
"Your long operations show progress. Users know something's happening and roughly how long."

**If Issue Found:**
"Your bulk import goes silent for 45 seconds—users think it froze and close browser mid-import. Show progress: 'Importing... 234 of 500 items (47%)'. Here's how to add progress indicators: [KB link]"

**Auto-Fixable:** No (requires progress tracking)
**Severity:** High (causes operation abandonment)
**Family:** workflows
**CANON Pillar:** ⚙️ Murphy's Law
**Commandment:** #8 (Inspire Confidence - status feedback)""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "commandment-8", "critical"]
    },
    {
        "title": "Failed Upload Has No Retry Mechanism",
        "body": """**Diagnostic:** Check if failed file uploads allow retry without reselecting file

**Why This Matters (Pillar ⚙️: Murphy's Law):**
Network glitches happen. Users shouldn't re-select 10 files because one failed—like making someone reload groceries into cart if one item doesn't scan.

**User Impact:**
- Frustration with file re-selection
- Time wasted
- Large files especially painful
- Mobile users affected more (spotty connections)

**What We Check:**
- Failed uploads show "Retry" button
- Retry uses already-selected file
- Shows which files failed
- Batch uploads continue despite one failure

**Expected Result:**
"Your uploads allow retry without reselection. Network glitches don't force starting over."

**If Issue Found:**
"Your upload fails on network timeout and clears file selection. Users must browse and reselect files. Add retry button that reuses file object. Here's how to implement retry: [KB link]"

**Auto-Fixable:** No (requires upload logic changes)
**Severity:** Medium (user frustration)
**Family:** workflows
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law"]
    },
    {
        "title": "API Calls Don't Have Timeout or Retry Logic",
        "body": """**Diagnostic:** Check if external API calls have timeouts and exponential backoff retry

**Why This Matters (Pillar ⚙️: Murphy's Law):**
External services fail. Hanging forever or giving up immediately both fail users—like knocking on a door once then leaving vs pounding forever.

**User Impact:**
- Requests hang indefinitely
- Pages never load
- Or fail instantly without retry
- Unreliable user experience

**What We Check:**
- API calls have timeout (5-10 seconds)
- Failed calls retry with exponential backoff
- Max retry attempts (3-5)
- Graceful degradation if all retries fail

**Expected Result:**
"Your API calls have timeouts and retry logic. Temporary failures are handled gracefully."

**If Issue Found:**
"Your weather widget calls API with no timeout—if API server is slow, your page hangs forever. Add 5-second timeout and 3 retries with exponential backoff. Here's how to implement resilient API calls: [KB link]"

**Auto-Fixable:** No (requires API call refactoring)
**Severity:** High (causes page hangs)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "No Fallback When External Service Fails",
        "body": """**Diagnostic:** Check if site gracefully degrades when CDN/API/service is unavailable

**Why This Matters (Pillar ⚙️: Murphy's Law):**
External services fail. Site shouldn't break completely—like a car that still drives if radio stops working.

**User Impact:**
- Entire site breaks if one service fails
- Features unavailable unnecessarily
- Cascade failures
- Poor user experience

**What We Check:**
- Stale cache used when API fails
- Local fallback for CDN resources
- Core functionality works without external services
- Degraded mode clearly communicated

**Expected Result:**
"Your site degrades gracefully. External service failures don't break everything."

**If Issue Found:**
"Your homepage fails completely if Google Fonts CDN is down. User sees blank page. Use local font fallback and load Google Fonts asynchronously. Here's how to implement fallbacks: [KB link]"

**Auto-Fixable:** No (requires architecture changes)
**Severity:** High (single point of failure)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "Database Writes Don't Verify Success",
        "body": """**Diagnostic:** Check if database operations verify success and handle failures

**Why This Matters (Pillar ⚙️: Murphy's Law):**
Database writes fail (disk full, connection lost, locked tables). Assuming success causes silent data loss—like mailing a letter without checking if it was delivered.

**User Impact:**
- Silent data loss
- Users think action succeeded
- Confusion and distrust
- Lost work without warning

**What We Check:**
- `$wpdb->insert()` return value checked
- `$wpdb->update()` success verified
- Failures logged and user notified
- Transactions used for multi-step operations

**Expected Result:**
"Your database writes verify success. Failures are caught and handled."

**If Issue Found:**
"Your settings save calls `$wpdb->update()` but doesn't check return value. If database locked or disk full, save fails silently and users think settings saved. Check return value and notify user if failure. Here's how to verify database writes: [KB link]"

**Auto-Fixable:** No (requires error handling code)
**Severity:** High (silent data loss)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "Sessions Lost on Server Restart",
        "body": """**Diagnostic:** Check if user sessions/carts survive server restart

**Why This Matters (Pillar ⚙️: Murphy's Law + Commandment #8: Inspire Confidence):**
Servers restart for updates. Users shouldn't lose cart or be logged out—like a store closing for inventory and making customers start shopping over.

**User Impact:**
- Shopping carts lost
- Users logged out unexpectedly
- Work in progress lost
- Frustration and abandonment

**What We Check:**
- Sessions stored in database (not just memory)
- Or persistent cache (Redis, Memcached)
- Session data survives restart
- Graceful session recovery

**Expected Result:**
"Your sessions survive server restarts. Users aren't unexpectedly logged out or lose carts."

**If Issue Found:**
"Your sessions are memory-only (default PHP sessions). When server restarts for updates, all users logged out and carts cleared. Use database sessions or persistent cache. Here's how to persist sessions: [KB link]"

**Auto-Fixable:** No (requires infrastructure change)
**Severity:** Medium (inconvenient, not data loss)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law
**Commandment:** #8 (Inspire Confidence - reliable)""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "commandment-8"]
    },
    {
        "title": "No Disk Space Checks Before Large Operations",
        "body": """**Diagnostic:** Check if operations that use disk space verify availability first

**Why This Matters (Pillar ⚙️: Murphy's Law):**
Running out of disk mid-operation corrupts data—like a photocopier that runs out of paper halfway and jams.

**User Impact:**
- Corrupted backups
- Failed uploads mid-transfer
- Partial file writes
- System instability

**What We Check:**
- Backup operations check disk space first
- Upload handlers verify space available
- Operations fail cleanly if insufficient space
- User warned before large operations

**Expected Result:**
"Your large operations check disk space first. No mid-operation failures due to full disk."

**If Issue Found:**
"Your backup function starts writing without checking disk space. If disk fills mid-backup, you get corrupted backup files. Check `disk_free_space()` first and abort if insufficient. Here's how to check disk space: [KB link]"

**Auto-Fixable:** No (requires preemptive checks)
**Severity:** High (data corruption risk)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "Concurrent Operations Not Prevented",
        "body": """**Diagnostic:** Check if operations that shouldn't run simultaneously have locking

**Why This Matters (Pillar ⚙️: Murphy's Law):**
Running same operation twice simultaneously causes race conditions—like two people trying to edit same document simultaneously and overwriting each other.

**User Impact:**
- Data corruption
- Duplicate records
- System overload
- Unpredictable behavior

**What We Check:**
- Long operations use transient locks
- Cron jobs check if already running
- Admin actions prevent double-submission
- Database uses transactions where needed

**Expected Result:**
"Your operations prevent concurrent execution. Race conditions are avoided."

**If Issue Found:**
"Your cron backup can trigger multiple times if previous run is slow. Two backups run simultaneously, corrupting files and overloading server. Use transient lock to prevent concurrent runs. Here's how to implement operation locking: [KB link]"

**Auto-Fixable:** No (requires locking mechanism)
**Severity:** High (data corruption risk)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "No Input Validation Maximum Lengths",
        "body": """**Diagnostic:** Check if text inputs have reasonable maximum lengths to prevent abuse

**Why This Matters (Pillar ⚙️: Murphy's Law + Pillar 🛡️: Safe by Default):**
Unbounded inputs enable DOS attacks and database errors—like allowing someone to order infinite items and crashing order system.

**User Impact:**
- Database errors on too-long input
- System slowdown from processing huge inputs
- DOS attack vulnerability
- Memory exhaustion

**What We Check:**
- Text fields have `maxlength` attribute
- Server-side validates length too
- Reasonable limits (255 for names, 5000 for comments)
- Textarea limits appropriate to use case

**Expected Result:**
"Your inputs have maximum lengths. Abuse and errors from oversized input prevented."

**If Issue Found:**
"Your comment form has no length limit. Users can submit 1MB+ text causing database errors or DOS. Add `maxlength` attribute and server-side validation. Here's how to set input limits: [KB link]"

**Auto-Fixable:** Yes (can add maxlength attributes)
**Severity:** Medium (abuse prevention)
**Family:** security
**CANON Pillar:** ⚙️ Murphy's Law + 🛡️ Safe by Default""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "pillar-safe-by-default", "security", "auto-fixable"]
    },
    {
        "title": "JavaScript Errors Break Entire Page Functionality",
        "body": """**Diagnostic:** Check if single JavaScript error cascades to break all interactions

**Why This Matters (Pillar ⚙️: Murphy's Law):**
One script error shouldn't kill everything. Isolation prevents cascade—like circuit breakers that prevent one blown fuse from blacking out entire house.

**User Impact:**
- One error breaks all JavaScript
- Buttons stop working site-wide
- Forms can't submit
- Poor user experience

**What We Check:**
- Scripts use try-catch for critical sections
- Error handlers prevent cascade
- One feature failing doesn't break others
- Errors logged, not shown to users

**Expected Result:**
"Your JavaScript errors are contained. One feature failing doesn't break everything."

**If Issue Found:**
"Your analytics script throws error and stops all JavaScript execution. Users can't submit forms or use any interactive features. Wrap scripts in try-catch and isolate failures. Here's how to contain JS errors: [KB link]"

**Auto-Fixable:** No (requires error handling)
**Severity:** High (breaks site functionality)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "critical"]
    },
    {
        "title": "No Logging of Critical Failures",
        "body": """**Diagnostic:** Check if system logs critical failures for debugging

**Why This Matters (Pillar ⚙️: Murphy's Law + Commandment #9: Everything Has KPI):**
Failures that aren't logged can't be fixed. Logging enables debugging—like a car's check engine light vs. silently breaking down.

**User Impact:**
- Issues can't be diagnosed
- Repeat failures not detected
- No way to prevent future problems
- Support tickets unsolvable

**What We Check:**
- Failed operations are logged
- Logs include context (user, action, error)
- Critical failures trigger alerts
- Log retention policy exists

**Expected Result:**
"Your failures are logged. Issues can be diagnosed and prevented."

**If Issue Found:**
"Your payment processing fails silently with no logging. When customers report failed charges, you have no way to debug what happened. Log all payment attempts with error details. Here's how to implement logging: [KB link]"

**Auto-Fixable:** No (requires logging infrastructure)
**Severity:** High (prevents debugging)
**Family:** monitoring
**CANON Pillar:** ⚙️ Murphy's Law
**Commandment:** #9 (Everything Has KPI - measure failures)""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "commandment-9", "critical"]
    },
    {
        "title": "Stale Cache Not Used When Fresh Data Unavailable",
        "body": """**Diagnostic:** Check if system uses stale cache as fallback when update fails

**Why This Matters (Pillar ⚙️: Murphy's Law):**
APIs fail. Fresh data unavailable. Showing stale data is better than showing nothing—like weather app showing yesterday's forecast when today's fails to load.

**User Impact:**
- Features break completely when API down
- Users see errors instead of slightly-old data
- Worse experience than necessary
- Site appears broken

**What We Check:**
- Cache has backup copy with no expiration
- Failed update uses stale cache
- Stale data clearly marked as such
- Graceful degradation messaging

**Expected Result:**
"Your cache uses stale data as fallback. API failures don't break features completely."

**If Issue Found:**
"Your stock price widget shows error when API times out. It has cached data from 1 hour ago but ignores it. Better to show: 'Stock prices (last updated 1 hour ago, refresh failed)' than error. Here's how to implement stale cache fallback: [KB link]"

**Auto-Fixable:** No (requires cache strategy)
**Severity:** Medium (user experience)
**Family:** reliability
**CANON Pillar:** ⚙️ Murphy's Law""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law"]
    },
    {
        "title": "Time-Based Logic Uses Server Time Not User Time",
        "body": """**Diagnostic:** Check if scheduling/deadlines use user's timezone, not server's

**Why This Matters (Pillar ⚙️: Murphy's Law + Pillar 🌐: Culturally Respectful):**
"Sale ends at midnight" means different times globally. Using server time confuses international users—like announcing closing time in only one timezone.

**User Impact:**
- Users miss deadlines due to timezone confusion
- Promotions end at wrong time for them
- Scheduling conflicts
- International users disadvantaged

**What We Check:**
- Deadlines display in user's timezone
- Or clearly show which timezone used
- Scheduling respects user timezone
- JavaScript converts server time to local

**Expected Result:**
"Your time-based features respect user timezones. Deadlines and schedules are clear."

**If Issue Found:**
"Your flash sale countdown shows server time (EST) to all global users. Someone in Tokyo sees '11 hours remaining' but doesn't know that's EST, not their local time. Convert to user's timezone with JavaScript. Here's how to handle timezones: [KB link]"

**Auto-Fixable:** No (requires timezone logic)
**Severity:** Medium (confuses global users)
**Family:** internationalization
**CANON Pillar:** ⚙️ Murphy's Law + 🌐 Culturally Respectful""",
        "labels": ["diagnostic", "reliability", "pillar-murphys-law", "pillar-culturally-respectful", "internationalization"]
    },

    # ═══════════════════════════════════════════════════════════════
    # COMMANDMENT-FOCUSED DIAGNOSTICS (remaining diagnostics)
    # ═══════════════════════════════════════════════════════════════
    {
        "title": "Third-Party API Calls Not Disclosed to Users",
        "body": """**Diagnostic:** Check if external API calls are documented/disclosed (privacy page)

**Why This Matters (Commandment #10: Beyond Pure - Privacy First):**
Users should know what services their browser connects to. Hidden API calls violate trust—like a phone that calls unknown numbers without telling you.

**User Impact:**
- Privacy violation
- GDPR non-compliance
- User trust damaged
- Legal liability

**What We Check:**
- Privacy policy lists all external services
- API calls disclosed before making them
- Opt-in for non-essential services
- Users can disable external connections

**Expected Result:**
"Your external API calls are disclosed. Users know what services connect to their browser."

**If Issue Found:**
"Your site calls Google Analytics, Stripe, and Mailchimp APIs without disclosure in privacy policy. Users don't know their browser is connecting to these services. List all external services in privacy policy. Here's how to audit third-party calls: [KB link]"

**Auto-Fixable:** Partial (can detect calls, but disclosure needs writing)
**Severity:** High (privacy violation)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - transparency)""",
        "labels": ["diagnostic", "privacy", "commandment-10", "critical", "gdpr"]
    },
    {
        "title": "Analytics Don't Respect Do Not Track",
        "body": """**Diagnostic:** Check if analytics respect browser's Do Not Track (DNT) header

**Why This Matters (Commandment #10: Beyond Pure - Privacy First):**
DNT is a privacy preference. Ignoring it disrespects user choice—like someone asking not to be photographed but you do it anyway.

**User Impact:**
- Privacy preference ignored
- User trust damaged
- Ethical issue
- May violate privacy laws

**What We Check:**
- JavaScript checks `navigator.doNotTrack`
- Analytics disabled if DNT=1
- Or alternative privacy-respecting tracking used
- User informed of tracking method

**Expected Result:**
"Your analytics respect Do Not Track. Users who set DNT aren't tracked."

**If Issue Found:**
"Your Google Analytics runs regardless of user's Do Not Track setting. Check `navigator.doNotTrack` and skip tracking if set. Or use privacy-respecting alternative like Plausible. Here's how to respect DNT: [KB link]"

**Auto-Fixable:** No (requires policy decision + code)
**Severity:** Medium (privacy ethics)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - respect choices)""",
        "labels": ["diagnostic", "privacy", "commandment-10", "gdpr"]
    },
    {
        "title": "Cookie Consent Banner Missing or Non-Compliant",
        "body": """**Diagnostic:** Check if site has GDPR/CCPA-compliant cookie consent

**Why This Matters (Commandment #10: Beyond Pure - Privacy First):**
Non-essential cookies require consent. Pre-checked boxes or implied consent aren't compliant—like signing someone up for marketing without asking.

**User Impact:**
- GDPR fines (up to 4% revenue)
- Legal liability
- User trust damaged
- Privacy violation

**What We Check:**
- Consent banner before non-essential cookies
- Explicit opt-in (not pre-checked boxes)
- Easy opt-out
- Granular cookie categories
- Consent logged/verifiable

**Expected Result:**
"Your cookie consent is compliant. Users explicitly opt-in to non-essential cookies."

**If Issue Found:**
"Your site sets analytics cookies before consent. GDPR requires consent before non-essential cookies. Implement consent banner that blocks cookies until user accepts. Here's how to implement GDPR consent: [KB link]"

**Auto-Fixable:** No (requires consent mechanism)
**Severity:** Critical (legal requirement in EU)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - lawful)""",
        "labels": ["diagnostic", "privacy", "commandment-10", "critical", "gdpr", "legal"]
    },
    {
        "title": "No Way for Users to Export Their Data",
        "body": """**Diagnostic:** Check if users can export their data (GDPR right to data portability)

**Why This Matters (Commandment #10: Beyond Pure - Privacy First):**
GDPR grants users right to export their data. Not providing this violates their rights—like a bank refusing to give you account statements.

**User Impact:**
- GDPR non-compliance
- Legal liability
- User trust damaged
- Lock-in perception

**What We Check:**
- User dashboard has "Export My Data" option
- Exports all user data in readable format
- Includes profile, content, activity
- Format is portable (JSON, CSV)

**Expected Result:**
"Your users can export their data. GDPR right to portability is honored."

**If Issue Found:**
"Your site has no data export feature. GDPR requires users be able to download their data in portable format. Add 'Export My Data' to user profile downloading JSON file. Here's how to implement data export: [KB link]"

**Auto-Fixable:** No (requires export functionality)
**Severity:** High (GDPR requirement)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - user rights)""",
        "labels": ["diagnostic", "privacy", "commandment-10", "critical", "gdpr", "legal"]
    },
    {
        "title": "No Way for Users to Delete Their Account and Data",
        "body": """**Diagnostic:** Check if users can request account deletion (GDPR right to erasure)

**Why This Matters (Commandment #10: Beyond Pure - Privacy First):**
GDPR grants right to be forgotten. Not allowing deletion violates user rights—like a hotel refusing to let you check out.

**User Impact:**
- GDPR non-compliance (right to erasure)
- Legal liability
- User trust damaged
- Vendor lock-in feeling

**What We Check:**
- User can request account deletion
- Process is clear and accessible
- Data deleted (not just disabled account)
- Deletion confirmed to user

**Expected Result:**
"Your users can delete their accounts. GDPR right to erasure is honored."

**If Issue Found:**
"Your site has no account deletion feature. Users can't remove their data (GDPR violation). Add 'Delete My Account' option that permanently removes user data within 30 days. Here's how to implement data deletion: [KB link]"

**Auto-Fixable:** No (requires deletion mechanism)
**Severity:** High (GDPR requirement)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - user control)""",
        "labels": ["diagnostic", "privacy", "commandment-10", "critical", "gdpr", "legal"]
    },
    {
        "title": "User Data Not Encrypted At Rest",
        "body": """**Diagnostic:** Check if sensitive user data is encrypted in database

**Why This Matters (Commandment #10: Beyond Pure - Privacy First + Pillar 🛡️: Safe by Default):**
Database breaches happen. Encrypted data is useless to attackers—like a safe vs. shoebox for storing valuables.

**User Impact:**
- Data breach exposes PII
- Legal liability (GDPR, HIPAA)
- User trust destroyed
- Reputation damage

**What We Check:**
- Payment info encrypted (PCI requirement)
- PII encrypted (SSN, passport, etc.)
- Encryption at rest enabled (database level)
- Encryption keys secured separately

**Expected Result:**
"Your sensitive data is encrypted at rest. Database breach doesn't expose PII."

**If Issue Found:**
"Your database stores customer payment methods in plaintext. If database is breached, credit card numbers are exposed (PCI violation, massive liability). Encrypt all payment data. Here's how to encrypt data: [KB link]"

**Auto-Fixable:** No (requires encryption implementation)
**Severity:** Critical (data breach risk)
**Family:** privacy
**Commandment:** #10 (Beyond Pure - protect data)
**CANON Pillar:** 🛡️ Safe by Default""",
        "labels": ["diagnostic", "privacy", "commandment-10", "pillar-safe-by-default", "security", "critical", "vulnerability", "gdpr"]
    },
    {
        "title": "Activity/Feature Not Logged for KPI Tracking",
        "body": """**Diagnostic:** Check if important features log usage to Activity Logger

**Why This Matters (Commandment #9: Everything Has KPI):**
Can't measure what you don't track. Every feature should demonstrate value—like a business tracking sales, not just hoping they're happening.

**User Impact:**
- Can't prove feature value
- No data for improvements
- Can't justify development time
- Missing success metrics

**What We Check:**
- Important actions call `Activity_Logger::log()`
- Events include context (user, outcome, time)
- KPIs are measurable
- Dashboard shows metrics

**Expected Result:**
"Your features log activity. Impact is measurable and demonstrable."

**If Issue Found:**
"Your backup feature doesn't log to Activity Logger. You can't show users 'You've created 47 backups protecting 12GB of data'. Log all backup events with size, duration, success. Here's how to implement activity logging: [KB link]"

**Auto-Fixable:** No (requires logging code)
**Severity:** Low (quality-of-life feature)
**Family:** monitoring
**Commandment:** #9 (Everything Has KPI - measure impact)""",
        "labels": ["diagnostic", "monitoring", "commandment-9"]
    },
    {
        "title": "404 Pages Not Helpful",
        "body": """**Diagnostic:** Check if 404 error pages help users find what they need

**Why This Matters (Commandment #1: Helpful Neighbor + Pillar 🎓: Learning Inclusive):**
"Page not found" is useless. Helpful 404s guide users to content—like a store clerk helping you find product instead of just saying "we don't have that".

**User Impact:**
- Users leave site immediately
- No guidance on where to go
- Missed opportunity to help
- Poor user experience

**What We Check:**
- 404 shows search box
- Suggests related/popular content
- Links to homepage and main sections
- Explains error in friendly terms

**Expected Result:**
"Your 404 pages help users find content. Errors become navigation opportunities."

**If Issue Found:**
"Your 404 page just says 'Error 404: Page Not Found'. Users hit dead end. Add: search box, links to popular pages, 'Looking for...' suggestions, contact info. Here's how to create helpful 404 pages: [KB link]"

**Auto-Fixable:** No (requires page design)
**Severity:** Low (convenience)
**Family:** content
**Commandment:** #1 (Helpful Neighbor - always helpful)
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "user-experience", "commandment-1", "pillar-learning-inclusive"]
    },
    {
        "title": "Search Results Don't Explain No Results",
        "body": """**Diagnostic:** Check if zero search results show helpful suggestions

**Why This Matters (Commandment #1: Helpful Neighbor + Pillar 🎓: Learning Inclusive):**
"No results found" is a dead end. Helpful searches suggest alternatives—like a librarian suggesting related books when exact title isn't available.

**User Impact:**
- Users hit dead end
- Spelling errors lead to frustration
- No guidance on alternatives
- Higher bounce rate

**What We Check:**
- Zero results show suggestions
- Spell-check offers corrections
- "Did you mean..." functionality
- Shows popular searches or categories

**Expected Result:**
"Your zero results help users. Suggestions and corrections guide toward content."

**If Issue Found:**
"Your search shows 'No results found for: wrdpress' (user misspelled WordPress). Offer 'Did you mean: WordPress? (237 results)'. Include popular searches and spelling suggestions. Here's how to improve search results: [KB link]"

**Auto-Fixable:** No (requires search enhancement)
**Severity:** Low (user experience)
**Family:** content
**Commandment:** #1 (Helpful Neighbor - always helpful)
**CANON Pillar:** 🎓 Learning Inclusive""",
        "labels": ["diagnostic", "user-experience", "commandment-1", "pillar-learning-inclusive"]
    }
]

print(f"Creating {len(diagnostics)} diagnostic issues...")

for i, diagnostic in enumerate(diagnostics, 1):
    try:
        # Create GitHub issue
        cmd = [
            "gh", "issue", "create",
            "--repo", "thisismyurl/wpshadow",
            "--title", diagnostic["title"],
            "--body", diagnostic["body"],
            "--label", ",".join(diagnostic["labels"])
        ]
        
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        issue_url = result.stdout.strip()
        
        print(f"✅ {i}/{len(diagnostics)}: {diagnostic['title']}")
        print(f"   {issue_url}")
        
    except subprocess.CalledProcessError as e:
        print(f"❌ {i}/{len(diagnostics)}: {diagnostic['title']}")
        print(f"   Error: {e.stderr}")
        continue

print(f"\n🎉 Created {len(diagnostics)} diagnostic issues!")
print("\nView all issues:")
print("https://github.com/thisismyurl/wpshadow/issues?q=is%3Aissue+label%3Adiagnostic")
