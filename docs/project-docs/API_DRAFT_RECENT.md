# BizInvestify — Recent API Draft (for frontend wiring)

Base URL: `{API_URL}/api`

## Auth & profile
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/me` | Bearer | Returns `roles[].permissions` + `user_permissions` |
| POST | `/login` | Public | Same user payload shape as `/me` |

## Notifications
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/notifications` | Bearer | Supports `unread_only`, `search`, `category` |
| GET | `/user/notification-settings` | Bearer | |
| PUT | `/user/notification-settings` | Bearer | |

Categories: `message`, `kyc`, `transaction`, `investment`, `marketplace`, `security`, `system`

## Activity logs
| Method | Path | Auth | Permission |
|--------|------|------|------------|
| GET | `/activity-logs` | Bearer | `activity_logs.view` |
| GET | `/activity-logs/recent` | Bearer | `activity_logs.view` |

## Realtime (polling)
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/realtime/poll` | Bearer | `?since=` ISO timestamp |

## Currency (public)
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/public/currency/rate` | Public | `?from=USD&to=NGN` |
| POST | `/public/currency/convert` | Public | 422 if pair unavailable |

## Wallet & withdrawals
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/wallet/summary` | Bearer | Available balance excludes pending withdrawals |
| GET | `/wallet/transactions` | Bearer | |
| POST | `/wallet/bank-accounts` | Bearer | |
| POST | `/wallet/withdrawals` | Bearer | |
| GET | `/payments/stripe-connect/status` | Bearer | |
| POST | `/payments/stripe-connect/onboard` | Bearer | |

## Admin
| Method | Path | Auth | Permission |
|--------|------|------|------------|
| GET | `/admin/reports/overview` | Bearer | Admin |
| GET | `/admin/reports/revenue` | Bearer | Admin |
| GET | `/admin/reports/users` | Bearer | Admin |
| GET | `/admin/withdrawals` | Bearer | `payments.manage` |

## Orders
| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/orders` | Bearer | Seller/buyer scoped |
| POST | `/orders` | Bearer | |
| PUT | `/orders/{id}/status` | Bearer | Fulfillment transitions |
| GET | `/public/orders/{orderNumber}/tracking` | Public | |

## Email-triggering payment events (no extra endpoints)
- Product purchase, investment, refund, subscription, featured listing payment
- Order status: shipped, delivered, completed, cancelled, refunded
- Featured listing admin approve/reject
