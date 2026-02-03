# WPShadow Blocks - Developer Guide

**Audience:** Developers extending or customizing WPShadow blocks  
**Version:** 1.6034.1200

## Quick Start

### Block Architecture

All blocks extend WordPress's `register_block_type()` with server-side rendering:

```php
namespace WPShadow\Blocks;

class Example_Block {
    public static function register() {
        register_block_type( 'wpshadow/example', array(
            'attributes'      => self::get_attributes(),
            'render_callback' => array( __CLASS__, 'render' ),
        ) );
    }
    
    public static function get_attributes() {
        return array(
            'title' => array(
                'type'    => 'string',
                'default' => 'Example',
            ),
        );
    }
    
    public static function render( $attributes ) {
        $title = esc_html( $attributes['title'] );
        return "<div class='wpshadow-example'>{$title}</div>";
    }
}
```

### Adding a New Block

1. **Create Block Class** (`includes/blocks/class-my-block.php`):
```php
<?php
/**
 * My Custom Block
 *
 * @package WPShadow\Blocks
 * @since   1.6034.1200
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class My_Block {
    public static function register() {
        register_block_type( 'wpshadow/my-block', array(
            'attributes'      => self::get_attributes(),
            'render_callback' => array( __CLASS__, 'render' ),
        ) );
    }
    
    public static function get_attributes() {
        return array(
            'content' => array(
                'type'    => 'string',
                'default' => '',
            ),
        );
    }
    
    public static function render( $attributes ) {
        $content = wp_kses_post( $attributes['content'] );
        
        ob_start();
        ?>
        <div class="wpshadow-my-block">
            <?php echo $content; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
```

2. **Register in Registry** (`includes/blocks/class-block-registry.php`):
```php
require_once WPSHADOW_PATH . 'includes/blocks/class-my-block.php';

// In register_blocks() method:
My_Block::register();
```

3. **Add Styles** (`assets/css/blocks/blocks.css`):
```css
.wpshadow-my-block {
    padding: 2rem;
    background: #f7f7f7;
}
```

4. **Add JavaScript** (if interactive - `assets/js/blocks/frontend.js`):
```javascript
function initMyBlock() {
    jQuery('.wpshadow-my-block').on('click', function() {
        // Handle interaction
    });
}

// Add to document ready:
jQuery(document).ready(function($) {
    initMyBlock();
});
```

5. **Require in Plugin** (`wpshadow.php`):
```php
require_once WPSHADOW_PATH . 'includes/blocks/class-my-block.php';
```

---

## Block Attribute Types

### Common Attribute Schemas

```php
public static function get_attributes() {
    return array(
        // Text
        'title' => array(
            'type'    => 'string',
            'default' => 'Default Title',
        ),
        
        // Number
        'count' => array(
            'type'    => 'number',
            'default' => 5,
        ),
        
        // Boolean
        'enabled' => array(
            'type'    => 'boolean',
            'default' => true,
        ),
        
        // Array of objects
        'items' => array(
            'type'    => 'array',
            'default' => array(
                array(
                    'title' => 'Item 1',
                    'value' => 100,
                ),
            ),
        ),
        
        // Image
        'image' => array(
            'type'    => 'object',
            'default' => array(
                'url' => '',
                'alt' => '',
                'id'  => 0,
            ),
        ),
        
        // Select/Enum
        'layout' => array(
            'type'    => 'string',
            'default' => 'grid',
            'enum'    => array( 'grid', 'list', 'carousel' ),
        ),
        
        // Color
        'backgroundColor' => array(
            'type'    => 'string',
            'default' => '#ffffff',
        ),
    );
}
```

---

## Rendering Patterns

### Basic HTML Output

```php
public static function render( $attributes ) {
    $title = esc_html( $attributes['title'] );
    
    return sprintf(
        '<div class="wpshadow-block">
            <h3>%s</h3>
        </div>',
        $title
    );
}
```

### Using Output Buffering (Recommended for Complex HTML)

```php
public static function render( $attributes ) {
    $items = $attributes['items'];
    
    ob_start();
    ?>
    <div class="wpshadow-block">
        <?php foreach ( $items as $item ) : ?>
            <div class="wpshadow-block__item">
                <h4><?php echo esc_html( $item['title'] ); ?></h4>
                <p><?php echo esc_html( $item['description'] ); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
```

### With CSS Classes from Attributes

```php
public static function render( $attributes ) {
    $classes = array( 'wpshadow-block' );
    
    if ( ! empty( $attributes['className'] ) ) {
        $classes[] = sanitize_html_class( $attributes['className'] );
    }
    
    if ( $attributes['featured'] ) {
        $classes[] = 'is-featured';
    }
    
    $class_string = implode( ' ', $classes );
    
    return sprintf(
        '<div class="%s">Content</div>',
        esc_attr( $class_string )
    );
}
```

---

## JavaScript Patterns

### Basic Interaction

```javascript
function initMyBlock() {
    const blocks = document.querySelectorAll('.wpshadow-my-block');
    
    blocks.forEach(function(block) {
        const button = block.querySelector('.wpshadow-my-block__button');
        
        if (button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                // Handle click
            });
        }
    });
}

jQuery(document).ready(function($) {
    initMyBlock();
});
```

### Scroll-Triggered Animation

```javascript
function initMyBlockAnimation() {
    const blocks = document.querySelectorAll('.wpshadow-my-block');
    
    if (!blocks.length) return;
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    blocks.forEach(function(block) {
        observer.observe(block);
    });
}

jQuery(document).ready(function($) {
    initMyBlockAnimation();
});
```

### Real-Time Updates

```javascript
function initMyBlockUpdates() {
    const blocks = document.querySelectorAll('.wpshadow-my-block');
    
    blocks.forEach(function(block) {
        const valueElement = block.querySelector('.value');
        
        setInterval(function() {
            // Update value every second
            const newValue = Math.random() * 100;
            valueElement.textContent = newValue.toFixed(2);
        }, 1000);
    });
}

jQuery(document).ready(function($) {
    initMyBlockUpdates();
});
```

---

## CSS Patterns

### Responsive Grid

```css
.wpshadow-my-block {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .wpshadow-my-block {
        grid-template-columns: 1fr;
    }
}
```

### CSS Custom Properties for Theming

```css
.wpshadow-my-block {
    --block-color: #1E40AF;
    --block-bg: #f7f7f7;
    
    background-color: var(--block-bg);
    border-left: 4px solid var(--block-color);
}

/* Allow attribute override */
.wpshadow-my-block[style*="--block-color"] {
    border-color: var(--block-color);
}
```

### Accessibility Focus States

```css
.wpshadow-my-block__button {
    /* Base styles */
}

.wpshadow-my-block__button:focus {
    outline: 2px solid #0073aa;
    outline-offset: 2px;
}

.wpshadow-my-block__button:focus:not(:focus-visible) {
    outline: none;
}

.wpshadow-my-block__button:focus-visible {
    outline: 2px solid #0073aa;
    outline-offset: 2px;
}
```

---

## Accessibility Checklist

When creating blocks, ensure:

### Keyboard Navigation
```php
// Add keyboard event handlers
<button 
    type="button"
    class="wpshadow-block__trigger"
    aria-expanded="false"
    aria-controls="panel-id"
>
    Toggle Panel
</button>
```

```javascript
button.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        togglePanel();
    }
});
```

### ARIA Attributes
```php
// For accordions
<button aria-expanded="false" aria-controls="panel-1">Question</button>
<div id="panel-1" role="region" aria-labelledby="trigger-1">Answer</div>

// For tabs
<div role="tablist">
    <button role="tab" aria-selected="true" aria-controls="panel-1">Tab 1</button>
</div>
<div role="tabpanel" id="panel-1">Content</div>

// For sliders
<div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
    Slider
</div>
```

### Screen Reader Text
```php
<span class="screen-reader-text">
    <?php esc_html_e( 'Additional context for screen readers', 'wpshadow' ); ?>
</span>
```

```css
.screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(1px, 1px, 1px, 1px);
    white-space: nowrap;
}
```

---

## Security Best Practices

### Always Escape Output

```php
public static function render( $attributes ) {
    $title       = esc_html( $attributes['title'] );
    $url         = esc_url( $attributes['url'] );
    $description = wp_kses_post( $attributes['description'] );
    $class       = esc_attr( $attributes['className'] );
    
    return sprintf(
        '<div class="%s">
            <h3>%s</h3>
            <p>%s</p>
            <a href="%s">Link</a>
        </div>',
        $class,
        $title,
        $description,
        $url
    );
}
```

### Sanitize Inputs

```php
public static function get_attributes() {
    return array(
        'url' => array(
            'type'    => 'string',
            'default' => '',
            // Sanitize in save
        ),
    );
}
```

### Validate Attribute Values

```php
public static function render( $attributes ) {
    // Validate enum
    $allowed_layouts = array( 'grid', 'list', 'carousel' );
    $layout = in_array( $attributes['layout'], $allowed_layouts, true ) 
        ? $attributes['layout'] 
        : 'grid';
    
    // Validate number range
    $columns = max( 1, min( 4, intval( $attributes['columns'] ) ) );
    
    // Validate URL
    $url = filter_var( $attributes['url'], FILTER_VALIDATE_URL ) 
        ? $attributes['url'] 
        : '';
    
    // Use validated values
}
```

---

## Performance Optimization

### Lazy Load Assets

```php
// Only load when block is on page
add_action( 'render_block', function( $content, $block ) {
    if ( $block['blockName'] === 'wpshadow/my-block' ) {
        wp_enqueue_style( 'wpshadow-my-block' );
        wp_enqueue_script( 'wpshadow-my-block' );
    }
    return $content;
}, 10, 2 );
```

### Use Intersection Observer

```javascript
// Don't animate until visible
const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            startAnimation(entry.target);
            observer.unobserve(entry.target);
        }
    });
});
```

### Debounce Frequent Updates

```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Usage
const handleResize = debounce(function() {
    updateLayout();
}, 250);

window.addEventListener('resize', handleResize);
```

---

## Testing Checklist

### Functionality
- [ ] Block registers successfully
- [ ] Default attributes render correctly
- [ ] Custom attributes override defaults
- [ ] All interactive features work
- [ ] Edge cases handled (empty arrays, missing images)

### Accessibility
- [ ] Keyboard navigation works
- [ ] Screen reader announces content
- [ ] ARIA attributes correct
- [ ] Focus indicators visible
- [ ] Color contrast meets WCAG AA

### Responsive Design
- [ ] Mobile layout works (< 768px)
- [ ] Tablet layout works (768px - 1024px)
- [ ] Desktop layout works (> 1024px)
- [ ] Images scale properly
- [ ] Text remains readable

### Performance
- [ ] No JavaScript errors in console
- [ ] Assets load only when needed
- [ ] Animations smooth (60fps)
- [ ] No memory leaks
- [ ] Lazy loading works

### Security
- [ ] All output escaped
- [ ] User input sanitized
- [ ] No XSS vulnerabilities
- [ ] No SQL injection risks
- [ ] Nonces used where applicable

---

## Hooks & Filters

### Modify Block Output

```php
// Filter before rendering
add_filter( 'wpshadow_my_block_render', function( $html, $attributes ) {
    // Modify HTML
    return $html;
}, 10, 2 );
```

### Modify Default Attributes

```php
// Change defaults
add_filter( 'wpshadow_my_block_attributes', function( $attributes ) {
    $attributes['title']['default'] = 'Custom Title';
    return $attributes;
} );
```

### Add Custom CSS Classes

```php
// Add class based on condition
add_filter( 'wpshadow_my_block_classes', function( $classes, $attributes ) {
    if ( $attributes['featured'] ) {
        $classes[] = 'is-featured';
    }
    return $classes;
}, 10, 2 );
```

---

## Common Patterns

### Block with Dynamic Content

```php
public static function render( $attributes ) {
    // Query posts
    $query = new \WP_Query( array(
        'post_type'      => 'testimonial',
        'posts_per_page' => $attributes['count'],
    ) );
    
    if ( ! $query->have_posts() ) {
        return '<p>' . esc_html__( 'No testimonials found.', 'wpshadow' ) . '</p>';
    }
    
    ob_start();
    ?>
    <div class="wpshadow-testimonials">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <div class="testimonial">
                <h3><?php the_title(); ?></h3>
                <?php the_content(); ?>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
```

### Block with AJAX Loading

```php
// PHP: Return unique ID
public static function render( $attributes ) {
    $block_id = 'wpshadow-block-' . wp_unique_id();
    
    return sprintf(
        '<div id="%s" class="wpshadow-block" data-load-more="true">
            <div class="content"></div>
            <button class="load-more">Load More</button>
        </div>',
        esc_attr( $block_id )
    );
}

// JavaScript: Load via AJAX
jQuery('.wpshadow-block .load-more').on('click', function() {
    const block = jQuery(this).closest('.wpshadow-block');
    
    jQuery.ajax({
        url: wpShadowData.ajaxUrl,
        data: {
            action: 'wpshadow_load_more',
            nonce: wpShadowData.nonce,
        },
        success: function(response) {
            block.find('.content').append(response.data);
        }
    });
});
```

---

## Troubleshooting

### Block Doesn't Appear in Editor
1. Check `Block_Registry::register_blocks()` includes your block
2. Verify namespace is correct
3. Check WordPress console for JavaScript errors
4. Clear browser cache

### Styles Not Loading
1. Verify CSS file path in `Block_Registry::enqueue_block_assets()`
2. Check file permissions (644)
3. Inspect element to see if styles applied
4. Check for CSS conflicts with theme

### JavaScript Not Working
1. Check browser console for errors
2. Verify jQuery dependency loaded
3. Check `frontend.js` includes your initialization
4. Ensure block class name matches JavaScript selector

### Block Renders Incorrectly
1. Check output escaping (might be stripping HTML)
2. Verify attribute defaults exist
3. Check for PHP errors in render method
4. Inspect HTML structure in browser

---

## Resources

### WordPress Block API
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
- [Attributes](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/)
- [Server-side Rendering](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/creating-dynamic-blocks/)

### WPShadow Documentation
- [Coding Standards](/docs/CODING_STANDARDS.md)
- [Accessibility Canon](/docs/ACCESSIBILITY_AND_INCLUSIVITY_CANON.md)
- [Product Philosophy](/docs/PRODUCT_PHILOSOPHY.md)

### Tools
- [Block Development Tools](https://developer.wordpress.org/block-editor/getting-started/devenv/)
- [PHPCS for WordPress](https://github.com/WordPress/WordPress-Coding-Standards)
- [Accessibility Testing](https://www.w3.org/WAI/test-evaluate/)

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-02  
**Maintainer:** WPShadow Core Team
