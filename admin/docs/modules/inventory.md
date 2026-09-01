# Inventory

## Purpose

Product stock: categories, items, adjustments, low-stock alerts, purchase orders, barcode/reorder hubs.

## Key files

| Layer | Path |
|-------|------|
| Web | `Web\InventoryController` |
| API | `Api\InventoryController` (includes purchase orders) |
| Models | `InventoryItem`, `InventoryCategory`, `InventoryAdjustment`, `PurchaseOrder`, `PurchaseOrderItem` |
| Support | `DefaultInventoryCatalog` |
| Job/Command | `CheckLowStock`, `AlertLowStock` |
| Views | `resources/views/inventory/*` |

## Routes

- `inventory` resource + `POST inventory/{id}/adjust`
- Export, barcode, reorder, adjust-hub endpoints
- API: `purchase-orders` apiResource, receive, generate

## Report

Inventory report: SKU counts, retail/cost value, low stock, out of stock, adjustments in period.

## Permissions

`inventory.view` and related; report type gated in `ReportCatalog`.

## Default catalog

`DefaultInventoryCatalog` seeds common salon retail SKUs for new salons (if seeder invoked).
