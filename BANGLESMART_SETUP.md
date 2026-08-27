# BanglesMart production setup / troubleshooting

## Backend

From `backend/`:

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate
php artisan storage:link
php artisan serve
```

After updating routes/controllers, always run:

```powershell
php artisan optimize:clear
php artisan route:list --path=api/customer/orders
php artisan route:list --path=api/customer/products
php artisan route:list --path=api/admin/reviews
```

You should see these routes:

- `GET /api/customer/orders/{order}/invoice`
- `POST /api/customer/products/{product}/reviews`
- `GET /api/customer/products/{product}/reviews/mine`
- `GET /api/admin/reviews`

If the browser says `The route ... could not be found`, the running Laravel process is using an older route cache or an older backend folder. Stop the server, run `php artisan optimize:clear`, and start Laravel again.

## Frontend

From `frontend/`:

```powershell
npm install
npm run dev
```

Set `NEXT_PUBLIC_API_URL` in `.env.local` to the Laravel API, for example:

```env
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
NEXT_PUBLIC_BACKEND_URL=http://127.0.0.1:8000
```

## Native PHP mail()

BanglesMart ecommerce emails use PHP's native `mail()` function and do not use SMTP or Laravel's SMTP transport. The server/PHP installation must still have a working local mail transport for `mail()` to actually deliver messages.

## Invoice

Invoices are generated on demand and also prepared when an order is placed. The invoice download endpoint returns a PDF directly. The invoice database migration must be applied for invoice records to be persisted.

## Slow first page in Next.js development

Next.js development mode compiles routes on demand. The first request to a page can take several seconds while Turbopack compiles that route. The screenshot warning `Slow filesystem detected` indicates the project is running from a slow/network/mapped drive. Put the project on a local SSD path such as `C:\Projects\banglesmart_final` for significantly faster development rebuilds.
