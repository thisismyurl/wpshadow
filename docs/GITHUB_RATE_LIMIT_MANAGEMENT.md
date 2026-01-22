# GitHub Rate Limit Management Guide

**Last Updated:** January 22, 2026  
**Current Limits:** 50,000/hour core API (authenticated)  
**Current Usage:** Minimal (13 used, 49,987 remaining)

---

## 🎯 Current Status

**You're in EXCELLENT shape:**
- ✅ Authenticated with GitHub (50,000/hour limit vs 60/hour unauthenticated)
- ✅ Usage: 13/50,000 = 0.026% utilized
- ✅ GraphQL: 54/50,000 used
- ✅ Search API: 0/30 used (more restrictive, but separate quota)

**Rate limit resets:**
- Core API: Every hour
- Search API: Every minute
- Code search: Every minute (10 requests/min)

---

## 🚨 Rate Limit Prevention Strategies

### 1. **Agent Tool Usage Optimization**

**Current Agent Tools with GitHub API Access:**
```yaml
tools: ['vscode','read','edit','search','grep_search','list_dir','execute','run_task','problems','github/*','web','todo']
```

**The `github/*` tools include:**
- `github-pull-request_activePullRequest` - Get active PR details
- `github-pull-request_openPullRequest` - Get open PR details
- `github-pull-request_issue_fetch` - Fetch specific issue
- `github-pull-request_doSearch` - Search issues/PRs
- `github-pull-request_formSearchQuery` - Form search queries
- `github-pull-request_renderIssues` - Display issues
- `github-pull-request_suggest-fix` - AI-powered fix suggestions
- `github-pull-request_copilot-coding-agent` - Background coding agent

**Optimization Rules:**

#### ✅ **DO: Prefer Local Operations**
```bash
# Instead of fetching issue via API
gh api repos/:owner/:repo/issues/:number

# Use local git commands when possible
git log --oneline -10
git diff main
git status
```

#### ✅ **DO: Batch Operations**
```bash
# Good: Single API call for multiple issues
gh api graphql -f query='query { 
  repository(owner:"thisismyurl", name:"wpshadow") {
    issues(first:10, states:OPEN) { nodes { number title } }
  }
}'

# Bad: Multiple sequential calls
gh api repos/thisismyurl/wpshadow/issues/1
gh api repos/thisismyurl/wpshadow/issues/2
gh api repos/thisismyurl/wpshadow/issues/3
```

#### ✅ **DO: Cache Aggressively**
```bash
# Cache issue list for 5 minutes
if [ -f /tmp/gh_issues_cache.json ] && [ $(find /tmp/gh_issues_cache.json -mmin -5) ]; then
    cat /tmp/gh_issues_cache.json
else
    gh api repos/thisismyurl/wpshadow/issues | tee /tmp/gh_issues_cache.json
fi
```

#### ❌ **DON'T: Polling**
```bash
# Bad: Check PR status every second
while true; do
    gh pr status
    sleep 1
done

# Good: Use webhooks or check once per operation
gh pr status
```

#### ❌ **DON'T: Unnecessary API Calls in Loops**
```bash
# Bad: API call inside loop
for issue in $(seq 1 100); do
    gh api repos/thisismyurl/wpshadow/issues/$issue
done

# Good: Single GraphQL query
gh api graphql -f query='...'
```

### 2. **Search API Special Handling**

**Search API has STRICT limits:**
- 30 requests/minute (authenticated)
- 10 requests/minute (unauthenticated)

**Optimization:**
```bash
# Instead of searching GitHub
gh search issues "label:bug repo:thisismyurl/wpshadow"

# Use local git grep when possible
git log --all --grep="bug" --oneline
git grep -i "TODO" -- '*.php'
```

**Code search is even more restrictive:**
- 10 requests/minute
- Use semantic_search or grep_search tools instead

### 3. **Agent Profile Rate Limit Awareness**

Add these rules to agent behavior:

```yaml
github_api_rules:
  prefer_local: true           # Always try local git/grep first
  cache_duration: 300          # Cache API results 5 minutes
  batch_operations: true       # Batch multiple requests
  avoid_polling: true          # Never poll APIs
  search_last_resort: true     # Search API only when necessary
  check_limits_first: false    # Don't check limits before every call (wastes quota)
```

---

## 📊 Rate Limit Monitoring

### Check Current Limits
```bash
# Full rate limit status
gh api rate_limit

# Just the numbers
gh api rate_limit --jq '.rate | "Used: \(.used)/\(.limit) | Remaining: \(.remaining)"'

# Check search API limits
gh api rate_limit --jq '.resources.search'
```

### Create Rate Limit Check Script
```bash
#!/bin/bash
# /workspaces/wpshadow/scripts/check-rate-limits.sh

REMAINING=$(gh api rate_limit --jq '.rate.remaining')
LIMIT=$(gh api rate_limit --jq '.rate.limit')
PERCENT=$((100 * REMAINING / LIMIT))

if [ $PERCENT -lt 10 ]; then
    echo "⚠️  WARNING: Only $PERCENT% rate limit remaining ($REMAINING/$LIMIT)"
    echo "Consider pausing GitHub API operations"
elif [ $PERCENT -lt 25 ]; then
    echo "⚡ CAUTION: $PERCENT% rate limit remaining ($REMAINING/$LIMIT)"
else
    echo "✅ HEALTHY: $PERCENT% rate limit remaining ($REMAINING/$LIMIT)"
fi

# Check search API (more restrictive)
SEARCH_REMAINING=$(gh api rate_limit --jq '.resources.search.remaining')
SEARCH_LIMIT=$(gh api rate_limit --jq '.resources.search.limit')
echo "Search API: $SEARCH_REMAINING/$SEARCH_LIMIT"
```

### Pre-Operation Rate Limit Check
```bash
# Before expensive operations, check limits
check_rate_limit() {
    local remaining=$(gh api rate_limit --jq '.rate.remaining')
    if [ "$remaining" -lt 100 ]; then
        echo "Rate limit low ($remaining remaining). Waiting..."
        sleep 60
    fi
}

# Use before batch operations
check_rate_limit
for issue in "${issues[@]}"; do
    gh issue view "$issue"
done
```

---

## 🔧 Environment Optimizations

### 1. **Git Configuration for Reduced API Calls**

```bash
# Configure git to use local operations
git config --global advice.detachedHead false
git config --global fetch.prune true
git config --global fetch.pruneTags true

# Reduce GitHub API dependencies
git config --global hub.protocol https
git config --global credential.helper cache
```

### 2. **Local Caching Layer**

Create `/workspaces/wpshadow/scripts/gh-cached.sh`:
```bash
#!/bin/bash
# Cached GitHub API wrapper

CACHE_DIR="/tmp/gh_cache"
CACHE_TTL=300  # 5 minutes

mkdir -p "$CACHE_DIR"

# Generate cache key from command
CACHE_KEY=$(echo "$@" | md5sum | cut -d' ' -f1)
CACHE_FILE="$CACHE_DIR/$CACHE_KEY"

# Check cache
if [ -f "$CACHE_FILE" ] && [ $(($(date +%s) - $(stat -c %Y "$CACHE_FILE"))) -lt $CACHE_TTL ]; then
    cat "$CACHE_FILE"
    exit 0
fi

# Execute and cache
gh "$@" | tee "$CACHE_FILE"
```

Usage:
```bash
# Instead of: gh api repos/thisismyurl/wpshadow/issues
./scripts/gh-cached.sh api repos/thisismyurl/wpshadow/issues
```

### 3. **Prefer GitHub GraphQL API**

GraphQL uses same quota but gets more data per request:

```bash
# REST API: Multiple calls needed
gh api repos/thisismyurl/wpshadow/issues/1
gh api repos/thisismyurl/wpshadow/issues/1/comments
gh api repos/thisismyurl/wpshadow/issues/1/labels

# GraphQL: Single call
gh api graphql -f query='
query {
  repository(owner:"thisismyurl", name:"wpshadow") {
    issue(number:1) {
      title
      body
      comments(first:10) { nodes { body } }
      labels(first:10) { nodes { name } }
    }
  }
}'
```

---

## 🤖 Agent Behavior Modifications

### Add to Agent Profile

```yaml
## 🚫 GitHub API Rate Limit Protection

### CRITICAL: API Call Hierarchy (Use in Order)

1. **Local Git Operations (NO API CALLS)**
   - Use `git log`, `git diff`, `git grep` instead of API
   - Read local files instead of fetching via API
   - Use `grep_search` instead of GitHub code search

2. **Cached GitHub CLI (5-minute cache)**
   - Use `./scripts/gh-cached.sh` wrapper for repeated queries
   - Cache issue lists, PR details, repository info

3. **Batched GraphQL (Efficient API)**
   - Fetch multiple resources in single query
   - Use for complex data requirements

4. **REST API (Last Resort)**
   - Only when GraphQL unavailable
   - Batch with `gh api --paginate` when needed

5. **Search API (AVOID)**
   - 30 requests/minute limit
   - Use only when local search fails
   - Never in loops or automated processes

### GitHub Tool Usage Rules

**✅ ALLOWED (No Rate Limit Impact):**
- `grep_search` - Local code search (preferred)
- `semantic_search` - Local semantic search
- `read_file` - Local file operations
- `list_dir` - Local directory listing
- `run_in_terminal` with git commands

**⚠️ USE SPARINGLY (Consumes Rate Limit):**
- `github-pull-request_issue_fetch` - 1 API call per issue
- `github-pull-request_doSearch` - 1 search quota per query
- `github-pull-request_activePullRequest` - 1 API call

**🚫 AVOID (High Rate Limit Cost):**
- Fetching issues in loops
- Repeated PR status checks
- GitHub code search (use grep_search instead)
- Polling APIs

### Rate Limit Check Protocol

**Before batch operations:**
```bash
# Check if we have headroom
REMAINING=$(gh api rate_limit --jq '.rate.remaining')
if [ "$REMAINING" -lt 1000 ]; then
    echo "⚠️  Rate limit low. Using local operations only."
    exit 1
fi
```

**Agent should:**
- Prefer local git/grep operations 90%+ of the time
- Cache GitHub API responses for 5 minutes
- Batch multiple API needs into single GraphQL query
- Never poll or check in loops
- Use search API only as absolute last resort
```

---

## 📈 Monitoring & Alerts

### Daily Rate Limit Report
```bash
#!/bin/bash
# /workspaces/wpshadow/scripts/daily-rate-limit-report.sh

echo "GitHub API Usage Report - $(date)"
echo "======================================"

# Core API
CORE=$(gh api rate_limit --jq '.resources.core')
echo "Core API:"
echo "  Limit: $(echo $CORE | jq -r '.limit')"
echo "  Used: $(echo $CORE | jq -r '.used')"
echo "  Remaining: $(echo $CORE | jq -r '.remaining')"
echo "  Reset: $(date -d @$(echo $CORE | jq -r '.reset'))"

# Search API
SEARCH=$(gh api rate_limit --jq '.resources.search')
echo -e "\nSearch API:"
echo "  Limit: $(echo $SEARCH | jq -r '.limit')"
echo "  Used: $(echo $SEARCH | jq -r '.used')"
echo "  Remaining: $(echo $SEARCH | jq -r '.remaining')"

# GraphQL API
GRAPHQL=$(gh api rate_limit --jq '.resources.graphql')
echo -e "\nGraphQL API:"
echo "  Limit: $(echo $GRAPHQL | jq -r '.limit')"
echo "  Used: $(echo $GRAPHQL | jq -r '.used')"
echo "  Remaining: $(echo $GRAPHQL | jq -r '.remaining')"
```

### Log API Calls (Debug Mode)
```bash
# Add to .bashrc or .zshrc
gh_with_logging() {
    echo "[$(date +%H:%M:%S)] GitHub API: $@" >> /tmp/gh_api_calls.log
    gh "$@"
}

alias gh=gh_with_logging
```

---

## 🎯 Quick Wins for Your Environment

### 1. Create Cache Directory
```bash
mkdir -p /workspaces/wpshadow/.cache/github
echo "/.cache/" >> /workspaces/wpshadow/.gitignore
```

### 2. Install Monitoring Script
```bash
cat > /workspaces/wpshadow/scripts/check-rate-limits.sh << 'EOF'
#!/bin/bash
REMAINING=$(gh api rate_limit --jq '.rate.remaining')
LIMIT=$(gh api rate_limit --jq '.rate.limit')
PERCENT=$((100 * REMAINING / LIMIT))

if [ $PERCENT -lt 10 ]; then
    echo "⚠️  WARNING: Only $PERCENT% rate limit remaining"
elif [ $PERCENT -lt 25 ]; then
    echo "⚡ CAUTION: $PERCENT% rate limit remaining"
else
    echo "✅ HEALTHY: $PERCENT% rate limit remaining"
fi
EOF

chmod +x /workspaces/wpshadow/scripts/check-rate-limits.sh
```

### 3. Add Git Aliases
```bash
git config --global alias.recent 'log --oneline -10'
git config --global alias.issues 'log --all --grep="issue" --oneline'
git config --global alias.todos 'grep -i "TODO" -- "*.php"'
```

### 4. Update .bashrc
```bash
# Add to /home/codespace/.bashrc
export GH_CACHE_DIR="/workspaces/wpshadow/.cache/github"
export GH_CACHE_TTL=300  # 5 minutes

# Show rate limit on prompt (optional)
show_gh_limit() {
    gh api rate_limit --jq '.rate | "\(.remaining)/\(.limit)"' 2>/dev/null || echo "N/A"
}
```

---

## 🔍 Debugging Rate Limit Issues

### If You Hit Rate Limits:

**1. Check current status:**
```bash
gh api rate_limit --jq '.rate'
```

**2. See when limit resets:**
```bash
gh api rate_limit --jq '.rate.reset' | xargs -I {} date -d @{}
```

**3. Review recent API calls:**
```bash
cat /tmp/gh_api_calls.log | tail -20
```

**4. Wait for reset (if needed):**
```bash
RESET=$(gh api rate_limit --jq '.rate.reset')
WAIT=$((RESET - $(date +%s)))
echo "Rate limit resets in $WAIT seconds"
sleep $WAIT
```

### Emergency Fallback

If rate limited:
1. ✅ Switch to local git operations only
2. ✅ Use cached data from `.cache/github/`
3. ✅ Wait for hourly reset
4. ✅ Use unauthenticated requests (60/hour, but separate quota)
5. ❌ DON'T create multiple accounts (violates ToS)

---

## 📝 Best Practices Summary

### Agent Should:
- ✅ Use `grep_search` instead of GitHub code search
- ✅ Use `git log` instead of issue API when possible
- ✅ Cache API responses for 5+ minutes
- ✅ Batch operations with GraphQL
- ✅ Check rate limits before batch operations

### Agent Should NOT:
- ❌ Poll GitHub APIs in loops
- ❌ Use search API for every query
- ❌ Fetch issues sequentially (batch with GraphQL)
- ❌ Check rate limits before every operation (wastes quota)
- ❌ Use GitHub code search (we have local code)

---

## 🚀 Implementation Checklist

- [ ] Create cache directory structure
- [ ] Install rate limit check script
- [ ] Add GitHub API logging (debug mode)
- [ ] Configure git aliases for local operations
- [ ] Update agent profile with rate limit rules
- [ ] Test cached GitHub CLI wrapper
- [ ] Document rate limit policies in README
- [ ] Add rate limit check to CI/CD (if applicable)

---

*Last Updated: January 22, 2026*  
*Current Status: ✅ Excellent (0.026% usage)*
