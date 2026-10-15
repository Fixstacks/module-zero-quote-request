# Zero_QuoteRequest

Request a quote, and price on request.

- **Price on Request** (a product attribute, Catalog > Products): the price
  is hidden wherever Magento shows it (product page, listings, widgets; not in
  structured data, analytics or reward points either), and Add to Cart
  becomes "Ask us for a price": quantity, options, name, email, a comment,
  sent with HTMX. Such a product reaches the cart only through an accepted
  quote.
- **From the cart**: "Buying in bulk? Request a quote" under the checkout
  button (Stores > Configuration > Zero > Quote Requests to hide it) opens
  `/quotes/request`: the cart's lines, your details, a comment.
- **Answering** (Sales > Quote Requests): a price per line (in the currency
  asked in), a note, valid for N days; **Send Quote** emails the customer the
  link, or **Decline**.
- **Accepting**: the link (`/quotes/view?token=...`, also in My Account > My
  Quotes) shows the quote; **Accept and Check Out** makes the cart the
  quote's lines at its prices and quantities (they stay so), and the order
  marks the request ordered.
- **Emails**: a new request to the store (New Requests Go To), "we got it"
  and "your quote" to the customer (templates under Marketing > Email
  Templates).

Statuses: new, quoted (until its date), ordered, declined. Tables:
`zero_quote_request`, `zero_quote_request_item`; `quote.zero_quote_request_id`.

## Install

Requires Magento Open Source 2.4.7–2.4.9 and PHP 8.2–8.4 with the GD extension built with WebP (`php -r 'var_dump(function_exists("imagewebp"));'` says `true`; Magento's own PHP requirements cover the rest). Without WebP in GD, Zero skips its WebP image copies and serves the originals.

```bash
composer require fixstacks/module-zero-quote-request
bin/magento setup:upgrade
bin/magento setup:di:compile           # production mode
bin/magento setup:static-content:deploy # production mode
bin/magento cache:flush
```

Composer installs `fixstacks/module-zero-core`, `fixstacks/module-zero-seo` with it.

Part of [`fixstacks/zero`](https://fixstacks.com/zero), which installs the Zero theme and every Zero module at once.

## Tests

`dev/tests/zero-e2e/tests/quote-request.spec.ts` (`ZERO_QUOTE_ONLY_PRODUCT`),
`quote-request-admin.spec.ts` (ask, quote, accept, order).

## Disabling or removing

Its data lives in its own tables (`zero_quote_request`, `zero_quote_request_item`). With the module's code still in
place, `bin/magento module:disable Zero_QuoteRequest` followed by `setup:upgrade`
**drops those tables** (Magento's declarative schema), and enabling the module
again brings them back empty. To keep the data, switch the feature off in
Stores > Configuration where it has a setting, or back up first:
`bin/magento setup:upgrade --safe-mode=1` dumps the data before anything is
dropped (`--data-restore=1` puts it back). Deleting the module's code instead
leaves the tables in place.

## License

OSL-3.0 or AFL-3.0, your choice: see `NOTICE.md`.

---

<a href="https://vilmate.com"><picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Fixstacks/zero/main/docs/images/vilmate-logo-dark.svg">
  <img src="https://raw.githubusercontent.com/Fixstacks/zero/main/docs/images/vilmate-logo.svg" alt="Vilmate" height="34" align="right">
</picture></a>

Zero is made by [Fixstacks](https://fixstacks.com/zero) in partnership with [Vilmate](https://vilmate.com), a software development company.
Documentation: https://fixstacks.com/zero/docs. Demo store: https://zero.fixstacks.com.
