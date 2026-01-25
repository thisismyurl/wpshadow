---
name: Code Review Request
about: Request a code review with standards and philosophy checklist
title: "[REVIEW] "
labels: "quality/code-review"
assignees: ""
---

## 🔍 Code Review Request

### Pull Request or Branch
_Link to PR or branch name:_

### Description of Changes
_Brief summary of what was changed and why:_

---

## 📋 Standards Checklist

### Code Quality
- [ ] Follows [CODING_STANDARDS.md](/docs/CODING_STANDARDS.md) naming conventions
- [ ] Uses appropriate base classes (Diagnostic_Base, Treatment_Base, AJAX_Handler_Base)
- [ ] No DRY violations - code is not duplicated unnecessarily
- [ ] Proper WordPress coding standards (PHPCS compliant)
- [ ] Security best practices followed (nonce checks, capability checks, sanitization)
- [ ] Performance optimized (no N+1 queries, uses caching where appropriate)

### Architecture Alignment
- [ ] Follows [ARCHITECTURE.md](/docs/ARCHITECTURE.md) patterns
- [ ] Uses proper namespacing (`WPShadow\Category`)
- [ ] Class files in correct directory structure
- [ ] Proper separation of concerns

### Testing
- [ ] All tests pass
- [ ] New features have test coverage
- [ ] Edge cases considered and tested

---

## 🎯 Philosophy Checklist

Before merge, verify alignment with the [11 Commandments](/docs/PRODUCT_PHILOSOPHY.md):

- [ ] **Helpful Neighbor:** Helps or empowers users genuinely
- [ ] **Free First:** Free tier is complete, not paywalled (Commandment #2, #3)
- [ ] **Educational:** Educational content, not sales-focused (Commandment #4, #5, #6)
- [ ] **Quality:** Ridiculously good quality (Commandment #7)
- [ ] **Confidence:** UX inspires confidence (Commandment #8)
- [ ] **Show Value:** Will track KPIs showing value (Commandment #9)
- [ ] **Privacy:** Respects privacy, consent-first (Commandment #10)
- [ ] **Worth Sharing:** Worth talking about with friends (Commandment #11)

---

## 📚 Documentation

- [ ] KB article linked (if diagnostic/treatment)
- [ ] Training video linked (if treatment)
- [ ] Code comments for complex logic
- [ ] README/docs updated if needed

---

## 🔗 Related

- Related issue #
- Related diagnostic/treatment:
- Related milestone/phase:

---

## 📝 Additional Context

_Screenshots, examples, performance metrics, or other relevant information:_
