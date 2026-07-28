# Google Ads — Audit Framework and Improvement Plan

**Status:** framework and hypotheses, pending account data
**Prepared:** July 2026

> **Important:** this plan was written without access to the Google Ads account. Every recommendation below is a hypothesis based on the business profile — UK B2B enterprise consultancy, long sales cycles, high deal values, fragmented domain estate. Validate each against the account data listed in section 1 before changing anything. Nothing here should be actioned on faith.

---

## 1. What to export first

Pull these before making any changes. Twelve months where available, so seasonality doesn't mislead.

| Report | Columns needed | What it answers |
|---|---|---|
| Campaign performance | Campaign, type, cost, clicks, conversions, conv. value, CPA, impression share, IS lost (budget), IS lost (rank) | Where the money goes and what it returns |
| Search terms report | Search term, matched keyword, match type, cost, clicks, conversions | The single most revealing report — what you're *actually* paying for |
| Keyword performance | Keyword, match type, Quality Score, exp. CTR, ad relevance, LP experience, cost, conversions | Where Quality Score is inflating your costs |
| Landing pages | Page URL, cost, conversions, conv. rate, mobile speed score | Which domains are receiving paid traffic |
| Conversion actions | Action name, category, count, "included in Conversions" flag, attribution model, counting method | **Check this first** — see section 2 |
| Geographic | Location, matched location type, cost, conversions | Overseas waste |
| Device | Device, cost, conversions, conv. rate | Mobile spend on a desktop-research buyer |
| Ad schedule | Day/hour, cost, conversions | Out-of-hours waste |
| Auction insights | Domain, impression share, overlap rate, outranking share | Who you're bidding against |
| Assets/extensions | Asset type, impressions, CTR | Whether extensions are set up at all |

Also note from the CRM: **closed-won deal value by source over 24 months.** Without this, everything below is optimising toward form fills rather than revenue — which for a business with your deal sizes is the difference between a profitable account and an expensive one.

---

## 2. The improvements, in priority order

### Priority 1 — Fix what counts as a conversion

**Hypothesis:** the account optimises toward raw form submissions, with every conversion weighted equally and no feedback from the CRM about which leads became opportunities.

This is the most common and most expensive failure in B2B Google Ads, and it matters more for you than for most advertisers. Google's Smart Bidding optimises toward whatever you tell it a conversion is. Tell it "form fill", and it will reliably find you the cheapest form fills available — which means students looking for Cognos tutorials, job seekers, overseas enquiries, and vendors selling you things. All of them convert. None of them buy.

**What to do:**

1. Audit every conversion action. Anything that isn't a genuine sales enquiry — newsletter signups, PDF downloads, page views, phone clicks under 30 seconds — comes out of the primary "Conversions" column and becomes a secondary action. Keep measuring it; stop bidding toward it.
2. Implement **offline conversion import** (or Enhanced Conversions for Leads). Capture the GCLID on form submission, store it against the CRM record, and upload back to Google when a lead becomes an MQL, an SQL, and an opportunity. This is the unlock: it lets Google optimise toward *qualified* leads rather than any lead.
3. Move to **value-based bidding** once offline data is flowing. A training enquiry and an enterprise migration enquiry are not worth the same, and the account should know that.

**Expected effect:** this typically reduces lead volume and increases lead quality — sometimes dramatically. Agree that expectation with stakeholders beforehand, or month one looks like a failure.

**Effort:** medium (needs CRM and dev involvement). **Impact:** very high.

---

### Priority 2 — Mine the search terms report for waste

**Hypothesis:** a substantial share of spend goes to informational, educational, job-seeking, and overseas traffic — because your product keywords (Cognos, BusinessObjects, Power BI, SharePoint, Java, PHP) are magnets for students, self-learners, and job hunters.

**What to do:** work the search terms report line by line and build a negative keyword library. A starter list, to be extended from actual data:

**Employment:** jobs, job, career, careers, salary, salaries, vacancy, vacancies, recruitment, recruiter, cv, resume, apprenticeship, internship, hiring, contractor rates

**Learning/DIY:** tutorial, tutorials, free, how to, what is, guide, learn, course free, youtube, pdf download, cheat sheet, examples, sample, exercises, w3schools, udemy, coursera, certification exam, dumps, practice test

**Software piracy/self-serve:** download, crack, keygen, licence key free, trial, open source alternative, cheapest

**Wrong intent:** definition, meaning, wikipedia, reddit, vs, comparison (unless deliberately targeting comparison intent), review, salary guide

**Irrelevant products:** any product names you don't support — worth checking, as broad match will find them

Add these at **account level** as a shared negative list so they apply everywhere, then maintain campaign-specific lists on top.

**Effort:** low. **Impact:** high — this is usually the fastest money saved.

---

### Priority 3 — Geography and network settings

**Hypothesis:** the account is running on default settings that leak budget internationally.

Three checks, all quick:

1. **Location targeting must be set to "Presence" only**, not the default "Presence or interest". The default serves ads to anyone *interested in* the UK — which, for globally-used products like Cognos and BusinessObjects, means paying for clicks from all over the world. This single setting is often the largest source of waste in accounts like yours.
2. **Search Partners and Display Expansion off** for lead-gen search campaigns, at least until you can prove they convert. Check their performance separately before deciding.
3. **Ad scheduling** — your buyer is at work. If out-of-hours traffic converts materially worse, restrict or bid down. Check before assuming.

**Effort:** minimal. **Impact:** high.

---

### Priority 4 — Restructure around intent, not products

**Hypothesis:** campaigns are organised by product or by microsite, which mixes buyers with wildly different value and cycle length in the same budget and bidding pool.

A structure that fits the strategy:

| Campaign | Intent | Why it's separate |
|---|---|---|
| **Legacy migration** — "Cognos to Power BI", "BusinessObjects migration", "Sybase end of support" | High-intent, low-competition, high value | Your best opportunity. Cheap clicks, serious buyers. Deserves its own protected budget |
| **Extended support** — "IBM extended support", "PowerHouse support", legacy support terms | High-intent, urgent | Different message (continuity, not change). Often the entry offer that leads to migration |
| **Modern platform consultancy** — Power BI, Azure, Boomi, SAP Analytics Cloud consultancy terms | Competitive, mid-intent | Expensive terms; needs tight qualification and strong landing pages |
| **AI services** | Emerging, unproven | Small test budget. Do not scale until conversion quality is proven — this market is full of tyre-kickers |
| **Training** | Different buyer entirely, lower deal value | Must not share a budget with consultancy. Lower CPA target, different landing pages |
| **Brand** | Defensive | Cheap, high-converting. Check whether competitors bid on your name |

**On bidding with low volume:** B2B accounts frequently have too few conversions per campaign for Smart Bidding to learn — Target CPA generally needs ~30 conversions/month per campaign. If you're below that, either use **portfolio bid strategies** to pool data across campaigns, or stay on Maximise Clicks/Manual CPC with tight keyword control until offline conversion import (Priority 1) increases usable signal. Fragmenting into many small campaigns each starved of data is a common and costly mistake.

**On Performance Max:** if any PMax campaign is running, scrutinise it. For low-volume B2B lead gen it frequently absorbs brand traffic you'd have won for free and reports it as new conversions. Check the brand/non-brand split before crediting it with anything.

**On match types:** broad match only works when the conversion signal is trustworthy. Until Priority 1 is done, favour phrase and exact.

**Effort:** high. **Impact:** high.

---

### Priority 5 — Fix the landing page problem

**Hypothesis:** paid traffic lands on the product microsites, which means slow pages, dated design, weak or missing forms, and conversion tracking scattered across many domains.

This connects directly to the consolidation strategy, and **sequencing matters**:

- **Don't** invest in optimising landing pages on domains scheduled for retirement.
- **Don't** run paid traffic to a domain the week you migrate it — you'll lose the redirect in the middle of a learning period and corrupt your data.
- **Do** pause or redirect paid campaigns pointing at any domain before it migrates, and rebuild those landing pages on the consolidated site.

Landing page requirements for paid traffic: message-matched to the ad (if the ad says "Cognos migration", the page headline says Cognos migration), a form above the fold, no more than five fields, proof visible without scrolling, mobile load under 2.5s LCP, and a single call to action per page.

**Effort:** high. **Impact:** high — but must follow the domain migration schedule.

---

### Priority 6 — Ad copy and assets

**Hypothesis:** Responsive Search Ads are running with default assets and limited extensions.

- Every campaign needs at least one RSA with 15 headlines and 4 descriptions, with 2–3 headlines pinned to position 1 to guarantee the message (product name and intent) always appears.
- Lead with what differentiates you: **UK-based**, **since 1993**, **certified partner**, **we support what others won't**. These are unusual and credible claims in a market of generic consultancies.
- Enable all relevant assets: sitelinks (4+), callouts, structured snippets, call, lead form (test carefully — lead form quality is often poor), and location.
- Test one ad per ad group against the RSA. Don't run more than 2–3 ads per ad group with low volume; you'll never reach significance.

**Effort:** medium. **Impact:** medium.

---

### Priority 7 — Audiences

Layer these as **observation** first (to gather data without restricting reach), then move the best performers to targeting with bid adjustments:

- **Customer Match** — upload your CRM contact list. Exclude existing clients from acquisition campaigns; target them separately for cross-sell.
- **Remarketing (RLSA)** — bid up for people who've visited the site before. In a nine-month buying cycle this is disproportionately valuable.
- **In-market segments** — Business Software, IT Consulting, Enterprise Software.
- **Detailed demographics** — company size where available.
- **Exclusions** — job seekers and students where the segments allow.

**Effort:** low–medium. **Impact:** medium.

---

## 3. Sequencing

Run in this order — several items depend on those before them.

**Weeks 1–2 (quick wins, low risk):**
- Location targeting → Presence only
- Search Partners / Display Expansion off
- Account-level negative keyword list applied
- Conversion actions audited; junk actions demoted to secondary

**Weeks 3–6 (foundations):**
- GCLID capture and CRM storage built
- Offline conversion import live
- Campaign restructure planned (not yet executed)
- Brand campaign checked and protected

**Weeks 7–12 (rebuild):**
- Restructure executed, aligned to the domain migration schedule
- Landing pages rebuilt on the consolidated domain
- RSAs and assets rewritten
- Audiences layered as observation

**Week 13+ (optimise):**
- Move to value-based bidding once offline data has 60+ days of history
- Scale the legacy migration campaign if quality holds
- Test AI services campaign with a capped budget

---

## 4. What success looks like

Stop reporting on cost per lead alone. For a business with your deal values and cycle length it's actively misleading — the cheapest leads are almost always the worst ones.

**Report monthly:**
- Cost per **qualified** lead (MQL), not per form fill
- Cost per opportunity
- Pipeline value generated, by campaign
- Lead-to-opportunity conversion rate, by campaign — this is what tells you which campaigns bring real buyers
- Wasted spend percentage (cost on search terms with zero qualified leads over 90 days)

**Expect, after Priority 1:** fewer leads, higher cost per lead, more pipeline. If lead volume drops and pipeline holds or grows, the account is working correctly. Brief stakeholders on this before you start, not after.

---

## 5. Open questions for the account

Answer these from the exports; they'll materially change the recommendations:

1. What is total monthly spend, and how is it split across campaign types?
2. Are there any Performance Max or Display campaigns running, and what share of conversions do they claim?
3. What percentage of spend goes to brand versus non-brand terms?
4. Which domains currently receive paid traffic?
5. What is the current conversion action list, and which are counted as primary?
6. Is there any CRM connection today, or is the account blind after the form fill?
7. Impression share lost to budget on the highest-intent campaigns — are you leaving cheap, qualified clicks on the table?
