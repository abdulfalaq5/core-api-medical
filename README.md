# Medical Core API

A Laravel-based medical system API with CQRS (Command Query Responsibility Segregation) pattern.

## System Requirements

- PHP 8.1 or higher
- PostgreSQL 12 or higher
- Composer
- RabbitMQ
- Redis (optional, for caching)

## Setup Instructions

### 1. Clone the Repository
```bash
git clone [repository-url]
cd core-api-medical
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
Copy the `.env.example` file to `.env` and update the following configurations:

#### Database Configuration
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_tirta_medical_enter_command_api
DB_USERNAME=postgres
DB_PASSWORD=772277

DB_QUERY_CONNECTION=pgsql_query
DB_QUERY_HOST=127.0.0.1
DB_QUERY_PORT=5432
DB_QUERY_DATABASE=db_tirta_medical_enter_query_api
DB_QUERY_USERNAME=postgres
DB_QUERY_PASSWORD=772277
```

#### RabbitMQ Configuration
```
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
RABBITMQ_QUEUE=category_sync
```

#### JWT Configuration
```
JWT_SECRET=your_jwt_secret
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Database Setup
```bash
# Create databases
createdb db_tirta_medical_enter_command_api
createdb db_tirta_medical_enter_query_api

# Run migrations
php artisan migrate
```

### 6. Start the Development Server
```bash
php artisan serve --port=9191
```

## API Documentation

API documentation is available at:
```
http://localhost:9191/api/documentation
```

## Features

- CQRS Pattern Implementation
- JWT Authentication
- Swagger API Documentation
- RabbitMQ Message Queue
- PostgreSQL Database
- Redis Caching (optional)

## Development

### Running Tests
```bash
php artisan test
```

### Queue Worker
```bash
php artisan queue:work
```

## Troubleshooting

1. If you encounter database connection issues:
   - Verify PostgreSQL is running
   - Check database credentials in `.env`
   - Ensure both databases exist

2. If RabbitMQ connection fails:
   - Verify RabbitMQ service is running
   - Check connection details in `.env`

---

# Medical Core API (Bahasa Indonesia)

API sistem medis berbasis Laravel dengan pola CQRS (Command Query Responsibility Segregation).

## Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- PostgreSQL 12 atau lebih tinggi
- Composer
- RabbitMQ
- Redis (opsional, untuk caching)

## Instruksi Setup

### 1. Clone Repository
```bash
git clone [repository-url]
cd core-api-medical
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Konfigurasi Environment
Salin file `.env.example` ke `.env` dan perbarui konfigurasi berikut:

#### Konfigurasi Database
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_tirta_medical_enter_command_api
DB_USERNAME=postgres
DB_PASSWORD=772277

DB_QUERY_CONNECTION=pgsql_query
DB_QUERY_HOST=127.0.0.1
DB_QUERY_PORT=5432
DB_QUERY_DATABASE=db_tirta_medical_enter_query_api
DB_QUERY_USERNAME=postgres
DB_QUERY_PASSWORD=772277
```

#### Konfigurasi RabbitMQ
```
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
RABBITMQ_QUEUE=category_sync
```

#### Konfigurasi JWT
```
JWT_SECRET=your_jwt_secret
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Setup Database
```bash
# Buat database
createdb db_tirta_medical_enter_command_api
createdb db_tirta_medical_enter_query_api

# Jalankan migrasi
php artisan migrate
```

### 6. Mulai Development Server
```bash
php artisan serve --port=9191
```

## Dokumentasi API

Dokumentasi API tersedia di:
```
http://localhost:9191/api/documentation
```

## Fitur

- Implementasi Pola CQRS
- Autentikasi JWT
- Dokumentasi API Swagger
- Message Queue RabbitMQ
- Database PostgreSQL
- Caching Redis (opsional)

## Pengembangan

### Menjalankan Test
```bash
php artisan test
```

### Queue Worker
```bash
php artisan queue:work
```

## Pemecahan Masalah

1. Jika mengalami masalah koneksi database:
   - Verifikasi PostgreSQL sedang berjalan
   - Periksa kredensial database di `.env`
   - Pastikan kedua database sudah ada

2. Jika koneksi RabbitMQ gagal:
   - Verifikasi layanan RabbitMQ sedang berjalan
   - Periksa detail koneksi di `.env`

## Develop By Nur Muhammad Abdul Falaq

