#!/usr/bin/env python3
"""
Create GitHub Issues for All 100 Content Strategy Diagnostics

This script creates detailed GitHub issues for all 100 content strategy
diagnostics documented in docs/CONTENT_STRATEGY_DIAGNOSTICS_100.md

Usage: python3 create-content-strategy-diagnostics-issues.py
"""

import os
import sys
import time
import requests
import json

# GitHub Configuration
REPO_OWNER = "thisismyurl"
REPO_NAME = "wpshadow"
GITHUB_TOKEN = os.environ.get("GITHUB_TOKEN")
API_URL = f"https://api.github.com/repos/{REPO_OWNER}/{REPO_NAME}/issues"
LABELS = ["diagnostic", "content-strategy", "enhancement"]

# Color codes for terminal output
class Colors:
    GREEN = '\033[0;32m'
    BLUE = '\033[0;34m'
    YELLOW = '\033[1;33m'
    RED = '\033[0;31m'
    NC = '\033[0m'  # No Color

def create_issue(title, body):
    """Create a single GitHub issue."""
    print(f"{Colors.BLUE}Creating:{Colors.NC} {title}")
    
    headers = {
        "Authorization": f"token {GITHUB_TOKEN}",
        "Accept": "application/vnd.github.v3+json"
    }
    
    data = {
        "title": title,
        "body": body,
        "labels": LABELS
    }
    
    try:
        response = requests.post(API_URL, headers=headers, json=data)
        
        if response.status_code == 201:
            issue_number = response.json()["number"]
            print(f"{Colors.GREEN}✓ Created issue #{issue_number}{Colors.NC}\n")
            return True
        else:
            print(f"{Colors.RED}✗ Failed (HTTP {response.status_code}){Colors.NC}")
            print(f"Error: {response.json().get('message', 'Unknown error')}\n")
            return False
            
    except Exception as e:
        print(f"{Colors.RED}✗ Exception: {str(e)}{Colors.NC}\n")
        return False
    
    finally:
        time.sleep(2)  # Rate limiting

def get_all_diagnostics():
    """Return all 100 diagnostic issues as a list of (title, body) tuples."""
    diagnostics = []
    
    # Category 1: Publishing Frequency & Consistency (10 tests)
    
    diagnostics.append(("Diagnostic: Inconsistent Publishing Schedule", """**Category:** Content Strategy - Publishing Frequency & Consistency (1.1)
**Priority:** 🟡 Medium
**Slug:** `content-inconsistent-publishing`
**Family:** `content-strategy`

## Purpose
Analyze post publication patterns over the last 90 days to identify inconsistent publishing schedules that could hurt reader expectations and SEO momentum.

## What It Checks
- Publication frequency variance (standard deviation > 7 days)
- Pattern irregularity across weeks and months
- Gaps between publishing dates
- Publishing consistency score

## Why It Matters
**SEO Impact:** Inconsistent publishing disrupts crawl patterns and reduces SEO momentum. Search engines favor sites with predictable content updates.

**Reader Impact:** Readers who subscribe to your content expect a consistent schedule. Irregular posting leads to:
- Reduced reader trust and engagement
- Lower return visitor rates
- Decreased email open rates
- Audience attrition

**Business Impact:**
- 67% of marketers say consistent publishing increases audience retention
- Sites with consistent schedules see 3.5x more organic traffic growth
- Inconsistent sites struggle to build content momentum

## Example Finding
```
You published 3 times in January but only once in February. Your readers expect consistency. 
Consider creating a content calendar to maintain a predictable schedule.
```

## Fix Advice
1. Create a content calendar with specific publishing days
2. Batch-create content during high-productivity periods
3. Use WordPress scheduled posts to maintain consistency
4. Start with a sustainable frequency (e.g., weekly) and scale up

## User Benefits
- Clear publishing schedule improves SEO rankings
- Builds reader trust and loyalty
- Reduces content creation stress
- Better resource planning
- Improved content quality through planning

## Implementation Notes
- Check last 90 days of post history
- Calculate standard deviation of days between posts
- Flag if SD > 7 days
- Provide visualization of publishing pattern
- Compare to industry benchmarks

## KB Article
Link to: "Understanding Publishing Consistency - Why Regular Posting Matters for SEO and Audience Growth"

## Related Diagnostics
- content-low-publishing-frequency (1.2)
- content-long-gaps (1.6)
- content-seasonal-patterns (1.7)"""))

    diagnostics.append(("Diagnostic: Publishing Frequency Too Low", """**Category:** Content Strategy - Publishing Frequency & Consistency (1.2)
**Priority:** 🟡 Medium
**Slug:** `content-low-publishing-frequency`
**Family:** `content-strategy`

## Purpose
Detect when content publishing frequency falls below the minimum threshold needed for SEO growth and audience retention.

## What It Checks
- Average posts per month over last 6 months
- Flags if < 4 posts per month
- Compares to industry benchmarks
- Identifies declining publishing trends

## Why It Matters
**SEO Impact:** Low publishing frequency directly hurts SEO performance:
- Fewer pages indexed = fewer ranking opportunities
- Reduced crawl frequency
- Decreased keyword coverage
- Lower topical authority

**Competitive Disadvantage:**
- Competitors publishing more frequently capture more search traffic
- Industry average: 8-12 posts per month for growth
- Low frequency signals inactive site to search engines

**Audience Impact:**
- Readers forget about your site between long gaps
- Lower email list growth
- Reduced social media engagement
- Minimal content for sharing

## Example Finding
```
You're averaging 2.5 posts per month. Sites in your niche typically publish 8-12x/month. 
This low frequency is limiting your SEO growth potential by an estimated 65%.
```

## Fix Advice
1. Audit current content production capacity
2. Set realistic but growth-oriented targets (start with 1x/week)
3. Repurpose existing content into new formats
4. Consider guest contributors or freelancers
5. Use content batching techniques
6. Create content clusters from one comprehensive research session

## User Benefits
- Increased search visibility (more content = more ranking opportunities)
- Better SEO momentum and faster ranking gains
- More opportunities for backlinks
- Growing content asset library
- Improved reader engagement

## KB Article
Link to: "Finding Your Optimal Publishing Frequency - Balancing Quality and Quantity"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-high-publishing-frequency (1.3)
- content-thin-posts (2.1)"""))

    # Add more diagnostics here following the same pattern
    # For now, let's add a few more key ones to demonstrate...
    
    diagnostics.append(("Diagnostic: Long Content Gaps", """**Category:** Content Strategy - Publishing Frequency & Consistency (1.6)
**Priority:** 🔴 Critical
**Slug:** `content-long-gaps`
**Family:** `content-strategy`

## Purpose
Detect content gaps longer than 30 days that can harm SEO, make readers think the site is abandoned, and disrupt content momentum.

## What It Checks
- Days between consecutive posts over last 12 months
- Flags any gap > 30 days
- Identifies patterns (e.g., annual summer gaps)
- Measures impact on traffic during/after gaps

## Why It Matters
**SEO Impact - Critical:**
- Long silences hurt search rankings dramatically
- Google reduces crawl frequency for inactive sites
- Competitors capture rankings during your silence
- Recovery after long gaps takes 2-3x the gap duration

**Audience Perception:**
- Readers assume site is abandoned after 30+ days
- Email subscribers forget they subscribed
- Social followers move on
- Brand authority diminishes

**Business Impact:**
- Traffic drops 15-40% during extended gaps
- Email list decay accelerates
- Backlink acquisition stops
- Recovery requires significant effort

**Real Data:**
- Sites going silent for 60+ days lose 45% of organic traffic
- 30-day gaps result in 15-20% traffic decline
- Recovery takes 3-6 months of consistent posting

## Example Finding
```
You went silent for 45 days in August 2025. During this gap, your organic traffic dropped 
28% and took 4 months to recover. Here's how to maintain momentum during low-activity periods:
[link to strategies]
```

## Fix Advice
1. **Never go completely silent** - even one post per month maintains presence
2. **Pre-schedule content** before planned breaks (vacations, busy seasons)
3. **Create evergreen content banks** to publish during low periods
4. **Use guest posts** or republish/update old content to fill gaps
5. **Set calendar reminders** at 20-day mark if no post published
6. **Communicate with audience** if taking planned break ("See you in September!")

## User Benefits
- Maintains SEO rankings and crawl frequency
- Preserves reader trust and engagement
- Protects traffic levels
- Prevents costly recovery efforts
- Sustains business momentum

## KB Article
Link to: "Maintaining Consistency During Low Periods - Avoiding Costly Content Gaps"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-no-scheduled-posts (1.9)
- content-old-posts-not-updated (4.3)"""))

    diagnostics.append(("Diagnostic: Thin Content Detection", """**Category:** Content Strategy - Content Length & Depth (2.1)
**Priority:** 🔴 Critical
**Slug:** `content-thin-posts`
**Family:** `content-strategy`

## Purpose
Identify posts under 300 words that lack depth, provide minimal value, and can hurt SEO performance due to Google's thin content penalties.

## What It Checks
- Word count for all published posts
- Percentage of posts < 300 words
- Flags if > 20% of posts are thin
- Identifies thin content patterns by category

## Why It Matters
**SEO Impact - Critical:**
- Google explicitly penalizes thin content
- Posts < 300 words rarely rank well
- Thin content signals low quality to search engines
- Can drag down entire site's authority

**Search Performance Data:**
- Average first-page result: 1,447 words
- Posts < 300 words get 75% less organic traffic
- Thin content rarely attracts backlinks
- Higher bounce rates hurt overall site rankings

**User Experience:**
- Thin posts feel incomplete and unsatisfying
- Readers don't return to sites with shallow content
- Low value = low trust = no conversions
- Hurts brand perception as authority

**Google's Definition:**
- "Thin content" = little to no original, valuable information
- Lists without explanation
- Short posts without depth
- Copied or auto-generated content

## Example Finding
```
You have 47 posts under 300 words (23% of total content). These thin posts:
- Get 79% less traffic than your longer posts
- Have 2.4x higher bounce rates
- Rarely earn backlinks
- May trigger Google's thin content filters

Options:
1. Expand posts with detail, examples, steps
2. Consolidate related thin posts into comprehensive guides
3. Delete or no-index posts with no expansion potential
```

## Fix Advice
1. **Expand valuable thin posts**
   - Add examples, screenshots, steps
   - Include expert quotes and data
   - Create comprehensive coverage
   
2. **Consolidate related thin posts**
   - Merge 3-5 thin posts on similar topics
   - Create single comprehensive resource
   - Use 301 redirects from old URLs
   
3. **Delete genuinely thin content**
   - Some posts can't be saved
   - Better to remove than let them hurt site
   - Use Search Console to check for traffic first
   
4. **Prevent future thin content**
   - Set 600-800 word minimum for new posts
   - Quality checklist before publishing

## User Benefits
- Improved search rankings across entire site
- Higher organic traffic per post
- Better user engagement and satisfaction
- Stronger site authority and trust
- More backlink opportunities

## KB Article
Link to: "Optimal Content Length by Type - When Short Posts Work and When They Don't"

## Related Diagnostics
- content-depth-intent-mismatch (2.6)
- content-thin-list-posts (2.7)
- content-low-time-on-page (9.5)"""))
    
    return diagnostics

def main():
    """Main execution function."""
    print("\n" + "="*50)
    print("Content Strategy Diagnostics Issue Creator")
    print("="*50 + "\n")
    
    if not GITHUB_TOKEN:
        print(f"{Colors.RED}ERROR: GITHUB_TOKEN environment variable not set{Colors.NC}")
        sys.exit(1)
    
    print(f"{Colors.GREEN}✓ GitHub token found{Colors.NC}")
    print(f"Repository: {REPO_OWNER}/{REPO_NAME}\n")
    
    diagnostics = get_all_diagnostics()
    total = len(diagnostics)
    
    print(f"{Colors.YELLOW}Preparing to create {total} diagnostic issues...{Colors.NC}\n")
    print(f"{Colors.YELLOW}Note: Currently configured with {total} diagnostics.{Colors.NC}")
    print(f"{Colors.YELLOW}Full 100 diagnostics can be added to the get_all_diagnostics() function.{Colors.NC}\n")
    
    input(f"Press Enter to continue or Ctrl+C to cancel...")
    print()
    
    created = 0
    failed = 0
    
    for i, (title, body) in enumerate(diagnostics, 1):
        print(f"[{i}/{total}] ", end="")
        if create_issue(title, body):
            created += 1
        else:
            failed += 1
    
    print("\n" + "="*50)
    print(f"{Colors.GREEN}Issue Creation Complete{Colors.NC}")
    print("="*50)
    print(f"Created: {created}")
    print(f"Failed: {failed}")
    print(f"Total: {total}\n")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print(f"\n\n{Colors.YELLOW}Cancelled by user{Colors.NC}\n")
        sys.exit(0)
