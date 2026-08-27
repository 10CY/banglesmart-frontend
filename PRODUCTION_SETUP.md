# BanglesMart Production Setup

## 1. Backend

```bash
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set the database, `APP_URL`, `FRONTEND_URL`, and `MAIL_FROM_ADDRESS` in `.env`.

## 2. Native PHP email

BanglesMart ecommerce emails use **PHP's native `mail()` function**. SMTP is not required by the application mail service.

The server/PHP environment must have a working local mail transport. On Linux production servers this is normally provided by the host MTA. On Windows development machines, PHP `mail()` needs a configured mail transport; otherwise the application will log that `mail()` could not send.

Order confirmation emails are sent with the invoice PDF attached. Order status and shipping updates also use native `mail()`.

## 3. Frontend

```bash
cd frontend
npm install
npm run build
npm run start
```

Set `NEXT_PUBLIC_API_URL` and `NEXT_PUBLIC_BACKEND_URL` in `.env.local`.

## 4. Database changes

Run migrations before testing the invoice/review features. New migrations include:

- invoices
- order shipping tracking
- product reviews

## 5. Storefront behavior

The customer Header/Footer are loaded only for storefront routes. Admin routes intentionally use the Admin Sidebar/Topbar and do not render the customer Header/Footer.

The storefront Header and Footer consume the shared store catalog context, so category additions/changes made in the admin category system automatically appear in navigation and footer links.

## 6. Reviews

Customers can submit a product review only after a delivered order containing that product. Reviews start as `pending`; admins can approve, reject, or delete them from **Admin > Reviews**.

## 7. Invoice

Invoices are generated on demand and at order creation. The customer and admin order detail pages provide an invoice download button. Invoice generation does not depend on an external PDF package.
