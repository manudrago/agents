# Domain Portfolio Audit — Keep / Merge / Kill Worksheet

**Purpose:** decide the fate of every domain in the Influential Software estate using evidence, not sentiment. Complete one row per domain, then apply the decision rules at the bottom.

**Data sources needed before starting:**

- Google Search Console (per property): clicks, impressions, top queries, indexed pages — last 12 months
- Analytics (GA4 or equivalent): sessions, engaged sessions, conversions/enquiries — last 12 months
- CRM: leads/opportunities attributable to each domain — last 24 months (B2B cycles are long)
- Domain/hosting admin: renewal dates, hosting cost, CMS and version, SSL status
- Backlink tool (Ahrefs/Semrush/Moz): referring domains, domain rating

---

## Audit criteria (fill per domain)

| # | Field | How to assess |
|---|---|---|
| 1 | Organic clicks / 12 mo | Search Console total clicks |
| 2 | Top 3 ranking queries | Search Console — note intent (migration/support/training = valuable) |
| 3 | Leads / 24 mo | CRM attribution; forms, calls, quoted enquiries |
| 4 | Referring domains | Backlink tool — equity worth preserving via 301s |
| 5 | Content freshness | Date of last meaningful update; does it mention current product versions? |
| 6 | Technical state | HTTPS valid? Mobile OK? CMS patched? Core Web Vitals? |
| 7 | Strategic tier | Growth / Legacy-migration / Generic (see tiers below) |
| 8 | Annual cost | Hosting + domain + maintenance time |
| 9 | Brand risk | Would a prospect finding this site think less of us? (Y/N) |
| 10 | **Decision** | KEEP / MERGE / KILL |

---

## Domain inventory (pre-tiered)

### Tier 1 — Growth tech (default: MERGE into main site as service sections or subdomains)

| Domain | Product/practice | Clicks/12mo | Leads/24mo | Ref. domains | Fresh? | Decision | Notes |
|---|---|---|---|---|---|---|---|
| powerbi-influential.com | Power BI consulting | | | | | | Candidate: `/power-bi/` or subdomain |
| microsoft-azure-influential.com | Azure services | | | | | | Candidate: `/azure/` |
| sap-analytics-cloud.com | SAP Analytics Cloud | | | | | | Strong exact-match domain — check equity before deciding |
| sap-success-factors.com | SuccessFactors | | | | | | Trademark-sensitive domain; legal check advised |
| planning-analytics-influential.com | IBM Planning Analytics | | | | | | |
| app-development-influential.com | Custom development | | | | | | Merge into `/software-development/` |
| boomi.influentialsoftware.com | Boomi integration | | | | | | **Already on target model — KEEP** |
| jamf-training-influential.com | Jamf training | | | | | | Merge into training hub |
| addigy-training.co.uk | Addigy training | | | | | | Merge into training hub |

### Tier 2 — Legacy / migration on-ramps (default: KEEP content, reposition around migration + extended support; host on main domain where possible)

| Domain | Product/practice | Clicks/12mo | Leads/24mo | Ref. domains | Fresh? | Decision | Notes |
|---|---|---|---|---|---|---|---|
| business-objects-influential.com | SAP BusinessObjects | | | | | | Reposition: support + migration to Power BI/SAC |
| business-objects-training.co.uk | BO training | | | | | | Merge into BO property or training hub |
| cognos-analytics-influential.com | Cognos Analytics | | | | | | Reposition: support + modernisation |
| cognos-training.com | Cognos training | | | | | | Merge into Cognos property |
| sybase-influential.com | Sybase | | | | | | Reposition: extended support + migration off Sybase |
| powerhouse-support.com | PowerHouse | | | | | | Niche with near-zero competition — verify traffic before touching |
| metalogix-influential.com | Metalogix | | | | | | Product effectively absorbed by Quest — likely KILL unless traffic proves otherwise |
| ibm-extended-support.com | IBM extended support | | | | | | Strong commercial intent — audit carefully before merging |

### Tier 3 — Generic / utility (default: KILL with 301s to the relevant main-site service page)

| Domain | Product/practice | Clicks/12mo | Leads/24mo | Ref. domains | Fresh? | Decision | Notes |
|---|---|---|---|---|---|---|---|
| buyalicence.com | Licence sales | | | | | | If licence revenue is real, merge into `/licensing/` instead |
| cms-development.com | CMS development | | | | | | Broad term, unwinnable |
| java-influential.com | Java development | | | | | | 301 → software development page |
| php-influential.com | PHP development | | | | | | 301 → software development page |
| dot-net-influential.com | .NET development | | | | | | 301 → software development page |
| sharepoint-influential.com | SharePoint | | | | | | Could justify Tier 1 if traffic exists — check first |
| influential-training.com | Training hub | | | | | | Candidate to become `/training/` or training subdomain |
| influentialsoftware.ai | AI services | | | | | | KEEP as campaign door → 301 or point to AI hub on main site |

---

## Decision rules

Apply in order; first match wins.

1. **KILL** if: <100 organic clicks/12mo AND 0 leads/24mo AND <10 referring domains. 301 the whole domain to the closest main-site service page. Keep the domain registration for 2+ years (protect the redirect and stop squatters); drop hosting.
2. **MERGE** if: real traffic or backlinks, but the content belongs on the main domain. Migrate content to `influentialsoftware.com/<service>/` (or a subdomain for partner-branded practices, like Boomi/Vena), map every old URL to its new home with page-level 301s, verify in Search Console after 30 days.
3. **KEEP (standalone)** only if: the site generates meaningful leads today AND merging would demonstrably lose something (e.g. a partner co-marketing requirement, or an exact-match domain still ranking #1 for a commercial term). Expect very few of these.

## Migration hygiene (for every MERGE/KILL)

- [ ] Page-level 301 map written before anything moves (no blanket redirects to the homepage)
- [ ] Top backlinked pages identified and redirected to equivalent content, not the homepage
- [ ] Search Console change-of-address submitted where applicable
- [ ] Analytics annotations added on migration date
- [ ] Old sitemaps removed; new sitemap submitted
- [ ] Rankings for top 20 queries per domain tracked weekly for 90 days post-migration
- [ ] Any partner-portal listings, directories, and Google Business Profiles updated to new URLs

## Output

When every row has a decision: a one-page summary for sign-off — number of domains kept/merged/killed, estimated annual saving, migration order (start with the lowest-risk KILLs, end with the highest-traffic MERGEs).
