# PHP Laravel & Angular E-commerce Demo Project

## Project Structure
```
PHPecom/
├── backend/            # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── ...
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       └── api.php
└── frontend/           # Angular App
    ├── src/
    │   ├── app/
    │   │   ├── components/
    │   │   ├── services/
    │   │   └── models/
    │   └── ...
    └── ...
```

## Development Prerequisites

### For Local Development
- PHP 8.1+ for Windows
- Composer
- Node.js and NPM
- Angular CLI

### For Railway.io Deployment
- GitHub repository for the project
- Railway.io account
- PostgreSQL will be configured directly on Railway.io

## Backend Setup (Laravel)
```bash
composer create-project laravel/laravel backend
cd backend
# Configure .env for local development
composer require laravel/sanctum
```

## Frontend Setup (Angular)
```bash
npm install -g @angular/cli
ng new frontend
cd frontend
# Add necessary packages
npm install @angular/material
```

## API Endpoints Plan
- POST /api/auth/register
- POST /api/auth/login
- GET /api/products
- GET /api/products/{id}
- POST /api/cart/add
- GET /api/cart
- POST /api/orders
- GET /api/orders

## Database Schema
- users
- products
- cart_items
- orders
- order_items

## Railway.io Deployment Setup
- Backend: Configure PostgreSQL service from Railway dashboard
- Update environment variables in Railway.io dashboard
- Connect GitHub repository for automatic deployments
- Railway will automatically detect Laravel and install dependencies
- Configure proper environment variables for database connection
- Run migrations during deployment 