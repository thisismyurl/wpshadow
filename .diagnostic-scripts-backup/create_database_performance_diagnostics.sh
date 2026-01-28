#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 40 Database & Performance Diagnostics ==="

# CATEGORY 1: Database Optimization (12 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Database Table Optimization Status" \
  --body "**Purpose:** Checks if WordPress database tables are optimized and identifies fragmentation.

**What to Test:**
- Query SHOW TABLE STATUS for Data_free (fragmentation)
- Calculate overhead per table
- Flag tables with >10% overhead
- Test last optimization timestamp

**Why It Matters:** Fragmented tables slow queries by 30-50%. Regular optimization maintains performance as sites grow.

**Expected Detection:** Tables with significant overhead, never-optimized databases, fragmentation >100MB.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Autoload Options Size" \
  --body "**Purpose:** Measures autoloaded options size and flags excessive autoload data.

**What to Test:**
- Query wp_options for autoload='yes' total size
- Flag sites with >1MB autoloaded data
- Identify top 10 largest autoload options
- Check for plugin contributions to autoload

**Why It Matters:** Autoload data loads on EVERY page request. >1MB autoload adds 100-500ms to every page load.

**Expected Detection:** 2-5MB autoload (common on older sites), single options >500KB, abandoned plugin data.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Transient Cleanup Efficiency" \
  --body "**Purpose:** Validates expired transients are being cleaned up properly.

**What to Test:**
- Count expired transients in database
- Calculate percentage of expired vs active transients
- Check if cron job 'delete_expired_transients' is running
- Test for accumulation over time

**Why It Matters:** Expired transients accumulate (100,000+), bloating databases and slowing queries. Indicates cron issues.

**Expected Detection:** 10,000+ expired transients, transient accumulation, broken cleanup cron, 50%+ expired rate.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Index Efficiency" \
  --body "**Purpose:** Validates WordPress tables have proper indexes for common queries.

**What to Test:**
- Check for missing indexes on post_name, post_type
- Verify meta_key indexes exist on meta tables
- Test for unused indexes consuming space
- Profile slow queries for missing index opportunities

**Why It Matters:** Missing indexes cause full table scans. A missing index on 100K posts turns 1ms queries into 500ms queries.

**Expected Detection:** Missing post_name index, meta_key without index, inefficient composite indexes.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Post Revision Accumulation" \
  --body "**Purpose:** Detects excessive post revisions bloating database.

**What to Test:**
- Count total revisions vs published posts
- Calculate average revisions per post
- Flag posts with >50 revisions
- Measure database space used by revisions

**Why It Matters:** Unlimited revisions bloat databases. Sites with 10,000 posts can have 200,000+ revisions adding gigabytes.

**Expected Detection:** 10+ avg revisions per post, individual posts with 100+ revisions, GBs of revision data.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned Post Meta Cleanup" \
  --body "**Purpose:** Identifies post meta rows without corresponding posts (orphaned data).

**What to Test:**
- Query postmeta where post_id not in wp_posts
- Count orphaned meta rows
- Calculate database space used by orphaned meta
- Check for plugin-specific orphaned meta

**Why It Matters:** Orphaned meta accumulates from deleted posts/plugins. 50,000+ orphaned rows slow meta queries.

**Expected Detection:** 10,000+ orphaned postmeta rows, 100MB+ of orphaned data, plugin cleanup failures.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned Comment Meta Cleanup" \
  --body "**Purpose:** Identifies comment meta rows without corresponding comments.

**What to Test:**
- Query commentmeta where comment_id not in wp_comments
- Count orphaned comment meta rows
- Calculate database space waste
- Check spam comment cleanup effectiveness

**Why It Matters:** Spam comment deletion often leaves meta behind. Accumulates to 100,000+ rows over time.

**Expected Detection:** 5,000+ orphaned comment meta, spam plugin cleanup failures, bloated commentmeta table.

**Threat Level:** 45" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned User Meta Cleanup" \
  --body "**Purpose:** Identifies user meta rows without corresponding users.

**What to Test:**
- Query usermeta where user_id not in wp_users
- Count orphaned user meta rows
- Check for sensitive data in orphaned meta
- Identify plugin sources of orphaned data

**Why It Matters:** Deleted users leave meta behind. Can contain PII violating GDPR. Accumulates to thousands of rows.

**Expected Detection:** 1,000+ orphaned usermeta, PII in orphaned data, plugin cleanup failures.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned Term Relationships" \
  --body "**Purpose:** Detects term relationships pointing to deleted posts or terms.

**What to Test:**
- Query term_relationships for non-existent object_ids
- Check for relationships to deleted terms
- Count total orphaned relationships
- Test taxonomy integrity

**Why It Matters:** Orphaned relationships corrupt taxonomy queries and counts. Causes wrong post counts on category archives.

**Expected Detection:** 5,000+ orphaned relationships, broken term counts, taxonomy query errors.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Table Corruption Check" \
  --body "**Purpose:** Runs database integrity checks for table corruption.

**What to Test:**
- Execute CHECK TABLE on all WordPress tables
- Identify corrupted tables
- Check for MyISAM vs InnoDB issues
- Test table repair necessity

**Why It Matters:** Corrupted tables cause data loss, query failures, and site crashes. Early detection prevents catastrophic failures.

**Expected Detection:** Corrupted indexes, table structure issues, storage engine problems.

**Threat Level:** 85" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Storage Engine Consistency" \
  --body "**Purpose:** Validates all WordPress tables use appropriate storage engine (InnoDB recommended).

**What to Test:**
- Query SHOW TABLE STATUS for engine type
- Flag MyISAM tables (should be InnoDB)
- Check for mixed engine usage
- Verify foreign key support where needed

**Why It Matters:** MyISAM lacks crash recovery and transactions. InnoDB is 2-3x faster for WordPress. Mixed engines cause issues.

**Expected Detection:** MyISAM tables on modern installations, mixed engines, no foreign key support.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Connection Pool Efficiency" \
  --body "**Purpose:** Tests database connection handling and pool configuration.

**What to Test:**
- Check max_connections setting
- Verify connection timeout configuration
- Test for connection pool exhaustion
- Monitor connection wait times

**Why It Matters:** Connection pool exhaustion causes '500 Internal Server Error'. Improper settings cause site crashes under load.

**Expected Detection:** max_connections too low (<100), connection timeouts, pool exhaustion under load.

**Threat Level:** 75" && sleep 2

# CATEGORY 2: Query Performance (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Slow Query Detection" \
  --body "**Purpose:** Identifies database queries taking >1 second to execute.

**What to Test:**
- Enable slow query log temporarily
- Profile queries during page loads
- Identify queries without indexes
- Check for N+1 query patterns

**Why It Matters:** Slow queries block page rendering. A single 2-second query makes pages load in 2+ seconds minimum.

**Expected Detection:** Queries >1 second, missing indexes causing full table scans, inefficient WHERE clauses.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Query Count Per Page" \
  --body "**Purpose:** Measures database query count per page load.

**What to Test:**
- Count queries on homepage, single post, archive
- Flag pages with >50 queries
- Identify query sources (theme, plugins)
- Test for cacheable queries

**Why It Matters:** Each query adds latency. Pages with 100+ queries load 3-5x slower than optimized pages with 20-30 queries.

**Expected Detection:** 100+ queries per page, N+1 problems, uncached repeated queries, inefficient theme/plugin code.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Duplicate Query Detection" \
  --body "**Purpose:** Identifies identical queries executed multiple times per page load.

**What to Test:**
- Profile queries during page load
- Detect identical queries run 2+ times
- Check for missing query result caching
- Identify duplicate query sources

**Why It Matters:** Duplicate queries waste database resources. Running same query 10x per page is pure inefficiency.

**Expected Detection:** Same query running 5-10x per page, missing WP_Query caching, inefficient loop implementations.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Uncached Query Identification" \
  --body "**Purpose:** Finds queries that should be cached but aren't.

**What to Test:**
- Identify queries run on every page load
- Check if object cache is being used
- Test for transient caching opportunities
- Verify query result caching

**Why It Matters:** Uncached queries hit database on every request. Proper caching reduces database load by 80-90%.

**Expected Detection:** Queries run every page load without caching, unused object cache, missing transient implementation.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Complex JOIN Query Performance" \
  --body "**Purpose:** Identifies complex multi-table JOINs causing performance issues.

**What to Test:**
- Profile queries with 3+ JOIN statements
- Check JOIN query execution time
- Verify proper indexes on JOIN columns
- Test for Cartesian product problems

**Why It Matters:** Complex JOINs without indexes cause exponential query time. A 100ms query can become 5 seconds.

**Expected Detection:** Multi-table JOINs without indexes, missing foreign key optimization, inefficient JOIN conditions.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] ORDER BY Query Optimization" \
  --body "**Purpose:** Validates queries with ORDER BY are optimized with proper indexes.

**What to Test:**
- Identify queries with ORDER BY on unindexed columns
- Check for filesort in EXPLAIN output
- Test ORDER BY performance on large datasets
- Verify compound index usage

**Why It Matters:** ORDER BY without indexes requires filesort, sorting entire result set in memory. Scales terribly.

**Expected Detection:** ORDER BY on unindexed columns, filesort on 10,000+ rows, missing compound indexes.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] LIKE Query Performance" \
  --body "**Purpose:** Detects inefficient LIKE queries with leading wildcards.

**What to Test:**
- Identify queries using LIKE '%text%'
- Check for leading wildcard searches
- Test full-text search alternatives
- Verify LIKE query performance

**Why It Matters:** LIKE with leading wildcard forces full table scan. Cannot use indexes. 100x slower than indexed searches.

**Expected Detection:** LIKE '%keyword%' on large tables, search queries not using full-text indexes.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Meta Query Performance" \
  --body "**Purpose:** Validates meta queries are optimized and not causing performance issues.

**What to Test:**
- Profile WP_Query with meta_query parameters
- Check for meta_query on large post counts
- Verify meta_key indexes are used
- Test for multiple meta_query combining inefficiently

**Why It Matters:** Meta queries are inherently slow. Multiple meta queries can create O(n²) complexity.

**Expected Detection:** Multiple meta_query clauses, meta queries on 10,000+ posts, missing meta_key indexes.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Taxonomy Query Optimization" \
  --body "**Purpose:** Ensures taxonomy queries use proper joins and indexes.

**What to Test:**
- Profile WP_Query with tax_query
- Check for efficient term_taxonomy_id usage
- Test multiple taxonomy query performance
- Verify proper index usage

**Why It Matters:** Taxonomy queries join 3 tables. Without optimization, slow dramatically with many terms.

**Expected Detection:** Inefficient tax_query implementation, missing term_taxonomy_id indexes, slow multi-taxonomy queries.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Search Query Performance" \
  --body "**Purpose:** Validates WordPress search queries are optimized.

**What to Test:**
- Profile default WordPress search (LIKE queries)
- Check for full-text search index usage
- Test search performance on large content
- Verify search query caching

**Why It Matters:** Default WordPress search is slow (LIKE with wildcards). On 10,000+ posts, search can take 2-5 seconds.

**Expected Detection:** Default search on large sites, no full-text indexes, uncached search results, slow LIKE queries.

**Threat Level:** 60" && sleep 2

# CATEGORY 3: Caching & Performance (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Object Cache Implementation" \
  --body "**Purpose:** Validates persistent object cache (Redis, Memcached) is properly configured.

**What to Test:**
- Check for object-cache.php dropin
- Test if Redis/Memcached is actually connected
- Verify cache hit rates (should be >80%)
- Check cache memory usage and eviction

**Why It Matters:** Without persistent object cache, WordPress queries database repeatedly. Reduces load by 80%+.

**Expected Detection:** No object cache on high-traffic sites, disconnected Redis, low cache hit rates (<50%).

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Page Cache Effectiveness" \
  --body "**Purpose:** Tests page caching plugin effectiveness and coverage.

**What to Test:**
- Detect page caching plugins
- Verify cache is actually being used (check headers)
- Test cache hit rates
- Check for dynamic content breaking cache

**Why It Matters:** Page caching is #1 performance optimization. Properly cached sites serve pages in 50-100ms vs 1-2 seconds.

**Expected Detection:** No page caching, cache not being used, low hit rates, dynamic content breaking cache.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Query Caching" \
  --body "**Purpose:** Validates database query results are being cached appropriately.

**What to Test:**
- Check query_cache settings (MySQL)
- Test for application-level query caching
- Verify transient usage for expensive queries
- Check cache TTL appropriateness

**Why It Matters:** Query caching reduces database load by 60-80% for repeated queries. Essential for high-traffic sites.

**Expected Detection:** Query cache disabled, no application caching, missing transients, inappropriate TTLs.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Browser Caching Configuration" \
  --body "**Purpose:** Validates proper browser caching headers for static assets.

**What to Test:**
- Check Cache-Control and Expires headers
- Verify appropriate cache durations (CSS/JS: 1 year)
- Test for versioned assets (bust cache on update)
- Check for no-cache on dynamic content

**Why It Matters:** Browser caching eliminates repeat downloads. Properly configured saves 50-80% bandwidth on repeat visits.

**Expected Detection:** Missing cache headers, short cache durations, unversioned assets, dynamic content cached.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] CDN Configuration & Effectiveness" \
  --body "**Purpose:** For sites using CDN, validates proper configuration and usage.

**What to Test:**
- Detect CDN configuration (Cloudflare, etc.)
- Verify assets are actually served from CDN
- Check CDN cache hit rates
- Test for origin server bypass issues

**Why It Matters:** Misconfigured CDN provides no benefit. Properly configured CDN reduces origin load by 70%+ and improves global speed.

**Expected Detection:** CDN misconfiguration, assets not using CDN, low CDN hit rates, origin bypass.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Fragment Caching Implementation" \
  --body "**Purpose:** Checks if dynamic page fragments are cached separately.

**What to Test:**
- Identify dynamic content (user-specific, time-based)
- Check for fragment caching strategy
- Verify ESI or similar implementation
- Test cache effectiveness with dynamic content

**Why It Matters:** Full page caching breaks with user-specific content. Fragment caching enables 90% caching with 10% dynamic.

**Expected Detection:** No fragment caching with personalized content, full cache bypass for dynamic elements.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Cache Invalidation Strategy" \
  --body "**Purpose:** Validates cache is properly invalidated when content changes.

**What to Test:**
- Test cache purge on post publish/update
- Check for stale content in cache
- Verify selective cache purging (not full flush)
- Test cache invalidation speed

**Why It Matters:** Broken cache invalidation shows stale content to users. Too-aggressive invalidation wastes cache benefits.

**Expected Detection:** Stale cached content, overly aggressive full cache purges, slow invalidation.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Transient Caching Usage" \
  --body "**Purpose:** Evaluates WordPress transient API usage for expensive operations.

**What to Test:**
- Identify expensive operations (API calls, complex queries)
- Check if transients are used for caching
- Verify appropriate transient durations
- Test for transient effectiveness

**Why It Matters:** Transients cache expensive operations. Without them, every page load repeats expensive work.

**Expected Detection:** Uncached API calls, missing transients for expensive queries, inappropriate durations.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Cache Memory Allocation" \
  --body "**Purpose:** Validates cache systems have adequate memory allocated.

**What to Test:**
- Check Redis/Memcached memory limits
- Verify memory isn't being exhausted
- Test eviction policies and rates
- Monitor cache memory usage trends

**Why It Matters:** Insufficient cache memory causes excessive evictions, reducing effectiveness to near-zero.

**Expected Detection:** Cache memory exhaustion, high eviction rates (>50%), undersized cache allocation.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Opcache Configuration" \
  --body "**Purpose:** Validates PHP opcache is enabled and properly configured.

**What to Test:**
- Check if opcache is enabled
- Verify opcache memory size (128MB+ recommended)
- Test for opcache exhaustion/thrashing
- Check revalidation frequency

**Why It Matters:** Opcache speeds PHP execution by 3-5x by caching compiled code. Essential for performance.

**Expected Detection:** Opcache disabled, insufficient memory (<64MB), exhaustion causing recompilation, too-frequent revalidation.

**Threat Level:** 75" && sleep 2

# CATEGORY 4: Server Resource Optimization (8 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] PHP Memory Limit Configuration" \
  --body "**Purpose:** Validates PHP memory limit is appropriately configured for site needs.

**What to Test:**
- Check memory_limit setting
- Monitor peak memory usage per request
- Flag if approaching limits regularly
- Test memory usage on resource-intensive pages

**Why It Matters:** Insufficient memory causes fatal errors. Too much memory wastes server resources and enables memory leaks.

**Expected Detection:** memory_limit too low (256MB hits limit), excessive allocation (512MB+ on simple sites).

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] PHP Execution Time Limits" \
  --body "**Purpose:** Validates max_execution_time is appropriate and not causing timeouts.

**What to Test:**
- Check max_execution_time setting
- Monitor script execution times
- Flag requests approaching timeout
- Test for long-running requests

**Why It Matters:** Execution timeouts cause incomplete operations and user frustration. Too high masks performance problems.

**Expected Detection:** Timeouts on legitimate operations, execution times approaching limit, masked slow performance.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] PHP-FPM Worker Configuration" \
  --body "**Purpose:** For PHP-FPM setups, validates worker process configuration.

**What to Test:**
- Check pm.max_children setting
- Monitor worker pool exhaustion
- Verify worker memory usage
- Test for worker spawn delays

**Why It Matters:** Worker exhaustion causes queued requests and 502/504 errors. Improper config wastes memory.

**Expected Detection:** max_children too low (pool exhaustion), excessive workers wasting memory, spawn delays.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Database Connection Pooling" \
  --body "**Purpose:** Validates database connection pooling is optimized.

**What to Test:**
- Check MySQL max_connections vs usage
- Monitor connection pool utilization
- Test for connection exhaustion
- Verify persistent connections configuration

**Why It Matters:** Connection exhaustion causes database errors. Too many connections waste memory.

**Expected Detection:** max_connections exhaustion, excessive idle connections, persistent connection issues.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Disk I/O Performance" \
  --body "**Purpose:** Tests disk I/O performance for potential bottlenecks.

**What to Test:**
- Measure disk read/write speeds
- Check for I/O wait times
- Test wp-content/uploads performance
- Verify SSD vs HDD usage

**Why It Matters:** Slow disk I/O bottlenecks entire site. Media-heavy sites need fast storage.

**Expected Detection:** High I/O wait, HDD instead of SSD, slow upload directory access, I/O bottlenecks.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Image Processing Performance" \
  --body "**Purpose:** Validates image processing (GD vs ImageMagick) is optimized.

**What to Test:**
- Check which library is used (GD vs ImageMagick)
- Test image generation times
- Verify memory usage during processing
- Check for failed regenerations

**Why It Matters:** Inefficient image processing causes memory exhaustion and slow admin. ImageMagick is 3-5x faster than GD.

**Expected Detection:** GD library on high-volume sites, memory exhaustion during regeneration, slow processing.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Cron Job Execution Performance" \
  --body "**Purpose:** Monitors cron job execution times and resource usage.

**What to Test:**
- Profile cron job execution times
- Check for jobs taking >5 minutes
- Monitor memory usage during cron
- Test for cron job accumulation

**Why It Matters:** Long-running cron jobs block other scheduled tasks. Resource-intensive cron affects site performance.

**Expected Detection:** Cron jobs >5 minutes, memory-intensive jobs, job accumulation, blocking jobs.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] HTTP Request Performance" \
  --body "**Purpose:** Measures external HTTP request performance and impact.

**What to Test:**
- Identify all external HTTP requests during page load
- Measure request times and timeouts
- Check for blocking vs async requests
- Test impact on page load time

**Why It Matters:** Slow external APIs delay page rendering. Single 3-second API call makes pages load in 3+ seconds minimum.

**Expected Detection:** Blocking API calls, slow external services (>2s), missing timeouts, excessive external requests.

**Threat Level:** 65" && sleep 2

echo ""
echo "=== Database & Performance Diagnostics Complete ==="
echo "Total Created: 40 diagnostics"
echo ""
echo "Categories:"
echo "  • Database Optimization: 12"
echo "  • Query Performance: 10"
echo "  • Caching & Performance: 10"
echo "  • Server Resource Optimization: 8"
