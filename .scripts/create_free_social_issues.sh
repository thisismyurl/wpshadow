#!/bin/bash

# Create GitHub issues for free social media diagnostics

echo "Creating 6 free social media diagnostic issues in thisismyurl/wpshadow..."
echo ""

# Issue 1: Alt Text Validation
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Alt Text Validation for Social Images" \
  --label "enhancement,diagnostic,social-media,accessibility,free-feature" \
  --body "## Overview
Validate that all social media share images (og:image, twitter:image) have proper alt text for accessibility.

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Educate users about accessibility
- **Pillar 🌍:** Accessibility First - Screen reader users need alt text
- **Free:** No external API needed, local validation only

## What to Check
1. Scan all posts with og:image or twitter:image meta tags
2. Check if images have alt text in WordPress media library
3. Verify alt text is descriptive (>10 characters)
4. Check for common issues:
   - Missing alt text entirely
   - Generic alt text (\"image\", \"photo\", etc)
   - Alt text same as filename

## Detection Method
Use WordPress APIs:
\`\`\`php
// Get image attachment ID from meta
\$og_image_id = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-image-id', true);
// Get alt text
\$alt_text = get_post_meta(\$og_image_id, '_wp_attachment_image_alt', true);
\`\`\`

## Expected Output
\`\`\`
Severity: Medium
Threat Level: 30
Auto-fixable: false
Family: accessibility

Description: \"12 of your 45 posts have social share images without alt text. This excludes screen reader users and may violate accessibility guidelines (WCAG 2.1 AA).\"
\`\`\`

## KB Article Topics
- Why alt text matters for social sharing
- How screen readers interpret social media posts
- Best practices for writing descriptive alt text
- How to add/edit alt text in WordPress

## References
- WCAG 2.1: https://www.w3.org/WAI/WCAG21/quickref/#non-text-content
- WordPress Accessibility Handbook
- Social platform accessibility guides"

echo "✅ Created: Alt Text Validation"

# Issue 2: Character Count Validation
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Character Count Validation for Platforms" \
  --label "enhancement,diagnostic,social-media,seo,free-feature" \
  --body "## Overview
Validate that social media meta descriptions don't exceed platform character limits.

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Prevent truncated posts
- **Commandment #8:** Inspire Confidence - Clear feedback on what works
- **Free:** No external API needed, local validation only

## What to Check
1. Check og:description, twitter:description lengths
2. Validate against platform limits:
   - Twitter/X: 280 characters
   - Facebook og:description: 300 characters (recommended)
   - LinkedIn: 256 characters (recommended)
   - Meta description: 155-160 characters

## Detection Method
Use WordPress APIs:
\`\`\`php
\$og_description = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-description', true);
\$twitter_description = get_post_meta(\$post_id, '_yoast_wpseo_twitter-description', true);

if (strlen(\$og_description) > 300) {
    // Flag as issue
}
\`\`\`

## Expected Output
\`\`\`
Severity: Low
Threat Level: 15
Auto-fixable: false
Family: seo

Description: \"8 posts have social descriptions exceeding platform limits. Twitter descriptions will be truncated after 280 characters, reducing engagement.\"
\`\`\`

## KB Article Topics
- Platform-specific character limits
- How truncation affects engagement
- Writing effective short descriptions
- Testing social previews before publishing

## Implementation Notes
- Check both Yoast SEO and RankMath meta fields
- Provide specific post IDs with issues
- Show actual character count vs limit
- Link to KB article on writing effective social copy"

echo "✅ Created: Character Count Validation"

# Issue 3: Image Dimension Validation
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Image Dimension Validation" \
  --label "enhancement,diagnostic,social-media,performance,free-feature" \
  --body "## Overview
Validate social media images meet optimal dimensions for each platform.

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Ensure professional appearance
- **Commandment #7:** Ridiculously Good for Free - Enterprise-level validation
- **Free:** No external API needed, local image analysis

## What to Check
1. Optimal dimensions for platforms:
   - Facebook og:image: 1200x630px (recommended)
   - Twitter Card: 1200x675px (large card)
   - LinkedIn: 1200x627px
   - Minimum: 600x315px for all platforms
2. Check aspect ratios (1.91:1 for most platforms)
3. Validate file size (<8MB for most platforms)

## Detection Method
Use WordPress image functions:
\`\`\`php
\$image_id = get_post_thumbnail_id(\$post_id);
\$image_meta = wp_get_attachment_metadata(\$image_id);

if (\$image_meta['width'] < 1200 || \$image_meta['height'] < 630) {
    // Flag as suboptimal
}
\`\`\`

## Expected Output
\`\`\`
Severity: Low
Threat Level: 20
Auto-fixable: false
Family: seo

Description: \"15 posts have social images smaller than recommended (1200x630px). Small images may appear blurry or unprofessional when shared.\"
\`\`\`

## KB Article Topics
- Optimal image dimensions for social platforms
- Why image size matters for engagement
- How to resize images in WordPress
- Tools for creating social media graphics
- Image optimization best practices

## Implementation Notes
- Check both og:image and twitter:image
- Provide dimension breakdown per post
- Show before/after examples in KB
- Link to Canva/Figma templates (free resources)"

echo "✅ Created: Image Dimension Validation"

# Issue 4: Link Validation
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Link Validation for Social Meta Tags" \
  --label "enhancement,diagnostic,social-media,security,auto-fixable,free-feature" \
  --body "## Overview
Validate that URLs in social meta tags are properly formatted and accessible.

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Catch broken links before sharing
- **Commandment #8:** Inspire Confidence - Reliable validation
- **Free:** Local validation, no external requests needed

## What to Check
1. og:url is set and matches canonical URL
2. og:image URLs are absolute (not relative)
3. URLs use HTTPS (not HTTP)
4. Image URLs point to valid file extensions (.jpg, .png, .webp)
5. No broken internal links

## Detection Method
Use WordPress APIs:
\`\`\`php
\$og_url = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-url', true);
\$canonical = get_permalink(\$post_id);
\$og_image = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-image', true);

// Check if URLs match
if (\$og_url !== \$canonical) {
    // Flag mismatch
}

// Validate HTTPS
if (strpos(\$og_image, 'http://') === 0) {
    // Flag insecure image URL
}
\`\`\`

## Expected Output
\`\`\`
Severity: Medium
Threat Level: 35
Auto-fixable: true (for HTTP → HTTPS conversion)
Family: seo

Description: \"5 posts have HTTP image URLs (should be HTTPS). 3 posts have og:url that doesn't match canonical URL. This may cause sharing issues on social platforms.\"
\`\`\`

## Treatment
Auto-fix for HTTP → HTTPS conversion:
\`\`\`php
\$og_image = str_replace('http://', 'https://', \$og_image);
update_post_meta(\$post_id, '_yoast_wpseo_opengraph-image', \$og_image);
\`\`\`

## KB Article Topics
- Why HTTPS matters for social sharing
- Canonical URL best practices
- How social platforms crawl URLs
- Testing social previews

## Implementation Notes
- Check both Yoast and RankMath
- Provide specific post IDs with issues
- Show diff of what will be fixed
- Backup before auto-fix"

echo "✅ Created: Link Validation"

# Issue 5: Cross-Platform Consistency
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Cross-Platform Consistency Check" \
  --label "enhancement,diagnostic,social-media,seo,free-feature" \
  --body "## Overview
Validate that social meta tags are consistent across platforms (og: vs twitter:).

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Ensure consistent branding
- **Commandment #11:** Talk-About-Worthy - Professional consistency
- **Free:** Local validation, no external API needed

## What to Check
1. If twitter:title differs from og:title
2. If twitter:description differs from og:description
3. If twitter:image differs from og:image
4. Check for missing platform-specific tags
5. Validate brand consistency (site name, colors in images)

## Detection Method
Use WordPress APIs:
\`\`\`php
\$og_title = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-title', true);
\$twitter_title = get_post_meta(\$post_id, '_yoast_wpseo_twitter-title', true);

if (!empty(\$twitter_title) && \$twitter_title !== \$og_title) {
    // Check if difference is intentional or error
}
\`\`\`

## Expected Output
\`\`\`
Severity: Low
Threat Level: 10
Auto-fixable: false (requires review)
Family: seo

Description: \"12 posts have different titles for Facebook vs Twitter. This may cause brand confusion. Review for consistency.\"
\`\`\`

## KB Article Topics
- When to use platform-specific meta tags
- Brand consistency across social platforms
- Testing how posts appear on different platforms
- Common mistakes with social meta tags

## Implementation Notes
- Highlight significant differences (>20% word change)
- Show side-by-side comparison
- Provide guidance on when differences are intentional
- Link to platform-specific guides"

echo "✅ Created: Cross-Platform Consistency"

# Issue 6: Structured Data Validation
gh issue create \
  --repo thisismyurl/wpshadow \
  --title "Social Media: Structured Data Validation for Social Cards" \
  --label "enhancement,diagnostic,social-media,seo,free-feature" \
  --body "## Overview
Validate that structured data (Schema.org) aligns with social meta tags.

## Philosophy Alignment
- **Commandment #1:** Helpful Neighbor - Ensure all metadata works together
- **Commandment #7:** Ridiculously Good for Free - Advanced validation
- **Free:** Local validation, no external API needed

## What to Check
1. Article schema matches og:title, og:description
2. ImageObject schema matches og:image
3. Author schema matches article:author
4. Publisher schema matches og:site_name
5. Check for missing required structured data fields

## Detection Method
Use WordPress APIs and JSON-LD parsing:
\`\`\`php
// Get structured data from Yoast/RankMath
\$schema = json_decode(get_post_meta(\$post_id, '_yoast_wpseo_schema_data', true), true);

// Compare with og: meta
\$og_title = get_post_meta(\$post_id, '_yoast_wpseo_opengraph-title', true);
if (\$schema['headline'] !== \$og_title) {
    // Flag mismatch
}
\`\`\`

## Expected Output
\`\`\`
Severity: Low
Threat Level: 15
Auto-fixable: false
Family: seo

Description: \"8 posts have mismatched structured data and social meta tags. This can confuse search engines and social platforms.\"
\`\`\`

## KB Article Topics
- What is structured data and why it matters
- How Schema.org relates to social meta tags
- Best practices for consistent metadata
- Testing structured data with Google Rich Results Test

## Implementation Notes
- Check for JSON-LD, Microdata, and RDFa formats
- Validate against Schema.org specifications
- Highlight critical mismatches
- Link to testing tools"

echo "✅ Created: Structured Data Validation"

echo ""
echo "✅ All 6 free social media diagnostic issues created!"
