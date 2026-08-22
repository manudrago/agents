# Micropayment → Qomon integration — scoping note

**Prospect:** German progressive organisation (via Henry)
**Status:** Deal not signed. Indicative start mid-September.
**Verdict:** Feasible and well within our normal integration work. Both platforms expose the interfaces this needs.

---

## 1. What this is

A payments-to-CRM integration: donation and membership payments processed by
[Micropayment GmbH](https://www.micropayment.de/) (Berlin-based PSP — SEPA direct debit,
credit card, PayPal, subscriptions) need to land in
[Qomon](https://qomon.com/) as contacts and transactions, so the organisation's
campaigners and fundraisers work from one supporter record.

This is a well-trodden shape of problem. The engineering risk is low; the risk sits in
platform maturity, access, and German fundraising compliance (see §5).

## 2. Platform readiness

### Qomon (the destination)

- REST API, API key passed in the `Authorization` header, keys self-served from
  **Settings → Connect/API** in the web app.
- Resource groups: **Contacts**, **Search**, **Actions**, **Users**, **Fundraising**.
  Full CRUD, no extra per-call charge.
- A "transaction" in Qomon is a donation *or* a membership — exactly the right model
  for this client.
- Qomon explicitly positions the API as the way to "build a custom integration with any
  payment provider", so we are working with the grain of the product.
- **Caveat:** the Fundraising module is documented as **Beta**. Confirm at discovery that
  the transaction endpoints are stable, cover refunds/chargebacks, and can express the link
  between a recurring donation and its parent subscription.

### Micropayment (the source)

- Technical docs at [techdoc.micropayment.de](https://techdoc.micropayment.de/?lang=en):
  Payment Gateway, **Debit API** (SEPA Lastschrift), CreditCard Service, and a
  **Service Client** for server-side calls.
- Merchant identified by an `accessKey` issued at partner registration and retrievable
  from the control centre.
- Push notifications: a **Notification URL** configured in the Micropayment dashboard,
  with signing via the `secretfield` / `redirectDataHash` parameters — i.e. a standard
  signed webhook, which is what we want.
- Pull/backfill and subscription lifecycle handled through the Service Client APIs.
- **Caveat:** documentation is German-first and the interfaces are older-style
  (HTTP-parameter and service-client rather than a clean modern REST/JSON surface).
  Budget time for reading and for a sandbox account.

## 3. Proposed architecture

A small stateful middleware service — not a point-to-point script. Payments need an audit
trail and replay.

```
Micropayment  --(signed notification)-->  Integration service  --(REST)-->  Qomon
     ^                                          |
     |                                    event store / DLQ
     +--(Service Client: poll, backfill,        |
         subscription state, settlement) <------+
                                                |
                                         reconciliation job
                                         + discrepancy report
```

**Happy path**
1. Donation completes at Micropayment; notification POSTs to our endpoint.
2. Verify the signature, persist the raw event, ACK fast.
3. Idempotency: dedupe on the Micropayment transaction ID (a replayed webhook must never
   create a second donation).
4. Resolve the supporter in Qomon — search by email, fall back to name + postcode; create
   the contact if absent.
5. Create the Qomon transaction (donation or membership), stamped with the Micropayment
   transaction ID in a custom field for reconciliation.

**The parts people forget, and where the real value is**
- **SEPA returns.** With direct debit, money is not final: a *Rücklastschrift* can arrive
  up to eight weeks later. The CRM must reflect returns, not just successful collections,
  or the fundraising numbers are wrong.
- **Recurring / Abo lifecycle.** Renewals, failed collections, cancellations, amount
  changes — each needs a defined effect on the Qomon record.
- **Refunds and chargebacks** — reversal or negative transaction, per Qomon's model.
- **Nightly reconciliation** comparing the Micropayment settlement report against Qomon
  transactions, producing a discrepancy report. This is what makes the integration
  trustworthy at audit time.
- **Retries, dead-letter queue, alerting, and a manual replay screen** for the ops team.

## 4. Indicative shape of the work

Subject to discovery — these are planning figures, not a quote.

| Phase | Indicative |
|---|---|
| Discovery: API access, sandbox, data model, mapping workshop | 3–5 days |
| Build: webhook ingest, contact matching, transaction sync, idempotency | 2 weeks |
| Build: recurring lifecycle, returns/refunds, reconciliation + reporting | 1–2 weeks |
| UAT with the client, in German where needed, plus go-live and hypercare | 1 week |

Mid-September start is realistic **provided** API keys and a Micropayment sandbox are in
our hands roughly two weeks beforehand. That lead time is the single biggest schedule risk
and should be flagged in any proposal.

## 5. Risks and things to watch

| Risk | Why it matters | Mitigation |
|---|---|---|
| Qomon Fundraising API is Beta | Endpoints may not cover refunds, chargebacks or subscription linkage | Validate in discovery; fall back to contacts + custom fields/actions if needed |
| Sandbox access lead time | Micropayment test accounts are merchant-provisioned | Request on day one of discovery, before the build phase |
| GDPR / DSGVO | Donation data for a political organisation can imply political opinion — Art. 9 special category | EU-only hosting, AVV/DPA in place, data minimisation, defined retention |
| German fundraising law | Donation receipts (*Zuwendungsbescheinigung*), and party donation publication thresholds under *Parteiengesetz* §25 if the client is a party | Explicitly scope in or out, in writing, before signature |
| Direction of sync | One-way (payments → CRM) is a fraction of the cost of two-way | Pin down before estimating |
| Historic backfill | Volume drives effort and rate-limit design | Ask for transaction counts and date range |
| Language | German-first docs and likely German-speaking stakeholders | Confirm working language for UAT and documentation |

## 6. Questions for Henry

**Scope**
1. One-way (Micropayment → Qomon) or does Qomon also need to trigger or cancel payments?
2. Which Micropayment products are in use — SEPA direct debit, credit card, PayPal, subscriptions?
3. One-off donations, recurring, memberships, or all three?
4. Where does the donor journey start — a Micropayment-hosted page, the organisation's own
   donation form, or Qomon's fundraising forms?
5. Are donation receipts or statutory reporting in scope, or handled elsewhere?

**Access and environment**
6. Is the Qomon Fundraising module enabled on their plan, and can we get API keys early?
7. Who owns the Micropayment merchant account, and can they provide a sandbox and a
   technical contact?
8. Do they already have an integration, manual process, or CSV workflow we are replacing?

**Volume and operations**
9. Transactions per month, supporter base size, and how much history needs backfilling?
10. How are refunds and returned direct debits handled today, and by whom?
11. Who hosts and supports the middleware after go-live — us, them, or their infra?

**Commercial**
12. When is the decision expected, and what is the hard deadline behind mid-September
    (a campaign, a fundraising push, a contract end)?
13. Working language for delivery and UAT?
