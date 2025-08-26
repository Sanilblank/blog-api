# Laravel Blog API

This project is a **RESTful Blog API** built with **Laravel 12** and **PHP 8.2**, using **PostgreSQL (12 or 17)** as the database.

It implements users, posts, categories, tags, comments, authentication, role-based access control (RBAC), search, and filtering.

API documentation is auto-generated using [Scramble](https://github.com/dedoc/scramble), and an API collection (Bruno) is provided in the repo with two environments (Local and Local Sail).

---

## 🚀 Tech Stack

- **PHP**: 8.2
- **Laravel**: 12.x
- **Database**: PostgreSQL 12+ (tested with 12 and 17)
- **Composer**: 2.7.2
- **Authentication**: Laravel Sanctum
- **RBAC**: Spatie Laravel Permission
- **API Docs**: Scramble
- **API Collection**: Bruno (in `/app/blog-api-bruno`)
- **Testing**: Pest

---

## ⚙️ Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/Sanilblank/blog-api.git
cd your-repo
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Create Environment File and Configure Database
**Option A**: Local PHP + PostgreSQL
* Copy .env.example to .env
```bash
cp .env.example .env
```
* Update .env with your local PostgreSQL credentials:
```bash
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=blog_api
DB_USERNAME=postgres
DB_PASSWORD=postgres
```
**Option B**: Using Laravel Sail (Docker)
* Copy .env.example to .env
```bash
cp .env.example .env
```
* Update .env for Sail (Docker) setup:
```bash
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel_blog
DB_USERNAME=sail
DB_PASSWORD=password
```
_Sail sets up a pgsql container by default with username **sail** and password **password**._

### 4. Run Migrations and Seeders
**Local**

* Clear the caches
```bash
php artisan optimize:clear
```

* Generate an application key
```bash
php artisan key:generate
```

* Run the migrations and seeders
```bash
php artisan migrate:fresh --seed
```
_An admin user with email **admin@admin.com** and password **password123** are seeded._

**Sail**

* Build and start Container in detached mode
```bash
./vendor/bin/sail up --build
```
* Clear the caches
```bash
./vendor/bin/sail artisan optimize:clear
```

* Generate an application key
```bash
./vendor/bin/sail artisan key:generate
```
* Run the migrations and seeders
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

_An admin user with email **admin@admin.com** and password **password123** are seeded._

### 5. Start the Development Server
**Local**
```bash
php artisan serve
```
Access the api at http://127.0.0.1:8000/

**Sail**
* **Note**: Only do this if it has not been started yet, it should have already started from previous steps
```bash
./vendor/bin/sail up
```
Access the api at http://localhost:80/

---

### 👤 Authentication
* Uses Laravel Sanctum for token-based authentication.
* Register/Login to receive an API token.
* Use the token for authenticated requests in headers: Authorization: Bearer <token>.

---

### 👮 Roles & Permissions
- **Admin**
    - Full access to posts, categories, tags, and comments.
    - Can manage all authors.
    - Can create new admins.
    - Can update or delete **only their own admin account**, not other admins.
- **Author**
    - Can manage their own posts and comments.
    - Can update or delete **only their own account**.
- Role-based access control (RBAC) is implemented using **Spatie Laravel Permission**.

---

### 📝 API Documentation

- The API documentation is **auto-generated** using **Scramble** and can be found at `/docs/api`.
- The **Bruno API collection** is available in `/app/blog-api-bruno` for reference and implementation.

---

### 🧪 Running Tests

1. **Create a testing database named 'testing'**

2. **Run all tests**:

- **Local**:  
```bash
php artisan test
```

- **Sail**: 
```bash
  ./vendor/bin/sail artisan test
```
---
