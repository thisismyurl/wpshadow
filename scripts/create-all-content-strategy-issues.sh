#!/bin/bash
###############################################################################
# Create All 100 Content Strategy Diagnostic Issues
# Automated GitHub Issue Creation with Rate Limiting
###############################################################################

set -e

API_URL="https://api.github.com/repos/thisismyurl/wpshadow/issues"
TOTAL=0
FAILED=0

create_issue() {
    local num="$1"
    local title="$2"
    local body="$3"
    
    printf "%-3s %s" "$num." "$title"
    
    response=$(curl -s -w "\n%{http_code}" -X POST "$API_URL" \
        -H "Authorization: token $GITHUB_TOKEN" \
        -H "Accept: application/vnd.github.v3+json" \
        -d "$(jq -n --arg title "$title" --arg body "$body" \
            '{title: $title, body: $body, labels: ["diagnostic", "content-strategy", "enhancement"]}')")
    
    http_code=$(echo "$response" | tail -n1)
    
    if [ "$http_code" = "201" ]; then
        issue_num=$(echo "$response" | head -n-1 | jq -r '.number')
        printf " ✅ #%s\n" "$issue_num"
        TOTAL=$((TOTAL + 1))
    else
        printf " ❌ HTTP %s\n" "$http_code"
        FAILED=$((FAILED + 1))
    fi
    
    sleep 2
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📦 Creating All Content Strategy Diagnostic Issues"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Issues 23-30: Content Freshness & Updates
create_issue 23 "Diagnostic: Outdated Statistics" "**Category:** Content Freshness 🔴 Critical
**Slug:** \`outdated-statistics\`
**Impact:** Statistics 3+ years old damage credibility. Updates increase trust by 67%."

create_issue 24 "Diagnostic: Broken Links" "**Category:** Content Freshness 🔴 Critical
**Slug:** \`broken-links\`
**Impact:** 3+ broken links per post = 45% trust loss. SEO penalty. 100% fixable."

create_issue 25 "Diagnostic: Outdated Screenshots" "**Category:** Content Freshness 🟡 Medium
**Slug:** \`outdated-screenshots\`
**Impact:** UI screenshots 2+ years old confuse users. Update top 10 posts for 30% engagement boost."

create_issue 26 "Diagnostic: No Update Timestamps" "**Category:** Content Freshness 🟡 Medium
**Slug:** \`missing-update-dates\`
**Impact:** Hidden update dates reduce trust. Show \"Updated: [date]\" for 23% credibility increase."

create_issue 27 "Diagnostic: Content Not Updated After Major Changes" "**Category:** Content Freshness 🔴 Critical
**Slug:** \`missing-major-updates\`
**Impact:** WordPress 6.0 released, no posts updated. Algorithm changes ignored. Competitors win."

create_issue 28 "Diagnostic: Seasonal Content Not Refreshed" "**Category:** Content Freshness 🟡 Medium
**Slug:** \`stale-seasonal-content\`
**Impact:** 2024 holiday guide still showing 2022. 80% traffic loss vs. updated competitor content."

create_issue 29 "Diagnostic: No Content Pruning Strategy" "**Category:** Content Freshness 🟡 Medium
**Slug:** \`no-content-pruning\`
**Impact:** 200+ posts with <10 visits/month dilute authority. Prune or improve for 25% site-wide boost."

create_issue 30 "Diagnostic: Evergreen Content Not Monitored" "**Category:** Content Freshness 🟡 Medium
**Slug:** \`evergreen-monitoring-missing\`
**Impact:** Best posts declining 40% YoY unnoticed. Monitor + update = sustained rankings."

# Issues 31-35: Keyword & Topic Strategy
create_issue 31 "Diagnostic: Keyword Cannibalization" "**Category:** Keyword Strategy 🔴 Critical
**Slug:** \`keyword-cannibalization\`
**Impact:** 5+ posts targeting \"WordPress security\" compete with each other. Consolidate for 3x ranking."

create_issue 32 "Diagnostic: Missing Primary Keyword in Title" "**Category:** Keyword Strategy 🔴 Critical
**Slug:** \`keyword-missing-title\`
**Impact:** Target keyword not in H1. Basic SEO issue. 100% auto-detectable."

create_issue 33 "Diagnostic: Keyword Stuffing Detected" "**Category:** Keyword Strategy 🔴 Critical
**Slug:** \`keyword-stuffing\`
**Impact:** Keyword density >3% triggers Google penalty. Modern SEO requires natural language."

create_issue 34 "Diagnostic: No Long-Tail Keywords" "**Category:** Keyword Strategy 🟡 Medium
**Slug:** \`missing-long-tail-keywords\`
**Impact:** Only targeting 1-2 word keywords (impossible competition). Long-tail = 70% of traffic."

create_issue 35 "Diagnostic: Keyword Gaps vs Competitors" "**Category:** Keyword Strategy 🟡 Medium
**Slug:** \`keyword-gaps\`
**Impact:** Competitors rank for 200+ keywords you don't cover. Identifies content opportunities."

# Issues 36-45: Internal & External Linking
create_issue 36 "Diagnostic: Orphan Content (No Internal Links)" "**Category:** Internal Linking 🔴 Critical
**Slug:** \`orphan-content\`
**Impact:** 35% of posts have zero internal links pointing to them. Google can't find them."

create_issue 37 "Diagnostic: Weak Internal Linking Structure" "**Category:** Internal Linking 🔴 Critical
**Slug:** \`weak-internal-linking\`
**Impact:** <2 internal links per post. Strong sites: 5-10 contextual links. Authority distribution."

create_issue 38 "Diagnostic: Too Many Outbound Links" "**Category:** External Linking 🟡 Medium
**Slug:** \`excessive-outbound-links\`
**Impact:** 20+ external links in 1,000 word post. Sends PageRank away. Max: 1-2 per 500 words."

create_issue 39 "Diagnostic: No Outbound Links to Authority Sites" "**Category:** External Linking 🟡 Medium
**Slug:** \`no-authority-links\`
**Impact:** Zero links to credible sources. Google values pages that cite authorities."

create_issue 40 "Diagnostic: Links to Low-Quality Sites" "**Category:** External Linking 🔴 Critical
**Slug:** \`low-quality-outbound-links\`
**Impact:** Links to spam/low-authority domains damage your trust score. Audit quarterly."

create_issue 41 "Diagnostic: Missing Nofollow on Affiliate Links" "**Category:** External Linking 🔴 Critical
**Slug:** \`affiliate-links-not-nofollowed\`
**Impact:** FTC compliance violation. Google penalty risk. Must use rel=\"sponsored\"."

create_issue 42 "Diagnostic: Broken Internal Links" "**Category:** Internal Linking 🔴 Critical
**Slug:** \`broken-internal-links\`
**Impact:** Links to deleted posts create 404s. Poor UX. SEO damage. Auto-fixable."

create_issue 43 "Diagnostic: Deep Content Buried" "**Category:** Internal Linking 🟡 Medium
**Slug:** \`deep-content-buried\`
**Impact:** Best content requires 5+ clicks from homepage. Should be 2-3 max."

create_issue 44 "Diagnostic: No Cross-Linking Between Related Posts" "**Category:** Internal Linking 🟡 Medium
**Slug:** \`no-related-post-linking\`
**Impact:** Related posts don't reference each other. Missed engagement + SEO opportunity."

create_issue 45 "Diagnostic: Homepage Links to Few Posts" "**Category:** Internal Linking 🟡 Medium
**Slug:** \`homepage-link-deficit\`
**Impact:** Homepage only links to 3-5 posts. Should highlight 10-15 top/recent posts."

# Issues 46-55: Content Structure & Formatting
create_issue 46 "Diagnostic: Missing H1 Tag" "**Category:** Structure 🔴 Critical
**Slug:** \`missing-h1\`
**Impact:** No H1 tag = major SEO issue. Every post needs exactly one H1."

create_issue 47 "Diagnostic: Multiple H1 Tags" "**Category:** Structure 🔴 Critical
**Slug:** \`multiple-h1-tags\`
**Impact:** 3 H1 tags per post confuses search engines. Use H2-H6 for subheadings."

create_issue 48 "Diagnostic: Poor Heading Hierarchy" "**Category:** Structure 🟡 Medium
**Slug:** \`broken-heading-hierarchy\`
**Impact:** H4 after H2 (skipping H3). Screen readers + SEO confused. 15% of posts affected."

create_issue 49 "Diagnostic: No Table of Contents on Long Posts" "**Category:** Structure 🟡 Medium
**Slug:** \`missing-toc\`
**Impact:** 3,000+ word posts without TOC = 45% higher bounce. Auto-generable from headings."

create_issue 50 "Diagnostic: Wall of Text (No Visual Breaks)" "**Category:** Structure 🟡 Medium
**Slug:** \`no-visual-breaks\`
**Impact:** 1,000+ words without images/lists/breaks. 73% abandon. Add visual every 300 words."

create_issue 51 "Diagnostic: Too Few Images" "**Category:** Structure 🟡 Medium
**Slug:** \`insufficient-images\`
**Impact:** <1 image per 1,000 words. Posts with 3-7 images get 94% more views."

create_issue 52 "Diagnostic: No Featured Image" "**Category:** Structure 🟡 Medium
**Slug:** \`missing-featured-image\`
**Impact:** Missing featured images reduce social shares by 40%. Required for rich snippets."

create_issue 53 "Diagnostic: Non-Descriptive Headings" "**Category:** Structure 🟡 Medium
**Slug:** \`vague-headings\`
**Impact:** Headings like \"Introduction\" or \"Tips\" hurt scannability. Use specific, keyword-rich."

create_issue 54 "Diagnostic: No Lists or Bullets" "**Category:** Structure 🟡 Medium
**Slug:** \`no-lists\`
**Impact:** 1,500+ word post with zero lists. Lists increase scannability 300%."

create_issue 55 "Diagnostic: No Code Blocks in Technical Content" "**Category:** Structure 🟡 Medium
**Slug:** \`inline-code-not-formatted\`
**Impact:** Code in plain text unreadable. Proper \`<code>\` blocks = 80% better UX."

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Batch Complete: Created $TOTAL issues"
if [ $FAILED -gt 0 ]; then
    echo "❌ Failed: $FAILED issues"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
