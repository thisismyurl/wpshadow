# WPShadow KB Article Batch Creator

This script automatically creates Knowledge Base (KB) articles on the live wpshadow.com website based on all KB links referenced throughout the plugin code.

## Overview

- **Total KB Articles**: ~2,756 unique KB links found in the plugin
- **Status**: All created as **DRAFT** posts (ready for editing before publishing)
- **Category**: Assigned to KB category (ID: 3)
- **Content**: Auto-generated with proper structure and placeholder content
- **Credentials**: Uses Basic Auth with github/github

## Features

✅ **Batch Processing**: Processes 50 articles at a time with progress saving  
✅ **Smart Extraction**: Finds all KB links in plugin code automatically  
✅ **Duplicate Detection**: Skips articles that already exist  
✅ **Rate Limiting**: Respectful delays between requests (300ms)  
✅ **Progress Tracking**: Real-time progress with batch summaries  
✅ **Error Handling**: Gracefully handles failed requests  
✅ **Performance Metrics**: Shows creation rate and total duration  

## Scripts Available

### 1. `create_kb_articles_batch.py` (RECOMMENDED)
**Optimized for 2700+ articles**
- Batch processing (50 at a time)
- Progress saving
- Performance metrics
- Graceful error handling

```bash
python3 create_kb_articles_batch.py
```

### 2. `create_kb_articles.py`
**Original version with detailed logging**
- Individual article details
- Post ID tracking
- Detailed error messages

```bash
python3 create_kb_articles.py
```

### 3. `create-kb-articles.sh`
**Shell script version**
- Uses curl directly
- Simple and lightweight

```bash
bash create-kb-articles.sh
```

## Usage

### Step 1: Run the Batch Creator

```bash
# Start the batch creation process
python3 create_kb_articles_batch.py

# Or run in background with output logging
nohup python3 create_kb_articles_batch.py > kb_creation.log 2>&1 &

# Monitor progress
tail -f kb_creation.log
```

### Step 2: Monitor Progress

The script will show:
- Real-time progress percentage
- Batch completion times
- Statistics for each batch
- Final summary with creation rate

Example output:
```
📦 Batch 1 (1-50):
------
✅ 1% [1/2756] plugins-wordfence-firewall
✅ 2% [2/2756] plugins-wordfence-license
⏭️  3% [3/2756] pods-framework-field-debugging
...

Batch Stats: ✅50 ⏭️0 ❌0 (15.2s)
```

### Step 3: Review Created Articles

Once created, all KB articles will be:
- In **DRAFT** status (not published)
- In the KB category
- Ready for editing and content enhancement
- Accessible via WordPress admin

## Content Structure

Each auto-generated KB article includes:

```html
<h2>Overview</h2>
- What it is and why it matters

<h2>Key Topics</h2>
- Impact on WordPress sites
- Best practices
- Common issues
- Solutions and resources

<h2>Best Practices</h2>
- Step-by-step implementation
- Validation and testing
- Ongoing monitoring

<p>Updated: [Current Date]</p>
```

## Configuration

Edit these variables in the script to customize:

```python
SITE_URL = 'https://wpshadow.com'        # Target site
USERNAME = 'github'                       # WordPress user
PASSWORD = 'github'                       # WordPress password
CATEGORY_ID = 3                          # KB category ID
STATUS = 'draft'                         # Post status
BATCH_SIZE = 50                          # Articles per batch
DELAY_BETWEEN_REQUESTS = 0.3             # Delay in seconds
```

## Performance

**Estimated Times** (for 2,756 articles):

| Metric | Value |
|--------|-------|
| Total Articles | 2,756 |
| Batch Size | 50 |
| Delay/Request | 300ms |
| Time/Batch | ~15-20 seconds |
| Estimated Total | ~45-60 minutes |
| Creation Rate | 45-60 articles/minute |

**Network Usage**:
- Average payload: ~2KB per article
- Total data: ~5-6 MB
- Minimal server impact with rate limiting

## Troubleshooting

### Authentication Errors
```
❌ HTTP 401
```
- Verify username and password
- Check WordPress REST API is enabled
- Ensure user has capability to create posts

### Already Exists
```
⏭️ Skipped: article-name (already exists)
```
- Article with this slug already exists
- Will be skipped automatically

### Connection Timeouts
```
❌ HTTP 0 or Connection refused
```
- Check site URL is accessible
- Verify internet connection
- Check if server is under heavy load
- Increase DELAY_BETWEEN_REQUESTS if needed

### 400 Bad Request
```
❌ HTTP 400
```
- Invalid payload data
- Slug already exists (will be caught)
- Check WordPress installation

## After Creation

### 1. Review Articles
- Navigate to WordPress Posts in admin
- Filter by KB category
- Review auto-generated content
- Edit titles, content, and metadata as needed

### 2. Enhance Content
- Add specific implementation details
- Include code examples where relevant
- Link to related articles
- Add FAQ sections
- Include troubleshooting tips

### 3. Publish
- Update post status from Draft to Published
- Set featured images if desired
- Optimize for SEO
- Schedule if needed

### 4. Organize
- Add tags for better navigation
- Create hierarchical categories
- Build internal linking structure
- Create index/navigation pages

## API Endpoints Used

```
POST /wp-json/wp/v2/posts
```

Parameters:
- `title` - Article title
- `content` - HTML content
- `status` - 'draft' (can be changed)
- `slug` - URL slug
- `categories` - Category IDs
- `comment_status` - 'open' (for community engagement)
- `ping_status` - 'open' (for trackbacks)

## Security Notes

⚠️ **Important**: The credentials are currently hardcoded in scripts. For production:

1. Use environment variables:
```bash
export WP_USER="github"
export WP_PASSWORD="your_password"
python3 create_kb_articles_batch.py
```

2. Or create `.env` file:
```
WP_USER=github
WP_PASSWORD=github
```

3. Better: Use WordPress Application Passwords instead of user credentials

## Support

For issues or questions:
- Check the troubleshooting section above
- Review WordPress error logs: `wp-content/debug.log`
- Check site health in WordPress admin
- Monitor REST API response codes

## License

Part of WPShadow plugin development workflow.
