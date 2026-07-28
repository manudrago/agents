# Influential Software — Claude Code Subagents

Claude Code subagent definitions for Influential Software's senior marketing team and delivery team. Each agent lives in `.claude/agents/` and is available automatically in any Claude Code session opened in this repository.

## Usage

Ask Claude to use an agent by name or by task, e.g.:

- "Use the content-strategist to draft a case study about the ERP integration project"
- "Have the delivery-manager produce this week's status report from these notes"
- "Ask the seo-specialist to write a content brief for 'systems integration consultancy'"

Claude will also delegate to the right agent automatically based on each agent's description.

## Marketing team

| Agent | Use for |
|---|---|
| `marketing-director` | Strategy, positioning, messaging, campaign prioritisation, and final review of marketing output |
| `content-strategist` | Blog posts, case studies, white papers, landing pages, emails, editorial calendars |
| `seo-specialist` | Keyword research, on-page and technical SEO, search-driven content briefs |
| `social-media-manager` | LinkedIn strategy and posts, social calendars, content repurposing |
| `demand-gen-manager` | Multi-channel campaign plans, paid media, lead nurture, funnel measurement |

## Delivery team

| Agent | Use for |
|---|---|
| `delivery-manager` | Delivery plans, RAID logs, client status reports, scope and change control |
| `solutions-architect` | Solution designs, architecture reviews, technical proposals and estimates |
| `qa-lead` | Test strategies, test cases, UAT plans, quality gates |
| `client-success-manager` | Client communications, account reviews, renewal and expansion planning |

## How the team fits together

- The **marketing-director** sets strategy and reviews work from the other marketing agents.
- The **seo-specialist** produces briefs the **content-strategist** writes from; the **social-media-manager** repurposes the results; the **demand-gen-manager** wires it all into campaigns.
- The **delivery-manager** coordinates the **solutions-architect** and **qa-lead**, and feeds updates to the **client-success-manager**, who owns everything the client sees.
- The **client-success-manager** hands case-study candidates to the marketing team (subject to client approval).

## Marketing work in progress

The `marketing/` directory holds live strategy documents:

| Document | What it is |
|---|---|
| [`marketing-strategy.md`](marketing/marketing-strategy.md) | Full marketing strategy — domain consolidation, legacy-as-migration-engine, AI service integration, channel plan, phased roadmap |
| [`domain-audit-checklist.md`](marketing/domain-audit-checklist.md) | Keep/merge/kill worksheet for the ~25-domain estate, with decision rules and migration hygiene checks |
| [`ai-services-page-copy.md`](marketing/ai-services-page-copy.md) | Draft copy for the AI services hub, with SEO notes and a supporting content cluster |

All three are drafts for review. The strategy is based on publicly available information — its quantitative claims need validating against Search Console, analytics, and CRM data before budget is committed.

## Editing agents

Each agent is a Markdown file with YAML frontmatter (`name`, `description`, optional `model`) followed by its system prompt. Edit the files directly, or ask Claude to update them. Docs: <https://code.claude.com/docs/en/sub-agents>
