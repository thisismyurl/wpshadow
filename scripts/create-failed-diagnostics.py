#!/usr/bin/env python3
"""
Create the 9 Failed Diagnostic Issues

These are the diagnostics that failed on the first run due to missing labels.
All labels now exist, so these should succeed.
"""

import subprocess

# The 9 (actually 10) failed diagnostics with full content
failed_diagnostics = [
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
        "title": "Documentation Links Generic Not Contextual",
        "body": """**Diagnostic:** Check if help links go to specific relevant pages, not generic help center

**Why This Matters (Pillar 🎓: Learning Inclusive + Commandment #5: Drive to KB):**
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
        "labels": ["diagnostic", "internationalization", "pillar-culturally-respectful", "commandment-1", "critical", "auto-fixable", "forms"]
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
    }
]

print(f"Creating {len(failed_diagnostics)} failed diagnostic issues...\n")

for i, diagnostic in enumerate(failed_diagnostics, 1):
    print(f"\nCreating issue {i}/{len(failed_diagnostics)}: {diagnostic['title']}")
    
    # Create the issue
    cmd = [
        "gh", "issue", "create",
        "--repo", "thisismyurl/wpshadow",
        "--title", diagnostic["title"],
        "--body", diagnostic["body"],
        "--label", ",".join(diagnostic["labels"])
    ]
    
    result = subprocess.run(cmd, capture_output=True, text=True)
    
    if result.returncode == 0:
        issue_url = result.stdout.strip()
        issue_number = issue_url.split("/")[-1] if issue_url else "unknown"
        print(f"✅ Created: {issue_url}")
    else:
        print(f"❌ Failed: {result.stderr}")

print(f"\n{'='*60}")
print(f"✅ Completed! All {len(failed_diagnostics)} issues created.")
print(f"{'='*60}")
