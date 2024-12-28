# EarthWire README

## **Overview**
EarthWire is a RESTful API for a news aggregator service that pulls articles from various
sources and provides endpoints for a frontend application to consume.

---

## **Setup Instructions**

### **Step 1: Clone the Repository**
Clone the project repository to your local machine:
```bash
git clone https://github.com/mohamad-zatar/earth-wire.git
cd earth-wire
```

### **Step 2: Install Dependencies**
Install PHP dependencies using Composer:
```bash
composer install
```

### **Step 3: Set Up Environment Variables**
Copy the `.env.example` file to `.env` and configure the environment variables:
```bash
cp .env.example .env
```
```bash
php artisan key:generate
```
```env
NEWS_API_KEY=
GUARDIAN_API_KEY=
NYT_API_KEY=
```
Make sure to configure database credentials, Redis settings, and other environment variables as needed.

### **Step 4: Start the Development Environment**
Run Laravel Sail to start the development environment:
```bash
./vendor/bin/sail up -d
```

### **Step 5: Run Migrations**
Run the database migrations to set up the schema:
```bash
./vendor/bin/sail artisan migrate
```
### **Step 6: Start Laravel Horizon**
Start Laravel Horizon to manage queues:
```bash
./vendor/bin/sail artisan horizon
```

### **Step 7: Run Laravel Scheduler**
To ensure scheduled tasks run continuously, start the Laravel scheduler:
```bash
./vendor/bin/sail artisan schedule:work
```
---

### **Step 9: Access API Documentation**
You can view the Scribe-generated API documentation by visiting:
```
http://localhost/docs
```
---

## **Testing the Application**

### **Run Tests**
Run the automated test suite to verify the functionality of the application:
```bash
./vendor/bin/sail artisan test
```

---


## **Tools and Technologies**

### **1. Laravel Sail**
Laravel Sail is a lightweight command-line interface for interacting with Laravel's default Docker development environment. It provides an easy way to set up and run a Laravel project with Docker.

- **Why Use Sail?**
    - Simplifies Docker setup for Laravel projects.
    - Provides pre-configured Docker services for PHP, MySQL, Redis, and more.

- **Documentation:** [Laravel Sail Documentation](https://laravel.com/docs/sail)

### **2. Laravel Horizon**
Laravel Horizon provides a beautiful dashboard and code-driven configuration for Redis-powered queues in Laravel. It allows you to monitor and manage your queue workers.

- **Why Use Horizon?**
    - Visual monitoring of queue jobs.
    - Provides insights into queue performance and worker status.

- **Documentation:** [Laravel Horizon Documentation](https://laravel.com/docs/horizon)

### **3. Laravel Sanctum**
Laravel Sanctum provides a simple way to authenticate Single Page Applications (SPAs) and APIs. It issues secure API tokens that can be used for user authentication.

- **Why Use Sanctum?**
    - Lightweight and easy to implement for API authentication.
    - Supports token-based authentication and session-based SPA authentication.

- **Documentation:** [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

### **4. Predis**
Predis is a PHP client library for Redis, allowing seamless integration with Laravel's caching and queue systems.

- **Why Use Predis?**
    - Simple and flexible Redis integration.
    - Works seamlessly with Laravel's Redis abstractions.

- **Documentation:** [Predis GitHub](https://github.com/predis/predis)

### **5. Laravel Pint**
Laravel Pint is an opinionated PHP code style fixer. It ensures your codebase adheres to consistent coding standards.

- **Why Use Pint?**
    - Automated code formatting.
    - Improves readability and maintainability of the codebase.

- **Documentation:** [Laravel Pint Documentation](https://laravel.com/docs/pint)


## **Key Features**

### **1. Redis Integration**
- Efficient caching and queue management using Predis.

### **2. Queue Monitoring**
- Laravel Horizon provides a real-time dashboard for queue monitoring.

### **3. Code Style Enforcement**
- Laravel Pint ensures consistent code formatting across the project.

---

## **Additional Notes**
- Ensure Docker is installed and running on your system before using Laravel Sail.
- Go to `http://localhost/horizon` to monitor queue workers and jobs in the Horizon dashboard.

