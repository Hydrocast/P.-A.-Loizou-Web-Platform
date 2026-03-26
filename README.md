# P. & A. Loizou Web Platform

A full-stack web platform for **P. & A. Loizou Ltd**, a commercial print and graphic design business based in Cyprus. The platform enables customers to browse products, customize print items in-browser, and place orders for in-store pickup, while providing staff with a back-office dashboard for order processing, product management, pricing, and analytics.

Developed as the primary project for **CSE 324: Software Engineering** and **CSE 328: Software Engineering Project & Professional Practice** at the Cyprus University of Technology.

---

## Overview

The system supports two main areas:

- **Customer-facing workflows**, including product browsing, wishlist management, browser-based product customization, cart persistence, checkout, and order history
- **Staff-facing workflows**, including order management, product administration, pricing configuration, carousel management, clipart management, and sales analytics

The platform was designed to reflect a realistic business context and was supported by formal software engineering analysis, design, and documentation.

---

## Key Features

### Customer Features
- Browse and search standard and customizable products
- Customize print products in-browser using a Fabric.js-powered design workspace
- Add text, images, and clipart directly onto product mockups
- Save and reload designs from a personal design library
- Add customized items to a persistent shopping cart
- Place orders for in-store pickup
- Manage wishlisted products
- View order history and pricing breakdowns
- Receive automated email notifications for order confirmation and order status updates

### Staff Features
- View, filter, and manage customer orders
- Update order status, assign orders to staff, and add internal notes
- Manage standard and customizable products
- Manage product categories
- Configure tiered pricing for customizable products
- Manage homepage carousel slides
- Manage clipart assets for the design workspace
- View sales analytics with date-range filtering

### Administrator Features
- Manage staff accounts
- Activate and deactivate staff users with built-in safeguards
- Ensure at least one active administrator account remains in the system

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | React 19 + TypeScript |
| Application Bridge | Inertia.js |
| Styling | Tailwind CSS v4 |
| Canvas / Customization | Fabric.js 7 |
| Database | MySQL |
| Queue | Laravel Queue (database driver) |
| Email | Resend |
| Build Tool | Vite 7 |
| Authentication | Laravel Fortify with custom dual-guard authentication |

---

## Architecture

The backend follows a **service-layer architecture**, keeping controllers thin and delegating business logic to dedicated services.

```text
Controllers → Services → Models → Database

Examples of core services include:

CheckoutService
CartService
OrderProcessingService
ProductService
DesignService
PricingConfigurationService
AnalyticsService

The frontend is built as a React + Inertia.js single-page application with TypeScript support throughout. The product customization workflow is centered around a Fabric.js-based design workspace and a custom hook for canvas state management, undo handling, object manipulation, and design export.

Software Engineering Documentation

In addition to implementation, the project included formal software engineering documentation and modelling work produced in compliance with IEEE software engineering standards. Deliverables included:

Software Requirements Specification (SRS)
Software Specification
Design Document
Implementation and Integration Document
Metrics Document

Supporting analysis and design artefacts included:

Data Flow Diagrams (DFDs)
UML use-case diagrams
UML class diagrams
Database design documentation
Architectural design documentation
Project planning and traceability artefacts
Key Design Decisions
Frozen Order Data

Pricing, contact details, and design snapshots are copied at the moment of order submission and are not recalculated afterwards. This preserves historical accuracy even if products, prices, or configuration change later.

Dual Authentication Guards

Customers and staff use separate Laravel authentication guards, each with its own session and middleware pipeline.

VAT-Inclusive Pricing

All stored prices are VAT-inclusive. VAT is reverse-calculated during checkout.

No Online Payments

The platform supports order placement for in-store pickup and payment. It does not integrate with an online payment gateway.

Design Immutability

Saved designs and cart and order design snapshots are treated as immutable records. Changes require creating a new design entry rather than modifying an existing one.

Getting Started
Requirements
PHP 8.2+
Composer
Node.js 18+ and npm
MySQL
Installation
git clone <repo-url>
cd <project-folder>

composer install
npm install

cp .env.example .env
php artisan key:generate

Configure your .env file:

DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=resend
RESEND_API_KEY=your_resend_key
MAIL_FROM_ADDRESS=noreply@yourdomain.com

Run database migrations and build assets:

php artisan migrate
php artisan wayfinder:generate
npm run build

Create an administrator account:

php artisan tinker
\App\Models\Staff::create([
    'username' => 'admin',
    'password' => \Illuminate\Support\Facades\Hash::make('your-password'),
    'role' => 'Administrator',
    'full_name' => 'Your Name',
    'account_status' => 'Active',
]);
Local Development
composer dev

This starts the Laravel server, queue listener, and Vite development server concurrently.

Environment Notes
MAIL_DEV_OVERRIDE_ADDRESS can be set during local development to redirect all outgoing email to a single inbox
business.vat_rate controls the VAT rate applied during checkout
Project Context

This platform was developed as part of a university software engineering project simulating a realistic client engagement with P. & A. Loizou Ltd.

The project combined requirements analysis, system specification, architectural design, implementation, integration, and formal documentation. The SRS and Software Specification were produced in compliance with IEEE 830-1993 software engineering standards.

Development Team

Giannis Loizou
Andreas Christodoulou
Athanasios Papaspyrou
