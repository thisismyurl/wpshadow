# Quick Start: WPShadow Gutenberg Blocks

## 1. Activate Custom Post Types

Before using blocks, activate the post types you need:

1. Go to **WPShadow > Post Types**
2. Toggle ON the post types you want (e.g., Testimonials, Team Members, Portfolio)
3. Add content to those post types

## 2. Add a Block to Your Page

1. Open any page or post in the WordPress editor
2. Click the **+** (Add Block) button
3. Search for "WPShadow" or scroll to **WPShadow Content** category
4. Click on the block you want (e.g., "Testimonials")

## 3. Configure Block Settings

Once inserted, configure the block in the right sidebar:

### Common Settings (All Blocks)

- **Number of Items:** How many posts to display
- **Category/Filter:** Show only specific categories
- **Layout:** Grid or List view
- **Show/Hide Elements:** Toggle excerpt, dates, descriptions, etc.

### Example: Testimonials Block

```
Testimonial Settings
├── Number of Testimonials: 6
├── Category: Customer Reviews
├── Layout: Grid
└── Show Excerpt: Yes
```

## 4. Available Blocks

| Block | Best For | Default Count |
|-------|----------|---------------|
| **Testimonials** | Social proof, reviews | 3 |
| **Team Members** | About page, staff directory | 6 |
| **Portfolio** | Work showcase, projects | 6 |
| **Events** | Calendar, upcoming events | 6 |
| **Resources** | Downloads, guides | 9 |
| **Case Studies** | Success stories | 3 |
| **Services** | Service offerings | 6 |
| **Locations** | Store locator, branches | 10 |
| **Documentation** | Knowledge base, guides | 10 |

## 5. Layout Options

### Grid Layout
- Responsive columns (4→3→2→1)
- Great for visual content
- Cards with images

### List Layout
- Stacked items
- Better for detailed content
- Chronological display

## 6. Tips for Best Results

### Images
- Use consistent dimensions within each post type
- Recommended sizes:
  - Testimonials: 150x150px (square)
  - Team Members: 400x400px (square)
  - Portfolio: 800x600px (landscape)
  - Events: 1200x600px (banner)

### Content Length
- **Excerpts:** 15-25 words
- **Titles:** 3-8 words
- Keep it scannable!

### Categories
- Create 3-5 main categories per post type
- Use descriptive names
- Avoid too many subcategories

## 7. Common Patterns

### Homepage Testimonials
```
Block: Testimonials
Count: 3
Category: Featured
Layout: Grid
Show Excerpt: Yes
```

### About Page Team Section
```
Block: Team Members
Count: 8
Department: All
Layout: Grid
Show Bio: Yes
```

### Events Calendar Page
```
Block: Events
Count: 20
Category: All
Layout: List
Show Date: Yes
```

### Portfolio Gallery
```
Block: Portfolio
Count: 12
Category: Featured Work
Layout: Grid
Show Description: No
```

## 8. Troubleshooting

### Block Not Showing
**Problem:** Can't find the block in the inserter

**Solution:**
1. Verify the corresponding post type is activated (WPShadow > Post Types)
2. Refresh the editor page
3. Check that you're searching in the correct category

### No Items Displayed
**Problem:** Block shows "No items found"

**Solution:**
1. Add content to that post type first
2. Make sure posts are published (not drafts)
3. If using category filter, verify posts have that category assigned

### Styling Issues
**Problem:** Block doesn't match theme

**Solution:**
1. Check your theme's CSS isn't overriding block styles
2. Clear browser cache
3. Use custom CSS to adjust colors/spacing:
```css
.wp-block-wpshadow-testimonials {
    /* Your custom styles */
}
```

## 9. Keyboard Shortcuts

When block is selected:

- **Tab:** Focus next control
- **Shift+Tab:** Focus previous control
- **Enter:** Edit block content
- **Delete/Backspace:** Remove block
- **Arrow keys:** Navigate between blocks

## 10. Advanced Usage

### Mixing Layouts
Use multiple blocks on the same page:

```
[Header Section]
↓
[Team Members - Grid - 4 items]
↓
[Testimonials - Grid - 3 items]
↓
[Portfolio - Grid - 6 items]
```

### Category-Based Pages
Create category-specific pages:

**Service Page: Web Design**
```
Block: Services
Category: Web Design
Count: 6
```

**Service Page: Marketing**
```
Block: Services
Category: Marketing
Count: 6
```

### Combining with Other Blocks
Mix WPShadow blocks with core WordPress blocks:

```
[Heading Block] "What Our Clients Say"
↓
[Paragraph Block] "Here's what people are saying..."
↓
[Testimonials Block] Grid, 6 items
↓
[Button Block] "See All Testimonials"
```

## 11. Mobile Optimization

All blocks are automatically responsive:

- **Desktop (>1024px):** 3-4 columns
- **Tablet (768-1024px):** 2-3 columns
- **Mobile (<768px):** 1 column

No configuration needed!

## 12. Performance Tips

- Don't display too many items (max 12-20)
- Use featured images optimized for web
- Enable image lazy loading in WordPress
- Consider caching plugins for better performance

## Need More Help?

- **Full Documentation:** [GUTENBERG_BLOCKS_CPT.md](GUTENBERG_BLOCKS_CPT.md)
- **CPT Feature Guide:** [CUSTOM_POST_TYPES.md](CUSTOM_POST_TYPES.md)
- **Support:** https://wordpress.org/support/plugin/wpshadow/

---

**Quick Links:**
- [WPShadow Dashboard](/wp-admin/admin.php?page=wpshadow)
- [Post Types Settings](/wp-admin/admin.php?page=wpshadow-post-types)
- [Block Editor Guide](https://wordpress.org/support/article/wordpress-editor/)
