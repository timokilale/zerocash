# ZeroCash Banking System

A comprehensive cashless banking system built with Laravel, designed to minimize cash usage and provide seamless digital banking services.

## 🏦 System Overview

ZeroCash Banking System is a modern, cashless banking solution that provides:
- **Customer Interface**: Simple loan application and repayment system
- **Staff Interface**: Complete banking operations management
- **Automated Operations**: Intelligent algorithms for fees, interest rates, and transfers
- **NIDA Integration**: Automated customer verification and account creation

## ✅ Core Features (All Implemented & Working)

### 1. **Automatic Loan Disbursement**
- Loans automatically transfer to customer accounts when approved
- Real-time balance updates and transaction records
- Automatic status changes from 'approved' to 'active'

### 2. **Dynamic Transaction Fee Calculation**
- Automatic fee calculation based on transaction type and amount
- Support for fixed, percentage, and tiered fee structures
- Real-time fee preview during transactions

### 3. **Role-Based Deposit Authorization**
- Only employees, CEO, and administrators can perform deposits
- Multi-level approval system for deposit processing
- Complete audit trail for all deposit operations

### 4. **Automatic Notification System**
- Real-time notifications for all transactions and registrations
- User-specific notification management
- Support for multiple notification types (transaction, loan, account)

### 5. **Intelligent Interest Rate Algorithm**
- Dynamic interest rate calculation based on:
  - Loan amount (higher amounts get lower rates)
  - Loan term (longer terms get higher rates)
  - Loan type and risk factors
- Automatic monthly payment calculation using compound interest formula

### 6. **Auto Account Number Generation**
- Unique account numbers using format: YYYYMMDDXXXXXX
- Collision detection and retry mechanism
- Automatic generation during account creation

### 7. **Employee Password Auto-Generation**
- Secure 8-character random passwords for new employees
- Automatic username generation from first and last names
- Password auto-generation flag tracking

### 8. **Customer Password Auto-Generation**
- Automatic password generation for new customers
- Secure password delivery through notifications
- Support for password reset functionality

### 9. **NIDA Integration**
- Fetch customer details from National Identification Authority
- Automatic customer profile creation from NIDA data
- NIDA verification status tracking
- Mock implementation with realistic data simulation

### 10. **Employee Soft Deletion**
- Employees moved to dormant state instead of permanent deletion
- Data preservation for audit and compliance
- Restore functionality for reactivating employees
- Complete employment history tracking

## 🎯 User Interfaces

### Customer Interface
- **Dashboard**: Account balance, loan summary, quick actions
- **Loan Application**: Simple form with real-time calculations
- **Loan Management**: View all loans with progress tracking
- **Repayment System**: Cashless payments via account balance or mobile money
- **Notifications**: Real-time updates on all activities

### Staff Interface
- **Complete Banking Dashboard**: System statistics and management
- **Customer Management**: Create, view, and manage customer accounts
- **Loan Processing**: Approve, reject, and manage loan applications
- **Transaction Management**: Process transfers, deposits, and withdrawals
- **Employee Management**: Add, edit, and manage staff with soft deletion
- **Reporting**: Comprehensive reports and analytics

## 🔧 Technical Implementation

### Backend Architecture
- **Framework**: Laravel 11
- **Database**: MySQL with comprehensive migrations
- **Authentication**: Role-based access control
- **Models**: Eloquent ORM with observers and relationships
- **Services**: NIDA integration service for customer verification

### Frontend
- **UI Framework**: Bootstrap 5 with custom styling
- **JavaScript**: jQuery for dynamic interactions
- **Responsive Design**: Mobile-friendly interface
- **Real-time Updates**: AJAX-powered calculations and previews

### Security Features
- **Role-based Access**: Different interfaces for customers vs staff
- **Middleware Protection**: Route-level security
- **Data Validation**: Comprehensive input validation
- **Soft Deletes**: Data preservation for compliance

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js & NPM

### Installation Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd zerocash
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Configuration**
```bash
# Update .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zerocash
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Database Migration & Seeding**
```bash
php artisan migrate
php artisan db:seed --class=BankingSystemSeeder
```

6. **Start Development Server**
```bash
php artisan serve
```

## 👥 Default User Accounts

### System Administrators
- **Root**: `root` / `root123`
- **CEO**: `ceo` / `1234`
- **Admin**: `admin` / `1234`

### Test Customer
- **Username**: `michael.johnson`
- **Password**: `rHxvJzNO`
- **Account**: 20250529488967

## 🧪 Testing

### Run Feature Tests
```bash
php artisan banking:test-features
```

This command tests all 10 core requirements:
- NIDA customer creation
- Account number generation
- Employee creation with auto passwords
- Transaction fee calculations
- Loan interest rate algorithms
- Automatic loan transfers
- Employee deposit processing
- Employee soft deletion
- Automatic notifications
