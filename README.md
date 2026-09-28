### GE-System 1.1

A comprehensive enterprise-grade inventory, sales, and warehouse management system built with Laravel 13, featuring advanced batch tracking, single-warehouse design, role-based access control, and comprehensive reporting.

---

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Core Features](#core-features)
3. [Module Details](#module-details)
4. [Technology Stack](#technology-stack)
5. [Installation & Setup](#installation--setup)

---

## System Overview

GE-System is a fully-featured business management platform designed for enterprises managing complex inventory operations, sales workflows, and supplier relationships. It provides complete visibility into stock levels, streamlines purchase and sales operations, and enables data-driven decision-making through comprehensive reporting and analytics.

**Key Capabilities:**
- Multi-warehouse inventory management with batch tracking
- Complete sales and purchase lifecycle management
- Advanced role-based access control
- Real-time notifications and alerts
- Flexible reporting with export functionality
- Customizable system settings and appearance

---

## Core Features

### 🏪 Dashboard & Analytics
- **Dashboard Overview** - Real-time statistics and key metrics
- **Sales Performance** - Current sales status and trends
- **Stock Status** - Inventory levels and alerts
- **Performance Metrics** - Key business indicators
- **Activity Summary** - Recent transactions and activities

### 📦 Product Management
- **Product Catalog** - Create, update, and manage products
  - Basic information (name, description, SKU, barcode)
  - Pricing and cost tracking
  - Product categorization
  - Brand assignment
  - Unit of measurement
  - Product images and specifications

- **Categories** - Organize products hierarchically
  - Create and manage product categories
  - Category-based filtering and reporting
  - Import/Export categories (CSV)

- **Brands** - Brand management and tracking
  - Create and manage brands
  - Brand-specific reporting
  - Import/Export brands (CSV)

- **Units of Measurement** - Flexible unit management
  - Define custom units (kg, pieces, meters, etc.)
  - Unit conversions
  - Import/Export units (CSV)

- **Batch Management** - Advanced batch tracking
  - Create batches with expiry dates
  - Batch-specific pricing (landed costs)
  - Expiry date monitoring and alerts
  - Batch status management (Active, Expired, Disposed)
  - Batch disposal tracking
  - Stock ledger by batch
  - Batch-specific inventory tracking

### 💰 Sales Management
- **Sales Orders** - Complete sales workflow
  - Create new sales orders
  - Search and add products to sales
  - Batch selection for each item
  - Customer assignment
  - Delivery address management
  - Terms and conditions
  - Sale confirmation/rejection workflow
  - Sale editing capabilities
  - Clone existing sales

- **Invoicing** - Professional invoice generation
  - Automatic invoice generation
  - PDF export and printing
  - Invoice customization
  - Invoice numbering
  - Multiple payment term options

- **Sales Returns** - Handle customer returns
  - Create return orders from sales
  - Batch-specific returns
  - Partial or full returns
  - Return invoice generation
  - Return approval workflow
  - Return payment tracking

- **Sales Payments** - Payment processing
  - Record customer payments
  - Partial and full payment tracking
  - Payment method tracking
  - Payment date recording
  - Balance due calculation
  - Payment reports

- **Reports & Exports**
  - All Sales report (with filters)
  - Sales Return report
  - Customer Payment report
  - PDF export of all sales
  - CSV export of all sales
  - PDF export of individual invoices
  - PDF export of individual return invoices

### 📥 Purchase Management
- **Purchase Orders** - Supplier purchase workflow
  - Create purchase orders
  - Supplier assignment
  - Search and add products
  - Batch creation during purchase
  - Purchase editing
  - Purchase cloning
  - Receiving workflow for items
  - Partial receiving capability
  - Receiving receipt generation

- **Purchase Payments** - Track supplier payments
  - Record payments to suppliers
  - Partial and full payment tracking
  - Payment method tracking
  - Balance due calculations
  - Supplier payment clearing

- **Purchase Returns** - Handle supplier returns
  - Create return orders
  - Return approval workflow
  - Return payment recording
  - Return invoice generation
  - Return editing and updating

- **Reports & Exports**
  - All Purchases report
  - Purchase Return report
  - Supplier Payment report
  - PDF export of purchases
  - CSV export of purchases
  - PDF export of purchase invoices

### 🏢 Warehouse Management
- **Multi-Warehouse Support** - Manage multiple warehouse locations
  - Create and manage warehouses
  - Set primary warehouse
  - Warehouse status management (Active/Inactive)
  - Warehouse batch inventory tracking
  - Import/Export warehouses (CSV)

- **Stock Transfers** - Transfer stock between warehouses
  - Create transfer orders
  - Transfer items between warehouses
  - Batch-specific transfers
  - Transfer status tracking
  - Transfer history and reporting

- **Stock Adjustments** - Inventory adjustments and corrections
  - Create adjustment records
  - Adjust batch quantities
  - Adjustment reasons (Damage, Theft, Loss, Correction, etc.)
  - Adjustment notes
  - Adjustment product search
  - Warehouse-specific adjustments
  - Adjustment reports

### 👥 Customer Management
- **Customer Database** - Comprehensive customer records
  - Create and manage customers
  - Contact information
  - Delivery addresses
  - Customer classification
  - Credit terms management
  - Import/Export customers (CSV)
  - Customer export to PDF/CSV

- **Customer Payments** - Payment tracking
  - Record customer payments
  - Payment history per customer
  - Balance due tracking
  - Payment clearing/settlement
  - Customer payment reports

- **Customer Notifications** - Direct communication
  - Send notifications to individual customers
  - Broadcast notifications to all customers
  - Notification log and history
  - Email and SMS support
  - Notification templates
  - Track notification status

### 🚚 Supplier Management
- **Supplier Database** - Supplier information management
  - Create and manage suppliers
  - Contact information
  - Payment terms
  - Supplier ratings and notes
  - Import/Export suppliers (CSV)
  - Supplier export to PDF/CSV

- **Supplier Payments** - Payment tracking
  - Payment recording per supplier
  - Payment history
  - Balance tracking
  - Payment clearing/settlement
  - Supplier payment reports

### 👔 Staff Management
- **Staff Directory** - Manage team members
  - Create and manage staff profiles
  - Contact information
  - Department/role assignment
  - Staff status management (Active/Inactive)
  - Staff impersonation (admin feature for support)
  - Staff login tracking
  - Staff information export

- **Role-Based Access Control** - Granular permissions
  - Create custom roles
  - Assign permissions to roles
  - Fine-grained permission matrix
    - Dashboard access
    - Product CRUD operations
    - Sales/Purchase operations
    - Warehouse operations
    - Staff and role management
    - Settings and configuration
    - Report access
    - Print/Download permissions
    - Import/Export capabilities
  - Role editing and management
  - Multiple role support per user

- **Staff Performance** - Performance tracking
  - Performance dashboard
  - Individual staff performance metrics
  - Target assignment and tracking
  - Performance goals
  - Target vs actual comparison

### 💰 Expense Management
- **Expense Categories** - Organize business expenses
  - Create expense categories/types
  - Category management
  - Import/Export expense types (CSV)

- **Expense Tracking** - Record and manage expenses
  - Create expense records
  - Categorize expenses
  - Date and amount tracking
  - Description and notes
  - Warehouse assignment
  - Expense status
  - Import/Export expenses (CSV)
  - Expense reports with filtering

### 🔔 Notification System
- **System Notifications** - Internal alerts
  - Real-time notifications
  - Notification dashboard
  - Mark notifications as read
  - Delete notifications
  - Notification count tracking
  - Bulk notification actions

- **Email & SMS Settings** - Configure communication
  - SMTP email configuration
  - SMS provider setup (Twilio/Vonage)
  - Notification template management
  - Test email sending
  - Test SMS sending
  - Template customization

- **Notification Triggers** - Automatic alerts
  - Low stock alerts
  - Expiry date warnings
  - Sale confirmations
  - Purchase notifications
  - Payment reminders
  - Staff notifications
  - System-wide announcements

### ⚙️ System Settings
- **General Settings** - Basic configuration
  - Company name and details
  - Default currency and locale
  - Date and time format
  - Business hours
  - General system preferences

- **Logo & Favicon** - Branding
  - Upload company logo
  - Upload favicon
  - Logo placement settings
  - Favicon customization

- **Page Appearance** - UI/UX Customization
  - **Light/Dark Mode**
    - Toggle between light and dark themes
    - User-specific theme preference
    - System-wide default theme
    - Automatic theme switching options
  - Color scheme customization
  - Sidebar appearance settings
  - Page layout options
  - Font preferences
  - Button and accent colors
  - Theme preview

- **System Configuration** - Advanced settings
  - Financial year configuration
  - Tax settings
  - Discount settings
  - Payment methods
  - Invoice settings
  - Report settings
  - System preferences

- **Notification Templates** - Email/SMS templates
  - Edit notification templates
  - Template variables
  - Test template sending
  - Template versioning

- **Extensions** - Plugin management
  - Enable/Disable extensions
  - Extension configuration
  - Extension marketplace
  - Update extension settings

### 📊 Reporting & Analytics
- **Sales Reports**
  - All sales listing and filtering
  - Sales return reports
  - Customer payment reports
  - Sales by customer
  - Sales by product
  - Sales timeline analysis

- **Purchase Reports**
  - All purchases listing
  - Purchase returns
  - Supplier payment reports
  - Purchase by supplier
  - Purchase by product

- **Inventory Reports**
  - Stock status report
  - Batch expiry tracking
  - Stock movements
  - Adjustment history
  - Warehouse inventory breakdown
  - Low stock alerts

- **Financial Reports**
  - Payment receivables
  - Payment payables
  - Expense reports
  - Cash flow analysis

- **Export Capabilities**
  - PDF export of all major reports
  - CSV export for Excel compatibility
  - Print-ready format
  - Scheduled exports
  - Custom report builder

### 🖨️ Print & Download
- **Invoice Printing**
  - Print sales invoices
  - Print purchase invoices
  - Print return invoices
  - Batch printing
  - Custom print settings

- **Report Downloads**
  - Download reports as PDF
  - Download reports as CSV
  - Download products catalog
  - Download customer/supplier lists
  - Download stock reports
  - Scheduled downloads

### 🔐 Security & Access Control
- **Authentication**
  - User login
  - Email verification
  - Password reset
  - Multi-user support
  - Session management

- **Authorization**
  - Role-based access control (RBAC)
  - Permission-based actions
  - Resource-level permissions
  - Activity logging
  - Audit trail

- **Data Protection**
  - Database encryption
  - Password hashing
  - Secure API endpoints
  - CSRF protection

---

## Module Details

### Product Management Flow
1. Create product categories, brands, and units
2. Create products with assigned category, brand, and unit
3. Add products to inventory through purchases
4. Track products through batches with expiry dates
5. Monitor stock levels across warehouses
6. Adjust inventory as needed
7. Use products in sales transactions

### Sales Process Flow
1. Create sales order
2. Select customer and products (by batch)
3. Enter quantities and verify pricing
4. Confirm or reject sale
5. Generate invoice
6. Record customer payment(s)
7. Process returns if needed
8. Generate payment reports

### Purchase Process Flow
1. Create purchase order
2. Select supplier and products
3. Create batches during purchase (including expiry dates)
4. Set batch quantities and pricing
5. Receive items into warehouse
6. Record supplier payment(s)
7. Process returns if needed
8. Generate purchase reports

### Warehouse Operations
1. Transfer stock between warehouses
2. Adjust inventory for losses/damages
3. Track batch movements
4. Monitor warehouse-specific inventory
5. Generate warehouse reports

### Staff & Permissions
1. Create staff members
2. Assign roles to staff
3. Configure role permissions
4. Monitor staff performance
5. Track staff activities
6. Assign performance targets

---

## Technology Stack

- **Backend Framework**: Laravel 13 with PHP 8.3+
- **Database**: MySQL / SQLite / PostgreSQL / SQL Server
- **Frontend**: Blade Templates with Tailwind CSS
- **Build Tools**: Vite, PostCSS
- **Caching**: Redis
- **PDF Generation**: DomPDF
- **Email**: SMTP with customizable templates
- **SMS**: Twilio / Vonage integration
- **Task Scheduling**: Laravel Queue with Redis support
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit

---

## Installation & Setup

### Prerequisites
- PHP 8.3 or higher
- Composer
- Node.js & NPM
- MySQL or compatible database
- Redis (optional but recommended)
- PHP `zip` extension enabled, or a system archive tool such as `unzip` / `7z` available on `PATH` (Windows)

### Quick Start

1. **Clone or download the repository**

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your database** in the `.env` file

5. **Run migrations:**
   ```bash
   php artisan migrate
   ```

6. **Build assets:**
   ```bash
   npm run build
   ```

7. **Start the development server:**
   ```bash
   composer run dev
   ```

### Automated Setup

Run the complete setup script:
```bash
composer run setup
```

This will:
- Copy `.env` file
- Install dependencies
- Generate application key
- Set up DomPDF
- Build assets
- Run migrations
- Test Redis connection

## Available Commands

- `composer run setup` - Complete system setup
- `composer run dev` - Start development server with queue worker and logs
- `composer run test` - Run the test suite
- `php artisan migrate` - Run database migrations
- `npm run dev` - Run Vite dev server for assets

## License

This system is built on Laravel, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).