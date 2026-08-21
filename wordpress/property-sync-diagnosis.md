# Why inactive properties still show on lettsgetsmart.com

**Sites involved**

| Role | URL |
|---|---|
| Booking engine front end (property set A) | `https://travelaccommodationlondon.bookeddirectly.host/` |
| Booking engine front end (property set B) | `https://lettsgetsmartbookings.bookeddirectly.host/` |
| WordPress marketing site (the copy) | `https://lettsgetsmart.com/` |
| API the WordPress site pulls from | Postman collection `view/1320372/SWTBfdW6` |

**The core point:** "active" is a state that lives in the booking system. WordPress holds a *copy* of
each property as a local post. The `bookeddirectly.host` front ends render live data, so they go
correct the moment a property is deactivated. WordPress only becomes correct if something actively
tells it to. Nothing currently does — that is the bug, in one form or another.

---

## The six causes, in order of likelihood

### 1. The importer is create/update-only — there is no deactivation pass  ← most likely

The sync loops the API response and calls `wp_insert_post()` / `wp_update_post()` for each property
returned. A property that has *dropped out* of the response is never visited by the loop, so its post
is never touched and stays `publish` forever. The importer has no concept of "things I imported last
time that are gone now".

**Confirms it:** the missing property does not appear in the API response at all, and its WP post's
`post_modified` is old (it stopped being updated on the date it was deactivated).

**Fix:** a reconciliation pass — record every remote ID seen in a run, then draft every previously
synced post whose remote ID was not seen. That's what the mu-plugin in this directory does.

### 2. The endpoint returns *all* properties with a status flag, and the importer ignores it

Common in PMS APIs: `GET /properties` returns everything, each object carrying `status` /
`active` / `is_live` / `published` / `visible` / `archived`. The `bookeddirectly.host` front ends
filter on that flag; the WP importer maps the response straight to posts and never reads it. Related
variant: the endpoint accepts a filter parameter (`?status=active`, `?live=1`,
`include_inactive=false`) that the importer isn't sending.

**Confirms it:** the property *is* in the API response, carrying a flag that marks it inactive.

**Fix:** map the flag onto `post_status` on every import — inactive ⇒ `draft`. Do this *as well as*
the reconciliation pass; they catch different cases.

### 3. Wrong scope — one API key covering more properties than either public site shows

Two `bookeddirectly.host` sites means two site/channel scopes over what is probably one parent
account. If WordPress authenticates at the account level, it pulls properties belonging to *neither*
public site — including properties that are technically "active" in the PMS but not assigned to any
website. Those show nowhere on `bookeddirectly.host` and everywhere on WordPress.

**Confirms it:** the property is in the API response and flagged active, yet appears on neither
booking site.

**Fix:** scope the WP request to the same site/channel ID the public booking sites use, or filter on
the property's website-assignment field.

### 4. The sync hasn't actually run in weeks

WP-Cron only fires on page loads. Behind a full-page cache (LiteSpeed / WP Rocket, both common on
cPanel stacks) it barely fires at all, and if `DISABLE_WP_CRON` is set in `wp-config.php` without a
real server crontab entry it never fires. WordPress is then just displaying an old snapshot, and
*any* fix to the import logic will appear to do nothing.

**Confirms it:** the plugin's "last sync" option/timestamp is stale, or `wp cron event list` shows
the hook overdue.

**Fix:** a real cron entry —
`*/15 * * * * cd /home/USER/public_html && /usr/local/bin/php wp-cron.php >/dev/null 2>&1`
(cPanel → Cron Jobs), with `define( 'DISABLE_WP_CRON', true );` in `wp-config.php`.

### 5. Duplicate posts — the importer matches on title/slug instead of remote ID

If the upsert looks the property up by post title or slug rather than by a stored remote ID, a
renamed property creates a *second* post. The importer then keeps the new one current and the
original is orphaned as a published, permanently stale listing.

**Confirms it:** slugs like `apartment-x-2`, or two posts for one property, one of which has no
remote-ID meta.

**Fix:** always upsert on the remote ID stored in post meta. The reconciliation pass drafts the
orphan only if it carries a remote ID, so orphans with no meta must be cleaned up by hand once.

### 6. The post *is* a draft already, and something else is serving it

If the post shows as Draft in wp-admin but the page still loads publicly, the sync is fine and the
problem is downstream: a full-page or object cache holding the old HTML, a stale sitemap or search
index, or a custom `WP_Query` in the theme/shortcode using `'post_status' => 'any'` (or
`'post_status' => array('publish','draft')`).

**Confirms it:** post status is Draft in the admin, but the URL still returns 200 publicly.

**Fix:** purge the cache and grep the theme for `post_status`.

---

## Diagnostic order — five minutes, and it isolates the cause

Pick one property that is inactive on `bookeddirectly.host` but still showing on WordPress.

1. **wp-admin → the property post. What is its status?**
   Draft ⇒ **cause 6**, stop here, it's caching or a query. Published ⇒ continue.
2. **Call the API yourself** with the site's key (Postman collection `view/1320372/SWTBfdW6`,
   or `curl` from cPanel Terminal) and search the response for that property.
   - Absent ⇒ **cause 1**.
   - Present, flagged inactive ⇒ **cause 2**.
   - Present, flagged active ⇒ **cause 3**.
3. **Check `post_modified` on the post** and the plugin's last-sync timestamp. Both stale ⇒
   **cause 4** is in play as well, and must be fixed first or nothing else will take effect.
4. **Search the property list in wp-admin for near-duplicate titles/slugs** ⇒ **cause 5**.

Causes stack. On a site that has been running unattended, 1 + 4 together is the usual finding.

---

## Finding the sync code

Via cPanel File Manager (`public_html/wp-content/plugins/`) or Terminal:

```bash
cd ~/public_html
grep -rl "bookeddirectly" wp-content/plugins wp-content/themes
grep -rn "wp_remote_get\|wp_remote_post" wp-content/plugins/<the-plugin>/
grep -rn "post_status\|wp_insert_post\|wp_update_post" wp-content/plugins/<the-plugin>/
wp option list --search='*sync*' --search='*propert*'   # if WP-CLI is available
```

**Do not patch it through `wp-admin/plugin-editor.php`.** Two reasons: a plugin update overwrites
your edit and the bug silently returns, and a syntax error saved there white-screens the whole site
with no undo. Use the mu-plugin below instead — it sits *alongside* the existing plugin, survives
updates, and can be removed by deleting one file over FTP if anything goes wrong.

---

## The fix

`mu-plugins/lgs-property-status-reconcile.php` in this directory. Upload it to
`wp-content/mu-plugins/` (create the folder if it doesn't exist — files there load automatically,
with no activation step).

What it does, hourly:

- fetches the authoritative set of active property IDs from the API;
- drafts every published property post whose remote ID isn't in that set;
- re-publishes a property it previously drafted if it comes back as active;
- **refuses to act on a suspicious response** — an API error, an empty list, or a list smaller than
  40% of what's currently published aborts the run and logs it, so one bad API response can never
  mass-unpublish the site;
- logs every run to an option you can read in the admin footer or via WP-CLI.

Before it works you must set the six constants at the top of the file to match the actual install —
post type, remote-ID meta key, API base URL, endpoint path, auth header, and the JSON shape of the
response. Those are the details I need from the API docs and the existing plugin; they're marked
`TODO` in the file.

Run it in dry-run mode first:

```bash
wp lgs-props reconcile --dry-run     # lists what it would change, changes nothing
wp lgs-props reconcile               # applies
```

No WP-CLI: log in as an administrator and load
`https://lettsgetsmart.com/?lgs_reconcile=dry` — the same report renders in the browser for
logged-in admins only.

---

## What I need to finish this properly

1. The API docs — the Postman page is blocked from this environment. Export the collection to JSON
   and drop it in this repo, or paste the properties endpoint's example response.
2. The sync plugin's source (zip or paste of the main PHP file), so I can fix the root cause in it
   rather than only reconciling after the fact.
3. The property post type slug and the meta key holding the remote ID — `wp post-type list` and one
   property's `wp post meta list <ID>` output is enough.

Alternatively, if this session's environment is recreated with a network policy that allows
`lettsgetsmart.com`, `*.bookeddirectly.host` and `documenter.getpostman.com`, I can pull the
property lists and diff them directly. See
<https://code.claude.com/docs/en/claude-code-on-the-web> for how the environment's network policy
is configured.
