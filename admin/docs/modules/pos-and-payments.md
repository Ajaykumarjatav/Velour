# POS and payments

## Purpose

Point of sale: checkout, line items (services/products), payment methods, completion, refunds, invoices, Stripe.

## Key files

| Layer | Path |
|-------|------|
| Web POS | `Web\PosController` |
| API POS | `Api\PosController` |
| Service | `PosService` |
| Models | `PosTransaction`, `PosTransactionItem` |
| Gateway | `PaymentGateway`, `Web\PaymentGatewayController` |
| Policy | `PosTransactionPolicy` |
| Mail | `PosTransactionInvoiceMail` |
| Support | `PosInvoiceFormatting` |
| Views | `resources/views/pos/*` |

## Routes

- `pos` resource: index, create, store, show
- `GET pos/{id}/invoice/pdf` — PDF download
- Signed public invoice route (no login)
- `POST pos/{id}/send-invoice` — email
- `payments.*` — gateway keys, charge

## Revenue recognition

Reports count POS where `status` is completed. Date = `completed_at` if set, else `created_at`, in salon timezone.

## Stripe

- Webhook: `POST api/v1/webhooks/stripe` → `Api\PosController@stripeWebhook`
- Salon Connect / keys in `payment_gateways` table

## Vouchers

`Voucher` model + validation during POS (API `voucher/validate`).

## Permissions

`pos.view`, `pos.create`, etc.

Methods: `PosService`, `PosController` in [CODE_REFERENCE.md](../reference/CODE_REFERENCE.md).
