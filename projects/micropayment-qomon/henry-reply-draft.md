# Draft reply to Henry

*Tone: warm, confident, short. Questions grouped so he can answer what he knows and pass
the rest on. Don't send all thirteen from the scoping note — these are the ones that change
the estimate.*

---

**Subject:** Re: Micropayment → Qomon integration

Hi Henry,

Thanks for thinking of us — yes, this is squarely the kind of work we do, and both
platforms have what we'd need. Micropayment supports signed notifications plus a
server-side API for backfill and subscription state, and Qomon's API models donations and
memberships as transactions against a contact record, so the shape of the integration is
clear enough already.

The pattern we'd build is a small middleware service rather than a point-to-point script:
signed webhook in, idempotent contact match and transaction create out, plus a nightly
reconciliation against the Micropayment settlement report. With SEPA direct debit in
particular that last piece matters — a collection can be returned weeks after the fact, and
the CRM needs to reflect that or the fundraising figures drift.

A few questions that would let me put a proper shape and number on it:

1. Is this one-way (payments into Qomon), or does Qomon also need to start or cancel
   payments?
2. Which Micropayment products are in play — SEPA direct debit, cards, subscriptions — and
   is it one-off giving, recurring, memberships, or all of them?
3. Roughly what transaction volume per month, and is there history to backfill?
4. Is Qomon's Fundraising module already switched on for them? It's still marked beta, so
   I'd want to confirm the transaction endpoints early.
5. Are donation receipts or any statutory reporting in scope, or handled elsewhere?

One practical note on timing: a mid-September start is very doable, but we'd want Qomon API
keys and a Micropayment sandbox in hand around two weeks beforehand. Worth flagging to them
now, since that provisioning tends to be the thing that slips rather than the build.

Happy to jump on a call with their technical contact once there's something to talk about.

Very best,
Emanuel
