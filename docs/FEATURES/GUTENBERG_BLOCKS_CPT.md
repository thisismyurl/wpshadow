# WPShadow Gutenberg Blocks for Custom Post Types

## Overview

The WPShadow Gutenberg Blocks system provides a comprehensive set of editor blocks that integrate seamlessly with the Custom Post Types feature. Each block allows users to display and customize CPT content directly within the WordPress block editor with live previews and extensive customization options.

## Available Blocks

All blocks are available under the **WPShadow Content** category in the block inserter when their corresponding Custom Post Type is activated.

### 1. Testimonials Block (`wpshadow/testimonials`)

**Purpose:** Display customer testimonials in grid or list format.

**Attributes:**
- `count` (number): Number of testimonials to display (1-12, default: 3)
- `category` (string): Filter by testimonial category (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showExcerpt` (boolean): Show/hide excerpt (default: true)

**Features:**
- Responsive grid layout (3 columns desktop, 2 tablet, 1 mobile)
- Star ratings display
- Client name and company information
- Image support with rounded styling
- Category filtering

**Use Cases:**
- Homepage testimonials section
- Service pages with social proof
- Dedicated testimonials page
- Landing pages

---

### 2. Team Members Block (`wpshadow/team-members`)

**Purpose:** Showcase team members with photos, titles, and bios.

**Attributes:**
- `count` (number): Number of team members to display (1-20, default: 6)
- `department` (string): Filter by department (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showBio` (boolean): Show/hide biography excerpt (default: true)

**Features:**
- Professional grid layout (4 columns desktop, 3 tablet, 2 mobile)
- Job title and department display
- Social media links integration
- Contact information display
- Department filtering

**Use Cases:**
- About Us page
- Leadership team showcase
- Department-specific pages
- Staff directory

---

### 3. Portfolio Block (`wpshadow/portfolio`)

**Purpose:** Display portfolio items showcasing work, projects, or products.

**Attributes:**
- `count` (number): Number of portfolio items (1-12, default: 6)
- `category` (string): Filter by portfolio category (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showDescription` (boolean): Show/hide description (default: true)

**Features:**
- Masonry-style grid layout
- Category and tag filtering
- Featured image with hover effects
- Project details and metadata
- Client information display

**Use Cases:**
- Portfolio page
- Work showcase
- Case studies overview
- Product gallery

---

### 4. Events Block (`wpshadow/events`)

**Purpose:** Display upcoming and past events with dates and details.

**Attributes:**
- `count` (number): Number of events to display (1-20, default: 6)
- `category` (string): Filter by event category (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'list')
- `showDate` (boolean): Show/hide event date (default: true)

**Features:**
- Chronological sorting (upcoming first)
- Date and time display
- Location information
- Category filtering (conferences, webinars, workshops, etc.)
- Event type badges

**Use Cases:**
- Events calendar
- Conference schedule
- Training sessions listing
- Webinar announcements

---

### 5. Resources Block (`wpshadow/resources`)

**Purpose:** Display downloadable resources, guides, and documents.

**Attributes:**
- `count` (number): Number of resources to display (1-20, default: 9)
- `type` (string): Filter by resource type (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showExcerpt` (boolean): Show/hide excerpt (default: true)

**Features:**
- Resource type icons and badges
- File format display (PDF, DOC, etc.)
- Download count tracking
- Topic/category filtering
- Grid layout optimized for document display

**Use Cases:**
- Resource library
- Download center
- Documentation hub
- Marketing materials repository

---

### 6. Case Studies Block (`wpshadow/case-studies`)

**Purpose:** Showcase detailed case studies with results and testimonials.

**Attributes:**
- `count` (number): Number of case studies (1-12, default: 3)
- `industry` (string): Filter by industry (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showStats` (boolean): Show/hide statistics (default: true)

**Features:**
- Industry filtering
- Client information display
- Results metrics showcase
- Challenge/solution format
- Featured image with overlay text

**Use Cases:**
- Success stories page
- Industry-specific showcases
- Sales enablement content
- Marketing landing pages

---

### 7. Services Block (`wpshadow/services`)

**Purpose:** Display services offered with descriptions and pricing.

**Attributes:**
- `count` (number): Number of services to display (1-12, default: 6)
- `category` (string): Filter by service category (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'grid')
- `showPricing` (boolean): Show/hide pricing (default: false)

**Features:**
- Service category filtering
- Icon/image support
- Pricing display (optional)
- Call-to-action buttons
- Feature list display

**Use Cases:**
- Services page
- Pricing page
- Service category pages
- Landing pages

---

### 8. Locations Block (`wpshadow/locations`)

**Purpose:** Display business locations with addresses and contact details.

**Attributes:**
- `count` (number): Number of locations to display (1-20, default: 10)
- `region` (string): Filter by region (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'list')
- `showMap` (boolean): Show/hide map placeholder (default: true)

**Features:**
- Region filtering
- Full address display
- Contact information (phone, email)
- Hours of operation
- Map integration placeholder

**Use Cases:**
- Contact page
- Store locator
- Branch locations
- Service areas

---

### 9. Documentation Block (`wpshadow/documentation`)

**Purpose:** Display documentation articles with navigation and search.

**Attributes:**
- `count` (number): Number of docs to display (1-20, default: 10)
- `category` (string): Filter by documentation category (optional)
- `layout` (string): Display layout ('grid' or 'list', default: 'list')
- `showTOC` (boolean): Show/hide table of contents (default: false)

**Features:**
- Category filtering (guides, API references, tutorials)
- Version tagging
- Difficulty level indicators
- Estimated reading time
- Hierarchical navigation

**Use Cases:**
- Knowledge base
- API documentation
- User guides
- Technical documentation

---

## Block Architecture

### File Structure

```
includes/content/
├── class-post-types-blocks.php    # PHP block registration and rendering
└── block-category.php             # Block category registration

assets/js/
└── cpt-blocks.js                   # Gutenberg block JavaScript

assets/css/
├── cpt-blocks.css                  # Frontend block styles
└── cpt-blocks-editor.css           # Editor-specific styles
```

### Class: Post_Types_Blocks

**Location:** `includes/content/class-post-types-blocks.php`

**Purpose:** Manages block registration, asset loading, and server-side rendering.

**Key Methods:**

#### `init()`
Initializes the blocks system and registers hooks.

```php
public static function init()
```

**Hooks:**
- `init` - Registers all blocks
- `enqueue_block_editor_assets` - Loads editor JavaScript and styles
- `wp_enqueue_scripts` - Loads frontend styles

---

#### `register_blocks()`
Registers all 9 Gutenberg blocks with WordPress.

```php
public static function register_blocks()
```

**Block Registration Pattern:**
```php
register_block_type(
    'wpshadow/testimonials',
    array(
        'render_callback' => array( self::class, 'render_testimonials_block' ),
        'attributes'      => array(
            'count'       => array( 'type' => 'number', 'default' => 3 ),
            'category'    => array( 'type' => 'string', 'default' => '' ),
            'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
            'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
        ),
    )
);
```

---

#### Render Callbacks

Each block has a dedicated render callback method:

- `render_testimonials_block( $attributes )` - Renders testimonials
- `render_team_members_block( $attributes )` - Renders team members
- `render_portfolio_block( $attributes )` - Renders portfolio items
- `render_events_block( $attributes )` - Renders events
- `render_resources_block( $attributes )` - Renders resources
- `render_case_studies_block( $attributes )` - Renders case studies
- `render_services_block( $attributes )` - Renders services
- `render_locations_block( $attributes )` - Renders locations
- `render_documentation_block( $attributes )` - Renders documentation

**Common Render Pattern:**
```php
public static function render_testimonials_block( $attributes ) {
    // Extract attributes
    $count        = isset( $attributes['count'] ) ? absint( $attributes['count'] ) : 3;
    $category     = isset( $attributes['category'] ) ? sanitize_text_field( $attributes['category'] ) : '';
    $layout       = isset( $attributes['layout'] ) ? sanitize_text_field( $attributes['layout'] ) : 'grid';
    $show_excerpt = isset( $attributes['showExcerpt'] ) ? (bool) $attributes['showExcerpt'] : true;

    // Build query arguments
    $args = array(
        'post_type'      => 'testimonial',
        'posts_per_page' => $count,
        'post_status'    => 'publish',
    );

    if ( ! empty( $category ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'testimonial_category',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        );
    }

    // Execute query
    $query = new \WP_Query( $args );

    // Generate output
    ob_start();
    // ... HTML rendering ...
    return ob_get_clean();
}
```

---

### JavaScript Block Registration

**Location:** `assets/js/cpt-blocks.js`

**Dependencies:**
- `wp-blocks` - Block registration API
- `wp-element` - React elements
- `wp-components` - UI components
- `wp-block-editor` - Block editor components
- `wp-server-side-render` - Server-side rendering
- `wp-i18n` - Internationalization

**Registration Pattern:**
```javascript
const { registerBlockType } = wp.blocks;
const { ServerSideRender } = wp.serverSideRender;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;
const { __ } = wp.i18n;

registerBlockType('wpshadow/testimonials', {
    title: __('Testimonials', 'wpshadow'),
    icon: 'testimonial',
    category: 'wpshadow-cpt',
    attributes: {
        count: { type: 'number', default: 3 },
        category: { type: 'string', default: '' },
        layout: { type: 'string', default: 'grid' },
        showExcerpt: { type: 'boolean', default: true }
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;

        return [
            // Inspector Controls (sidebar)
            <InspectorControls key="inspector">
                <PanelBody title={__('Testimonial Settings', 'wpshadow')}>
                    <RangeControl
                        label={__('Number of Testimonials', 'wpshadow')}
                        value={attributes.count}
                        onChange={(value) => setAttributes({ count: value })}
                        min={1}
                        max={12}
                    />
                    {/* Additional controls... */}
                </PanelBody>
            </InspectorControls>,
            
            // Block preview
            <ServerSideRender
                key="preview"
                block="wpshadow/testimonials"
                attributes={attributes}
            />
        ];
    },
    save: function() {
        return null; // Server-side rendered
    }
});
```

---

## Styling System

### Frontend Styles (`cpt-blocks.css`)

**Purpose:** Styles for blocks as displayed on the frontend.

**Features:**
- Responsive grid layouts using CSS Grid
- Mobile-first design approach
- RTL (right-to-left) language support
- Accessibility features (focus states, skip links)
- Hover effects and transitions
- Reduced motion support for accessibility

**Key Patterns:**

**Grid Layout:**
```css
.wp-block-wpshadow-testimonials.layout-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

@media (max-width: 768px) {
    .wp-block-wpshadow-testimonials.layout-grid {
        grid-template-columns: 1fr;
    }
}
```

**RTL Support:**
```css
body.rtl .testimonial-item {
    text-align: right;
}

body.rtl .testimonial-meta {
    flex-direction: row-reverse;
}
```

**Accessibility:**
```css
.testimonial-item:focus-within {
    outline: 2px solid #0073aa;
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .testimonial-item {
        transition: none;
    }
}
```

---

### Editor Styles (`cpt-blocks-editor.css`)

**Purpose:** Additional styles for the block editor interface.

**Features:**
- Block preview container styling
- Inspector control customization
- Empty state messages
- Block alignment support
- Editor-specific UI elements

**Key Styles:**
```css
/* Block preview container */
.wp-block-wpshadow-testimonials {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

/* Empty state */
.wpshadow-block-empty-state {
    padding: 40px;
    text-align: center;
    color: #666;
}

/* Inspector controls */
.wpshadow-inspector-help {
    padding: 10px;
    background: #f0f0f0;
    border-left: 3px solid #0073aa;
    margin-top: 15px;
}
```

---

## Block Category

**File:** `includes/content/block-category.php`

**Purpose:** Registers a custom block category "WPShadow Content" in the block inserter.

**Implementation:**
```php
function wpshadow_register_cpt_block_category( $categories, $post ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'wpshadow-cpt',
                'title' => __( 'WPShadow Content', 'wpshadow' ),
                'icon'  => 'shield-alt',
            ),
        )
    );
}
add_filter( 'block_categories_all', 'wpshadow_register_cpt_block_category', 10, 2 );
```

This creates a dedicated section in the block inserter where all WPShadow CPT blocks appear together, making them easy to find and insert.

---

## Usage Examples

### Example 1: Adding Testimonials to Homepage

1. Open the page/post editor
2. Click the block inserter (+)
3. Find "WPShadow Content" category
4. Select "Testimonials" block
5. Configure in sidebar:
   - Number of testimonials: 6
   - Category: "Customer Reviews"
   - Layout: Grid
   - Show excerpt: Yes
6. Publish/Update

**Result:** A responsive grid of 6 customer testimonials from the "Customer Reviews" category.

---

### Example 2: Team Members Section

1. Add "Team Members" block
2. Configure settings:
   - Number: 8
   - Department: "Engineering"
   - Layout: Grid
   - Show bio: Yes
3. Save

**Result:** Engineering team members displayed in a 4-column grid (responsive).

---

### Example 3: Upcoming Events List

1. Add "Events" block
2. Configure:
   - Number: 10
   - Category: "Webinars"
   - Layout: List
   - Show date: Yes
3. Publish

**Result:** Chronological list of next 10 webinars with dates.

---

## Integration with Custom Post Types

### Activation Check

Blocks automatically check if their corresponding CPT is active:

```php
// In render callback
$manager = \WPShadow\Content\Post_Types_Manager::class;
if ( ! $manager::is_post_type_active( 'testimonial' ) ) {
    return '<div class="wpshadow-notice">' . 
           __( 'Testimonials post type is not activated. Please activate it in WPShadow > Post Types.', 'wpshadow' ) . 
           '</div>';
}
```

**User Experience:**
- If CPT is inactive, block displays friendly message with link to activation page
- No errors or broken displays
- Clear path to enable feature

---

### Taxonomy Integration

Blocks respect active taxonomies:

```php
// Only query if taxonomy exists
if ( taxonomy_exists( 'testimonial_category' ) ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'testimonial_category',
            'field'    => 'slug',
            'terms'    => $category,
        ),
    );
}
```

---

## Performance Considerations

### Query Optimization

1. **Limited Posts per Page:** Default to reasonable numbers (3-10) to avoid large queries
2. **Caching:** Consider implementing transient caching for block output
3. **Lazy Loading:** Images use WordPress lazy loading by default

### Asset Loading

**Conditional Loading:**
```php
// Only load editor assets in admin
if ( is_admin() ) {
    wp_enqueue_script( 'wpshadow-cpt-blocks' );
}

// Only load frontend styles when blocks are present
// (WordPress handles this automatically with block rendering)
```

---

## Security

### Input Sanitization

All block attributes are sanitized:
```php
$count    = isset( $attributes['count'] ) ? absint( $attributes['count'] ) : 3;
$category = isset( $attributes['category'] ) ? sanitize_text_field( $attributes['category'] ) : '';
$layout   = isset( $attributes['layout'] ) ? sanitize_text_field( $attributes['layout'] ) : 'grid';
```

### Output Escaping

All output is properly escaped:
```php
echo '<h3 class="testimonial-title">' . esc_html( get_the_title() ) . '</h3>';
echo '<div class="testimonial-content">' . wp_kses_post( $excerpt ) . '</div>';
echo '<a href="' . esc_url( $link ) . '">' . esc_html( $link_text ) . '</a>';
```

### Capability Checks

Block registration respects WordPress capabilities:
```php
// Blocks are only available to users who can edit posts
// (handled automatically by WordPress block system)
```

---

## Accessibility

### Keyboard Navigation

All blocks support full keyboard navigation:
- Tab through items
- Enter to activate links
- Focus indicators visible

### Screen Readers

**Semantic HTML:**
```html
<article class="testimonial-item" role="article">
    <h3 class="testimonial-title">{Title}</h3>
    <div class="testimonial-content">{Content}</div>
    <footer class="testimonial-meta">
        <cite class="testimonial-author">{Author}</cite>
    </footer>
</article>
```

**ARIA Labels:**
```php
echo '<nav aria-label="' . esc_attr__( 'Team members', 'wpshadow' ) . '">';
```

### Color Contrast

All text meets WCAG AA standards:
- Normal text: 4.5:1 minimum
- Large text: 3:1 minimum

---

## Internationalization

All strings are translation-ready:

**PHP:**
```php
__( 'Testimonials', 'wpshadow' )
_n( '%d testimonial', '%d testimonials', $count, 'wpshadow' )
esc_html__( 'No testimonials found.', 'wpshadow' )
```

**JavaScript:**
```javascript
const { __ } = wp.i18n;
title: __('Testimonials', 'wpshadow')
label: __('Number of Testimonials', 'wpshadow')
```

---

## Troubleshooting

### Block Not Appearing in Inserter

**Possible Causes:**
1. Custom Post Type not activated
2. Block JavaScript not loaded
3. Block category not registered

**Solutions:**
```bash
# Check if CPT is active
Go to WPShadow > Post Types and ensure the CPT is toggled ON

# Verify JavaScript is loaded
Open browser console, check for errors
Confirm wp.blocks is available

# Check block category
console.log(wp.blocks.getCategories())
# Should show 'wpshadow-cpt' category
```

---

### Block Preview Not Rendering

**Possible Causes:**
1. PHP error in render callback
2. Query returning no results
3. ServerSideRender configuration issue

**Debug Steps:**
```php
// Enable WP_DEBUG in wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Check error log
tail -f wp-content/debug.log

// Add debug output to render callback
error_log( 'Block attributes: ' . print_r( $attributes, true ) );
error_log( 'Query results: ' . $query->post_count );
```

---

### Styling Not Applied

**Possible Causes:**
1. CSS not enqueued
2. Theme CSS conflicts
3. Caching issue

**Solutions:**
```bash
# Clear all caches
- Plugin caches
- Browser cache
- CDN cache

# Check if CSS is loaded
View page source, search for "cpt-blocks"

# Test with default theme
Switch to Twenty Twenty-Four to isolate theme conflicts
```

---

## Future Enhancements

### Planned Features

1. **Block Patterns:**
   - Pre-configured block combinations
   - Common layouts (3-column testimonials, team grid, etc.)

2. **Advanced Filtering:**
   - Multiple taxonomy filters
   - Date range filters for events
   - Search/keyword filtering

3. **Additional Layouts:**
   - Carousel/Slider layout
   - Masonry layout
   - Timeline layout (for events)

4. **Performance:**
   - Block output caching
   - Lazy loading for images
   - Infinite scroll option

5. **Customization:**
   - Color scheme controls
   - Typography controls
   - Spacing controls

---

## API Reference

### Available Filters

#### `wpshadow_cpt_block_query_args`
Modify WP_Query arguments for any block.

**Parameters:**
- `$args` (array): Query arguments
- `$block_name` (string): Block identifier (e.g., 'testimonials')
- `$attributes` (array): Block attributes

**Example:**
```php
add_filter( 'wpshadow_cpt_block_query_args', function( $args, $block_name, $attributes ) {
    if ( 'testimonials' === $block_name ) {
        // Only show testimonials from last 30 days
        $args['date_query'] = array(
            array(
                'after' => '30 days ago',
            ),
        );
    }
    return $args;
}, 10, 3 );
```

---

#### `wpshadow_cpt_block_output`
Filter the final HTML output of a block.

**Parameters:**
- `$output` (string): HTML output
- `$block_name` (string): Block identifier
- `$attributes` (array): Block attributes
- `$posts` (array): Array of WP_Post objects

**Example:**
```php
add_filter( 'wpshadow_cpt_block_output', function( $output, $block_name, $attributes, $posts ) {
    if ( 'events' === $block_name ) {
        // Add custom wrapper
        $output = '<div class="custom-events-wrapper">' . $output . '</div>';
    }
    return $output;
}, 10, 4 );
```

---

### Available Actions

#### `wpshadow_before_block_render`
Fires before a block is rendered.

**Parameters:**
- `$block_name` (string): Block identifier
- `$attributes` (array): Block attributes

**Example:**
```php
add_action( 'wpshadow_before_block_render', function( $block_name, $attributes ) {
    // Log block usage for analytics
    if ( function_exists( 'my_analytics_log' ) ) {
        my_analytics_log( 'block_render', $block_name );
    }
}, 10, 2 );
```

---

#### `wpshadow_after_block_render`
Fires after a block is rendered.

**Parameters:**
- `$block_name` (string): Block identifier
- `$attributes` (array): Block attributes
- `$post_count` (int): Number of posts displayed

**Example:**
```php
add_action( 'wpshadow_after_block_render', function( $block_name, $attributes, $post_count ) {
    // Update usage statistics
    update_option( "wpshadow_block_{$block_name}_renders", get_option( "wpshadow_block_{$block_name}_renders", 0 ) + 1 );
}, 10, 3 );
```

---

## Developer Notes

### Adding a New Block

To add a new block for a custom post type:

1. **Register the block in PHP** (`class-post-types-blocks.php`):
```php
register_block_type(
    'wpshadow/my-new-block',
    array(
        'render_callback' => array( self::class, 'render_my_new_block' ),
        'attributes'      => array(
            'count' => array( 'type' => 'number', 'default' => 6 ),
            // ... more attributes
        ),
    )
);
```

2. **Create render callback** (`class-post-types-blocks.php`):
```php
public static function render_my_new_block( $attributes ) {
    // Sanitize attributes
    // Build query
    // Execute query
    // Generate HTML
    // Return output
}
```

3. **Register in JavaScript** (`cpt-blocks.js`):
```javascript
registerBlockType('wpshadow/my-new-block', {
    title: __('My New Block', 'wpshadow'),
    icon: 'admin-post',
    category: 'wpshadow-cpt',
    attributes: { /* ... */ },
    edit: function(props) { /* ... */ },
    save: function() { return null; }
});
```

4. **Add styles** (`cpt-blocks.css`):
```css
.wp-block-wpshadow-my-new-block {
    /* Your styles */
}
```

---

## Related Documentation

- [Custom Post Types Feature](CUSTOM_POST_TYPES.md)
- [WPShadow Architecture](../REFERENCE/ARCHITECTURE.md)
- [Coding Standards](../REFERENCE/CODING_STANDARDS.md)
- [Security Best Practices](../CORE/SECURITY_BEST_PRACTICES.md)

---

## Support

For issues, questions, or feature requests related to WPShadow Gutenberg Blocks:

- **Documentation:** https://wpshadow.com/docs/gutenberg-blocks
- **Support Forum:** https://wordpress.org/support/plugin/wpshadow/
- **GitHub Issues:** https://github.com/thisismyurl/wpshadow/issues

---

**Last Updated:** 2026-02-02
**Version:** 1.26033.1600
