#!/bin/bash
# Create Content Strategy Diagnostic Issues 5-15
# Batch creation with proper rate limiting

set -e

if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN not set"
    exit 1
fi

API_URL="https://api.github.com/repos/thisismyurl/wpshadow/issues"
COUNT=0

create_issue() {
    local title="$1"
    local body="$2"
    
    echo "📝 Creating: $title"
    
    response=$(curl -s -w "\n%{http_code}" -X POST "$API_URL" \
        -H "Authorization: token $GITHUB_TOKEN" \
        -H "Accept: application/vnd.github.v3+json" \
        -d "$(jq -n --arg title "$title" --arg body "$body" \
            '{title: $title, body: $body, labels: ["diagnostic", "content-strategy", "enhancement"]}')")
    
    http_code=$(echo "$response" | tail -n1)
    
    if [ "$http_code" = "201" ]; then
        issue_num=$(echo "$response" | head -n-1 | jq -r '.number')
        echo "✅ Created #$issue_num"
        COUNT=$((COUNT + 1))
    else
        echo "❌ Failed (HTTP $http_code)"
    fi
    
    sleep 3
}

# Issue 5: No Scheduled Future Content
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

## KB Article
Link to: \"Content Batching and Scheduling - Working Smarter, Not Harder\"

## Related Diagnostics
- content-inconsistent-publishing (1.1)
- content-long-gaps (1.6)"

# Issue 6: No Content Update Strategy
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

## KB Article
Link to: \"Content Update Strategy - The Overlooked Traffic Multiplier\"

## Related Diagnostics
- content-old-posts-not-updated (4.3)
- content-outdated-statistics (4.1)
- content-high-traffic-needs-refresh (4.9)"

# Issue 7: Excessively Long Posts Without Structure  
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

## KB Article
Link to: \"Structuring Long-Form Content - Making Comprehensive Posts Readable\"

## Related Diagnostics
- content-walls-of-text (3.6)
- content-no-toc (3.15)
- content-no-subheadings (3.11)"

# Issue 8: Inconsistent Content Depth
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

## KB Article
Link to: \"Creating Content Tiers - Strategic Depth for Every Purpose\"

## Related Diagnostics
- content-thin-posts (2.1)
- content-depth-intent-mismatch (2.6)
- content-single-type-dominance (8.1)"

# Issue 9: No Long-Form Content
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

## KB Article
Link to: \"Benefits of Long-Form Content - Why Comprehensive Beats Brief\"

## Related Diagnostics
- content-no-pillar-posts (2.9)
- content-no-clusters (2.8)
- content-depth-intent-mismatch (2.6)"

# Issue 10: No Short-Form Content
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

## KB Article
Link to: \"Mixing Content Lengths - Strategic Use of Short-Form\"

## Related Diagnostics
- content-inconsistent-depth (2.3)
- content-thin-posts (2.1)
- content-high-publishing-frequency (1.3)"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Batch Complete!"
echo "Created: $COUNT issues"
echo "Issues 5-10 complete"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
