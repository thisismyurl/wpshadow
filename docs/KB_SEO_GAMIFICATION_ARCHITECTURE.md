# KB SEO & Gamification Architecture

**Version:** 1.0  
**Created:** January 21, 2026  
**Purpose:** Technical specification for KB search ranking and user engagement systems

---

## Part 1: SEO Architecture

### Keyword Research & Targeting Strategy

#### Primary Research Sources
1. **Google Search Console** (after publishing)
   - Monitor impressions vs. clicks
   - Identify high-impression, low-rank articles
   - Track query variations

2. **WordPress.org Support Forums**
   - Common user questions
   - Pain points and confusion areas
   - Real terminology users search for

3. **SEO Tools** (optional)
   - Semrush, Ahrefs, Moz for keyword volume
   - Analyze competitor content
   - Identify content gaps

#### Keyword Categories

**Tier 1: High Intent, High Volume (50+ searches/month)**
- "How to [action] in WordPress"
- "WordPress [feature] guide"
- "Best way to [action]"
- *Strategy:* These are your high-ROI articles. Target first.

**Tier 2: Medium Intent, Medium Volume (20-50 searches/month)**
- "[Feature] in WordPress" (what is it)
- "[Feature] not working" (troubleshooting)
- "[Feature] settings explained"
- *Strategy:* Fills in gaps, supports Tier 1 articles.

**Tier 3: Long-Tail, Specific (5-20 searches/month)**
- "[Feature] [specific use case]"
- "[Feature] with [plugin name]"
- "How to [advanced action]"
- *Strategy:* Drives niche traffic, builds authority.

### On-Page SEO Implementation

#### Technical Elements

**1. URL Structure**
```
/kb/[category]/[article-slug]/
/kb/security/how-to-enable-ssl/
/kb/performance/increase-php-memory-limit/
/kb/config/set-correct-permalinks/
```

**2. Meta Tags**
```html
<!-- 1. Page Title (50-60 characters) -->
<title>How to Delete Posts in WordPress | WPShadow KB</title>

<!-- 2. Meta Description (150-160 characters) -->
<meta name="description" content="Learn how to delete WordPress posts, pages, and items permanently. Includes safety tips, undo methods, and WPShadow automation options.">

<!-- 3. Canonical Tag (prevent duplicates) -->
<link rel="canonical" href="https://wpshadow.com/kb/how-to-delete-wordpress-posts/">

<!-- 4. Open Graph (social sharing) -->
<meta property="og:title" content="How to Delete Posts in WordPress">
<meta property="og:description" content="...">
<meta property="og:image" content="https://wpshadow.com/assets/kb-images/security/delete-item-ui.jpg">
```

**3. Schema Markup (Structured Data)**
```json
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "How to Delete a WordPress Post",
  "description": "Step-by-step guide to permanently delete WordPress posts with safety considerations",
  "image": "https://wpshadow.com/assets/kb-images/delete-item.jpg",
  "step": [
    {
      "@type": "HowToStep",
      "name": "Navigate to Posts",
      "text": "Go to Posts in WordPress admin sidebar",
      "image": "https://wpshadow.com/assets/kb-images/delete-item-step-1.jpg"
    },
    {
      "@type": "HowToStep",
      "name": "Find the item to delete",
      "text": "Hover over the post and click 'Trash'"
    },
    {
      "@type": "HowToStep",
      "name": "Permanently delete",
      "text": "Go to Trash folder and click 'Delete Permanently'"
    }
  ]
}
```

**4. Heading Structure (for Google PAA - "People Also Ask")**
```
H1: How to Delete WordPress Posts [PRIMARY KEYWORD]
  H2: What Happens When You Delete a Post? [INTENT: WHAT]
  H2: Step-by-Step: How to Delete [INTENT: HOW]
    H3: Step 1: Navigate to Posts
    H3: Step 2: Select Items to Delete
    H3: Step 3: Confirm Deletion
  H2: Delete vs. Trash: What's the Difference? [INTENT: COMPARISON]
  H2: Oops! How to Undo a Deletion [INTENT: PROBLEM-SOLVE]
  H2: Advanced: Delete Multiple Items with WPShadow [INTENT: ADVANCED]
```

#### Content Optimization

**1. Content Depth**
- TLDR: 50-100 words (featured snippet candidate)
- Tier 2: 800-1,200 words (main content)
- Tier 3: 400-600 words (advanced/technical)
- **Total: 1,500-2,000+ words** (optimal for ranking)

**2. Keyword Placement**
```
Primary keyword: "How to delete WordPress posts"
  - H1 title ✓
  - First 100 words ✓
  - At least 2-3x throughout ✓
  
Secondary keywords: "delete", "trash", "permanently remove", "undo"
  - H2/H3 headers ✓
  - Alt text of images ✓
  - Internal link anchor text ✓
```

**3. Content Readability**
- Flesch Reading Ease: 60-70 (conversational)
- Flesch-Kincaid Grade: 8-10 (accessible)
- Average sentence length: 12-15 words
- Average paragraph: 3-4 sentences
- Use short paragraphs for scanning

**4. Listicles & Scannable Content**
Google favors structured content:
```
- Numbered lists (step-by-step)
- Bullet lists (features, benefits)
- Tables (comparisons)
- Bolded key points
- Short paragraphs with one idea each
```

#### Image Optimization

**1. Image Requirements**
```
- Size: 1200x800px minimum (2:3 ratio)
- Format: JPG for photos, PNG for diagrams
- Compression: <100KB (use ImageOptim or similar)
- Alt text: Descriptive, includes keyword where natural
  - ✅ "WordPress Posts page showing delete button in row actions"
  - ❌ "Screenshot"
- Filename: Use keyword in filename
  - ✅ delete-post-step-1.jpg
  - ❌ screen-shot-2.jpg
```

**2. Featured Image** (CTR booster)
- 1200x630px recommended (for social + search results)
- Eye-catching, on-brand colors
- Include text overlay with article topic
- Test different images to see what gets clicks

#### Link Building

**1. Internal Linking Strategy**
- Link to 3-5 related KB articles per article
- Use descriptive anchor text (not "click here")
- Link breadcrumbs: Category → Article → Tier 1 → Tier 2 → Tier 3
- Example:
  ```
  [Learn about backups before deletion](/kb/set-up-automated-backups)
  [Related: Recovering deleted content](/kb/recover-deleted-wordpress-posts)
  [Advanced: WPShadow deletion automation](/kb/automate-with-wpshadow-workflows)
  ```

**2. External Linking Strategy**
- Link to WordPress.org official documentation
- Link to WordPress.org support forum searches
- Maximum 2 external links per article
- Use rel="noopener noreferrer" for security
- Examples:
  ```
  [WordPress.org: Posts Official Guide](https://wordpress.org/support/article/posts/)
  [WordPress.org Support: "delete post" search](https://wordpress.org/support/search.php?type=forum&forum=1&q=delete+post)
  ```

**3. Backlink Strategy** (6-12 months)
- Get mentioned in WordPress.org documentation
- Community article features
- Guest posts on WordPress blogs
- Press releases (if major feature)

### Content Calendar & Publishing

**Template for KB Article Planning:**
```
Article #: [number]
Title: How to [Action] in WordPress
Primary Keyword: [keyword] (search volume: X/month)
Secondary Keywords: [keywords]
Target Difficulty: [Easy/Medium/Hard]
Estimated Rank Position: [Top 100/Top 50/Top 20/Top 10]
Estimated Traffic: [X sessions/month at target position]
Related Articles: [Link to 3-5 others]
Academy Course: [Link or "TBD"]
```

**Publishing Schedule:**
- **Week 1-4:** 10 articles (high-intent, high-volume)
- **Week 5-8:** 15 articles (medium-volume)
- **Week 9-12:** 20 articles (long-tail + system coverage)
- **Month 4+:** 10-15 articles/month (ongoing)

---

## Part 2: Gamification Architecture

### Points System

#### Base Points Structure

```
Reading engagement:
  - View TLDR section: +5 points
  - Read Tier 2 (intermediate): +15 points
  - Read Tier 3 (advanced): +25 points
  - Watch embedded video: +10 points
  - Complete Academy course: +50 points
  - Take & pass quiz: +20 points
  
Engagement actions:
  - Like/helpful vote: +1 point (up to 5/day)
  - Share article: +5 points (max 1/day)
  - Add to bookmarks: +2 points (max 1/day)
  - Comment/answer question: +3 points
  - Answer peer's question: +5 points
  
Streak bonuses:
  - 3 days in a row: +5 bonus points
  - 7 days in a row: +15 bonus points
  - 30 days in a row: +50 bonus points
```

#### Calculation & Storage

**Database Schema:**
```sql
-- Track user reading progress
CREATE TABLE wp_wpshadow_kb_progress (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  article_id INT,
  tldr_read BOOLEAN DEFAULT FALSE,
  tier2_read BOOLEAN DEFAULT FALSE,
  tier3_read BOOLEAN DEFAULT FALSE,
  video_watched BOOLEAN DEFAULT FALSE,
  quiz_passed BOOLEAN DEFAULT FALSE,
  points_earned INT DEFAULT 0,
  last_read DATETIME,
  created_at DATETIME
);

-- Track user points
CREATE TABLE wp_wpshadow_user_stats (
  user_id INT PRIMARY KEY,
  total_points INT DEFAULT 0,
  articles_read INT DEFAULT 0,
  current_streak INT DEFAULT 0,
  last_activity_date DATETIME,
  current_badge VARCHAR(100),
  points_history JSON -- {date, action, points}
);
```

#### Implementation Code

```php
<?php
// Fire when user reads TLDR
do_action('wpshadow_kb_tier_read', $user_id, $article_id, 'tldr'); // +5 points

// Fire when user reads Tier 2
do_action('wpshadow_kb_tier_read', $user_id, $article_id, 'tier2'); // +15 points

// Fire when user completes article
do_action('wpshadow_kb_article_complete', $user_id, $article_id); // totals points + adds to stats

// Handler class
class WPShadow_KB_Gamification {
    public static function init() {
        add_action('wpshadow_kb_tier_read', [__CLASS__, 'award_points']);
        add_action('wpshadow_kb_article_complete', [__CLASS__, 'check_badge_unlock']);
    }
    
    public static function award_points($user_id, $article_id, $tier) {
        $points = [
            'tldr' => 5,
            'tier2' => 15,
            'tier3' => 25,
            'video' => 10,
            'quiz' => 20
        ];
        
        $earned = $points[$tier] ?? 0;
        self::add_user_points($user_id, $earned, "Read {$tier} of article {$article_id}");
        self::check_streaks($user_id);
    }
    
    public static function add_user_points($user_id, $points, $reason) {
        $current = get_user_meta($user_id, 'wpshadow_kb_points', true) ?: 0;
        update_user_meta($user_id, 'wpshadow_kb_points', $current + $points);
        
        // Log for streaks & history
        $history = get_user_meta($user_id, 'wpshadow_points_history', true) ?: [];
        $history[] = [
            'date' => current_time('mysql'),
            'points' => $points,
            'reason' => $reason
        ];
        update_user_meta($user_id, 'wpshadow_points_history', array_slice($history, -100)); // Keep last 100
    }
}
```

### Badge System

#### Badge Definitions

```php
$badges = [
    'beginner_reader' => [
        'name' => 'Beginner Reader',
        'icon' => '📘',
        'requirement' => 'Read 5 KB articles',
        'points' => 25,
        'unlock_trigger' => 'articles_read >= 5'
    ],
    
    'security_scout' => [
        'name' => 'Security Scout',
        'icon' => '🛡️',
        'requirement' => 'Read 10 Security KB articles',
        'points' => 75,
        'unlock_trigger' => 'security_articles_read >= 10'
    ],
    
    'performance_pro' => [
        'name' => 'Performance Pro',
        'icon' => '⚡',
        'requirement' => 'Read 10 Performance KB articles',
        'points' => 75,
        'unlock_trigger' => 'performance_articles_read >= 10'
    ],
    
    'speed_demon' => [
        'name' => 'Speed Demon',
        'icon' => '🚀',
        'requirement' => 'Master Performance (read all Performance tier 3 articles)',
        'points' => 150,
        'unlock_trigger' => 'all_performance_tier3_read'
    ],
    
    'guardian_angel' => [
        'name' => 'Guardian Angel',
        'icon' => '👼',
        'requirement' => 'Master Security (read all Security tier 3 articles)',
        'points' => 150,
        'unlock_trigger' => 'all_security_tier3_read'
    ],
    
    'wpshadow_master' => [
        'name' => 'WPShadow Master',
        'icon' => '🏆',
        'requirement' => 'Read 50+ KB articles',
        'points' => 250,
        'unlock_trigger' => 'articles_read >= 50'
    ],
    
    'academy_graduate' => [
        'name' => 'Academy Graduate',
        'icon' => '🎓',
        'requirement' => 'Complete 3+ Academy courses',
        'points' => 150,
        'unlock_trigger' => 'academy_courses_completed >= 3'
    ],
    
    'streak_champion' => [
        'name' => 'Streak Champion',
        'icon' => '🔥',
        'requirement' => 'Maintain 30-day reading streak',
        'points' => 100,
        'unlock_trigger' => 'streak_days >= 30'
    ]
];
```

#### Leaderboard (Optional, Privacy-Aware)

```php
// Leaderboard display (opt-in)
class WPShadow_KB_Leaderboard {
    public static function get_top_readers($limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT u.display_name, um.meta_value as points
                FROM {$wpdb->users} u
                JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = 'wpshadow_kb_points'
                  AND um.meta_value > 0
                  AND (
                    SELECT meta_value FROM {$wpdb->usermeta}
                    WHERE user_id = u.ID
                    AND meta_key = 'wpshadow_leaderboard_public'
                  ) = '1'
                ORDER BY um.meta_value DESC
                LIMIT %d",
                $limit
            )
        );
    }
}

// Privacy control
// Users opt-in to leaderboard: Settings → Privacy → "Show my progress on leaderboard"
update_user_meta($user_id, 'wpshadow_leaderboard_public', true);
```

### Notification System

#### When to Notify

```php
// Points earned
if ($points > 0) {
    WPShadow_Notifications::notify(
        $user_id,
        "📚 You earned {$points} points! ({$total_so_far} total)",
        'success',
        3 // auto-dismiss after 3 seconds
    );
}

// Badge unlocked
if ($badge_unlocked) {
    WPShadow_Notifications::notify(
        $user_id,
        "🏆 Achievement Unlocked: {$badge_name}!",
        'success',
        10 // stays longer
    );
    
    // Email notification
    wp_mail(
        $user->user_email,
        "🏆 You earned the {$badge_name} badge!",
        "Congrats! You've unlocked a new badge on WPShadow. [View your profile]"
    );
}

// Milestone
if ($total_points % 250 === 0 && $total_points > 0) {
    WPShadow_Notifications::notify(
        $user_id,
        "🎉 Milestone: You've earned {$total_points} KB points!",
        'success',
        5
    );
}

// Streak reminder
if ($streak_days > 0 && $streak_days % 7 === 0) {
    WPShadow_Notifications::notify(
        $user_id,
        "🔥 {$streak_days}-day streak! Come back tomorrow to keep it going.",
        'info'
    );
}
```

### Dashboard Display

#### User Profile Widget
```
┌─────────────────────────────────────┐
│ Your WPShadow KB Learning Profile    │
├─────────────────────────────────────┤
│                                      │
│ 🏆 Current Badge: Security Scout   │
│ 📊 Total Points: 450                │
│ 📚 Articles Read: 18                │
│ 🔥 Current Streak: 7 days           │
│                                      │
│ Progress to WPShadow Master:        │
│ ████████░░░░░░░░░░░░ 40% (10/25)   │
│                                      │
│ [View All Badges] [View Leaderboard]│
│                                      │
└─────────────────────────────────────┘
```

#### Article Reading Progress
```
Article: How to Delete Posts in WordPress

Progress: ████░░░░░░░░░░░░░░░░ 25%

✅ TLDR read (+5 points)
▢ Read full article (+15 points remaining)
▢ Watch video (+10 points remaining)
▢ Take quiz (+20 points remaining)

You could earn up to 45 more points on this article!
```

---

## Implementation Timeline

### Month 1: SEO Foundation
- Week 1: Keyword research (50-75 high-volume keywords)
- Week 2: Write 10 core articles (Delete, SSL, Backup, Debug, Permalinks, etc.)
- Week 3: Add images + optimize for SEO (meta, schema, internal links)
- Week 4: Submit to Google Search Console + monitor

### Month 2: Gamification MVP
- Week 1: Implement points system (database + hooks)
- Week 2: Add badge system + notifications
- Week 3: Create user profile widget
- Week 4: Test gamification with internal team

### Month 3: Content Expansion
- Write 40-50 more articles (batch: 10 at a time)
- Optimize based on search data
- Add Academy course links
- Implement leaderboard (if approved)

### Month 4+: Ongoing
- Monitor rankings and update underperformers
- Add new articles based on user questions
- Create Academy courses to match KB
- Expand gamification with new badges

---

## Success Metrics

### SEO Metrics
- **Search Rankings:** 100+ keywords tracked in top 50 (Google)
- **Organic Traffic:** 10,000+ monthly sessions to KB (month 6)
- **CTR:** 2%+ average click-through rate in search results
- **Backlinks:** 50+ referring domains (month 6+)
- **Engagement:** 2+ minutes average time on page

### Gamification Metrics
- **Engagement:** 40%+ of KB visitors engage with gamification
- **Active Users:** 500+ users earning points/month (month 2)
- **Badges Earned:** 200+ badges distributed (month 3)
- **Streaks:** 50+ users with 7+ day streaks (month 3)
- **Retention:** 30% of users return within 7 days

### Business Metrics
- **Guardian Signups:** 5-10 from KB gamification (month 2+)
- **Academy Enrollments:** 20-50/month (month 2+)
- **Support Reduction:** 15-20% fewer support tickets (month 3)
- **Plugin Reviews:** Increase from 4.5 → 4.7+ stars (month 4)
- **Community Growth:** KB linked 100+ times on forums/blogs (month 6)

---

**Ready to implement?** Start with SEO audit + keyword research (Week 1), then write first 10 articles (Week 2-3), then build gamification system (Week 4+).
