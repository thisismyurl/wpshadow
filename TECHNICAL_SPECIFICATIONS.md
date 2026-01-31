# KB Article Batch Creation - Technical Specifications

## System Architecture

```
┌─────────────────────────────────────────────────────────┐
│  WPShadow Plugin (Local)                                │
│  ├─ includes/diagnostics/ (2,786 KB links)             │
│  ├─ includes/treatments/                               │
│  └─ All other diagnostic files                         │
└────────────┬──────────────────────────────────────────┘
             │
             │ KB Link Extraction
             │ (grep + regex)
             ▼
┌─────────────────────────────────────────────────────────┐
│  KB Article Database (2,786 slugs)                      │
│  ├─ 404-errors                                         │
│  ├─ 404-monitor                                        │
│  ├─ accessibility-audit-not-performed                  │
│  └─ ... (2,783 more)                                   │
└────────────┬──────────────────────────────────────────┘
             │
             │ Batch Processing
             │ (50 articles/batch)
             ▼
┌─────────────────────────────────────────────────────────┐
│  Batch Creator Script (Python)                          │
│  ├─ get_kb_links()              [Extract slugs]        │
│  ├─ slug_to_title()             [Generate titles]      │
│  ├─ generate_content()          [Create HTML]          │
│  ├─ create_article()            [REST API POST]        │
│  └─ main()                      [Orchestrate]          │
└────────────┬──────────────────────────────────────────┘
             │
             │ REST API Calls
             │ (2,786 × POST requests)
             ▼
┌─────────────────────────────────────────────────────────┐
│  WordPress REST API                                     │
│  Endpoint: /wp-json/wp/v2/posts                         │
│  Method: POST                                           │
│  Auth: Basic Authentication                            │
│  Rate Limited: 0.3s/request                            │
└────────────┬──────────────────────────────────────────┘
             │
             │ HTTP 201 Created
             │ (or 400/401 on error)
             ▼
┌─────────────────────────────────────────────────────────┐
│  WordPress Database (wpshadow.com)                      │
│  ├─ Posts Table (new draft articles)                   │
│  ├─ Post Meta (article metadata)                       │
│  ├─ Term Relationships (KB category)                   │
│  └─ Taxonomy (category assignment)                     │
└─────────────────────────────────────────────────────────┘
```

## Data Flow

### 1. KB Link Extraction
```bash
Input:  /workspaces/wpshadow/includes/**/*.php
Process: grep -r "wpshadow.com/kb/" --include="*.php"
         | grep -o "wpshadow.com/kb/[a-z0-9-]*"
         | sort | uniq
Output: [404-errors, 404-monitor, ..., zip-slip-vulnerability-not-prevented]
Count:  2,786 unique articles
```

### 2. Slug-to-Title Conversion
```
Input:  "woocommerce-product-bundle-pricing"
Process: Replace '-' with ' '
         Capitalize each word
Output: "Woocommerce Product Bundle Pricing"
```

### 3. Content Generation
```
Input:  slug="ssl-certificate-expiry", title="Ssl Certificate Expiry"
Process: Create HTML article structure with:
         - Overview paragraph
         - Key Topics (5 bullet points)
         - Best Practices (5 numbered steps)
         - Last updated timestamp
Output: <p>This knowledge base article...</p><h2>Overview</h2>...
```

### 4. REST API POST Request
```json
{
  "title": "Ssl Certificate Expiry",
  "content": "<p>This knowledge base...</p>...",
  "status": "draft",
  "slug": "ssl-certificate-expiry",
  "categories": [3],
  "comment_status": "open",
  "ping_status": "open"
}
```

### 5. Response Handling
```
HTTP 201 Created   → ✅ Article created successfully
HTTP 400 Bad Req   → ⏭️ Article already exists (skip)
HTTP 401 Unauth    → ❌ Authentication failed
HTTP 500 Server    → ❌ Server error (retry)
Timeout            → ❌ Connection timeout
```

## Batch Processing Algorithm

```python
for batch_num in range(TOTAL_BATCHES):
    batch = kb_links[batch_start : batch_end]
    batch_start_time = now()
    
    for article in batch:
        title = slug_to_title(article.slug)
        content = generate_content(article.slug, title)
        
        success, result = create_article(
            slug=article.slug,
            title=title,
            content=content
        )
        
        if success:
            stats.created += 1
            print(f"✅ {percent}% [{counter}/{total}] {slug}")
        elif result == "exists":
            stats.skipped += 1
            print(f"⏭️ {percent}% [{counter}/{total}] {slug}")
        else:
            stats.failed += 1
            print(f"❌ {percent}% [{counter}/{total}] {slug}")
        
        sleep(DELAY_BETWEEN_REQUESTS)  # Rate limiting
    
    batch_duration = now() - batch_start_time
    print(f"Batch Stats: ✅{created} ⏭️{skipped} ❌{failed}")
    
    return stats
```

## Performance Analysis

### Request Metrics
```
Requests Per Batch:     50
Delay Between:          0.3 seconds
Batch Duration:         ~40 seconds
Total Requests:         2,786
Total Time:             ~2,200 seconds (~36 minutes)

Time Calculation:
- 2,786 requests × 0.3s = 835 seconds of delays
- 56 batches × 4 seconds (HTTP overhead) = 224 seconds
- Total expected: ~1,000-1,200 seconds (~17-20 minutes)
- With progress printing: ~2,000-2,200 seconds (33-37 minutes)
```

### Network Impact
```
Average Request Size:   2-3 KB (POST payload)
Average Response Size:  500 bytes (JSON response)
Total Upload:          ~5.5 MB (2,786 × 2KB)
Total Download:        ~1.4 MB (2,786 × 500B)
Total Data Transfer:   ~7 MB

Server Load:
- 2,786 HTTP requests over 40 minutes
- ~70 requests/minute
- Rate-limited to 0.3s/request (max 3 concurrent)
- Minimal server impact
```

### Database Impact
```
Tables Modified:
- wp_posts                  (2,786 new rows)
- wp_postmeta              (2,786-5,572 new rows)
- wp_term_relationships    (2,786 new rows)
- wp_posts Cache           (cleared/refreshed)

Estimated Storage:
- Posts: 2,786 × 200 bytes = ~560 KB
- Post Meta: 2,786 × 100 bytes = ~280 KB
- Relationships: 2,786 × 50 bytes = ~140 KB
- Total: ~1 MB per 2,786 articles
```

## Authentication Flow

### Basic Authentication
```
1. Username: github
2. Password: github
3. Encode: base64("github:github")
   Result: "Z2l0aHViOmdpdGh1Yg=="
4. Header: Authorization: Basic Z2l0aHViOmdpdGh1Yg==
5. Sent with: curl -H "Authorization: ..." https://wpshadow.com/wp-json/...
```

### WordPress Requirements
```
User Must Have:
✅ WordPress user account on wpshadow.com
✅ Minimum role: Editor (or Administrator)
✅ Capability: 'create_posts'
✅ REST API enabled in WordPress settings
✅ Basic Auth support enabled (usually default)

Can Be Generated Via:
✅ Application Passwords (WordPress 5.6+)
✅ WP-CLI: wp user create github github@example.com --role=editor
✅ WordPress Admin: Users > Add New
```

## Error Handling Strategy

### Recoverable Errors
```
Error:              Action:              Result:
HTTP 400 (exists)   Skip article         Counted as ⏭️ skipped
HTTP 429 (rate)     Increase delay       Continue with slower pace
Connection timeout  Retry 3x             Move to next if still fails
Slug conflict       Skip & log           Note in statistics
```

### Non-Recoverable Errors
```
Error:                  Action:              Result:
HTTP 401 (unauth)       Stop script          Requires auth fix
HTTP 403 (forbidden)    Stop script          Requires permissions
HTTP 500 (server)       Log & continue       Continue with failed count
Authentication fail     Stop script          Requires credentials
```

## Monitoring & Logging

### Real-Time Output
```
📦 Batch 1 (1-50):
✅ 1% [1/2786] 404-errors
✅ 2% [2/2786] 404-monitor
⏭️ 3% [3/2786] existing-article
❌ 4% [4/2786] ssl-error
Batch Stats: ✅47 ⏭️2 ❌1 (42.3s)
```

### Final Summary
```
📊 FINAL SUMMARY:
✅ Created:  2,756
⏭️  Skipped:  30 (already existed)
❌ Failed:   0
📈 Total:    2,786
⏱️  Duration: 2,156 seconds (35.9 minutes)
📊 Rate:     76.9 articles/minute
```

## Rollback Procedures

### If Needed
```bash
# List created KB articles
wp post list --category=3 --post_type=post --status=draft

# Delete all KB draft articles
wp post delete $(wp post list --category=3 --status=draft --field=ID) --force

# Re-run batch creator
python3 create_kb_articles_batch.py
```

## Compatibility

### Environment Requirements
```
✅ Python 3.6+
✅ curl (system utility)
✅ bash (for shell script version)
✅ grep (for KB link extraction)
✅ No external Python dependencies
```

### WordPress Requirements
```
✅ WordPress 5.0+
✅ REST API enabled (default)
✅ Basic Authentication available
✅ Editor or Administrator user
✅ Post creation capability
```

### Network Requirements
```
✅ HTTPS access to wpshadow.com
✅ TCP 443 outbound (HTTPS)
✅ No proxy/firewall blocking
✅ 50+ Mbps recommended (not required)
```

## Success Metrics

### Quantitative
- ✅ 2,786 articles created (or 100% of extracted KB links)
- ✅ 0% failure rate
- ✅ 100% slug uniqueness (no duplicates)
- ✅ All articles in draft status
- ✅ All articles in KB category
- ✅ Creation time < 1 hour

### Qualitative
- ✅ Titles auto-generated correctly
- ✅ Content HTML well-formed
- ✅ Articles visible in WordPress admin
- ✅ Searchable in WordPress post list
- ✅ Category assignment correct
- ✅ No PHP errors/warnings

---

*Technical Specification Version*: 1.0  
*Created*: 2026-01-20  
*Status*: Implementation Ready (awaiting authentication)
