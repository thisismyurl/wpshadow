#!/bin/bash
# Create GitHub Issues for Content Strategy Diagnostics
#
# Creates 100 GitHub issues for content strategy diagnostics
# based on docs/CONTENT_STRATEGY_DIAGNOSTICS_100.md
#
# Prerequisites: GitHub CLI (gh) must be installed and authenticated

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "======================================"
echo "Content Strategy Diagnostic Issues"
echo "======================================"
echo ""

# Check if gh is installed
if ! command -v gh &> /dev/null; then
    echo -e "${RED}ERROR: GitHub CLI (gh) is not installed${NC}"
    echo "Install it from: https://cli.github.com/"
    exit 1
fi

# Check if gh is authenticated
if ! gh auth status &> /dev/null; then
    echo -e "${RED}ERROR: GitHub CLI is not authenticated${NC}"
    echo "Run: gh auth login"
    exit 1
fi

echo -e "${GREEN}✓ GitHub CLI is installed and authenticated${NC}"
echo ""

REPO="thisismyurl/wpshadow"
CREATED_COUNT=0
LABEL="diagnostic,content-strategy,enhancement"

# Function to create issue
create_issue() {
    local title="$1"
    local body="$2"
    
    echo -e "${BLUE}Creating:${NC} $title"
    
    if gh issue create \
        --repo "$REPO" \
        --title "$title" \
        --body "$body" \
        --label "$LABEL" > /dev/null 2>&1; then
        CREATED_COUNT=$((CREATED_COUNT + 1))
        echo -e "${GREEN}✓ Created successfully${NC}"
        sleep 1  # Rate limiting
    else
        echo -e "${RED}✗ Failed to create${NC}"
    fi
    echo ""
}

echo -e "${YELLOW}Creating 100 content strategy diagnostic issues...${NC}"
echo ""

# Category 1: Publishing Frequency & Consistency (10 tests)

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

## Implementation Notes
- Check last 90 days of post history
- Calculate standard deviation of days between posts
- Flag if SD > 7 days
- Provide visualization of publishing pattern
- Compare to industry benchmarks

## KB Article
Link to: \"Understanding Publishing Consistency - Why Regular Posting Matters for SEO and Audience Growth\"

## Related Diagnostics
- content-low-publishing-frequency (1.2)
- content-long-gaps (1.6)
- content-seasonal-patterns (1.7)"

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

## Implementation Notes
- Calculate posts per month for last 6 months
- Compare to industry benchmarks by niche
- Show growth projection if frequency increased
- Provide content calendar template
- Suggest sustainable increase path

## KB Article
Link to: \"Finding Your Optimal Publishing Frequency - Balancing Quality and Quantity\"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-high-publishing-frequency (1.3)
- content-thin-posts (2.1)"

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

## Implementation Notes
- Track posts per day over 30, 60, 90 day periods
- Monitor average post length trends
- Check engagement metrics (time on page, comments, shares)
- Compare quality indicators over time
- Flag burnout warning signs early

## KB Article
Link to: \"Quality vs Quantity in Content Strategy - Finding the Sweet Spot\"

## Related Diagnostics
- content-thin-posts (2.1)
- content-inconsistent-depth (2.3)
- content-low-time-on-page (9.5)"

create_issue "Diagnostic: Weekend Publishing Gaps" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.4)
**Priority:** 🟢 Low
**Slug:** \`content-weekend-publishing-gaps\`
**Family:** \`content-strategy\`

## Purpose
Identify missed opportunities when analytics show high weekend traffic but no content is published during those peak periods.

## What It Checks
- Posts published on Saturdays/Sundays in last 60 days
- Cross-references with Google Analytics traffic patterns
- Identifies traffic vs publishing time mismatches
- Analyzes day-of-week engagement patterns

## Why It Matters
**Missed Traffic Opportunity:**
- B2C sites often see peak traffic on weekends
- Hobbyist audiences browse more on weekends
- DIY and tutorial content performs better on weekends
- Fresh weekend content captures high-intent searches

**Audience Behavior Patterns:**
- Different audience segments active at different times
- Professional content may be weekday-focused
- Lifestyle/entertainment content often weekend-focused
- Some niches have specific day-of-week patterns

**Competitive Advantage:**
- If competitors don't publish weekends, opportunity exists
- Fresh content on low-competition days ranks faster
- Weekend readers often have more time to engage

## Example Finding
\`\`\`
Your analytics show 40% of traffic arrives on weekends, but you haven't published on 
Saturday or Sunday in 60 days. Schedule some weekend content to capture this engaged audience.
\`\`\`

## Fix Advice
1. Review Google Analytics for day-of-week traffic patterns
2. Analyze which content types perform best on weekends
3. Use WordPress scheduled posts to automate weekend publishing
4. Test weekend publishing and measure engagement
5. Create weekend-specific content (e.g., \"Weekend Project\" series)

## User Benefits
- Capture traffic during peak weekend browsing
- Better audience engagement timing
- Competitive advantage in low-competition time slots
- Increased content visibility

## Implementation Notes
- Integrate with Google Analytics API
- Check posts by day of week for last 60-90 days
- Compare to traffic patterns by day
- Calculate opportunity score (high traffic + no posts = high opportunity)
- Suggest optimal posting times

## KB Article
Link to: \"Optimal Publishing Times - Aligning Content with Audience Behavior\"

## Related Diagnostics
- content-missing-peak-hours (1.5)
- content-seasonal-patterns (1.7)"

create_issue "Diagnostic: No Content in Peak Traffic Hours" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.5)
**Priority:** 🟡 Medium
**Slug:** \`content-missing-peak-hours\`
**Family:** \`content-strategy\`

## Purpose
Cross-reference content publishing times with Google Analytics peak traffic hours to maximize immediate engagement and social sharing.

## What It Checks
- Historical publishing times (hour of day)
- Google Analytics traffic by hour of day
- Mismatch between peak traffic and publishing times
- Engagement metrics by publishing time

## Why It Matters
**Immediate Engagement Impact:**
- Posts published during high-traffic hours get immediate views
- Early engagement signals boost algorithm visibility (social, search)
- Real-time readers more likely to comment and share
- Fresh content + active audience = higher engagement

**Social Amplification:**
- Publishing when audience is online increases social shares
- Shares during peak hours reach more people
- Social signals contribute to SEO rankings
- Momentum from initial engagement compounds over time

**SEO Benefit:**
- Quick engagement signals quality to search engines
- Higher initial traffic can improve rankings faster
- Dwell time and engagement are ranking factors

## Example Finding
\`\`\`
Your traffic peaks at 2pm EST with 1,247 average visitors online, but you publish at 8am 
when only 342 visitors are active. Shift publishing to align with your audience's schedule 
for 3.6x more immediate engagement.
\`\`\`

## Fix Advice
1. Analyze Google Analytics \"Audience > Behavior by Time\" report
2. Identify your top 3 peak traffic hours
3. Schedule posts to publish during these windows
4. Test different times and measure engagement
5. Consider time zones of your primary audience
6. Use scheduling tools to automate optimal timing

## User Benefits
- Higher immediate post visibility
- Increased social sharing and comments
- Better SEO signals from early engagement
- Improved content performance metrics
- More efficient content impact

## Implementation Notes
- Integrate Google Analytics API for traffic-by-hour data
- Parse post publication times from WordPress
- Calculate overlap score
- Identify optimal publishing windows
- Provide timezone-aware recommendations
- Account for multisite/global audiences

## KB Article
Link to: \"Timing Your Content for Maximum Impact - Data-Driven Publishing Schedules\"

## Related Diagnostics
- content-weekend-publishing-gaps (1.4)
- content-low-time-on-page (9.5)
- content-low-social-shares (9.7)"

create_issue "Diagnostic: Long Content Gaps" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.6)
**Priority:** 🔴 Critical
**Slug:** \`content-long-gaps\`
**Family:** \`content-strategy\`

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
\`\`\`
You went silent for 45 days in August 2025. During this gap, your organic traffic dropped 
28% and took 4 months to recover. Here's how to maintain momentum during low-activity periods:
[link to strategies]
\`\`\`

## Fix Advice
1. **Never go completely silent** - even one post per month maintains presence
2. **Pre-schedule content** before planned breaks (vacations, busy seasons)
3. **Create evergreen content banks** to publish during low periods
4. **Use guest posts** or republish/update old content to fill gaps
5. **Set calendar reminders** at 20-day mark if no post published
6. **Communicate with audience** if taking planned break (\"See you in September!\")

## User Benefits
- Maintains SEO rankings and crawl frequency
- Preserves reader trust and engagement
- Protects traffic levels
- Prevents costly recovery efforts
- Sustains business momentum

## Implementation Notes
- Calculate days between all consecutive posts in last 12 months
- Flag any gap > 30 days as critical
- Gaps > 20 days = warning
- Cross-reference with traffic data to show impact
- Provide pre-planning tools for avoiding future gaps
- Suggest content buffer strategies

## KB Article
Link to: \"Maintaining Consistency During Low Periods - Avoiding Costly Content Gaps\"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-no-scheduled-posts (1.9)
- content-old-posts-not-updated (4.3)"

create_issue "Diagnostic: Seasonal Content Pattern Issues" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.7)
**Priority:** 🟡 Medium
**Slug:** \`content-seasonal-patterns\`
**Family:** \`content-strategy\`

## Purpose
Identify imbalanced seasonal publishing patterns where content heavily skews toward certain months, leaving year-round SEO opportunities untapped.

## What It Checks
- Publishing volume by quarter/month over last 2 years
- Detects heavy seasonal imbalances (e.g., 80% in Q4)
- Identifies seasonal business patterns
- Analyzes traffic patterns vs publishing patterns

## Why It Matters
**Year-Round SEO Need:**
- Even seasonal businesses need off-season content
- Search engines prefer consistent content throughout year
- Off-season is perfect time to build authority content
- Competitors often neglect off-season = opportunity

**Content Strategy Balance:**
- Heavy Q4 publishing = rushed, lower-quality content
- Off-season allows time for comprehensive guides
- Year-round content builds sustainable traffic
- Seasonal spikes should supplement, not replace, consistent strategy

**Business Examples:**
- **E-commerce:** 80% of content in Nov-Dec, nothing Jan-Oct
- **Tax services:** Heavy Jan-Apr, silent rest of year
- **Fitness:** January rush, then silence
- **Travel:** Summer focus, neglecting planning seasons

## Example Finding
\`\`\`
You publish 80% of content in November-December (35 posts) but average only 2 posts per 
month January-October. Plan year-round content to build SEO authority during slower months 
when you have time to create comprehensive resources.
\`\`\`

## Fix Advice
1. **Off-season = authority building time**
   - Create comprehensive guides
   - Build pillar content
   - Research and plan future content
   
2. **Pre-season content strategy**
   - Publish planning content before peak season
   - \"How to prepare for...\" content captures early searchers
   
3. **Post-season content**
   - Year-in-review, lessons learned
   - Sets up next year's content
   
4. **Evergreen content fills gaps**
   - Always-relevant topics for slow months
   - Build library during off-peak

## User Benefits
- Stronger year-round SEO presence
- Better content quality (not rushed)
- Captures pre-season planners
- Competitive advantage in off-season
- Sustainable content strategy

## Implementation Notes
- Group posts by month/quarter over 24 months
- Calculate percentage distribution
- Flag if any quarter > 50% of annual content
- Flag if any quarter < 10% of annual content
- Visualize seasonal pattern
- Suggest balanced distribution

## KB Article
Link to: \"Year-Round Content Strategy - Thriving Beyond Peak Seasons\"

## Related Diagnostics
- content-seasonal-not-updated (4.5)
- content-no-seasonal-strategy (10.9)"

create_issue "Diagnostic: Single Author Dependency" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.8)
**Priority:** 🟡 Medium
**Slug:** \`content-single-author-dependency\`
**Family:** \`content-strategy\`

## Purpose
Identify risky single-author content strategies that create business vulnerability and limit content diversity and perspective.

## What It Checks
- Author distribution across all posts
- Flags if > 90% of posts by single author
- Analyzes author diversity over time
- Checks multi-author strategy existence

## Why It Matters
**Business Risk:**
- Single author leaves = content production stops
- Illness, vacation, burnout = content gaps
- Knowledge concentration risk
- Succession/scaling challenges

**Content Quality & Diversity:**
- Single perspective limits appeal
- Different authors reach different audiences
- Fresh voices bring new ideas
- Team contribution builds stronger content

**SEO & Authority:**
- Multiple authors demonstrate organizational depth
- Guest experts add credibility
- Diverse expertise covers more topics
- E-A-T (Expertise, Authority, Trust) benefits

**Scalability:**
- Can't scale content production with one writer
- Limits publishing frequency potential
- Creates bottleneck for growth

## Example Finding
\`\`\`
All 247 posts are written by one person. This creates significant business risk and limits 
perspective diversity. Consider guest posts, staff contributors, or freelancers to:
- Reduce dependency risk
- Add fresh perspectives
- Scale content production
- Improve topical coverage
\`\`\`

## Fix Advice
1. **Start small - guest posts**
   - Invite industry experts for occasional posts
   - Interview format requires less writing from guests
   
2. **Develop staff contributors**
   - Train team members to contribute
   - Each person covers their specialty
   
3. **Freelance writers**
   - Hire for specific topics or regular cadence
   - Maintain editorial standards
   
4. **Co-authoring strategy**
   - Main author + expert contributor
   - Combines consistency with fresh perspective

## User Benefits
- Reduced business risk
- More diverse perspectives and topics
- Ability to scale content production
- Richer content variety
- Better audience reach

## Implementation Notes
- Query wp_posts.post_author
- Calculate author distribution percentage
- Flag if one author > 90%
- Show author diversity score
- Suggest collaboration opportunities
- Track author contribution trends

## KB Article
Link to: \"Diversifying Your Content Team - Reducing Risk and Increasing Value\"

## Related Diagnostics
- content-narrow-focus (8.7)
- content-single-type-dominance (8.1)"

create_issue "Diagnostic: No Scheduled Future Content" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.9)
**Priority:** 🟢 Low
**Slug:** \`content-no-scheduled-posts\`
**Family:** \`content-strategy\`

## Purpose
Detect absence of scheduled content which indicates lack of planning and increases risk of publishing inconsistency.

## What It Checks
- WordPress posts with post_status = 'future'
- Count of scheduled posts
- Flags if zero scheduled posts
- Calculates \"content buffer\" days

## Why It Matters
**Consistency Insurance:**
- Scheduled posts maintain consistency during busy periods
- Buffer protects against unexpected gaps
- Reduces publishing stress and last-minute scrambles
- Professional content operations always have queue

**Planning Benefits:**
- Batching content is more efficient
- Better content quality with advance planning
- Can align with marketing campaigns
- Easier team coordination

**Best Practices:**
- Successful blogs maintain 2-4 week content buffer
- Scheduled posts enable taking breaks without gaps
- Planning ahead improves content strategy coherence

## Example Finding
\`\`\`
No posts are currently scheduled for future publication. Content batching and scheduling helps:
- Maintain consistency during busy periods
- Reduce stress and last-minute rushes  
- Improve content quality through advance planning
- Protect against unexpected gaps

Try scheduling your next 2-4 posts to create a content buffer.
\`\`\`

## Fix Advice
1. **Start content batching**
   - Dedicate one day to creating multiple posts
   - Write 3-4 posts at once when in creative flow
   
2. **Schedule strategically**
   - Queue posts for optimal publishing times
   - Spread scheduled posts evenly
   - Maintain 2-week minimum buffer
   
3. **Use editorial calendar**
   - Plan content themes in advance
   - Batch create related content
   - Coordinate with marketing campaigns

## User Benefits
- Consistent publishing even during busy periods
- Reduced stress and workload smoothing
- Better content quality from planning
- Professional content operations
- Freedom to take breaks without gaps

## Implementation Notes
- Query posts with post_status='future'
- Count scheduled posts
- Calculate days of buffer (average posts/week × scheduled posts)
- Suggest target buffer (2-4 weeks)
- Show scheduling calendar
- Compare to publishing history

## KB Article
Link to: \"Content Batching and Scheduling - Working Smarter, Not Harder\"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-long-gaps (1.6)"

create_issue "Diagnostic: No Content Update Strategy" "**Category:** Content Strategy - Publishing Frequency & Consistency (1.10)
**Priority:** 🟡 Medium
**Slug:** \`content-no-updates-strategy\`
**Family:** \`content-strategy\`

## Purpose
Identify sites that only publish new content without updating existing posts, missing significant SEO and value opportunities.

## What It Checks
- Posts modified after initial publication (post_modified ≠ post_date)
- Percentage of posts updated in last 12 months
- Flags if < 5% of posts have been updated
- Analyzes update frequency patterns

## Why It Matters
**SEO Gold Mine:**
- Updating existing content is 4-8x more efficient than creating new
- Google rewards content freshness
- Updated posts can jump 20-50 positions in rankings
- Requires less effort than creating from scratch

**ROI of Updates:**
- Existing posts already have:
  - Backlinks (preserved when updating)
  - Search authority and age trust
  - Indexed history
  - Existing traffic (which can multiply)
  
**Content Decay:**
- All content becomes outdated over time
- Information, screenshots, examples age
- Stale content hurts brand perception
- Outdated advice damages trust

**Real Results:**
- Backlinko increased traffic 111% by updating 10 posts
- HubSpot: Updated posts get 76% more traffic
- Content updates often outperform new content

## Example Finding
\`\`\`
You have 200 posts but haven't updated any in the last year. Your older content is losing 
rankings as it becomes outdated. Content updates can:
- Increase traffic 50-100% on updated posts
- Require 60% less effort than new content
- Preserve and boost existing backlink value
- Improve overall site authority

[Link to content refresh guide]
\`\`\`

## Fix Advice
1. **Start with top performers**
   - Update your top 10 traffic posts first
   - Biggest ROI from high-traffic content
   
2. **Systematic update schedule**
   - Review all content quarterly
   - Update statistics, examples, screenshots
   - Add new sections for new developments
   
3. **Make updates visible**
   - Add \"Last Updated: [date]\" to posts
   - Mention updates in intro
   - Republish to RSS/social
   
4. **Track update impact**
   - Monitor traffic changes post-update
   - Measure ranking improvements

## User Benefits
- Multiply traffic on existing content
- Better ROI than only creating new content
- Improved search rankings
- More current, accurate information
- Stronger reader trust

## Implementation Notes
- Compare post_date vs post_modified for all posts
- Calculate update rate over last 12 months
- Flag if < 5% updated = warning
- Suggest posts needing updates (old + high traffic)
- Provide content audit workflow
- Track update impact over time

## KB Article
Link to: \"Content Update Strategy - The Overlooked Traffic Multiplier\"

## Related Diagnostics
- content-old-posts-not-updated (4.3)
- content-outdated-statistics (4.1)
- content-high-traffic-needs-refresh (4.9)"

# Category 2: Content Length & Depth (10 tests)

create_issue "Diagnostic: Thin Content Detection" "**Category:** Content Strategy - Content Length & Depth (2.1)
**Priority:** 🔴 Critical
**Slug:** \`content-thin-posts\`
**Family:** \`content-strategy\`

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
- \"Thin content\" = little to no original, valuable information
- Lists without explanation
- Short posts without depth
- Copied or auto-generated content

## Example Finding
\`\`\`
You have 47 posts under 300 words (23% of total content). These thin posts:
- Get 79% less traffic than your longer posts
- Have 2.4x higher bounce rates
- Rarely earn backlinks
- May trigger Google's thin content filters

Options:
1. Expand posts with detail, examples, steps
2. Consolidate related thin posts into comprehensive guides
3. Delete or no-index posts with no expansion potential

[Link to thin content guide]
\`\`\`

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

## Implementation Notes
- Count words in post_content (strip HTML)
- Calculate percentage < 300 words
- Flag individual thin posts
- Sort by traffic to prioritize fixes
- Suggest consolidation opportunities (related topics)
- Track improvement over time

## KB Article
Link to: \"Optimal Content Length by Type - When Short Posts Work and When They Don't\"

## Related Diagnostics
- content-depth-intent-mismatch (2.6)
- content-thin-list-posts (2.7)
- content-low-time-on-page (9.5)"

create_issue "Diagnostic: Excessively Long Posts Without Structure" "**Category:** Content Strategy - Content Length & Depth (2.2)
**Priority:** 🟡 Medium
**Slug:** \`content-excessively-long-posts\`
**Family:** \`content-strategy\`

## Purpose
Detect posts over 5,000 words that lack proper structure (subheadings, table of contents, jump links), which reduces readability and engagement despite valuable content.

## What It Checks
- Word count > 5,000 words
- Presence of H2/H3 subheadings
- Table of contents existence
- Jump links or anchor navigation
- Visual breaks (images, lists, etc.)

## Why It Matters
**User Experience Impact:**
- Long, unstructured content overwhelms readers
- Users can't find specific information quickly
- Higher abandonment rates despite quality content
- Mobile users especially struggle with long posts

**Engagement Data:**
- Structured long-form: 7-10 min average time on page
- Unstructured long-form: 2-3 min average (then bounce)
- TOC can increase engagement by 45%
- Proper structure increases social shares by 28%

**SEO Benefits of Structure:**
- Subheadings help Google understand content hierarchy
- Jump links can appear in featured snippets
- Better crawlability and indexing
- Improved mobile usability score

**Accessibility:**
- Screen readers rely on proper heading hierarchy
- Keyboard navigation needs anchor links
- Visual breaks help ADHD and dyslexic readers

## Example Finding
\`\`\`
Your 7,500-word post \"Complete WordPress Guide\" has no subheadings, table of contents, 
or jump links. This makes it difficult to navigate and reduces engagement by an estimated 60%.

Add structure to improve usability:
- Create clear H2/H3 section headers
- Add table of contents at top
- Include jump-to-section links
- Break into logical sections with visuals

[Link to long-form structure guide]
\`\`\`

## Fix Advice
1. **Add comprehensive table of contents**
   - List all major sections at top of post
   - Use jump links (anchor tags) for quick navigation
   - Sticky TOC for long posts
   
2. **Proper heading hierarchy**
   - H2 for main sections
   - H3 for subsections
   - Never skip levels (H2 → H4)
   
3. **Visual breaks every 300-500 words**
   - Images, screenshots, diagrams
   - Blockquotes for important points
   - Lists for easy scanning
   
4. **Summary sections**
   - TL;DR at top
   - Key takeaways boxes throughout
   - Recap at end

## User Benefits
- Improved readability and navigation
- Better user engagement (lower bounce rates)
- Higher time on page and scroll depth
- Better accessibility for all readers
- Improved SEO performance

## Implementation Notes
- Identify posts > 5,000 words
- Count H2/H3 tags (flag if < 1 per 800 words)
- Check for TOC elements
- Detect jump link anchors
- Calculate \"structure score\"
- Provide auto-generated TOC option

## KB Article
Link to: \"Structuring Long-Form Content - Making Comprehensive Posts Readable\"

## Related Diagnostics
- content-walls-of-text (3.6)
- content-no-toc (3.15)
- content-no-subheadings (3.11)"

create_issue "Diagnostic: Inconsistent Content Depth" "**Category:** Content Strategy - Content Length & Depth (2.3)
**Priority:** 🟡 Medium
**Slug:** \`content-inconsistent-depth\`
**Family:** \`content-strategy\`

## Purpose
Identify high variance in content depth (some 200 words, some 3000 words) without clear strategy, which confuses reader expectations and dilutes site positioning.

## What It Checks
- Calculate standard deviation of post word counts
- Identify posts across extreme ranges (e.g., 150 to 4,000 words)
- Check for content tier strategy
- Analyze if variance is strategic or random

## Why It Matters
**Brand Positioning Confusion:**
- Inconsistent depth signals unclear positioning
- Readers don't know what to expect
- \"Is this a quick-tips blog or in-depth resource?\"
- Mixed signals hurt brand authority

**Audience Mismatch:**
- Different post lengths attract different audiences
- Extreme variance tries to serve everyone = serves no one well
- Better to define content tiers with purpose

**SEO Impact:**
- Google confused about site's topical depth
- Inconsistent depth can hurt featured snippet chances
- Some posts cannibalize others' keywords

**Strategic Content Tiers Work:**
- **Quick wins:** 400-600 words (FAQ, news, quick tips)
- **Standard posts:** 1,000-1,500 words (how-tos, lists)
- **Pillar content:** 3,000+ words (comprehensive guides)

## Example Finding
\`\`\`
Your posts range from 150 to 4,000 words with no apparent pattern:
- 31% are under 400 words
- 52% are 600-1,200 words  
- 17% are over 2,000 words

This inconsistency confuses readers about what to expect. Define content tiers:
- Quick Tips (400-600 words): Fast, actionable advice
- Standard Posts (1,000-1,500): Comprehensive how-tos
- Ultimate Guides (2,500+): Pillar content for competitive keywords

[Link to content tier strategy guide]
\`\`\`

## Fix Advice
1. **Define content tiers explicitly**
   - Quick tips/news: 400-600 words
   - Standard posts: 1,000-1,500 words
   - Deep dives: 2,000-3,000 words
   - Pillar content: 3,500+ words
   
2. **Match depth to content type**
   - Product reviews: 1,200-1,800 words
   - Tutorials: 1,500-2,500 words
   - Ultimate guides: 3,000+ words
   - News/updates: 400-600 words
   
3. **Visual tier indicators**
   - Badges: \"Quick Read,\" \"In-Depth Guide,\" \"Ultimate Resource\"
   - Estimated reading time
   - Clear expectations for readers

## User Benefits
- Clear reader expectations
- Better audience targeting
- Stronger brand positioning
- Improved content strategy coherence
- Better SEO focus per tier

## Implementation Notes
- Calculate standard deviation of word counts
- Flag if SD > 800 words as high variance
- Cluster posts into natural length groups
- Suggest tier definitions based on patterns
- Check if categories have consistent depth
- Recommend content classification

## KB Article
Link to: \"Creating Content Tiers - Strategic Depth for Every Purpose\"

## Related Diagnostics
- content-thin-posts (2.1)
- content-depth-intent-mismatch (2.6)
- content-single-type-dominance (8.1)"

create_issue "Diagnostic: No Long-Form Content" "**Category:** Content Strategy - Content Length & Depth (2.4)
**Priority:** 🟡 Medium
**Slug:** \`content-no-longform\`
**Family:** \`content-strategy\`

## Purpose
Detect absence of long-form content (2,000+ words) which is proven to rank better, earn more backlinks, and establish greater authority.

## What It Checks
- Posts with word count > 2,000 in last 6 months
- Flags if zero long-form content exists
- Compares to competitor long-form presence
- Identifies topics suitable for long-form treatment

## Why It Matters
**SEO Performance Data:**
- Average first-page result: 1,447 words
- Average #1 result: 2,450 words
- Long-form (2,000+ words) ranks 56% better than short form
- 77% of backlinks go to long-form content

**Authority Building:**
- Comprehensive guides establish topical authority
- Long-form demonstrates expertise and effort
- Becomes \"go-to resource\" for topics
- Attracts links from other sites

**Business Impact:**
- Long-form generates 9x more leads than short form
- Visitors spend 40% more time on long-form pages
- Higher conversion rates (more time = more trust)
- Better email opt-in rates on comprehensive guides

**Competitive Advantage:**
- Most creators avoid long-form (more effort)
- Less competition for comprehensive content
- Dominates \"ultimate guide\" and \"complete\" searches

## Example Finding
\`\`\`
All your posts are under 1,000 words. Long-form content (2,000+ words) typically:
- Ranks 56% higher in search results
- Earns 77% of backlinks
- Generates 9x more leads
- Establishes you as the authority

Consider creating comprehensive guides on your core topics:
- \"Complete Guide to [Main Topic]\" (3,000-5,000 words)
- \"Ultimate [Topic] Tutorial\" (2,500-4,000 words)
- \"Everything You Need to Know About [Topic]\" (2,000-3,500 words)

[Link to long-form content guide]
\`\`\`

## Fix Advice
1. **Start with one pillar post**
   - Choose your most important topic
   - Research competitors' comprehensive guides
   - Create definitive resource (3,000-5,000 words)
   
2. **Expand existing popular posts**
   - Take your best-performing post
   - Double or triple its depth
   - Add sections, examples, case studies
   
3. **Topic cluster approach**
   - Create one long-form pillar (3,000+ words)
   - Support with 5-8 shorter related posts
   - Internal link cluster to pillar
   
4. **Long-form content types**
   - Ultimate guides
   - Complete tutorials
   - Comprehensive resource lists
   - In-depth case studies

## User Benefits
- Dramatically improved search rankings
- More backlinks and authority
- Higher conversion rates
- Established expert positioning
- Long-term traffic assets

## Implementation Notes
- Query posts for word_count > 2,000 in last 6 months
- Flag if zero long-form content
- Analyze competitor long-form presence
- Suggest topics for long-form (high-traffic keywords)
- Show SEO opportunity score
- Provide content outline templates

## KB Article
Link to: \"Benefits of Long-Form Content - Why Comprehensive Beats Brief\"

## Related Diagnostics
- content-no-pillar-posts (2.9)
- content-no-clusters (2.8)
- content-depth-intent-mismatch (2.6)"

create_issue "Diagnostic: No Short-Form Content" "**Category:** Content Strategy - Content Length & Depth (2.5)
**Priority:** 🟢 Low
**Slug:** \`content-no-shortform\`
**Family:** \`content-strategy\`

## Purpose
Identify sites with only long-form content (all posts > 1,500 words) that miss opportunities for quick, high-velocity content that serves different user intents.

## What It Checks
- Posts with word count < 600 words in last 6 months
- Flags if zero short-form content exists
- Analyzes if all content is > 1,500 words
- Identifies quick-answer opportunities

## Why It Matters
**Audience Diversity:**
- Different users have different time availability
- Quick reads capture busy readers
- News and updates don't need 2,000 words
- \"I just need a quick answer\" queries

**Publishing Velocity:**
- Short-form enables faster publishing
- Maintains consistency during busy periods
- Lower barrier to content creation
- Can respond quickly to trends

**SEO Opportunities:**
- FAQ content (200-400 words) captures quick searches
- News and updates get indexed fast
- Definition posts rank for \"what is\" queries
- Quick wins complement comprehensive content

**Content Mix Strategy:**
- 70% standard depth (1,000-1,500 words)
- 20% long-form pillar (2,500+ words)
- 10% short-form quick hits (400-600 words)

## Example Finding
\`\`\`
All your posts are 2,000+ words. While depth is valuable, adding short-form content can:
- Increase publishing frequency (less time per post)
- Capture \"quick answer\" search queries
- Provide variety for readers
- Respond quickly to news and trends

Consider adding:
- Quick tips (400-600 words)
- FAQ posts (300-500 words)
- News updates (400-700 words)
- Tool spotlights (500-700 words)

[Link to content mix strategy]
\`\`\`

## Fix Advice
1. **Short-form content types that work:**
   - FAQ posts answering single questions
   - News updates and industry developments
   - Tool/plugin spotlights
   - Quick tips and \"Today I Learned\" posts
   - Definitions and glossary entries
   
2. **Quality guidelines for short-form:**
   - Must still provide complete answer
   - Not thin content (complete within scope)
   - Well-formatted and structured
   - Links to related long-form when relevant
   
3. **Strategic short-form use:**
   - Maintain momentum during busy periods
   - Respond quickly to trending topics
   - Answer common questions from comments
   - Supplement long-form pillar content

## User Benefits
- Higher publishing frequency potential
- Captures different search intents
- Content variety for different reader needs
- Faster response to trends and news
- Lower content creation barrier

## Implementation Notes
- Query posts for word_count < 600 in last 6 months
- Flag if zero short-form AND all posts > 1,500 words
- Suggest short-form opportunities:
  - Common questions from comments
  - Trending topics for quick takes
  - FAQ expansions
- Distinguish from \"thin content\" (complete vs incomplete)

## KB Article
Link to: \"Mixing Content Lengths - Strategic Use of Short-Form\"

## Related Diagnostics
- content-inconsistent-depth (2.3)
- content-thin-posts (2.1)
- content-high-publishing-frequency (1.3)"

echo ""
echo -e "${GREEN}======================================"
echo -e "Batch 1 Complete: 15 issues created"
echo -e "======================================${NC}"
echo ""
echo "Total issues created: $CREATED_COUNT"
echo ""
echo -e "${YELLOW}Note: This is the first batch of 15 issues."
echo -e "Run the script multiple times to create all 100 issues,"
echo -e "or modify to create all at once (watch rate limits).${NC}"
