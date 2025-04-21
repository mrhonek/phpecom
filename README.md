# PHP Laravel & Angular E-commerce Demo

A demonstration e-commerce application showcasing full-stack development skills using PHP Laravel for the backend API and Angular for the frontend.

## Project Overview

This project is a simplified e-commerce platform that demonstrates:

- Secure API development with Laravel Sanctum authentication
- RESTful API design
- Database relationships and design
- Modern Angular frontend development
- Authentication and authorization
- Deployment on Railway.io

## Features

- User registration and authentication
- Product browsing and search
- Shopping cart management
- Order processing
- User order history

## Tech Stack

### Backend
- PHP Laravel
- Laravel Sanctum for JWT-based authentication
- PostgreSQL database
- RESTful API architecture

### Frontend
- Angular 17+
- Reactive forms
- Angular Router
- HTTP Interceptors for auth
- Responsive design

### Deployment
- Railway.io for backend hosting and PostgreSQL database
- Railway.io for frontend hosting

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

## API Endpoints

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get authentication token
- `GET /api/products` - Get all products
- `GET /api/products/{id}` - Get a specific product
- `POST /api/cart/add` - Add item to cart (protected)
- `GET /api/cart` - Get cart contents (protected)
- `DELETE /api/cart/{id}` - Remove item from cart (protected)
- `PUT /api/cart/{id}` - Update item quantity (protected)
- `POST /api/orders` - Create a new order (protected)
- `GET /api/orders` - Get user orders (protected)
- `GET /api/orders/{id}` - Get specific order details (protected)

## Development Setup

### Backend Setup

1. Clone the repository
2. Configure database in `.env` file
3. Run migrations and seeders:
   ```
   php artisan migrate --seed
   ```
4. Start the Laravel development server:
   ```
   php artisan serve
   ```

### Frontend Setup

1. Navigate to the frontend directory
2. Install dependencies:
   ```
   npm install
   ```
3. Start the Angular development server:
   ```
   ng serve
   ```
4. Navigate to `http://localhost:4200/`

## Deployment Instructions

### Railway.io Deployment

1. Push your code to a GitHub repository
2. Link your GitHub repository to Railway.io
3. Set up a PostgreSQL database service on Railway
4. Configure environment variables for Laravel
5. Deploy the Laravel backend
6. Deploy the Angular frontend
7. Set up custom domain (optional)

## License

This project is available as open source under the terms of the MIT License. 