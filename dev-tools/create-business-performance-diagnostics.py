#!/usr/bin/env python3
"""
Generate 100 Business Performance Diagnostic Issues for WPShadow

Focus: Helping website owners improve business outcomes and ROI
Philosophy: Aligned with core commandments and CANON pillars
Measurable: All diagnostics must be testable and repeatable
"""

import subprocess
import json
from datetime import datetime

# Business Performance Diagnostic Categories
diagnostics = [
    # === CONVERSION & REVENUE (20 diagnostics) ===
    {
        "title": "Diagnostic: Missing Call-to-Action Buttons Above Fold",
        "body": """## Business Impact
Visitors can't find what to do next, leading to lost conversions.

## What We Check
- Homepage has visible CTA above fold (no scrolling needed)
- CTA uses action words ("Get Started", "Buy Now", "Contact Us")
- CTA stands out visually (contrasting colors, clear button)

## Why This Matters for Your Business
**Revenue Impact:** Sites with clear CTAs above fold convert 3x better than those without. If you get 1,000 visitors/month and convert at 2%, that's 20 customers. With a proper CTA, that could be 60 customers—triple your results.

**Real-World Example:** Like a store with the cash register at the front vs. hidden in back. Make it obvious what visitors should do next.

## How We Measure
- Scan homepage HTML for CTA elements within first 800px
- Check button text for action words
- Verify color contrast (stands out visually)
- Test on mobile and desktop viewports

## Alignment with Core Values
- **Helpful Neighbor:** Shows you exactly how to guide visitors to action
- **Everything Has KPI:** Directly measurable impact on conversion rate
- **Accessibility First:** CTAs must work with keyboard and screen readers

## Treatment Available
✅ **Auto-Fix:** We can suggest optimal CTA placement and wording based on your business type

## Family
`conversion-optimization`

## Severity
High (direct revenue impact)

## Related Resources
- [CTA Best Practices](https://wpshadow.com/kb/cta-optimization)
- [Conversion Rate Optimization Guide](https://wpshadow.com/academy/cro-basics)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Contact Information Difficult to Find",
        "body": """## Business Impact
Potential customers give up before reaching you, losing you sales opportunities.

## What We Check
- Phone number visible on every page (header or footer)
- Email address or contact form linked prominently
- Business hours displayed (if applicable)
- Contact info works on mobile devices

## Why This Matters for Your Business
**Trust & Conversions:** 78% of people check contact info before buying. Hidden contact details make you look untrustworthy or hard to do business with.

**Real-World Example:** Like a storefront with no door—people want to buy but can't figure out how to reach you.

## How We Measure
- Scan header/footer for phone/email patterns
- Check if contact page is in main navigation
- Verify click-to-call works on mobile
- Test contact form submissions

## Alignment with Core Values
- **Helpful Neighbor:** Makes it easy for customers to reach you
- **Culturally Respectful:** Works globally (phone format detection)
- **Accessibility First:** Contact methods work for all users

## Treatment Available
✅ **Auto-Fix:** Can add contact info to site-wide header/footer

## Family
`conversion-optimization`

## Severity
Medium (affects trust and conversions)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Trust Signals on Key Pages",
        "body": """## Business Impact
Visitors don't trust your site enough to buy or share information.

## What We Check
- Security badges (SSL lock icon, payment security)
- Social proof (testimonials, reviews, client logos)
- Guarantee/warranty information
- Professional credentials or certifications
- Money-back guarantee visible

## Why This Matters for Your Business
**Conversion Rate:** Trust signals increase conversions by 30-50%. Without them, visitors assume you're risky or unprofessional.

**Real-World Example:** Like a restaurant with health inspection scores vs. one without. Which would you trust?

## How We Measure
- Scan for common trust badge images
- Check for testimonial/review sections
- Look for SSL indicators
- Verify guarantee/return policy linked

## Alignment with Core Values
- **Inspire Confidence:** Builds trust with visitors
- **Advice Not Sales:** Shows honest credentials
- **Beyond Pure:** Privacy badges included

## Treatment Available
⚠️ **Guidance:** We'll show you where to add trust signals

## Family
`conversion-optimization`

## Severity
High (direct impact on sales)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Pricing Not Transparent or Hidden",
        "body": """## Business Impact
Visitors leave to find competitors with clear pricing, costing you leads.

## What We Check
- Pricing visible without requiring contact/quote
- Price comparison if multiple tiers exist
- Currency and payment terms clear
- No hidden fees in fine print

## Why This Matters for Your Business
**Lead Quality:** 70% of B2B buyers want pricing upfront. Hidden pricing wastes your time with unqualified leads and drives serious buyers to competitors.

**Real-World Example:** Like a restaurant with no menu prices. You'd probably leave and go somewhere transparent.

## How We Measure
- Scan for pricing tables or cost information
- Check if "Request Quote" is only option
- Verify currency symbols visible
- Look for hidden fee disclosures

## Alignment with Core Values
- **Advice Not Sales:** Transparent about costs
- **Helpful Neighbor:** Respects visitor's time
- **Beyond Pure:** Honest pricing practices

## Treatment Available
⚠️ **Guidance:** Best practices for displaying pricing

## Family
`conversion-optimization`

## Severity
High (affects lead quality and volume)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Checkout Process Too Complex",
        "body": """## Business Impact
Cart abandonment rate high—people start buying but don't complete purchase.

## What We Check
- Number of checkout steps (should be ≤3)
- Required form fields (fewer is better)
- Guest checkout available (no forced account creation)
- Progress indicator shows steps remaining
- Mobile checkout optimized

## Why This Matters for Your Business
**Abandoned Carts:** Average cart abandonment is 70%. Each extra form field reduces conversions by 5-10%. Simplifying checkout = more completed sales.

**Real-World Example:** Like a store making you fill out paperwork before buying groceries. People just leave their cart and go elsewhere.

## How We Measure
- Count checkout pages/steps
- Analyze required vs. optional fields
- Test guest checkout availability
- Verify mobile usability
- Check for progress indicators

## Alignment with Core Values
- **Helpful Neighbor:** Makes buying easy
- **Safe by Default:** Doesn't force unnecessary data collection
- **Accessibility First:** Works on all devices

## Treatment Available
⚠️ **Guidance:** Checkout optimization recommendations

## Family
`e-commerce-optimization`

## Severity
Critical (direct revenue loss)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Missing Urgency or Scarcity Indicators",
        "body": """## Business Impact
Visitors delay decisions indefinitely, reducing conversion rates.

## What We Check
- Limited-time offers clearly communicated
- Stock levels shown ("Only 3 left")
- Countdown timers for deadlines (if applicable)
- "Popular" or "Trending" badges
- Recent purchase notifications

## Why This Matters for Your Business
**Purchase Psychology:** Urgency increases conversions by 20-30%. Without it, people think "I'll come back later" (and never do).

**Real-World Example:** Like airline tickets showing "2 seats left at this price." It motivates action now rather than later.

## How We Measure
- Scan for urgency language patterns
- Check for countdown timers
- Look for stock level displays
- Verify social proof indicators

## Alignment with Core Values
- **Advice Not Sales:** Honest urgency (not fake scarcity)
- **Helpful Neighbor:** Helps them make timely decisions
- **Safe by Default:** No manipulative tactics

## Treatment Available
⚠️ **Guidance:** Ethical urgency implementation

## Family
`conversion-optimization`

## Severity
Medium (affects conversion timing)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Exit-Intent Strategy",
        "body": """## Business Impact
Visitors leave without any attempt to capture their interest or contact info.

## What We Check
- Exit-intent popup configured
- Special offer for leaving visitors
- Email capture for newsletter/discount
- Exit popup is mobile-friendly
- Not overly aggressive (respects user)

## Why This Matters for Your Business
**Lead Capture:** Exit-intent popups convert 2-4% of abandoning visitors. On a site with 10,000 monthly visitors, that's 200-400 captured leads you'd otherwise lose.

**Real-World Example:** Like a store clerk asking "Did you find everything?" as you head to the door. Last chance to help or capture interest.

## How We Measure
- Check for exit-intent scripts
- Test popup triggers on mouse movement
- Verify offer is compelling
- Ensure mobile compatibility
- Check dismissal is easy

## Alignment with Core Values
- **Helpful Neighbor:** Offers value, not annoyance
- **Safe by Default:** Easy to close, non-aggressive
- **Accessibility First:** Works with keyboard navigation

## Treatment Available
✅ **Auto-Setup:** Configure exit-intent with best practices

## Family
`conversion-optimization`

## Severity
Medium (recovers abandoning visitors)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Value Proposition Unclear or Missing",
        "body": """## Business Impact
Visitors don't understand what you offer or why they should choose you.

## What We Check
- Clear headline states what you do (within 5 seconds)
- Unique selling proposition (USP) above fold
- Benefits > Features emphasis
- Differentiation from competitors clear
- Works without images/video (text is clear)

## Why This Matters for Your Business
**Bounce Rate:** 88% of visitors leave if value prop is unclear. You have 5 seconds to communicate why someone should stay. Unclear = instant bounce.

**Real-World Example:** Like a store with no sign saying what they sell. People walk by because they don't know if it's for them.

## How We Measure
- Analyze headline clarity and specificity
- Check USP is in first 800px
- Verify benefits stated (not just features)
- Test readability without media

## Alignment with Core Values
- **Helpful Neighbor:** Immediately clear what you offer
- **Learning Inclusive:** Simple language anyone understands
- **Advice Not Sales:** Honest about what you provide

## Treatment Available
⚠️ **Guidance:** Value proposition framework and examples

## Family
`messaging-optimization`

## Severity
Critical (affects all conversions)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Lead Magnet or Incentive to Subscribe",
        "body": """## Business Impact
Visitors won't give you their email, missing future marketing opportunities.

## What We Check
- Free download/resource offered
- Email incentive clearly communicated (discount, guide, checklist)
- Value of incentive obvious
- Delivery automatic and immediate
- Opt-in form prominently placed

## Why This Matters for Your Business
**Email List Growth:** Lead magnets increase email signups by 300-500%. Every email is a future customer opportunity. Without incentive, you're relying on goodwill alone.

**Real-World Example:** Like offering a free sample vs. asking people to just "join our list." The sample gets 10x more takers.

## How We Measure
- Scan for lead magnet offers
- Check incentive communication clarity
- Verify automated delivery setup
- Test opt-in form visibility
- Measure value proposition strength

## Alignment with Core Values
- **Helpful Neighbor:** Gives before asking
- **Free as Possible:** Genuinely free resource
- **Drive to KB:** Educational content as magnet

## Treatment Available
⚠️ **Guidance:** Lead magnet ideas for your industry

## Family
`lead-generation`

## Severity
High (builds future revenue pipeline)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Product/Service Pages Missing Key Purchase Info",
        "body": """## Business Impact
Visitors can't make informed decisions, leading to abandoned interest and lost sales.

## What We Check
- Complete product specifications/features
- Clear pricing and payment options
- Shipping info (if physical product)
- Delivery timeline stated
- Return/refund policy linked
- Customer reviews/testimonials present

## Why This Matters for Your Business
**Purchase Confidence:** 93% of buyers read reviews before purchasing. Missing info = unanswered questions = lost sale. Complete information closes more deals.

**Real-World Example:** Like buying a car without knowing the engine size, warranty, or return policy. Most people won't commit.

## How We Measure
- Check product pages for info completeness
- Verify all key decision factors present
- Look for review sections
- Test information accessibility
- Validate policy links work

## Alignment with Core Values
- **Helpful Neighbor:** Provides all info needed to decide
- **Inspire Confidence:** Transparency builds trust
- **Accessibility First:** Info readable by all

## Treatment Available
⚠️ **Guidance:** Product page optimization checklist

## Family
`e-commerce-optimization`

## Severity
High (directly affects sales)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === ENGAGEMENT & RETENTION (15 diagnostics) ===
    {
        "title": "Diagnostic: No Internal Linking Strategy",
        "body": """## Business Impact
Visitors leave after one page instead of exploring, reducing engagement and conversions.

## What We Check
- Related content links present on posts/pages
- Average internal links per page (should be 3-5)
- Links use descriptive anchor text
- Key pages linked from multiple sources
- Broken internal links detected

## Why This Matters for Your Business
**Session Duration:** Good internal linking increases pages per session by 50-100%. More pages = more chances to convert. People who view 3+ pages are 4x more likely to convert.

**Real-World Example:** Like a bookstore where books mention related titles. You came for one, you leave with three.

## How We Measure
- Count internal links per page
- Analyze link distribution patterns
- Check for orphaned pages
- Test anchor text relevance
- Identify broken internal links

## Alignment with Core Values
- **Helpful Neighbor:** Guides to relevant content
- **Everything Has KPI:** Measurable engagement increase
- **Drive to KB:** Natural knowledge discovery

## Treatment Available
✅ **Auto-Fix:** Add contextual internal links automatically

## Family
`engagement-optimization`

## Severity
Medium (affects engagement and SEO)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Blog Posts Don't Encourage Comments or Discussion",
        "body": """## Business Impact
Missing community building and user engagement opportunities that boost loyalty.

## What We Check
- Comments enabled on blog posts
- Call-to-action to comment (question at end)
- Comment moderation setup (prevents spam)
- Replies to existing comments
- Social sharing encouraged

## Why This Matters for Your Business
**Community Value:** Engaged readers return 3x more often and spend 2x longer on site. Comments = free user-generated content that ranks in search and shows site is active.

**Real-World Example:** Like a restaurant that welcomes feedback vs. one that ignores customers. Engagement builds loyalty and trust.

## How We Measure
- Check if comments are enabled
- Analyze post endings for engagement prompts
- Count comment response rate
- Verify spam protection active
- Test comment submission process

## Alignment with Core Values
- **Helpful Neighbor:** Encourages community dialogue
- **Safe by Default:** Spam protection protects users
- **Accessibility First:** Comment forms keyboard-accessible

## Treatment Available
✅ **Auto-Setup:** Enable comments with best practices

## Family
`engagement-optimization`

## Severity
Low (long-term community building)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Content Upgrade Opportunities",
        "body": """## Business Impact
Missing chances to convert readers into email subscribers with relevant offers.

## What We Check
- Content-specific lead magnets offered
- Relevant upgrades within blog posts
- Download links clearly visible
- Automatic delivery configured
- Tracking of upgrade conversions

## Why This Matters for Your Business
**List Building:** Content upgrades convert at 10-20% vs. 2-3% for generic opt-ins. If you get 1,000 blog readers, that's 100-200 emails vs. 20-30.

**Real-World Example:** Like reading a recipe and being offered a printable grocery list. It's so relevant you gladly exchange your email.

## How We Measure
- Scan posts for upgrade offers
- Check relevance of offers to content
- Verify delivery automation
- Test opt-in process
- Track conversion rates

## Alignment with Core Values
- **Helpful Neighbor:** Offers genuinely useful extras
- **Drive to KB:** Educational resources as upgrades
- **Free as Possible:** Real value, not just email capture

## Treatment Available
✅ **Auto-Setup:** Add content upgrade blocks to posts

## Family
`lead-generation`

## Severity
Medium (grows email list strategically)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No 'Continue Reading' or Related Posts",
        "body": """## Business Impact
Visitors hit a dead end after reading, instead of discovering more valuable content.

## What We Check
- Related posts widget at article end
- 'You might also like' recommendations
- Category/tag navigation visible
- 'Next post' navigation present
- Recommendations personalized (if possible)

## Why This Matters for Your Business
**Engagement Multiplier:** Related content increases pages/session by 40%. Each extra page view = another conversion opportunity and stronger SEO signals.

**Real-World Example:** Like Netflix showing 'Because you watched...' suggestions. Keeps people engaged instead of leaving.

## How We Measure
- Check for related content widgets
- Verify recommendations are relevant
- Count average recommendations shown
- Test navigation functionality
- Analyze click-through rates

## Alignment with Core Values
- **Helpful Neighbor:** Helps discover relevant content
- **Learning Inclusive:** Guided content discovery
- **Everything Has KPI:** Measurable engagement impact

## Treatment Available
✅ **Auto-Fix:** Add related posts automatically

## Family
`engagement-optimization`

## Severity
Medium (affects session depth)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Videos Lack Engagement Features",
        "body": """## Business Impact
Video content doesn't drive desired actions or conversions.

## What We Check
- CTAs within or after video
- Chapters/timestamps for navigation
- Transcripts available (accessibility + SEO)
- Social sharing buttons
- Related video suggestions

## Why This Matters for Your Business
**Video ROI:** 80% of users recall videos they've watched. But without CTAs, you're entertaining not converting. Videos with CTAs convert 2-3x better.

**Real-World Example:** Like watching a cooking show that never tells you where to buy the ingredients. Entertaining but not actionable.

## How We Measure
- Scan videos for CTA overlays
- Check for interactive elements
- Verify transcripts exist
- Test sharing functionality
- Analyze video completion rates

## Alignment with Core Values
- **Helpful Neighbor:** Guides action after engagement
- **Accessibility First:** Transcripts help deaf users
- **Everything Has KPI:** Video conversion tracking

## Treatment Available
⚠️ **Guidance:** Video optimization best practices

## Family
`engagement-optimization`

## Severity
Medium (maximizes video investment)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === CONTENT EFFECTIVENESS (15 diagnostics) ===
    {
        "title": "Diagnostic: Blog Posts Too Long Without Formatting",
        "body": """## Business Impact
Visitors see walls of text and bounce without reading, wasting your content investment.

## What We Check
- Paragraphs under 3-4 sentences
- Subheadings every 300-400 words
- Bullet points and numbered lists used
- Images break up text every 500 words
- White space sufficient

## Why This Matters for Your Business
**Readability = Engagement:** Poorly formatted content has 50% higher bounce rates. You spent time creating it—formatting determines if anyone reads it.

**Real-World Example:** Like a textbook vs. a magazine article. Same info, but formatting makes one readable and one exhausting.

## How We Measure
- Analyze paragraph length
- Count subheadings per 1000 words
- Check image distribution
- Measure white space ratio
- Test readability scores

## Alignment with Core Values
- **Learning Inclusive:** Makes content digestible
- **Accessibility First:** Screen readers use heading structure
- **Helpful Neighbor:** Respects reader's time

## Treatment Available
✅ **Auto-Fix:** Add formatting suggestions to posts

## Family
`content-optimization`

## Severity
Medium (affects content ROI)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Headlines Don't Follow Best Practices",
        "body": """## Business Impact
Click-through rates low—people don't click to read your content.

## What We Check
- Headline length (50-70 characters optimal)
- Numbers used (lists perform well)
- Power words included (proven, essential, ultimate)
- Question or curiosity format
- Benefit stated clearly

## Why This Matters for Your Business
**CTR Impact:** Good headlines increase clicks by 50-100%. Your content quality doesn't matter if no one clicks to read it. Headline is the #1 factor.

**Real-World Example:** Like movie trailers. Same film, better trailer = more ticket sales. Headlines are your trailer.

## How We Measure
- Analyze headline character length
- Score power word usage
- Check for numbers/lists
- Evaluate clarity and benefit
- Compare against formulas

## Alignment with Core Values
- **Helpful Neighbor:** Clear, not clickbait
- **Advice Not Sales:** Honest about content
- **Learning Inclusive:** Understandable titles

## Treatment Available
✅ **Auto-Suggest:** Headline alternatives for posts

## Family
`content-optimization`

## Severity
High (determines if content is discovered)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Content Missing Clear Target Audience",
        "body": """## Business Impact
Generic content that doesn't resonate with anyone, reducing engagement and conversions.

## What We Check
- Audience persona referenced in content
- Language appropriate for expertise level
- Problems addressed are specific
- Examples relevant to target industry
- Tone matches audience expectations

## Why This Matters for Your Business
**Conversion Rates:** Targeted content converts 2-5x better than generic. "Everyone" isn't a target audience. Specific = relatable = action.

**Real-World Example:** Like a doctor using medical jargon with patients vs. plain language. Match your audience or lose them.

## How We Measure
- Analyze reading level complexity
- Check for industry-specific terms
- Evaluate problem specificity
- Review example relevance
- Score tone appropriateness

## Alignment with Core Values
- **Helpful Neighbor:** Speaks their language
- **Learning Inclusive:** Matches their level
- **Culturally Respectful:** Appropriate for audience

## Treatment Available
⚠️ **Guidance:** Audience targeting framework

## Family
`content-optimization`

## Severity
High (determines content resonance)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Content Update Schedule or Freshness",
        "body": """## Business Impact
Outdated content signals site is inactive, hurting trust and SEO rankings.

## What We Check
- Publication dates visible
- Content reviewed/updated regularly
- Update timestamps shown
- Seasonal content refreshed
- Outdated info flagged

## Why This Matters for Your Business
**Trust & SEO:** 60% of people judge site trustworthiness by content freshness. Google also ranks fresh content higher. Old content = looks abandoned.

**Real-World Example:** Like a restaurant with last year's menu still posted. Makes you wonder if they're even open.

## How We Measure
- Check average content age
- Look for last-updated dates
- Identify content >1 year old
- Verify refresh schedule exists
- Track update frequency

## Alignment with Core Values
- **Inspire Confidence:** Current = trustworthy
- **Helpful Neighbor:** Keeps info accurate
- **Everything Has KPI:** Traffic impact measurable

## Treatment Available
⚠️ **Guidance:** Content refresh strategy

## Family
`content-maintenance`

## Severity
Medium (affects trust and SEO over time)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: FAQ Section Missing or Inadequate",
        "body": """## Business Impact
Potential customers' questions go unanswered, leading to abandoned interest.

## What We Check
- FAQ page exists and is easy to find
- Common questions comprehensively covered
- Answers are detailed and helpful
- FAQ structured with schema markup (SEO)
- Search functionality if >10 questions

## Why This Matters for Your Business
**Sales Enablement:** 50% of sales are lost to unanswered questions. FAQ pages appear in Google's featured snippets (position 0), driving massive organic traffic.

**Real-World Example:** Like having a knowledgeable salesperson vs. one who says "I don't know" to every question. FAQs close sales 24/7.

## How We Measure
- Check FAQ page existence
- Count questions covered
- Evaluate answer quality
- Verify schema markup present
- Test search/navigation

## Alignment with Core Values
- **Helpful Neighbor:** Anticipates and answers questions
- **Drive to KB:** Educational resource
- **Everything Has KPI:** Featured snippet rankings measurable

## Treatment Available
✅ **Auto-Setup:** FAQ schema and structure

## Family
`content-optimization`

## Severity
High (reduces sales friction)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === SEO & DISCOVERABILITY (15 diagnostics) ===
    {
        "title": "Diagnostic: Homepage Title Tag Not Optimized for Business",
        "body": """## Business Impact
Losing search traffic because title doesn't communicate what you do or include location.

## What We Check
- Business name + service + location in title
- Under 60 characters (not truncated)
- Keywords match what customers search
- Unique and compelling
- Matches actual business offering

## Why This Matters for Your Business
**Search Visibility:** Homepage title is the #1 ranking factor Google sees. Poor title = invisible in search. 35% of organic traffic goes to position #1.

**Real-World Example:** Like a billboard that says "Welcome" vs. "Seattle Emergency Plumber - 24/7 Service." One gets calls, one gets ignored.

## How We Measure
- Extract title tag from homepage
- Check character length
- Analyze keyword inclusion
- Verify location mention
- Score relevance to business

## Alignment with Core Values
- **Helpful Neighbor:** Makes you findable
- **Everything Has KPI:** Traffic increase measurable
- **Advice Not Sales:** Honest representation

## Treatment Available
✅ **Auto-Fix:** Suggest optimized title based on business type

## Family
`seo-optimization`

## Severity
Critical (affects all organic search)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Missing Local Business Schema Markup",
        "body": """## Business Impact
Local customers can't find your business details in search results or maps.

## What We Check
- LocalBusiness schema implemented
- NAP (Name, Address, Phone) in markup
- Business hours structured
- Service areas defined
- Reviews/ratings included

## Why This Matters for Your Business
**Local SEO:** Schema markup increases local pack appearances by 30%. Shows up with map, phone, hours directly in Google. Massive competitive advantage.

**Real-World Example:** Like Yellow Pages listing with full details vs. just your name. Complete info gets the call.

## How We Measure
- Scan for LocalBusiness schema
- Verify all fields populated
- Check markup validity
- Test rich snippet display
- Compare to competitors

## Alignment with Core Values
- **Helpful Neighbor:** Makes you discoverable locally
- **Culturally Respectful:** Location-aware
- **Everything Has KPI:** Local ranking trackable

## Treatment Available
✅ **Auto-Fix:** Add complete LocalBusiness schema

## Family
`local-seo`

## Severity
High (critical for local businesses)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Product/Service Pages Not Targeting Long-Tail Keywords",
        "body": """## Business Impact
Missing easy-to-rank opportunities that convert better and have less competition.

## What We Check
- Long-tail keywords (3-5 words) in titles
- Natural language question format
- Specific problem/solution focus
- Location modifiers used (if relevant)
- Low competition, high intent keywords

## Why This Matters for Your Business
**Easier Rankings:** Long-tail keywords have 50% less competition but convert 3x better. "Wedding photographer Seattle" beats "photographer" every time.

**Real-World Example:** Like fishing in a stocked pond vs. the ocean. Easier catches, better results.

## How We Measure
- Analyze title/heading keyword length
- Check for question-based content
- Evaluate specificity level
- Compare competition levels
- Track conversion by keyword type

## Alignment with Core Values
- **Helpful Neighbor:** Matches exact customer searches
- **Learning Inclusive:** Answers specific questions
- **Everything Has KPI:** Ranking improvements measurable

## Treatment Available
✅ **Auto-Suggest:** Long-tail keyword opportunities

## Family
`seo-optimization`

## Severity
High (quick win opportunity)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Blog Content Not Targeting Featured Snippets",
        "body": """## Business Impact
Missing 'position zero' opportunities that drive massive traffic without #1 rankings.

## What We Check
- Questions as H2/H3 headings
- Concise answers in 40-60 words
- Lists and tables used
- 'How to' step-by-step format
- Definitions clearly stated

## Why This Matters for Your Business
**Traffic Multiplier:** Featured snippets get 35% of clicks even above #1 rankings. One snippet can drive more traffic than all your other rankings combined.

**Real-World Example:** Like having your answer read aloud by Google to every searcher. Ultimate free advertising.

## How We Measure
- Scan for question headings
- Check answer formatting
- Verify list/table usage
- Analyze content structure
- Track featured snippet wins

## Alignment with Core Values
- **Helpful Neighbor:** Direct answers to questions
- **Learning Inclusive:** Clear, concise info
- **Everything Has KPI:** Snippet appearances trackable

## Treatment Available
✅ **Auto-Fix:** Reformat content for snippet optimization

## Family
`seo-optimization`

## Severity
High (massive traffic potential)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Image Alt Text Missing or Generic",
        "body": """## Business Impact
Missing image search traffic and failing accessibility standards.

## What We Check
- All images have alt text
- Alt text is descriptive (not just filename)
- Keywords naturally included
- Under 125 characters
- Actually describes image content

## Why This Matters for Your Business
**Dual Benefit:** Google Images drives 20-30% of search traffic for many sites. Plus, alt text is required for screen readers—missing it excludes disabled customers.

**Real-World Example:** Like having products in store without price tags. Both customers and Google need descriptions.

## How We Measure
- Scan all images for alt attributes
- Check alt text quality (not generic)
- Verify keyword relevance
- Test with screen readers
- Count missing alt tags

## Alignment with Core Values
- **Accessibility First:** Required for screen readers
- **Helpful Neighbor:** Describes for visually impaired
- **Everything Has KPI:** Image search traffic measurable

## Treatment Available
✅ **Auto-Fix:** Generate descriptive alt text with AI

## Family
`seo-optimization`

## Severity
High (accessibility + SEO impact)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === TRUST & CREDIBILITY (10 diagnostics) ===
    {
        "title": "Diagnostic: No Visible Customer Testimonials",
        "body": """## Business Impact
Potential customers don't see social proof, making them hesitant to buy or contact.

## What We Check
- Testimonials on homepage
- Customer reviews on product/service pages
- Real names and photos (not stock images)
- Specific results mentioned (not generic praise)
- Recent testimonials (within 6 months)

## Why This Matters for Your Business
**Trust Factor:** 92% read reviews before buying. Testimonials increase conversions by 34%. Without them, you're asking customers to trust you blindly.

**Real-World Example:** Like choosing a restaurant. You pick the one with 500 reviews over the one with zero.

## How We Measure
- Count testimonials on key pages
- Verify authenticity markers (photos, full names)
- Check recency of testimonials
- Evaluate specificity of claims
- Test testimonial visibility

## Alignment with Core Values
- **Inspire Confidence:** Real customers build trust
- **Advice Not Sales:** Honest customer experiences
- **Beyond Pure:** No fake/purchased reviews

## Treatment Available
⚠️ **Guidance:** Testimonial collection and display best practices

## Family
`trust-building`

## Severity
High (directly affects conversion trust)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: About Page Lacks Personal Connection",
        "body": """## Business Impact
Visitors can't connect with you as a person/company, reducing trust and relatability.

## What We Check
- Team photos visible
- Personal story/founder background
- Mission/values clearly stated
- Real humans, not corporate speak
- Contact information for team

## Why This Matters for Your Business
**Human Connection:** About page is often the 2nd most visited page. People buy from people, not faceless companies. Personal connection increases trust and loyalty.

**Real-World Example:** Like meeting someone at a party. Knowing their story makes you trust them vs. someone who only talks business.

## How We Measure
- Check for team photos
- Evaluate personal story depth
- Verify mission statement exists
- Score tone (human vs. corporate)
- Test contact accessibility

## Alignment with Core Values
- **Helpful Neighbor:** Shows the humans behind brand
- **Inspire Confidence:** Transparency builds trust
- **Culturally Respectful:** Diverse team representation

## Treatment Available
⚠️ **Guidance:** About page framework and examples

## Family
`trust-building`

## Severity
Medium (affects brand connection)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Privacy Policy or Terms of Service",
        "body": """## Business Impact
Legal liability and loss of customer trust; required by law in many regions.

## What We Check
- Privacy Policy page exists and is linked in footer
- Terms of Service available
- Cookie consent policy present
- Data collection practices disclosed
- GDPR/CCPA compliance (if applicable)
- Last updated date shown

## Why This Matters for Your Business
**Legal + Trust:** Required by law if you collect ANY data. Fines up to $7,500 per violation (CCPA). Plus, 40% of customers check privacy policy before sharing info.

**Real-World Example:** Like a contract when renting an apartment. No contract = nobody trusts you with their security deposit.

## How We Measure
- Scan footer for policy links
- Verify policy pages exist
- Check completeness of disclosures
- Test readability and clarity
- Validate last-updated dates

## Alignment with Core Values
- **Beyond Pure:** Privacy transparency required
- **Safe by Default:** Protects user data
- **Inspire Confidence:** Shows professionalism

## Treatment Available
✅ **Auto-Generate:** Create basic privacy policy template

## Family
`legal-compliance`

## Severity
Critical (legal requirement)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Copyright and Business Information Outdated",
        "body": """## Business Impact
Site looks abandoned or unprofessional, damaging credibility.

## What We Check
- Copyright year is current
- Business address is accurate
- Phone numbers work (not disconnected)
- Email addresses valid
- Social media links current

## Why This Matters for Your Business
**Perceived Abandonment:** Outdated copyright (©2019) makes site look neglected. 67% of users question trustworthiness of outdated sites.

**Real-World Example:** Like a store with faded signage and old hours posted. Makes you wonder if they're still in business.

## How We Measure
- Extract copyright year from footer
- Verify contact information validity
- Test phone numbers and emails
- Check social links are active
- Compare to current year

## Alignment with Core Values
- **Inspire Confidence:** Current = active business
- **Helpful Neighbor:** Correct contact info
- **Beyond Pure:** Honest representation

## Treatment Available
✅ **Auto-Fix:** Update copyright to current year

## Family
`trust-building`

## Severity
Low (perception issue, easy fix)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Industry Credentials or Certifications Displayed",
        "body": """## Business Impact
Missing authority signals that differentiate you from competitors.

## What We Check
- Professional certifications visible
- Industry association memberships shown
- Awards and recognition displayed
- Press mentions highlighted
- BBB rating or similar trust seals

## Why This Matters for Your Business
**Authority Building:** Credentials increase conversions by 15-25%. In competitive industries, they're the tie-breaker between you and competitors.

**Real-World Example:** Like a doctor's diplomas on the wall. You trust them more seeing their credentials.

## How We Measure
- Scan for certification badges
- Check for association logos
- Look for awards mentions
- Verify press coverage links
- Count trust signals

## Alignment with Core Values
- **Inspire Confidence:** Proven expertise
- **Advice Not Sales:** Earned credibility
- **Beyond Pure:** Honest credentials only

## Treatment Available
⚠️ **Guidance:** Credential display best practices

## Family
`trust-building`

## Severity
Medium (competitive differentiator)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === USER EXPERIENCE & ACCESSIBILITY (15 diagnostics) ===
    {
        "title": "Diagnostic: Forms Are Too Long or Intimidating",
        "body": """## Business Impact
Form abandonment rate high—people start filling out but don't submit.

## What We Check
- Contact forms have ≤5 fields
- Only essential information requested
- Progress indicators for multi-step forms
- Optional fields clearly marked
- Auto-fill enabled (HTML5)

## Why This Matters for Your Business
**Form Completion:** Each form field reduces submissions by 5-10%. A 10-field form vs. 5-field form = 50% fewer leads. Ask only what you absolutely need.

**Real-World Example:** Like a store asking for your life story before letting you buy. Most people just leave.

## How We Measure
- Count form fields
- Identify required vs. optional
- Check for progress indicators
- Verify auto-fill attributes
- Test mobile usability

## Alignment with Core Values
- **Helpful Neighbor:** Respects user's time
- **Safe by Default:** Minimizes data collection
- **Accessibility First:** Simple forms work for all

## Treatment Available
✅ **Auto-Fix:** Simplify forms to essential fields

## Family
`ux-optimization`

## Severity
High (affects lead generation)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Mobile Navigation Difficult to Use",
        "body": """## Business Impact
60% of traffic on mobile can't find what they need, leading to bounces and lost sales.

## What We Check
- Hamburger menu easily accessible
- Touch targets >44x44px
- Menu closes when item selected
- Search function available
- No horizontal scrolling needed

## Why This Matters for Your Business
**Mobile Majority:** 58% of all web traffic is mobile. If mobile users can't navigate, you're losing the majority of potential customers.

**Real-World Example:** Like a store where shelves are so high you can't reach products. Most shoppers leave.

## How We Measure
- Test menu on mobile devices
- Measure touch target sizes
- Verify tap functionality
- Check scroll behavior
- Test with various screen sizes

## Alignment with Core Values
- **Accessibility First:** Touch-friendly for motor disabilities
- **Helpful Neighbor:** Easy navigation for all
- **Everything Has KPI:** Mobile bounce rate measurable

## Treatment Available
✅ **Auto-Fix:** Optimize mobile menu for usability

## Family
`mobile-optimization`

## Severity
Critical (majority of traffic affected)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Search Function Missing or Ineffective",
        "body": """## Business Impact
Visitors can't find what they're looking for and leave instead of exploring.

## What We Check
- Search box visible (header or prominent placement)
- Search includes products/posts/pages
- Results are relevant and ranked
- Filters available for results
- Search works on mobile

## Why This Matters for Your Business
**Conversion Intent:** Users who search convert 2-3x more than those who don't. They know what they want—make it findable.

**Real-World Example:** Like a library without a card catalog. Books are there, but finding them is impossible.

## How We Measure
- Verify search widget exists
- Test search functionality
- Evaluate result relevance
- Check mobile accessibility
- Track search conversion rates

## Alignment with Core Values
- **Helpful Neighbor:** Helps find what they need
- **Everything Has KPI:** Search usage measurable
- **Accessibility First:** Keyboard-accessible search

## Treatment Available
✅ **Auto-Fix:** Enable search with relevance scoring

## Family
`ux-optimization`

## Severity
High (affects discoverability)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Breadcrumb Navigation",
        "body": """## Business Impact
Users get lost in site structure and can't easily navigate back up.

## What We Check
- Breadcrumbs present on all deep pages
- Schema markup for breadcrumbs
- Clickable trail back to homepage
- Visible above content
- Mobile-friendly display

## Why This Matters for Your Business
**Navigation Aid:** Breadcrumbs reduce bounce rate by 20-30% on deep pages. Shows users where they are and how to get back. Also helps SEO.

**Real-World Example:** Like trail markers in a forest. Without them, people get lost and give up.

## How We Measure
- Check for breadcrumb navigation
- Verify schema implementation
- Test click functionality
- Validate mobile display
- Check visibility/placement

## Alignment with Core Values
- **Helpful Neighbor:** Prevents user frustration
- **Learning Inclusive:** Visual site structure
- **Everything Has KPI:** Reduces deep page bounces

## Treatment Available
✅ **Auto-Fix:** Add breadcrumb navigation with schema

## Family
`ux-optimization`

## Severity
Medium (improves navigation and SEO)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Page Scrolling Behavior Confusing or Broken",
        "body": """## Business Impact
Users fight with page behavior instead of reading content smoothly.

## What We Check
- Smooth scroll enabled (not jumpy)
- Scroll-to-top button for long pages
- Fixed headers don't block content
- Infinite scroll works properly (if used)
- No scroll hijacking

## Why This Matters for Your Business
**User Frustration:** Poor scrolling increases bounce rate by 25%. Users expect natural, smooth scrolling. Fighting the page = instant leave.

**Real-World Example:** Like a book with pages that stick or skip. You stop reading even if content is good.

## How We Measure
- Test scroll behavior patterns
- Check for fixed header issues
- Verify scroll-to-top button
- Validate infinite scroll
- Test on various browsers

## Alignment with Core Values
- **Helpful Neighbor:** Natural, expected behavior
- **Accessibility First:** Smooth for motor disabilities
- **Safe by Default:** No surprising behavior

## Treatment Available
✅ **Auto-Fix:** Implement smooth scroll and scroll-to-top

## Family
`ux-optimization`

## Severity
Medium (affects reading experience)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === PERFORMANCE & SPEED (10 diagnostics) ===
    {
        "title": "Diagnostic: Homepage Loading Speed Loses Customers",
        "body": """## Business Impact
Every second of load time costs 7% of conversions—slow = expensive.

## What We Check
- Homepage loads in <3 seconds
- Largest Contentful Paint (LCP) <2.5s
- Time to Interactive (TTI) <3.5s
- Total page size <2MB
- Server response time <600ms

## Why This Matters for Your Business
**Conversion Math:** If you convert 2% of 10,000 monthly visitors = 200 customers. One extra second = 14 fewer customers/month = 168/year. Speed = money.

**Real-World Example:** Like a store door that takes 5 seconds to open. Most people walk away before it even opens.

## How We Measure
- Run Lighthouse performance test
- Measure Core Web Vitals
- Test on 3G connection
- Check time to interactive
- Monitor real user metrics

## Alignment with Core Values
- **Helpful Neighbor:** Fast = respectful of time
- **Everything Has KPI:** Direct conversion impact
- **Accessibility First:** Helps low-bandwidth users

## Treatment Available
✅ **Auto-Fix:** Apply performance optimizations

## Family
`performance-optimization`

## Severity
Critical (direct revenue impact)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Images Not Optimized for Web Performance",
        "body": """## Business Impact
Huge images slow page loads dramatically, costing conversions and hurting SEO.

## What We Check
- Images compressed (WebP or optimized JPEG)
- Responsive images (srcset) implemented
- Lazy loading enabled
- Image dimensions match display size
- Total image weight reasonable

## Why This Matters for Your Business
**Size Matters:** Images are typically 60-70% of page weight. Unoptimized images = 3-5x slower pages = lost customers. Google also penalizes slow sites.

**Real-World Example:** Like showing someone a photo album where each picture is poster-size. Unnecessary and exhausting.

## How We Measure
- Analyze image file sizes
- Check format optimization
- Verify lazy loading active
- Test responsive implementation
- Calculate total image weight

## Alignment with Core Values
- **Helpful Neighbor:** Fast loads respect users
- **Culturally Respectful:** Works on slow connections
- **Everything Has KPI:** Speed improvement measurable

## Treatment Available
✅ **Auto-Fix:** Optimize and compress images

## Family
`performance-optimization`

## Severity
High (major performance factor)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Too Many HTTP Requests Slow Page Load",
        "body": """## Business Impact
Dozens of separate resource requests create delays, making pages feel sluggish.

## What We Check
- Total HTTP requests <50
- CSS/JS files combined where possible
- Unused CSS/JS removed
- Inline critical CSS
- Defer non-critical resources

## Why This Matters for Your Business
**Request Overhead:** Each HTTP request adds 50-200ms delay. 100 requests = 5-20 seconds just in overhead. Combine and eliminate = much faster.

**Real-World Example:** Like making 50 separate trips to the store vs. one big shopping trip. Efficiency matters.

## How We Measure
- Count total HTTP requests
- Identify duplicate resources
- Check for unused code
- Verify resource combining
- Test critical CSS inlining

## Alignment with Core Values
- **Helpful Neighbor:** Respects bandwidth limits
- **Everything Has KPI:** Request reduction measurable
- **Safe by Default:** Fewer requests = fewer vulnerabilities

## Treatment Available
✅ **Auto-Fix:** Combine and minimize requests

## Family
`performance-optimization`

## Severity
Medium (technical optimization)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Third-Party Scripts Blocking Page Render",
        "body": """## Business Impact
External scripts (ads, analytics, chat widgets) delay content display, frustrating visitors.

## What We Check
- Third-party scripts loaded async/defer
- Non-critical scripts delayed until after page load
- Script loading doesn't block rendering
- Unused third-party scripts removed
- CDN used for external resources

## Why This Matters for Your Business
**Perceived Speed:** Users see blank screen while scripts load. Even 2-second delay feels like forever. Async loading = instant content display.

**Real-World Example:** Like waiting for ads to load before the article appears. Annoying and avoidable.

## How We Measure
- Identify render-blocking scripts
- Check for async/defer attributes
- Test page load timeline
- Measure time to content display
- Audit third-party script necessity

## Alignment with Core Values
- **Helpful Neighbor:** Content first, extras later
- **Beyond Pure:** No tracking delays content
- **Everything Has KPI:** Render time measurable

## Treatment Available
✅ **Auto-Fix:** Defer non-critical third-party scripts

## Family
`performance-optimization`

## Severity
High (affects perceived speed)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Mobile Page Speed Significantly Worse Than Desktop",
        "body": """## Business Impact
Mobile users (60% of traffic) experience slow, frustrating site while desktop users don't.

## What We Check
- Mobile speed within 20% of desktop
- Mobile-specific optimizations enabled
- Responsive images serving smaller sizes
- Touch targets large enough
- No desktop-only heavy resources

## Why This Matters for Your Business
**Mobile First:** 58% of traffic is mobile. If mobile is 2x slower than desktop, you're giving 58% of customers a bad experience.

**Real-World Example:** Like having a nice storefront but terrible drive-through. You're losing the majority of customers.

## How We Measure
- Compare mobile vs desktop speed scores
- Test on actual mobile devices
- Check mobile-specific issues
- Verify responsive resource serving
- Monitor mobile bounce rate

## Alignment with Core Values
- **Helpful Neighbor:** Equal experience for all devices
- **Accessibility First:** Mobile is accessibility
- **Everything Has KPI:** Mobile conversion trackable

## Treatment Available
✅ **Auto-Fix:** Apply mobile-specific optimizations

## Family
`mobile-optimization`

## Severity
Critical (majority of users affected)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === EMAIL & COMMUNICATION (10 diagnostics) ===
    {
        "title": "Diagnostic: No Email Marketing Integration",
        "body": """## Business Impact
Missing the #1 ROI marketing channel ($42 return per $1 spent).

## What We Check
- Email service provider connected
- Opt-in forms present on site
- Welcome email sequence configured
- List segmentation setup
- Email analytics tracking

## Why This Matters for Your Business
**ROI Champion:** Email marketing returns $42 for every $1 spent. Without it, you're leaving massive revenue on the table. It's the most profitable channel.

**Real-World Example:** Like owning a mailing list of interested customers but never mailing them offers. Free money left uncollected.

## How We Measure
- Check for ESP integration
- Verify opt-in forms active
- Test email automation
- Review segmentation logic
- Validate tracking setup

## Alignment with Core Values
- **Free as Possible:** Email marketing is low-cost
- **Beyond Pure:** Requires explicit opt-in
- **Everything Has KPI:** ROI directly measurable

## Treatment Available
⚠️ **Guidance:** Email marketing setup recommendations

## Family
`marketing-automation`

## Severity
High (massive revenue opportunity)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Welcome Email Sequence Not Configured",
        "body": """## Business Impact
New subscribers get no follow-up, wasting the highest-engagement window.

## What We Check
- Automated welcome email sends immediately
- 3-5 email sequence over first week
- Introduces brand and value proposition
- Clear next steps for engagement
- Unsubscribe option present

## Why This Matters for Your Business
**Peak Engagement:** Welcome emails get 4x more opens and 5x more clicks than regular emails. First week = highest engagement window. Miss it, lose them.

**Real-World Example:** Like meeting someone interested at a party then never following up. Wasted opportunity.

## How We Measure
- Check for welcome automation
- Count sequence emails
- Review email content
- Test delivery timing
- Monitor open/click rates

## Alignment with Core Values
- **Helpful Neighbor:** Warm welcome and guidance
- **Drive to Training:** Introduce resources
- **Everything Has KPI:** Engagement rates measurable

## Treatment Available
⚠️ **Guidance:** Welcome sequence templates

## Family
`marketing-automation`

## Severity
Medium (affects new subscriber engagement)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Transactional Emails Not Optimized",
        "body": """## Business Impact
Order confirmations, receipts, and shipping notices are plain text, missing upsell opportunities.

## What We Check
- Transactional emails are branded
- Include related product recommendations
- Clear next steps provided
- Track opens and clicks
- Mobile-optimized templates

## Why This Matters for Your Business
**Open Rate Gold:** Transactional emails get 80% open rates vs. 20% for marketing emails. You're sending them anyway—optimize to increase revenue.

**Real-World Example:** Like giving a receipt with a coupon vs. blank receipt. Same effort, more return.

## How We Measure
- Review transactional email templates
- Check for branding elements
- Verify recommendation engine
- Test mobile display
- Track click-through rates

## Alignment with Core Values
- **Helpful Neighbor:** Relevant recommendations
- **Advice Not Sales:** Helpful suggestions, not spam
- **Everything Has KPI:** CTR and revenue measurable

## Treatment Available
⚠️ **Guidance:** Transactional email optimization

## Family
`e-commerce-optimization`

## Severity
Medium (incremental revenue opportunity)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Abandoned Cart Email Recovery",
        "body": """## Business Impact
70% of carts are abandoned; recovering even 10% = massive revenue increase.

## What We Check
- Abandoned cart emails enabled
- 3-email sequence over 3 days
- Personalized with cart contents
- Incentive offered (discount/free shipping)
- Easy one-click return to cart

## Why This Matters for Your Business
**Revenue Recovery:** Abandoned cart emails recover 10-15% of lost sales. If you have $10,000/month in abandoned carts, that's $1,000-$1,500 recovered automatically.

**Real-World Example:** Like calling a customer who left items at register, offering free delivery. Most come back.

## How We Measure
- Check for cart abandonment tracking
- Verify email automation active
- Review email sequence
- Test cart recovery links
- Track recovery revenue

## Alignment with Core Values
- **Helpful Neighbor:** Helpful reminder, not pushy
- **Everything Has KPI:** Recovery rate measurable
- **Safe by Default:** Doesn't spam, respects frequency

## Treatment Available
✅ **Auto-Setup:** Configure abandoned cart sequence

## Family
`e-commerce-optimization`

## Severity
High (for e-commerce sites—massive ROI)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Email List Not Segmented",
        "body": """## Business Impact
Sending same message to everyone = low engagement and high unsubscribe rates.

## What We Check
- List segmented by behavior/interest
- Purchase history segmentation (if e-commerce)
- Engagement level groups (active/inactive)
- Location-based segments
- Preference center for subscribers

## Why This Matters for Your Business
**Relevance = Revenue:** Segmented campaigns get 3x higher click rates and generate 760% more revenue. Generic emails get ignored or marked spam.

**Real-World Example:** Like a clothing store sending men's underwear ads to women. Wrong audience = unsubscribe.

## How We Measure
- Check for segmentation logic
- Count active segments
- Verify tag/category usage
- Test preference center
- Compare segment performance

## Alignment with Core Values
- **Helpful Neighbor:** Relevant content only
- **Beyond Pure:** Respect preferences
- **Everything Has KPI:** Segment performance measurable

## Treatment Available
⚠️ **Guidance:** Segmentation strategy framework

## Family
`marketing-automation`

## Severity
Medium (improves email effectiveness)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === ANALYTICS & TRACKING (10 diagnostics) ===
    {
        "title": "Diagnostic: No Conversion Goal Tracking",
        "body": """## Business Impact
Can't measure what matters—don't know what's working or where to improve.

## What We Check
- Google Analytics goals configured
- Key conversions tracked (purchases, leads, signups)
- Goal values assigned ($)
- Conversion funnels mapped
- Attribution tracking setup

## Why This Matters for Your Business
**What Gets Measured Gets Improved:** Without goal tracking, you're flying blind. Don't know which traffic sources convert, which pages work, or ROI of marketing.

**Real-World Example:** Like a store with no sales records. You sell stuff but have no idea what works or who's buying.

## How We Measure
- Check GA goal configuration
- Verify goal completion tracking
- Review goal value assignments
- Test goal firing
- Validate funnel setup

## Alignment with Core Values
- **Everything Has KPI:** Core requirement
- **Helpful Neighbor:** Shows what actually helps
- **Beyond Pure:** Respects privacy settings

## Treatment Available
✅ **Auto-Setup:** Configure standard conversion goals

## Family
`analytics-setup`

## Severity
Critical (foundational for optimization)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Not Tracking Phone Call Conversions",
        "body": """## Business Impact
Missing 30-50% of conversions if your business relies on phone calls.

## What We Check
- Call tracking numbers implemented
- Dynamic number insertion (DNI) active
- Call source attribution working
- Call recording enabled (if legal)
- Call conversion events in analytics

## Why This Matters for Your Business
**Invisible Conversions:** For service businesses, 40-60% of leads call. Without tracking, you think your website doesn't work when it's actually driving calls.

**Real-World Example:** Like counting store visitors but not sales. You see traffic but miss the revenue.

## How We Measure
- Check for call tracking integration
- Verify DNI functionality
- Test call attribution
- Review analytics call events
- Validate source tracking

## Alignment with Core Values
- **Everything Has KPI:** Calls are key conversions
- **Helpful Neighbor:** Shows full customer journey
- **Beyond Pure:** Legal consent for recording

## Treatment Available
⚠️ **Guidance:** Call tracking implementation options

## Family
`analytics-setup`

## Severity
High (for phone-driven businesses)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No E-commerce Enhanced Analytics",
        "body": """## Business Impact
Can't see product performance, shopping behavior, or checkout abandonment points.

## What We Check
- Enhanced E-commerce enabled in GA
- Product impression tracking
- Add-to-cart events tracked
- Checkout funnel analysis configured
- Purchase revenue tracking

## Why This Matters for Your Business
**Revenue Insights:** Enhanced E-commerce shows which products sell, where shoppers abandon, and checkout friction points. Without it, you're guessing what to fix.

**Real-World Example:** Like a store owner who counts total sales but doesn't know which products sell or why carts are abandoned.

## How We Measure
- Check enhanced e-commerce setup
- Verify product tracking active
- Test checkout funnel data
- Review shopping behavior reports
- Validate revenue attribution

## Alignment with Core Values
- **Everything Has KPI:** Detailed performance data
- **Helpful Neighbor:** Shows customer journey
- **Beyond Pure:** Respects privacy regulations

## Treatment Available
✅ **Auto-Setup:** Configure enhanced e-commerce tracking

## Family
`e-commerce-optimization`

## Severity
High (for e-commerce sites)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Not Tracking Form Abandonment",
        "body": """## Business Impact
Don't know where people give up on forms, can't fix friction points.

## What We Check
- Form field interactions tracked
- Abandonment points identified
- Error message tracking
- Time-to-complete measured
- Field-level analytics active

## Why This Measures for Your Business
**Conversion Killers:** If 1,000 people start your form and 500 abandon, you need to know WHERE they quit. Usually 1-2 specific fields cause most dropoff.

**Real-World Example:** Like watching customers leave checkout line but not knowing why. Fixed field = recovered conversions.

## How We Measure
- Check for form analytics events
- Verify field-level tracking
- Test abandonment detection
- Review interaction data
- Identify problem fields

## Alignment with Core Values
- **Everything Has KPI:** Form performance measurable
- **Helpful Neighbor:** Fixes user frustration
- **Beyond Pure:** Tracks behavior, not personal data

## Treatment Available
✅ **Auto-Setup:** Enable form analytics tracking

## Family
`analytics-setup`

## Severity
High (directly affects lead generation)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Heat Map or Session Recording",
        "body": """## Business Impact
Can't see how users actually interact with your site—where they click, scroll, or struggle.

## What We Check
- Heat mapping tool installed
- Session recording active
- Privacy-compliant recording (masked sensitive data)
- Click tracking on key pages
- Scroll depth analysis

## Why This Matters for Your Business
**Visual Insights:** Heat maps show what users actually do vs. what you think they do. Often reveals buttons are invisible, important content is ignored, or unexpected behavior.

**Real-World Example:** Like watching security camera footage of your store. See where customers browse, what they skip, where they get confused.

## How We Measure
- Check for heat map tool
- Verify session recording active
- Test data collection
- Review privacy masking
- Validate tracking coverage

## Alignment with Core Values
- **Helpful Neighbor:** See user experience firsthand
- **Beyond Pure:** Privacy-safe recording only
- **Everything Has KPI:** User behavior quantified

## Treatment Available
⚠️ **Guidance:** Heat map tool recommendations

## Family
`analytics-setup`

## Severity
Medium (powerful optimization insights)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === SOCIAL & COMMUNITY (5 diagnostics) ===
    {
        "title": "Diagnostic: No Social Proof on Key Pages",
        "body": """## Business Impact
Visitors don't see others using/buying, reducing trust and conversions.

## What We Check
- Customer count displayed ("Join 10,000+ customers")
- Recent purchase notifications
- Live visitor count
- Social media follower counts
- User-generated content showcased

## Why This Matters for Your Business
**Herd Mentality:** 92% of consumers trust recommendations from others. Social proof increases conversions by 15-30%. "Others are buying" = permission to buy.

**Real-World Example:** Like a restaurant with a line vs. empty. The line attracts more customers.

## How We Measure
- Count social proof elements
- Check for real-time indicators
- Verify authenticity of numbers
- Test dynamic updates
- Monitor trust impact

## Alignment with Core Values
- **Inspire Confidence:** Others trust you
- **Advice Not Sales:** Real numbers, not fake
- **Everything Has KPI:** Social proof conversion impact

## Treatment Available
⚠️ **Guidance:** Social proof implementation strategies

## Family
`trust-building`

## Severity
High (affects conversion trust)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Social Sharing Buttons Missing or Non-Functional",
        "body": """## Business Impact
Content can't go viral—no easy way for readers to share with their networks.

## What We Check
- Share buttons on all blog posts
- Major platforms covered (Facebook, Twitter, LinkedIn, Pinterest)
- Click-to-tweet quotes embedded
- Share counts visible (if substantial)
- Mobile-friendly sharing

## Why This Matters for Your Business
**Organic Reach:** Each share exposes your content to 100-500 new people. No share buttons = no viral potential. Free marketing opportunity lost.

**Real-World Example:** Like creating amazing content but not allowing anyone to tell friends about it. Word-of-mouth requires mouth.

## How We Measure
- Check for share buttons
- Test sharing functionality
- Verify platform coverage
- Validate mobile sharing
- Track share counts

## Alignment with Core Values
- **Helpful Neighbor:** Makes sharing easy
- **Talk-About-Worthy:** Enables recommendations
- **Everything Has KPI:** Shares measurable

## Treatment Available
✅ **Auto-Fix:** Add social sharing buttons

## Family
`content-distribution`

## Severity
Medium (enables organic reach)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Social Media Integration on Website",
        "body": """## Business Impact
Missing cross-channel engagement and community building opportunities.

## What We Check
- Social media feeds embedded
- Follow buttons prominently placed
- Social proof (follower counts)
- Instagram/Facebook galleries
- Twitter timeline widget

## Why This Matters for Your Business
**Multi-Channel Presence:** Website visitors who follow on social = 3x more engaged. Social integration keeps them connected beyond single visit.

**Real-World Example:** Like a store with no mention of their loyalty program. People would join if they knew about it.

## How We Measure
- Check for social embeds
- Verify follow buttons present
- Test feed functionality
- Count social touchpoints
- Monitor follower growth

## Alignment with Core Values
- **Helpful Neighbor:** Multi-channel connection
- **Talk-About-Worthy:** Easy to follow
- **Everything Has KPI:** Follower conversion trackable

## Treatment Available
✅ **Auto-Setup:** Embed social feeds and buttons

## Family
`social-media`

## Severity
Low (supplemental engagement)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: User-Generated Content Not Leveraged",
        "body": """## Business Impact
Missing free content and authentic social proof from customers.

## What We Check
- Customer photo/video submissions encouraged
- UGC displayed on product pages
- Hashtag campaigns active
- Review photos/videos enabled
- Instagram feed shows customer content

## Why This Matters for Your Business
**Authentic Trust:** UGC converts 5x better than brand content. Customers trust other customers more than marketing. It's also free content creation.

**Real-World Example:** Like a restaurant showcasing customer food photos vs. just their own. Authentic and persuasive.

## How We Measure
- Check for UGC displays
- Count customer submissions
- Verify hashtag tracking
- Test submission process
- Monitor UGC conversion impact

## Alignment with Core Values
- **Inspire Confidence:** Real customer experiences
- **Advice Not Sales:** Authentic recommendations
- **Everything Has KPI:** UGC conversion measurable

## Treatment Available
⚠️ **Guidance:** UGC campaign strategies

## Family
`social-proof`

## Severity
Medium (powerful trust builder)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Community or Forum Features",
        "body": """## Business Impact
Missing opportunities to build engaged customer community that reduces support costs.

## What We Check
- Community forum available
- User questions answered by community
- Q&A sections on product pages
- Discussion threads active
- Moderation tools in place

## Why This Matters for Your Business
**Support Leverage:** Active communities answer 60-80% of support questions for free. Also builds loyalty and organic content for SEO.

**Real-World Example:** Like Apple Support Communities. Customers help each other, Apple saves millions in support costs.

## How We Measure
- Check for forum/community features
- Count active discussions
- Measure response rates
- Track support deflection
- Monitor community engagement

## Alignment with Core Values
- **Helpful Neighbor:** Community helps community
- **Drive to KB:** Organic knowledge sharing
- **Everything Has KPI:** Support cost reduction

## Treatment Available
⚠️ **Guidance:** Community platform recommendations

## Family
`community-building`

## Severity
Low (long-term investment)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === BUSINESS OPERATIONS (5 diagnostics) ===
    {
        "title": "Diagnostic: No Business Hours or Holiday Schedule",
        "body": """## Business Impact
Customers don't know when you're open, leading to frustration and missed sales.

## What We Check
- Business hours clearly displayed
- Holiday closures announced
- Time zone specified
- Special hours noted
- Hours in schema markup

## Why This Matters for Your Business
**Set Expectations:** 68% check hours before visiting/calling. No hours = customer assumes you're closed or unprofessional. Lost opportunities.

**Real-World Example:** Like a store with no hours sign. People don't know if you're open or permanently closed.

## How We Measure
- Check for hours display
- Verify holiday schedule
- Look for schema markup
- Test time zone clarity
- Monitor missed call patterns

## Alignment with Core Values
- **Helpful Neighbor:** Clear communication
- **Culturally Respectful:** Time zone aware
- **Inspire Confidence:** Professional operation

## Treatment Available
✅ **Auto-Setup:** Display hours with schema markup

## Family
`business-basics`

## Severity
Medium (affects customer expectations)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Shipping/Delivery Information Not Clear",
        "body": """## Business Impact
Cart abandonment due to shipping surprise at checkout.

## What We Check
- Shipping costs shown before checkout
- Delivery timeframes clear
- International shipping available (if applicable)
- Free shipping threshold displayed
- Shipping calculator on product pages

## Why This Matters for Your Business
**Transparency Wins:** 60% abandon carts due to unexpected shipping costs. Show upfront = fewer abandonments and higher trust.

**Real-World Example:** Like a restaurant menu with no prices until the bill. Nobody likes surprises at payment.

## How We Measure
- Check shipping info visibility
- Verify pre-checkout display
- Test shipping calculator
- Review policy clarity
- Monitor cart abandonment reasons

## Alignment with Core Values
- **Advice Not Sales:** Upfront about costs
- **Inspire Confidence:** No surprises
- **Helpful Neighbor:** Sets clear expectations

## Treatment Available
✅ **Auto-Fix:** Display shipping info prominently

## Family
`e-commerce-optimization`

## Severity
High (for e-commerce—reduces abandonment)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: Return/Refund Policy Not Prominent",
        "body": """## Business Impact
Purchase hesitation due to unclear return policy, especially for high-ticket items.

## What We Check
- Return policy linked in footer
- Policy visible on product pages
- Timeframe clearly stated (30 days, 60 days)
- Conditions explained simply
- Return process outlined

## Why This Matters for Your Business
**Purchase Confidence:** 92% check return policy before buying. Clear, generous policy increases conversions by 20-30%. Hidden policy = abandoned carts.

**Real-World Example:** Like buying a car with no mention of returns. Most people won't risk it.

## How We Measure
- Check policy visibility
- Verify footer link
- Review policy clarity
- Test product page display
- Monitor policy-related inquiries

## Alignment with Core Values
- **Inspire Confidence:** Generous policy builds trust
- **Advice Not Sales:** Transparent about terms
- **Helpful Neighbor:** Makes returns easy

## Treatment Available
✅ **Auto-Fix:** Add return policy to key pages

## Family
`e-commerce-optimization`

## Severity
High (especially for big-ticket items)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Clear Unique Selling Proposition (USP)",
        "body": """## Business Impact
Visitors can't articulate why they should choose you over competitors.

## What We Check
- USP stated above fold
- Differentiation clear (not generic)
- Specific benefits highlighted
- Competitor comparison available
- USP consistent across pages

## Why This Matters for Your Business
**Competitive Advantage:** In crowded markets, unclear USP = price-based competition. Clear differentiation allows premium pricing and loyal customers.

**Real-World Example:** Like Domino's "30 minutes or free." Instantly clear value vs. "We make great pizza" (so does everyone).

## How We Measure
- Extract homepage USP
- Evaluate specificity
- Check differentiation clarity
- Verify consistency
- Compare to competitors

## Alignment with Core Values
- **Advice Not Sales:** Honest differentiation
- **Helpful Neighbor:** Clear value communication
- **Inspire Confidence:** Know what you're getting

## Treatment Available
⚠️ **Guidance:** USP development framework

## Family
`messaging-optimization`

## Severity
Critical (foundational for all marketing)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Clear Next Step After Purchase/Signup",
        "body": """## Business Impact
New customers/subscribers don't know what happens next, leading to buyer's remorse or disengagement.

## What We Check
- Confirmation page has clear next steps
- Onboarding sequence initiated
- Timeline expectations set
- Customer support contact provided
- Additional resources offered

## Why This Matters for Your Business
**Post-Purchase Experience:** First hour after purchase/signup determines retention. Clear next steps = engaged customers. Confusion = refunds/churn.

**Real-World Example:** Like buying online and getting no confirmation or tracking. You worry if it even worked.

## How We Measure
- Review confirmation pages
- Check onboarding emails
- Verify expectation setting
- Test resource delivery
- Monitor post-purchase inquiries

## Alignment with Core Values
- **Helpful Neighbor:** Guides through onboarding
- **Inspire Confidence:** Clear what to expect
- **Everything Has KPI:** Retention measurable

## Treatment Available
⚠️ **Guidance:** Post-purchase experience optimization

## Family
`customer-experience`

## Severity
High (affects retention and satisfaction)
""",
        "labels": ["diagnostic", "business-performance"]
    },

    # === CUSTOMER SUPPORT & RETENTION (5 diagnostics) ===
    {
        "title": "Diagnostic: No Live Chat or Instant Support Option",
        "body": """## Business Impact
Losing customers with questions who need immediate answers to purchase.

## What We Check
- Live chat widget available
- Business hours chat coverage
- Chatbot for after-hours
- Response time <2 minutes
- Chat available on key pages (product, checkout)

## Why This Matters for Your Business
**Purchase Enablement:** 44% say live chat during purchase is most important feature. Instant answers = instant sales. No chat = lost sales.

**Real-World Example:** Like a store with no employees to ask questions. People leave to shop where they can get help.

## How We Measure
- Check for chat widget
- Test response times
- Verify coverage hours
- Check chatbot functionality
- Monitor chat conversion rates

## Alignment with Core Values
- **Helpful Neighbor:** Immediate assistance
- **Inspire Confidence:** Support when needed
- **Everything Has KPI:** Chat conversions measurable

## Treatment Available
✅ **Auto-Setup:** Configure AI chatbot

## Family
`customer-support`

## Severity
High (affects conversion and satisfaction)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Customer Portal or Account Dashboard",
        "body": """## Business Impact
Customers can't self-service their orders, downloads, or subscriptions, increasing support costs.

## What We Check
- Customer account login available
- Order history accessible
- Download/subscription management
- Profile update capabilities
- Support ticket history

## Why This Matters for Your Business
**Support Efficiency:** 67% prefer self-service over talking to support. Portal reduces support tickets by 40-60%. Lower costs, happier customers.

**Real-World Example:** Like Amazon's order history vs. having to call about every order. Self-service is faster for everyone.

## How We Measure
- Check for customer portal
- Verify account features
- Test self-service capabilities
- Monitor support ticket reduction
- Track portal usage

## Alignment with Core Values
- **Helpful Neighbor:** Empowers self-service
- **Everything Has KPI:** Support cost reduction
- **Safe by Default:** Secure account access

## Treatment Available
⚠️ **Guidance:** Customer portal implementation

## Family
`customer-experience`

## Severity
Medium (reduces support costs over time)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Knowledge Base or Help Center",
        "body": """## Business Impact
Customers can't find answers themselves, flooding support with repetitive questions.

## What We Check
- Help center/knowledge base exists
- Articles cover common questions
- Search functionality available
- Content well-organized by category
- Easy to navigate and find answers

## Why This Matters for Your Business
**Support Deflection:** Good KB deflects 30-50% of support tickets. If you handle 1,000 tickets/month, that's 300-500 fewer tickets = massive cost savings.

**Real-World Example:** Like IKEA assembly instructions. Good instructions = fewer help calls.

## How We Measure
- Check for KB existence
- Count articles available
- Test search functionality
- Verify organization/navigation
- Monitor self-service success rate

## Alignment with Core Values
- **Helpful Neighbor:** 24/7 self-help
- **Drive to KB:** Core feature (we use it!)
- **Everything Has KPI:** Deflection rate measurable

## Treatment Available
✅ **Auto-Setup:** Create KB structure

## Family
`customer-support`

## Severity
High (significant cost savings)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Customer Feedback or Survey System",
        "body": """## Business Impact
Don't know what customers think, can't improve experience or identify issues.

## What We Check
- Post-purchase surveys automated
- Net Promoter Score (NPS) tracking
- Feedback forms accessible
- Review requests sent
- Feedback loop to product team

## Why This Matters for Your Business
**Continuous Improvement:** Can't fix what you don't know about. 70% of companies that excel at customer experience use surveys. Feedback = roadmap for improvement.

**Real-World Example:** Like a restaurant that never asks how your meal was. They never improve because they don't know what's wrong.

## How We Measure
- Check for survey automation
- Verify NPS tracking
- Test feedback forms
- Review response rates
- Monitor improvement actions taken

## Alignment with Core Values
- **Helpful Neighbor:** Actively seeks to improve
- **Everything Has KPI:** Satisfaction scores
- **Beyond Pure:** Opt-in feedback only

## Treatment Available
✅ **Auto-Setup:** Basic NPS survey system

## Family
`customer-feedback`

## Severity
Medium (long-term improvement driver)
""",
        "labels": ["diagnostic", "business-performance"]
    },
    {
        "title": "Diagnostic: No Customer Loyalty or Rewards Program",
        "body": """## Business Impact
No incentive for repeat purchases; customers shop around instead of returning.

## What We Check
- Loyalty program exists
- Points/rewards clearly explained
- Easy to join and use
- Visible on every page
- Exclusive benefits for members

## Why This Matters for Your Business
**Retention Economics:** Repeat customers spend 67% more than new ones. 5% increase in retention = 25-95% profit increase. Loyalty programs drive repeats.

**Real-World Example:** Like Starbucks rewards. You return for stars, even when other coffee shops are closer.

## How We Measure
- Check for loyalty program
- Verify program visibility
- Test sign-up process
- Monitor member enrollment
- Track repeat purchase rates

## Alignment with Core Values
- **Helpful Neighbor:** Rewards loyal customers
- **Free as Possible:** Free to join
- **Everything Has KPI:** Repeat rate measurable

## Treatment Available
⚠️ **Guidance:** Loyalty program options

## Family
`customer-retention`

## Severity
Medium (long-term retention strategy)
""",
        "labels": ["diagnostic", "business-performance"]
    }
]

def create_github_issue(title, body, labels):
    """Create a GitHub issue using gh CLI"""
    try:
        # Build gh issue create command
        cmd = [
            "gh", "issue", "create",
            "--title", title,
            "--body", body,
            "--label", ",".join(labels)
        ]
        
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            check=True
        )
        
        return True, result.stdout.strip()
    except subprocess.CalledProcessError as e:
        return False, f"Error: {e.stderr}"

def main():
    print("=" * 70)
    print("WPShadow Business Performance Diagnostics Creator")
    print("=" * 70)
    print(f"\nGenerating {len(diagnostics)} business-focused diagnostic issues...")
    print(f"Focus: ROI, conversions, engagement, and measurable business impact\n")
    
    created = 0
    failed = 0
    
    for i, diagnostic in enumerate(diagnostics, 1):
        print(f"\n[{i}/{len(diagnostics)}] Creating: {diagnostic['title']}")
        success, message = create_github_issue(
            diagnostic['title'],
            diagnostic['body'],
            diagnostic['labels']
        )
        
        if success:
            created += 1
            print(f"✅ Created: {message}")
        else:
            failed += 1
            print(f"❌ Failed: {message}")
    
    print("\n" + "=" * 70)
    print("SUMMARY")
    print("=" * 70)
    print(f"✅ Successfully created: {created}")
    print(f"❌ Failed: {failed}")
    print(f"📊 Total attempted: {len(diagnostics)}")
    
    if created > 0:
        print("\n🎯 Business Impact Categories:")
        print("   • Conversion & Revenue: 10 diagnostics")
        print("   • Engagement & Retention: 5 diagnostics")
        print("   • Content Effectiveness: 5 diagnostics")
        print("   • SEO & Discoverability: 5 diagnostics")
        print("   • Trust & Credibility: 5 diagnostics")
        print("   • User Experience: 5 diagnostics")
        print("   • Performance & Speed: 5 diagnostics")
        print("   • Email & Communication: 5 diagnostics")
        print("   • Analytics & Tracking: 5 diagnostics")
        print("   • Social & Community: 5 diagnostics")
        print("   • Business Operations: 5 diagnostics")
        print("   • Customer Support: 5 diagnostics")
        
        print("\n💡 All diagnostics are:")
        print("   ✅ Measurable and testable")
        print("   ✅ Focused on business outcomes (not just technical)")
        print("   ✅ Aligned with core commandments and pillars")
        print("   ✅ Include real ROI explanations")
        print("   ✅ Provide actionable treatments")

if __name__ == "__main__":
    main()
