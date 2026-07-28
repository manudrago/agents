# Lovable Build Prompt — AI Services Site

**Purpose:** rebuild `influentialsoftware.ai` as a focused campaign site that feeds the main domain, per the marketing strategy.
**Source of truth for copy:** [`ai-services-page-copy.md`](ai-services-page-copy.md) — this prompt embeds that copy so Lovable doesn't invent its own.

## How to use this

1. Fill in the **placeholders** table below first. Do not paste the prompt with `[BRACKETS]` still in it — Lovable will happily invent plausible-looking client names and statistics, and you do not want those going live.
2. Paste **Prompt 1** into a new Lovable project. Let it build completely before touching anything.
3. Work through the follow-up prompts **one at a time**, checking the result after each. Lovable handles a series of focused instructions far better than one enormous one, and small prompts are much easier to undo.
4. Keep the "never do" constraints in every prompt where they matter. They tend to drift on longer builds.

## Placeholders to fill before pasting

| Placeholder | What to put | Where to find it |
|---|---|---|
| `[BRAND_PRIMARY]` | Primary brand hex | Existing site CSS or brand guidelines |
| `[BRAND_DARK]` | Dark neutral hex for text/footers | As above |
| `[BRAND_ACCENT]` | Accent/CTA hex | As above |
| `[PHONE]` | Switchboard number | Main site contact page |
| `[EMAIL]` | Enquiries address | As above |
| `[MAIN_SITE_URL]` | `https://www.influentialsoftware.com` | — |
| `[TRAINED_COUNT]` | People trained, e.g. `12,000` | Marketing records — verify before using |
| `[APPROVED_CLIENTS]` | Only clients with written sign-off | Client success manager |
| `[CASE_STUDY_1..3]` | Challenge / approach / outcome, 3 sentences each | Delivery team + client approval |

If a placeholder can't be filled with something true yet, delete that section from the prompt rather than shipping an invented version. It's much cheaper to add a testimonials section next week than to explain a fabricated client reference.

---

# PROMPT 1 — Initial build

> Paste everything between the rules below into a new Lovable project.

---

Build a marketing website for the AI services division of Influential Software, a UK enterprise software consultancy founded in 1993. The audience is IT directors, CTOs and heads of data at UK mid-market and enterprise organisations. This is a serious B2B consultancy site, not a startup landing page.

## Tech and structure

- React + Vite + TypeScript + Tailwind CSS + shadcn/ui
- Single-page site with smooth-scroll anchor navigation, plus a separate `/contact` route
- Fully responsive: mobile-first, tested at 375px, 768px, 1440px
- Light and dark mode, toggled from the header, respecting `prefers-color-scheme` by default
- Semantic HTML5 landmarks, WCAG 2.1 AA: 4.5:1 text contrast minimum, visible focus rings, keyboard-navigable, alt text on all images, `aria-label` on icon-only buttons
- Respect `prefers-reduced-motion` — disable all scroll and hover animation when set

## Design direction

Restrained, confident, enterprise. Think of the reference points as consultancies like Thoughtworks or Kainos rather than SaaS product sites. The visual job is to make a thirty-year-old consultancy look current without looking like it's chasing trends.

- **Colours:** primary `[BRAND_PRIMARY]`, dark neutral `[BRAND_DARK]`, accent `[BRAND_ACCENT]`. Build a full Tailwind scale from these. Backgrounds are predominantly white/near-white in light mode and a deep neutral (not pure black) in dark mode. Use the accent sparingly — CTAs and small highlights only.
- **Typography:** one clean geometric or neo-grotesque sans (Inter, Söhne-like, or similar) via a self-hosted or Google font. Large, tight-tracked headings; body text at 17–18px with generous line height (1.6+). Maximum 70 characters per line in body copy.
- **Layout:** generous whitespace, a consistent 8px spacing scale, max content width around 1200px with narrower 720px text columns for reading sections. Let sections breathe — this should feel unhurried.
- **Motion:** subtle only. Fade-and-rise on scroll for section entry (short duration, no stagger longer than 400ms total), gentle hover states on cards and buttons. No parallax, no counters spinning up, no typewriter effects.
- **Imagery:** abstract geometric shapes, subtle gradient meshes, or line-based system diagrams. Absolutely no stock photos of people pointing at screens, no glowing blue brains, no robot hands touching human hands, no circuit-board motifs.

## Page sections, in order

### Header
Sticky, transparent over the hero, gaining a background and subtle border on scroll. Left: "Influential Software" wordmark with a small "AI" designation. Centre/right: anchor links — Services, Why Us, Process, Contact. Far right: dark mode toggle and a primary button "Book an assessment". Mobile: hamburger opening a full-screen overlay menu.

### Hero
Full-viewport-height minus header, left-aligned text (not centred), with an abstract geometric visual on the right that collapses below the text on mobile.

- H1: **AI that works on the systems you already run**
- Subhead: Most enterprise AI projects don't fail because the models are wrong. They fail because the data underneath them is scattered across systems that were never designed to talk to each other.
- Second line: We've spent 30 years connecting those systems for UK enterprises. That's the part that makes AI work.
- Primary CTA: "Book an AI readiness assessment" · Secondary CTA (ghost/outline): "See how we work"
- Below the CTAs, a quiet single line of small text: Certified partners: Microsoft · IBM · SAP · Salesforce · Boomi · Apple

### Problem section — "Why enterprise AI stalls"
Intro paragraph: The pattern is consistent. A pilot succeeds in a controlled environment, executive enthusiasm follows, and then the rollout meets reality.

Four cards in a 2×2 grid (single column on mobile), each with a small line icon, a bold lead-in and body text:

1. **The data isn't ready.** It sits in a decade-old ERP, three departmental databases, a BI platform nobody has upgraded since 2018, and several thousand spreadsheets. AI trained on inconsistent data produces confident, inconsistent answers.
2. **The systems don't connect.** Getting a model into a workflow means integrating with the systems people actually use. That integration work is usually larger than the AI work — and it's the part nobody budgeted for.
3. **Nobody owns governance.** Who approves what the AI can access? What happens when it's wrong? Under UK GDPR, what is the lawful basis for the data it processes? Without answers, IT blocks the rollout — correctly.
4. **The team hasn't been brought along.** Tools nobody trusts or knows how to use don't get used, however good the technology.

Closing line below the grid, visually emphasised: None of these are AI problems. They're enterprise integration, data, and change problems — which is what we've been doing since 1993.

### Services section — "What we do"
Five services as alternating full-width rows (text left / visual right, then reversed), not a grid — each deserves room. Every row has a heading, body paragraph, a "Right for you if…" line in a subtly tinted box, and a text-link CTA with an arrow.

1. **AI-ready data foundations** — Before AI can be useful, your data has to be consistent, accessible, and trusted. We build that foundation using the analytics platforms you already run — Power BI, SAP Analytics Cloud, IBM Cognos, IBM Planning Analytics — or help you move to ones that fit better. Typically: a data audit, a plan to consolidate and clean the sources that matter, and a governed reporting layer AI can safely draw on.
   *Right for you if* your reporting is fragmented, your teams argue about whose numbers are correct, or an AI proof-of-concept has stalled on data quality.
   CTA: "Talk to us about data foundations"

2. **AI-powered integration** — We're a certified Boomi partner, and Boomi has been building AI capability directly into its integration platform. That means integrations that adapt to change rather than breaking, faster mapping between systems, and automation that handles exceptions instead of escalating every one of them to a person. We use it to connect enterprise systems — ERP, CRM, HR, finance, bespoke applications — so data moves reliably and AI has something coherent to work with.
   *Right for you if* you're running point-to-point integrations that break whenever a system is upgraded, or manual processes persist because the systems can't talk to each other.
   CTA: "Explore AI-powered integration"

3. **Microsoft Copilot and Azure OpenAI enablement** — Most UK enterprises already have the licences. Far fewer are getting value from them. We help you deploy Microsoft Copilot and Azure OpenAI properly: the permissions and data governance that stop Copilot surfacing things it shouldn't, integration with your existing Microsoft estate, and the enablement work that turns a licence into a habit.
   *Right for you if* you've bought Copilot licences and adoption is low, or you want to build on Azure OpenAI but need the security and governance settled first.
   CTA: "Get Copilot working properly"

4. **AI-assisted legacy modernisation** — Moving off a legacy platform has always meant a slow, expensive, risky rewrite. AI has genuinely changed the economics of the analysis phase — understanding what undocumented code does, mapping dependencies, and identifying what can be retired rather than rebuilt. We use AI to accelerate that work on the platforms we've supported for decades: Sybase, PowerHouse, legacy .NET, and ageing BI estates. The judgement stays with our architects. The tedious archaeology moves considerably faster.
   *Right for you if* you're running a platform approaching end of life, the people who built it have left, and nobody can say with confidence what it does.
   CTA: "Discuss a modernisation assessment"

5. **AI adoption training** — We've trained over [TRAINED_COUNT] people across UK enterprises. We now run practical AI training for teams that need to use these tools well and safely — what the tools do, where they fail, what to check before trusting an output, and what your governance policy actually means day to day. Delivered for your teams, using your tools, in your context. Not a generic vendor course.
   *Right for you if* you've rolled out AI tools and want confident, safe usage rather than either avoidance or over-trust.
   CTA: "See training options"

### Differentiation section — "Why Influential Software"
Five points, as a two-column list or compact cards, each a bold lead-in plus one or two sentences:

- **We've done the unglamorous part for 30 years.** AI sits on top of enterprise data and enterprise systems. Connecting those systems is what we've done since 1993, across manufacturing, publishing, government, insurance, and retail.
- **We're certified where it counts.** Partner status with Microsoft, IBM, SAP, Salesforce, Boomi, and Apple. Unusually broad for a boutique integrator — and it means our advice isn't shaped by having only one platform to sell.
- **We know your legacy estate.** We support platforms most consultancies won't touch. If your AI plan depends on data locked inside something ageing, we've probably worked on it.
- **We're a UK team.** London, Glasgow, and Kent. UK GDPR, UK data residency, and UK working hours are the default, not an add-on.
- **We'll tell you when the answer is no.** If AI isn't the right tool for the problem you've described, we'd rather say so early than sell you a project that disappoints.

### Process section — "How an engagement starts"
Four numbered steps as a horizontal timeline on desktop, vertical on mobile:

1. **AI readiness assessment.** A short, fixed-scope piece of work. We look at your data, systems, and governance and tell you what's genuinely ready, what needs work first, and what the realistic sequence is.
2. **A prioritised plan.** What to do, in what order, with effort ranges and the assumptions behind them. If something isn't worth doing yet, we say so.
3. **Delivery in phases.** We build the foundations and the first use case together, so you see value before committing to a programme.
4. **Enablement and handover.** Your team is trained to run it. We stay involved as much or as little as you need.

### Closing CTA section
Visually distinct band (deep brand colour in light mode, elevated surface in dark mode).

- Heading: **Start with an honest assessment**
- Body: No pitch deck. A conversation about what you're trying to achieve, what you're running today, and whether AI is genuinely the right answer.
- Primary CTA: "Book an AI readiness assessment"
- Below: `[PHONE]` and `[EMAIL]` as tel: and mailto: links

### Footer
Three columns: (1) wordmark and a one-line description, plus "Part of Influential Software" linking to `[MAIN_SITE_URL]`; (2) service anchor links; (3) contact details and LinkedIn. Bottom bar: copyright, privacy policy and cookie policy links (placeholder routes are fine), and company registration line.

## Never do these

- Never invent client names, logos, testimonials, statistics, case studies, or awards. If a section needs proof I haven't supplied, build the layout with a clearly visible `TODO: awaiting approved content` marker instead of plausible filler.
- No lorem ipsum anywhere — use the copy above verbatim.
- No American spellings. This is British English: "optimise", "specialise", "organisation", "programme".
- No hype vocabulary: avoid "revolutionary", "cutting-edge", "unleash", "supercharge", "game-changing", "10x".
- No AI clichés in imagery: no glowing brains, no robot hands, no humanoid robots, no binary rain, no circuit boards.
- No cookie banner, chat widget, or newsletter pop-up in this build.
- No fake urgency, countdown timers, or "limited slots" messaging.

## Also include

- Proper `<title>`, meta description, Open Graph and Twitter card tags. Title: `Enterprise AI Services UK | Influential Software`. Description: `AI that works on the systems you already run. Data foundations, AI-powered integration, Copilot enablement and modernisation from a UK partner since 1993.`
- JSON-LD structured data: `Organization` and `Service`.
- A favicon and a simple OG image generated from the wordmark and brand colours.
- Clean, componentised code: one component per section, design tokens in the Tailwind config rather than hardcoded hex values scattered through the markup.

---

# FOLLOW-UP PROMPTS

Run these in order, one at a time, after Prompt 1 has built successfully.

## 2 — Contact page and form

> Build the `/contact` route. Header: "Start with an honest assessment" and the line "Tell us what you're running today and what you're trying to achieve. We'll come back within one working day."
>
> Form fields: Name, Work email, Company, Job title, Phone (optional), "What are you trying to achieve?" (textarea), and a select for "Which service is closest to your need?" listing the five services plus "Not sure yet". Include a checkbox for consent to be contacted about the enquiry, with text explaining we'll only use these details to respond — do not pre-tick it, and do not add a marketing-consent checkbox.
>
> Client-side validation with clear inline errors, a loading state on submit, and a success state that thanks them and states the response time. Alongside the form, show the phone number, email address, and the three office locations (London, Glasgow, Kent) as text.
>
> Do not wire up a backend yet — leave the submit handler as a clearly marked TODO.

## 3 — Proof section (only once you have approved content)

> Add a proof section between "Why Influential Software" and the process timeline, headed "Where we've done this". Three case study cards, each with the client sector, a one-line challenge, the approach, and a measurable outcome. Use exactly this content and add nothing: [CASE_STUDY_1], [CASE_STUDY_2], [CASE_STUDY_3]. Below the cards, a line reading "Clients include [APPROVED_CLIENTS]". Do not add logos unless I supply the files.

## 4 — Performance and accessibility pass

> Audit and fix: ensure all images are lazy-loaded below the fold and served in modern formats, fonts are preloaded with `font-display: swap`, and there is no cumulative layout shift from late-loading fonts or images. Run through WCAG 2.1 AA — check colour contrast in both light and dark mode, confirm every interactive element is keyboard reachable with a visible focus state, verify heading hierarchy has no skipped levels, and confirm the mobile menu traps focus correctly and is dismissible with Escape. Report what you changed.

## 5 — Analytics and conversion tracking

> Add analytics with events for: hero primary CTA click, each service CTA click, closing CTA click, contact form start, and contact form submit. Use a single configurable analytics module so the provider can be swapped without touching the components. Leave the provider key as an environment variable placeholder.

## 6 — Copy tightening

> Review every line of copy on the site for length and rhythm. Anything over 25 words that can be split should be split. Remove any adjective that isn't doing work. Confirm British spellings throughout. Show me a diff of what you changed rather than applying it silently.

---

## After the build

- **Domain decision.** Per the strategy, `influentialsoftware.ai` should either 301 to `[MAIN_SITE_URL]/ai/` or run as a campaign site that links prominently back to the main domain. If it runs standalone, it needs a canonical strategy so it doesn't compete with the main site's AI pages in search.
- **Sign-off before publishing.** Solutions architect confirms every service line is deliverable as described; client success manager confirms any named client has written approval.
- **Content cluster.** The four supporting articles listed in `ai-services-page-copy.md` are what will actually earn search traffic. The site is the destination; the articles are the route in.
