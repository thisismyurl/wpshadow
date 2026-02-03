#!/bin/bash
# Create GitHub Issues for Content Strategy Diagnostics - Batch 1
# Issues 1-15: Publishing Frequency & Consistency + Content Length & Depth

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "======================================"
echo "Content Strategy Diagnostics - Batch 1"
echo "Issues 1-15 (Publishing + Length/Depth)"
echo "======================================"
echo ""

if [ -z "$GITHUB_TOKEN" ]; then
    echo -e "${RED}ERROR: GITHUB_TOKEN environment variable not set${NC}"
    exit 1
fi

REPO="thisismyurl/wpshadow"
API_URL="https://api.github.com/repos/$REPO/issues"
CREATED_COUNT=0

create_issue() {
    local title="$1"
    local body="$2"
    
    echo -e "${BLUE}Creating:${NC} $title"
    
    # Escape quotes and newlines for JSON
    body_json=$(echo "$body" | jq -Rs .)
    title_json=$(echo "$title" | jq -Rs .)
    
    response=$(curl -s -w "\n%{http_code}" -X POST "$API_URL" \
        -H "Authorization: token $GITHUB_TOKEN" \
        -H "Accept: application/vnd.github.v3+json" \
        -d "{\"title\":$title_json,\"body\":$body_json,\"labels\":[\"diagnostic\",\"content-strategy\",\"enhancement\"]}")
    
    http_code=$(echo "$response" | tail -n1)
    
    if [ "$http_code" = "201" ]; then
        CREATED_COUNT=$((CREATED_COUNT + 1))
        issue_number=$(echo "$response" | head -n-1 | jq -r '.number')
        echo -e "${GREEN}✓ Created issue #$issue_number${NC}"
    else
        echo -e "${RED}✗ Failed (HTTP $http_code)${NC}"
        echo "$response" | head -n-1 | jq -r '.message // .errors[0].message // "Unknown error"'
    fi
    
    sleep 2  # Rate limiting
    echo ""
}

# Issue 1
create_issue "Diagnostic: Inconsistent Publishing Schedule" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.1)
**Priority:** 🟡 Medium
**Slug:** \`content-inconsistent-publishing\`
**Family:** \`content-strategy\`

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
\`\`\`
You published 3 times in January but only once in February. Your readers expect consistency. 
Consider creating a content calendar to maintain a predictable schedule.
\`\`\`

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

## KB Article
Link to: \"Understanding Publishing Consistency - Why Regular Posting Matters for SEO and Audience Growth\"

## Related Diagnostics
- content-low-publishing-frequency (1.2)
- content-long-gaps (1.6)
- content-seasonal-patterns (1.7)"

# Issue 2
create_issue "Diagnostic: Publishing Frequency Too Low" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.2)
**Priority:** 🟡 Medium
**Slug:** \`content-low-publishing-frequency\`
**Family:** \`content-strategy\`

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
\`\`\`
You're averaging 2.5 posts per month. Sites in your niche typically publish 8-12x/month. 
This low frequency is limiting your SEO growth potential by an estimated 65%.
\`\`\`

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
Link to: \"Finding Your Optimal Publishing Frequency - Balancing Quality and Quantity\"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-high-publishing-frequency (1.3)
- content-thin-posts (2.1)"

# Issue 3
create_issue "Diagnostic: Publishing Frequency Too High (Burnout Risk)" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.3)
**Priority:** 🟢 Low
**Slug:** \`content-high-publishing-frequency\`
**Family:** \`content-strategy\`

## Purpose
Identify unsustainable publishing patterns that risk author burnout and often result in declining content quality.

## What It Checks
- Posts per day averaged over 30 days
- Flags if consistently > 3 posts per day
- Analyzes content length trends (decreasing = warning sign)
- Checks for quality decline indicators

## Why It Matters
**Quality Impact:** Publishing too frequently often leads to:
- Shorter, less comprehensive posts
- Increased errors and typos
- Reduced research depth
- Copy-paste or thin content
- Lower engagement metrics

**Reader Fatigue:**
- Audiences can't keep up with high-volume posting
- Email fatigue (if sending notifications per post)
- RSS feed overwhelm
- Decreased per-post engagement

**Sustainability Issues:**
- Author burnout risk
- Unsustainable pace leads to crashes (zero posts for weeks)
- Quality suffers, hurting brand reputation
- Diminishing returns on SEO value per post

## Example Finding
\`\`\`
You're publishing 5 posts per day. While volume is good, your average post length has 
decreased from 1,200 words to 400 words, and engagement per post has dropped 68%. 
Consider consolidating into fewer, higher-quality posts.
\`\`\`

## Fix Advice
1. Consolidate related short posts into comprehensive guides
2. Prioritize quality over quantity
3. Create sustainable publishing schedule (2-3x/week often better than daily)
4. Use content depth instead of content volume strategy
5. Invest time saved into better research and visuals

## User Benefits
- Higher quality content that ranks better
- Improved reader engagement per post
- Sustainable publishing pace
- Better work-life balance
- Stronger brand authority

## KB Article
Link to: \"Quality vs Quantity in Content Strategy - Finding the Sweet Spot\"

## Related Diagnostics
- content-thin-posts (2.1)
- content-inconsistent-depth (2.3)
- content-low-time-on-page (9.5)"

echo ""
echo -e "${GREEN}======================================"
echo "Batch 1 started"
echo "Creating issues 1-15..."
echo -e "======================================${NC}"
echo ""
echo "Total created so far: $CREATED_COUNT"
echo ""
echo -e "${YELLOW}This is just the first 3 issues as a test."
echo -e "To continue, expand the script with remaining issues.${NC}"
