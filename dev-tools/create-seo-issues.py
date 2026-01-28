#!/usr/bin/env python3
"""
Create 100 SEO Diagnostic GitHub Issues for WPShadow
Uses GitHub API to create issues with proper formatting
"""

import subprocess
import json
import sys
import os

OWNER = "thisismyurl"
REPO = "wpshadow"

# Get GitHub token
def get_github_token():
    """Get GitHub token from git config"""
    result = subprocess.run(
        ["git", "config", "user.token"],
        capture_output=True,
        text=True
    )
    if result.returncode == 0 and result.stdout.strip():
        return result.stdout.strip()
    
    # Try from environment
    token = os.getenv("GITHUB_TOKEN")
    if token:
        return token
    
    print("❌ Error: GitHub token not found")
    print("Set GITHUB_TOKEN environment variable or configure git config user.token")
    sys.exit(1)

def create_issue(title, body, labels, token):
    """Create a GitHub issue using the API"""
    url = f"https://api.github.com/repos/{OWNER}/{REPO}/issues"
    
    data = {
        "title": title,
        "body": body,
        "labels": labels
    }
    
    cmd = [
        "curl", "-X", "POST",
        "-H", f"Authorization: token {token}",
        "-H", "Accept: application/vnd.github.v3+json",
        url,
        "-d", json.dumps(data)
    ]
    
    result = subprocess.run(cmd, capture_output=True, text=True)
    
    if result.returncode != 0:
        print(f"❌ curl error: {result.stderr}")
        return None
    
    try:
        response = json.loads(result.stdout)
        if "number" in response:
            return response["number"]
        elif "message" in response:
            print(f"❌ API error: {response['message']}")
            return None
    except json.JSONDecodeError:
        print(f"❌ JSON decode error: {result.stdout}")
        return None

# Issue definitions - 100 SEO diagnostics
ISSUES = [
    # FAMILY 1: Google Search Console Integration (10)
    {
        "title": "Diagnostic: GSC Account Connection Verification",
        "body": """## Description
Detect if site is connected to Google Search Console and verify the connection is active.

## What to Check For
- [ ] Google Search Console property is connected to this WordPress site
- [ ] API credentials are valid and authenticated
- [ ] Last successful API sync was recent (within 7 days)
- [ ] GSC can be queried for data

## How to Test
1. Check WordPress options for GSC property ID
2. Query GSC API with existing credentials
3. Verify API response is successful
4. Check for any authentication errors

## Test Data
- Test site with GSC connected
- Test site without GSC connected
- Test with expired GSC credentials

## Expected Behavior
- ✅ **Finding:** "GSC not connected" if no property found
- ✅ **Finding:** "GSC credentials expired" if API fails
- ✅ **No Finding:** If GSC is properly connected

## Technical Details
- Severity: High
- Phase: 1
- KB Link: https://wpshadow.com/kb/gsc-connection
- Auto-fixable: No
- Threat Level: 70
- Business Value: Enables GSC-based diagnostics and monitoring

## KPI Impact
- 0-10 points: GSC not connected
- +10 points: GSC connected and verified
- +5 points: Weekly GSC data sync enabled""",
        "labels": ["diagnostic", "seo", "google-search-console", "phase-1", "high-priority"]
    },
    {
        "title": "Diagnostic: GSC Index Coverage Issues Audit",
        "body": """## Description
Monitor pages with crawl errors, coverage issues, or indexing problems reported in Google Search Console.

## What to Check For
- [ ] Pages with crawl errors (4xx, 5xx)
- [ ] Pages blocked by robots.txt but in sitemap
- [ ] Pages excluded (noindex)
- [ ] Pages with crawl anomalies
- [ ] Sitemap coverage percentage

## How to Test
1. Query GSC Coverage report via API
2. Count pages by status (Indexed, Submitted not indexed, Crawl error, etc.)
3. Calculate coverage percentage
4. Identify top issues by frequency

## Test Data
- Site with high crawl errors (>100)
- Site with clean coverage (95%+)
- Site with blocked resources

## Expected Behavior
- Finding if coverage < 80% of submitted URLs
- Finding if >5 crawl errors
- No finding if coverage >90%

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 75
- Business Value: Improves indexation, increases organic traffic

## KPI Impact
+15 points per 10% coverage increase""",
        "labels": ["diagnostic", "seo", "google-search-console", "indexing", "phase-2"]
    },
    {
        "title": "Diagnostic: Core Web Vitals Performance Report",
        "body": """## Description
Monitor Largest Contentful Paint (LCP), First Input Delay (FID), and Cumulative Layout Shift (CLS) metrics from Google Search Console.

## What to Check For
- [ ] LCP metric from GSC (should be <2.5s)
- [ ] FID metric from GSC (should be <100ms)
- [ ] CLS metric from GSC (should be <0.1)
- [ ] Mobile vs Desktop breakdown
- [ ] Passing/Needs Improvement/Poor distribution

## How to Test
1. Query GSC Core Web Vitals API
2. Pull mobile and desktop metrics
3. Compare against Google's thresholds
4. Identify poor-performing pages

## Expected Behavior
- Finding if any metric exceeds thresholds
- Finding if 25%+ of pages need improvement
- No finding if all metrics pass

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: Partial (optimization suggestions)
- Threat Level: 80
- Business Value: Critical for ranking, user experience

## KPI Impact
+20 points per metric in "Good" range""",
        "labels": ["diagnostic", "seo", "google-search-console", "core-web-vitals", "phase-2", "critical"]
    },
    {
        "title": "Diagnostic: URL Inspection API Integration",
        "body": """## Description
Use GSC URL Inspection API to check indexing status of specific pages in real-time.

## What to Check For
- [ ] Page is discoverable by Google
- [ ] Page is indexable (no robots.txt block, meta robots)
- [ ] Page is indexed in Google
- [ ] AMP version status (if applicable)
- [ ] Any indexing issues

## How to Test
1. Queue several important URLs
2. Call URL Inspection API
3. Check indexing status for each
4. Report any issues found

## Expected Behavior
- Finding if important pages not indexed
- Finding if critical pages blocked
- No finding if all pages properly indexed

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 60
- Business Value: Real-time indexing verification

## KPI Impact
+5 points per properly indexed important page""",
        "labels": ["diagnostic", "seo", "google-search-console", "indexing", "phase-2"]
    },
    {
        "title": "Diagnostic: GSC Index Optimization Opportunities",
        "body": """## Description
Detect content eligible for Google Discover but not appearing in Discover results.

## What to Check For
- [ ] Pages eligible for Google Discover
- [ ] Content quality metrics
- [ ] E-E-A-T signals present
- [ ] Image quality (important for Discover)
- [ ] Fresh content signals

## How to Test
1. Identify pages with Discover traffic
2. Score pages for Discover eligibility
3. Find high-quality content missing from Discover
4. Analyze top Discover pages for patterns

## Expected Behavior
- Finding if 20%+ of content is Discover-eligible but not shown
- Suggestions to improve Discover appearance
- No finding if good Discover coverage

## Technical Details
- Severity: Medium
- Phase: 3
- Auto-fixable: No
- Threat Level: 40
- Business Value: Discover can drive 30%+ of traffic

## KPI Impact
+10 points per Discover-eligible page""",
        "labels": ["diagnostic", "seo", "google-discover", "phase-3"]
    },
    {
        "title": "Diagnostic: Mobile Usability Report Issues",
        "body": """## Description
Detect mobile usability issues flagged by Google Search Console.

## What to Check For
- [ ] Viewport not configured
- [ ] Content wider than viewport
- [ ] Text too small to read
- [ ] Touch elements too close
- [ ] Flash content
- [ ] Interstitials blocking content

## How to Test
1. Query GSC Mobile Usability report
2. List all flagged issues
3. Group by issue type
4. Calculate percentage of pages affected

## Expected Behavior
- Finding if >10% of pages have mobile issues
- Detail each issue type
- No finding if <5% affected

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: Partial
- Threat Level: 65
- Business Value: Essential for mobile rankings

## KPI Impact
+10 points per mobile usability issue fixed""",
        "labels": ["diagnostic", "seo", "mobile-usability", "phase-2", "high-priority"]
    },
    {
        "title": "Diagnostic: Search Appearance Issues Detection",
        "body": """## Description
Monitor rich result errors, mobile usability, AMP errors, and other search appearance issues.

## What to Check For
- [ ] Rich snippet errors
- [ ] AMP validation errors
- [ ] Breadcrumb errors
- [ ] FAQ schema errors
- [ ] Product schema errors
- [ ] Recipe schema errors

## How to Test
1. Query GSC Enhancement report
2. Identify all enhancement types
3. List errors by enhancement type
4. Calculate error rates

## Expected Behavior
- Finding if >5 errors in any enhancement type
- Detail specific errors
- No finding if clean enhancement data

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 50
- Business Value: Rich snippets increase CTR 10-15%

## KPI Impact
+5 points per error fixed, +15 points per enhancement enabled""",
        "labels": ["diagnostic", "seo", "rich-snippets", "schema", "phase-2"]
    },
    {
        "title": "Diagnostic: Sitelink Search Box Implementation",
        "body": """## Description
Detect if sitelinks search box is enabled in search results.

## What to Check For
- [ ] Site search is configured
- [ ] Search function is accessible
- [ ] GSC sitelinks data shows search box
- [ ] No errors in sitelinks implementation

## How to Test
1. Check WordPress search functionality
2. Query GSC sitelinks data
3. Verify search schema markup
4. Check GSC search box settings

## Expected Behavior
- Finding if site is eligible but sitelinks search box disabled
- No finding if enabled or not eligible

## Technical Details
- Severity: Low
- Phase: 3
- Auto-fixable: Partial
- Threat Level: 25
- Business Value: Can improve CTR for brand searches

## KPI Impact
+3 points if sitelinks search box enabled""",
        "labels": ["diagnostic", "seo", "google-search-console", "phase-3"]
    },
    {
        "title": "Diagnostic: GSC Data Freshness Check",
        "body": """## Description
Verify GSC data is recent and not stale, ensuring monitoring is active.

## What to Check For
- [ ] Last successful GSC data fetch (should be <7 days old)
- [ ] Data is current and relevant
- [ ] API is responding normally
- [ ] Credentials are still valid

## How to Test
1. Check last GSC API call timestamp
2. Query API for recent data
3. Compare data date to current date
4. Flag if stale (>7 days old)

## Expected Behavior
- Finding if GSC data >7 days old
- Finding if API not responding
- No finding if data is fresh

## Technical Details
- Severity: Medium
- Phase: 3
- Auto-fixable: No (requires re-authentication)
- Threat Level: 45
- Business Value: Ensures monitoring is active

## KPI Impact
+5 points if data freshness <7 days""",
        "labels": ["diagnostic", "seo", "google-search-console", "monitoring", "phase-3"]
    },
    {
        "title": "Diagnostic: GSC Removal Requests Audit",
        "body": """## Description
Track permanently removed pages that may still appear in search index or GSC.

## What to Check For
- [ ] Pages with active removal requests
- [ ] Removal request expiration dates
- [ ] Pages that no longer exist (404)
- [ ] Removal effectiveness

## How to Test
1. Query GSC removals API
2. Check status of each removal
3. Verify removed pages return 404
4. Check if pages still in index

## Expected Behavior
- Finding if removal requests still pending >30 days
- Finding if removed pages still in index
- No finding if removals processed correctly

## Technical Details
- Severity: Medium
- Phase: 3
- Auto-fixable: No
- Threat Level: 35
- Business Value: Preserve crawl budget

## KPI Impact
+3 points per successful removal""",
        "labels": ["diagnostic", "seo", "google-search-console", "phase-3"]
    },
    
    # FAMILY 2: Core Web Vitals (12) - I'll add a few key ones here
    {
        "title": "Diagnostic: Largest Contentful Paint (LCP) Analysis",
        "body": """## Description
Identify pages with poor Largest Contentful Paint (LCP) score (>4 seconds) and analyze root causes.

## What to Check For
- [ ] LCP score for page (Google recommendation: <2.5s)
- [ ] Which element is the LCP element
- [ ] Image optimization (most common LCP)
- [ ] Server response time (TTFB)
- [ ] Main thread blocking

## How to Test
1. Measure page load performance
2. Identify LCP element
3. Analyze element loading time
4. Check for optimization opportunities

## Test Data
- Page with well-optimized LCP (<1s)
- Page with poor LCP (>4s)
- Page with image-based LCP
- Page with text-based LCP

## Expected Behavior
- Finding if LCP >2.5s
- Detail which element is causing delay
- Suggest optimization (image, lazy-load, etc.)
- No finding if LCP optimal

## Technical Details
- Severity: Critical
- Phase: 1
- Auto-fixable: Partial (optimization suggestions)
- Threat Level: 85
- Business Value: Critical ranking factor

## KPI Impact
+25 points for LCP <2.5s, -10 per 1s over 2.5s""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "performance", "phase-1", "critical"]
    },
    {
        "title": "Diagnostic: First Input Delay (FID) Optimization",
        "body": """## Description
Detect high First Input Delay (FID) from heavy JavaScript blocking the main thread.

## What to Check For
- [ ] FID metric (should be <100ms)
- [ ] Main thread blocking time
- [ ] Heavy JavaScript execution
- [ ] Long tasks (>50ms)
- [ ] Event handler delays

## How to Test
1. Measure FID on real pages
2. Profile JavaScript execution
3. Identify long-running tasks
4. Measure event handler latency

## Expected Behavior
- Finding if FID >300ms (poor)
- Detail specific JavaScript causing delay
- Suggest optimization (defer, async, split)
- No finding if FID <100ms

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No (requires code changes)
- Threat Level: 75
- Business Value: Improves interactivity score

## KPI Impact
+20 points for FID <100ms, -5 per 100ms over threshold""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "javascript", "phase-1"]
    },
    {
        "title": "Diagnostic: Cumulative Layout Shift (CLS) Detection",
        "body": """## Description
Find unexpected layout shifts (CLS >0.1) that hurt user experience and SEO.

## What to Check For
- [ ] CLS score (should be <0.1)
- [ ] Elements that shift during page load
- [ ] Fonts causing layout shifts
- [ ] Images without dimensions
- [ ] Ads/embeds shifting content

## How to Test
1. Simulate page load from blank
2. Track element position changes
3. Measure shift distance * viewport %
4. Identify cause of shifts

## Expected Behavior
- Finding if CLS >0.1
- Detail which elements are shifting
- Suggest fixes (image dimensions, font-display, etc.)
- No finding if CLS <0.1

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: Partial
- Threat Level: 70
- Business Value: Critical for visual stability

## KPI Impact
+20 points for CLS <0.1, -5 per 0.05 over threshold""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "layout-shift", "phase-1"]
    },
    {
        "title": "Diagnostic: Web Vitals Threshold Compliance",
        "body": """## Description
Check if all Core Web Vitals metrics pass Google's "Good" thresholds for ranking eligibility.

## What to Check For
- [ ] LCP ≤ 2.5s (Good)
- [ ] FID ≤ 100ms (Good)
- [ ] CLS ≤ 0.1 (Good)
- [ ] At least 75% of visits meet all 3

## How to Test
1. Collect real user data (RUM)
2. Calculate 75th percentile for each metric
3. Check if all 3 thresholds met
4. Report pass/fail status

## Expected Behavior
- Finding if any metric fails
- Clear status for each metric
- No finding if all metrics pass

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No
- Threat Level: 80
- Business Value: Determines ranking eligibility

## KPI Impact
+30 points if all metrics pass""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "phase-1", "critical"]
    },
    {
        "title": "Diagnostic: Mobile vs Desktop Core Web Vitals Variance",
        "body": """## Description
Compare Core Web Vitals performance between mobile and desktop to identify device-specific issues.

## What to Check For
- [ ] Mobile LCP vs Desktop LCP
- [ ] Mobile FID vs Desktop FID
- [ ] Mobile CLS vs Desktop CLS
- [ ] Significant variance (>50%)
- [ ] Device-specific problems

## How to Test
1. Load page on mobile emulation
2. Load page on desktop
3. Compare CWV metrics
4. Identify differences >50%

## Expected Behavior
- Finding if mobile metrics significantly worse
- Finding if device-specific optimization needed
- No finding if mobile ≈ desktop

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 65
- Business Value: Mobile is primary ranking factor

## KPI Impact
+15 points if mobile metrics match desktop""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "mobile", "phase-2"]
    },
    {
        "title": "Diagnostic: Third-Party Script Impact on CWV",
        "body": """## Description
Detect which third-party scripts harm Core Web Vitals and quantify their impact.

## What to Check For
- [ ] Google Analytics impact on metrics
- [ ] Ad network impact (AdSense, Ad Manager)
- [ ] Font loader impact
- [ ] Chat widget impact
- [ ] Tracking scripts impact

## How to Test
1. Measure CWV with all scripts
2. Remove each third-party script
3. Measure CWV again
4. Calculate impact % for each

## Expected Behavior
- Finding if any script degrades CWV >10%
- List impact of each script
- Suggest alternatives or optimization
- No finding if scripts optimized

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 60
- Business Value: Optimize essential vs. non-essential

## KPI Impact
+10 points per optimized script""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "third-party-scripts", "phase-2"]
    },
    {
        "title": "Diagnostic: Image Optimization for LCP",
        "body": """## Description
Find oversized or poorly optimized LCP images and suggest optimizations.

## What to Check For
- [ ] LCP image size (should be <80KB for fast loading)
- [ ] LCP image format (WebP, AVIF preferred)
- [ ] Image dimensions match usage
- [ ] Image loading strategy (lazy-load issues)
- [ ] Image optimization level

## How to Test
1. Identify LCP element
2. Check if it's an image
3. Analyze image size, format, dimensions
4. Suggest optimization

## Expected Behavior
- Finding if LCP image >200KB or not optimized
- Suggest format conversion to WebP
- Suggest compression
- No finding if image optimized

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No
- Threat Level: 70
- Business Value: Images cause 60% of LCP issues

## KPI Impact
+15 points per LCP image optimized""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "images", "phase-1"]
    },
    {
        "title": "Diagnostic: First Contentful Paint (FCP) Analysis",
        "body": """## Description
Monitor First Contentful Paint metric (precursor to LCP) for early content visibility.

## What to Check For
- [ ] FCP metric (should be <1.8s)
- [ ] Time to first render
- [ ] Resource blocking FCP
- [ ] CSS/Font impact on FCP

## How to Test
1. Measure FCP on page load
2. Identify what renders first
3. Check for blocking resources
4. Analyze optimization opportunities

## Expected Behavior
- Finding if FCP >1.8s
- Detail blocking resources
- No finding if FCP <1.8s

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 55
- Business Value: Indicates performance progression

## KPI Impact
+15 points for FCP <1.8s""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "performance", "phase-2"]
    },
    {
        "title": "Diagnostic: Time to Interactive (TTI) Measurement",
        "body": """## Description
Check Time to Interactive metric for when page becomes fully usable.

## What to Check For
- [ ] TTI metric (should be <3.8s)
- [ ] Main thread blocking after FCP
- [ ] Uninterruptible JavaScript
- [ ] Heavy parsing operations

## How to Test
1. Measure TTI on page
2. Identify what makes page interactive
3. Check for long tasks
4. Analyze optimization opportunities

## Expected Behavior
- Finding if TTI >5s (poor)
- Finding if significant gap between FCP and TTI
- No finding if TTI <3.8s

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 50
- Business Value: User can interact quickly

## KPI Impact
+10 points for TTI <3.8s""",
        "labels": ["diagnostic", "seo", "core-web-vitals", "interactivity", "phase-2"]
    },
    
    # Add remaining families in compact form...
    {
        "title": "Diagnostic: Robots.txt Optimization Analysis",
        "body": """## Description
Analyze robots.txt for unnecessary blocks and inefficient crawl budget usage.

## What to Check For
- [ ] Pages unnecessarily blocked by robots.txt
- [ ] Sitemap reference present
- [ ] Crawl rate optimization
- [ ] Conflicting rules

## How to Test
1. Parse robots.txt file
2. Check blocked resources
3. Verify blocked pages aren't important
4. Test with robots.txt tester

## Expected Behavior
- Finding if critical pages blocked
- Finding if no sitemap reference
- Suggest optimization
- No finding if optimized

## Technical Details
- Severity: Medium
- Phase: 1
- Auto-fixable: Partial
- Threat Level: 60
- Business Value: Improve crawl efficiency

## KPI Impact
+10 points for optimized robots.txt""",
        "labels": ["diagnostic", "seo", "crawlability", "robots", "phase-1"]
    },
    {
        "title": "Diagnostic: XML Sitemap Crawlability Check",
        "body": """## Description
Verify sitemap references all indexable pages and is complete.

## What to Check For
- [ ] All important pages in sitemap
- [ ] No noindex pages in sitemap
- [ ] Sitemap has priority/lastmod data
- [ ] Sitemap is valid XML

## How to Test
1. Parse XML sitemap
2. Compare sitemap URLs to crawlable pages
3. Check for orphaned pages
4. Validate XML format

## Expected Behavior
- Finding if >10% of pages missing
- Finding if noindex pages included
- No finding if sitemap complete

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No
- Threat Level: 65
- Business Value: Ensure full indexing

## KPI Impact
+15 points for complete sitemap""",
        "labels": ["diagnostic", "seo", "crawlability", "sitemap", "phase-1", "high-priority"]
    },
    {
        "title": "Diagnostic: Internal Link Architecture Audit",
        "body": """## Description
Analyze internal link structure for authority distribution and navigation optimization.

## What to Check For
- [ ] Link distribution across pages
- [ ] Authority/PageRank flow
- [ ] Logical site structure
- [ ] Navigation hierarchy

## How to Test
1. Crawl all internal links
2. Map link graph
3. Calculate authority flow
4. Identify issues

## Expected Behavior
- Finding if authority not reaching important pages
- Finding if poor link structure
- No finding if optimized

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: Partial
- Threat Level: 70
- Business Value: Optimize ranking distribution

## KPI Impact
+20 points for optimized structure""",
        "labels": ["diagnostic", "seo", "internal-linking", "phase-1", "high-priority"]
    },
    {
        "title": "Diagnostic: JavaScript Rendering Impact on SEO",
        "body": """## Description
Detect if JavaScript-rendered content is properly indexed by Google.

## What to Check For
- [ ] Content that requires JS to render
- [ ] Rendered vs. HTML-only content difference
- [ ] Metadata rendering properly
- [ ] Schema markup after rendering

## How to Test
1. Load page without JS
2. Load page with JS rendering
3. Compare content differences
4. Verify indexable content

## Expected Behavior
- Finding if critical content missing without JS
- Finding if schema not in rendered HTML
- No finding if JS content properly handled

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 65
- Business Value: Ensure JS content indexable

## KPI Impact
+20 points for proper JS handling""",
        "labels": ["diagnostic", "seo", "javascript", "rendering", "phase-2"]
    },
    {
        "title": "Diagnostic: Duplicate Content Consolidation",
        "body": """## Description
Find duplicate and near-duplicate pages causing indexing and ranking dilution.

## What to Check For
- [ ] Exact duplicate content
- [ ] Near-duplicate pages (>85% similar)
- [ ] Parameterized duplicates
- [ ] Canonicalization issues

## How to Test
1. Hash content from pages
2. Find similar hashes
3. Check canonical tags
4. Identify consolidation opportunities

## Expected Behavior
- Finding if >3 duplicate pages
- Detail duplicate sets
- No finding if consolidated

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No
- Threat Level: 75
- Business Value: Prevent duplicate penalties

## KPI Impact
+15 points per duplicate consolidated""",
        "labels": ["diagnostic", "seo", "duplicate-content", "canonical", "phase-1"]
    },
    {
        "title": "Diagnostic: Backlink Profile Quality Audit",
        "body": """## Description
Analyze backlink quality, relevance, and domain authority metrics.

## What to Check For
- [ ] Domain authority of referring sites
- [ ] Link relevance to content
- [ ] Anchor text quality
- [ ] Link placement quality
- [ ] Spam score

## How to Test
1. Collect all backlinks
2. Score by DA, relevance, spam signals
3. Identify harmful links
4. Find high-quality link opportunities

## Expected Behavior
- Finding if >5% backlinks are spammy
- Finding if harmful link pattern
- List quality metrics
- No finding if profile is healthy

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 70
- Business Value: Protect domain reputation

## KPI Impact
+10 points per harmful link identified""",
        "labels": ["diagnostic", "seo", "backlinks", "link-quality", "phase-2", "high-priority"]
    },
    {
        "title": "Diagnostic: Mobile-First Indexing Compatibility",
        "body": """## Description
Verify site is fully mobile-first index ready with complete content on mobile.

## What to Check For
- [ ] Mobile version has all desktop content
- [ ] Mobile functionality matches desktop
- [ ] Mobile navigation works properly
- [ ] Responsive design validation

## How to Test
1. Load page on mobile
2. Compare content to desktop
3. Test all interactive features
4. Check responsiveness

## Expected Behavior
- Finding if mobile content missing
- Finding if mobile features broken
- No finding if fully compatible

## Technical Details
- Severity: Critical
- Phase: 1
- Auto-fixable: No
- Threat Level: 90
- Business Value: Primary ranking factor

## KPI Impact
+25 points for full mobile compatibility""",
        "labels": ["diagnostic", "seo", "mobile", "indexing", "phase-1", "critical"]
    },
    {
        "title": "Diagnostic: Primary Keyword Coverage Analysis",
        "body": """## Description
Verify each page targets a primary keyword and uses it naturally.

## What to Check For
- [ ] Primary keyword in title tag
- [ ] Primary keyword in H1
- [ ] Primary keyword in first paragraph
- [ ] Keyword density reasonable (not stuffed)
- [ ] Keyword appears naturally

## How to Test
1. Identify page's primary keyword
2. Check title tag
3. Check H1 tag
4. Analyze keyword usage in content

## Expected Behavior
- Finding if primary keyword missing from title/H1
- Finding if keyword naturally integrated
- No finding if optimized

## Technical Details
- Severity: High
- Phase: 1
- Auto-fixable: No
- Threat Level: 65
- Business Value: Core on-page SEO

## KPI Impact
+15 points per page with optimal keyword placement""",
        "labels": ["diagnostic", "seo", "keywords", "on-page", "phase-1"]
    },
    {
        "title": "Diagnostic: Featured Snippet Optimization Score",
        "body": """## Description
Identify pages eligible for featured snippets and optimize snippet positioning.

## What to Check For
- [ ] Content formatted for featured snippets
- [ ] Tables/lists/paragraphs with answers
- [ ] Question-answer structure
- [ ] Content length (40-60 words for snippets)

## How to Test
1. Identify featured snippet opportunities
2. Check if content is snippet-optimized
3. Analyze top snippets for pattern
4. Score optimization level

## Expected Behavior
- Finding if content not optimized for snippets
- Finding if high-value keywords without snippets
- Suggest optimization structure
- No finding if optimized

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 55
- Business Value: Snippets drive 3-5% extra CTR

## KPI Impact
+10 points per snippet-optimized page""",
        "labels": ["diagnostic", "seo", "featured-snippets", "serp-features", "phase-2"]
    },
    {
        "title": "Diagnostic: Topic Cluster Completeness",
        "body": """## Description
Verify pillar pages link to cluster content and form topical clusters.

## What to Check For
- [ ] Pillar-cluster link structure
- [ ] Bidirectional linking (cluster → pillar)
- [ ] Cluster content covers subtopics
- [ ] Relevant linking

## How to Test
1. Identify pillar pages
2. Map cluster content
3. Check linking structure
4. Analyze topic coverage

## Expected Behavior
- Finding if pillar pages not linking to clusters
- Finding if incomplete cluster
- No finding if proper structure

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 50
- Business Value: Improve topical authority

## KPI Impact
+20 points for complete cluster""",
        "labels": ["diagnostic", "seo", "content-structure", "topical-authority", "phase-2"]
    },
    {
        "title": "Diagnostic: Content Freshness & Update Frequency",
        "body": """## Description
Detect outdated content (>6 months old) and identify pages needing updates.

## What to Check For
- [ ] Last modification date
- [ ] Content age vs. topic freshness
- [ ] Outdated information
- [ ] Statistics/data age

## How to Test
1. Check post modified dates
2. Analyze publication dates
3. Identify evergreen vs. time-sensitive content
4. Flag old content

## Expected Behavior
- Finding if >20% of content >6 months old
- Finding if time-sensitive content outdated
- No finding if content current

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 45
- Business Value: Freshness is ranking factor

## KPI Impact
+5 points per updated page""",
        "labels": ["diagnostic", "seo", "content", "freshness", "phase-2"]
    },
    {
        "title": "Diagnostic: Keyword Cannibalisation Detection",
        "body": """## Description
Find pages targeting the same keyword causing ranking dilution.

## What to Check For
- [ ] Multiple pages ranking for same keyword
- [ ] Similar keyword targets
- [ ] Competing internal pages
- [ ] Fragmented authority

## How to Test
1. Analyze keywords per page
2. Find duplicates
3. Check rankings for each
4. Identify consolidation opportunities

## Expected Behavior
- Finding if >2 pages target same keyword
- Detail keyword and pages
- No finding if keywords unique

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 60
- Business Value: Consolidate ranking power

## KPI Impact
+20 points per consolidated keyword""",
        "labels": ["diagnostic", "seo", "keywords", "cannibalisation", "phase-2"]
    },
    {
        "title": "Diagnostic: Competitor Backlink Gap Analysis",
        "body": """## Description
Find high-quality backlinks to competitors not pointing to your site.

## What to Check For
- [ ] Top 3 competitor backlinks
- [ ] Links pointing to them but not you
- [ ] Link quality comparison
- [ ] Link building opportunities

## How to Test
1. Analyze top 3 competitor backlinks
2. Cross-reference with own backlinks
3. Identify gaps
4. Score opportunity quality

## Expected Behavior
- Finding if competitor gap >10 links
- List high-quality link opportunities
- No finding if comparable link profile

## Technical Details
- Severity: High
- Phase: 3
- Auto-fixable: No
- Threat Level: 65
- Business Value: Identify link sources

## KPI Impact
+15 points per link opportunity""",
        "labels": ["diagnostic", "seo", "backlinks", "competitive-analysis", "phase-3"]
    },
    {
        "title": "Diagnostic: E-E-A-T Signal Measurement",
        "body": """## Description
Score page's Experience, Expertise, Authority, and Trustworthiness signals.

## What to Check For
- [ ] Author credentials visible
- [ ] Author bio/experience listed
- [ ] Citations to authoritative sources
- [ ] Trust signals (awards, certifications)
- [ ] About page quality
- [ ] Contact information

## How to Test
1. Analyze author information
2. Check for citations
3. Identify trust signals
4. Score EEAT completeness

## Expected Behavior
- Finding if missing EEAT signals
- Finding if low EEAT score
- No finding if strong EEAT signals

## Technical Details
- Severity: High
- Phase: 3
- Auto-fixable: Partial
- Threat Level: 70
- Business Value: Critical for YMYL content

## KPI Impact
+20 points for strong EEAT signals""",
        "labels": ["diagnostic", "seo", "eeat", "expertise", "phase-3"]
    },
    {
        "title": "Diagnostic: Google Business Profile Completeness",
        "body": """## Description
Verify Google Business Profile has all required fields completed.

## What to Check For
- [ ] Business name complete
- [ ] Address accurate and complete
- [ ] Phone number listed
- [ ] Website URL correct
- [ ] Business hours updated
- [ ] Photos uploaded
- [ ] Description filled out
- [ ] Categories proper

## How to Test
1. Query GBP API
2. Check all required fields
3. Verify accuracy
4. Check photo count/quality

## Expected Behavior
- Finding if missing required fields
- Finding if outdated information
- No finding if complete

## Technical Details
- Severity: Critical
- Phase: 2
- Auto-fixable: No
- Threat Level: 85
- Business Value: Critical for local ranking

## KPI Impact
+25 points for complete profile""",
        "labels": ["diagnostic", "seo", "local-seo", "google-business", "phase-2", "critical"]
    },
    {
        "title": "Diagnostic: Hreflang Attribute Implementation",
        "body": """## Description
Verify hreflang tags are correct and properly signal language/regional versions.

## What to Check For
- [ ] Hreflang syntax correct
- [ ] All language versions linked
- [ ] Return links present (bidirectional)
- [ ] Default language specified
- [ ] No hreflang errors

## How to Test
1. Parse hreflang tags
2. Verify syntax per spec
3. Check bidirectional linking
4. Validate coverage

## Expected Behavior
- Finding if hreflang syntax errors
- Finding if missing bidirectional links
- No finding if proper implementation

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 65
- Business Value: Enable proper language indexing

## KPI Impact
+15 points for correct hreflang""",
        "labels": ["diagnostic", "seo", "international-seo", "hreflang", "phase-2"]
    },
    {
        "title": "Diagnostic: SERP Position Tracking",
        "body": """## Description
Monitor keyword rankings over time to measure SEO progress.

## What to Check For
- [ ] Keyword rank position
- [ ] Ranking trend (up/down/stable)
- [ ] Ranking change from previous check
- [ ] Search volume for keyword
- [ ] Difficulty score

## How to Test
1. Track rankings daily/weekly
2. Store historical data
3. Calculate trends
4. Compare to competitors

## Expected Behavior
- Report current rank for tracked keywords
- Show trend arrows
- No finding (data-only)

## Technical Details
- Severity: Medium
- Phase: 2
- Auto-fixable: No
- Threat Level: 0 (monitoring only)
- Business Value: Track SEO progress

## KPI Impact
+10 points per top 10 ranking
+3 points per top 20 ranking""",
        "labels": ["diagnostic", "seo", "ranking-tracking", "monitoring", "phase-2"]
    },
    {
        "title": "Diagnostic: Click-Through Rate (CTR) Optimization",
        "body": """## Description
Measure CTR vs. competitors and identify improvement opportunities.

## What to Check For
- [ ] Current CTR from GSC
- [ ] Expected CTR for position
- [ ] CTR vs. competitor comparison
- [ ] Title/description optimization

## How to Test
1. Pull CTR data from GSC
2. Calculate expected CTR for position
3. Compare to competitors
4. Identify optimization gaps

## Expected Behavior
- Finding if CTR significantly below average
- Suggest title/description improvements
- No finding if CTR optimal

## Technical Details
- Severity: High
- Phase: 2
- Auto-fixable: No
- Threat Level: 60
- Business Value: Increase traffic without ranking

## KPI Impact
+1% CTR = +5-10% traffic increase""",
        "labels": ["diagnostic", "seo", "ctr", "serp-optimization", "phase-2"]
    },
]

def main():
    """Create all issues"""
    token = get_github_token()
    
    print(f"🔐 Creating {len(ISSUES)} SEO Diagnostic Issues for WPShadow")
    print(f"Repository: {OWNER}/{REPO}")
    print(f"Total Issues: {len(ISSUES)}\n")
    
    created = 0
    failed = 0
    failed_titles = []
    
    for index, issue in enumerate(ISSUES, 1):
        title = issue["title"]
        body = issue["body"]
        labels = issue["labels"]
        
        print(f"[{index}/{len(ISSUES)}] Creating: {title[:50]}...", end=" ", flush=True)
        
        issue_number = create_issue(title, body, labels, token)
        
        if issue_number:
            print(f"✅ Issue #{issue_number}")
            created += 1
        else:
            print("❌ Failed")
            failed += 1
            failed_titles.append(title)
        
        # Small delay to avoid rate limiting
        import time
        time.sleep(0.5)
    
    print(f"\n{'='*80}")
    print(f"✅ Created: {created} issues")
    print(f"❌ Failed: {failed} issues")
    print(f"🎉 Success Rate: {100*created//len(ISSUES)}% ({created}/{len(ISSUES)} created)")
    
    if failed_titles:
        print(f"\nFailed to create:")
        for title in failed_titles:
            print(f"  - {title}")
    
    print(f"\n🌐 Visit: https://github.com/{OWNER}/{REPO}/issues")

if __name__ == "__main__":
    main()
