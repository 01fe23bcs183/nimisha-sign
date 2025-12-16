# HRMS - Human Resource Management System

A comprehensive Human Resource Management System backend built with Laravel 11, providing a complete API for managing employees, attendance, leave, payroll, and more.

## Overview

HRMS is a full-featured backend API designed to handle all aspects of human resource management for organizations of any size. The system provides RESTful API endpoints for managing staff members, tracking attendance, processing leave requests, handling payroll calculations, and generating reports.

## Tech Stack

- **Framework:** Laravel 11
- **PHP Version:** 8.2+
- **Database:** MySQL (production) / SQLite (development/testing)
- **Authentication:** Laravel Sanctum (API tokens)
- **Authorization:** Spatie Laravel Permission (role-based access control)

## Features

### Authentication & Authorization
User registration and login with API tokens, role-based access control with four default roles (Administrator, Manager, HR Officer, Staff Member), password reset functionality, and profile management.

### Organization Structure
Office location management supporting multiple branches, division/department management, job title management with hierarchical structure, and file category management for document organization.

### Staff Management
Comprehensive staff member profiles with 40+ fields covering personal information, employment details, banking information, and emergency contacts. The system also supports staff file uploads, recognition and awards tracking, role upgrades (promotions), location transfers, discipline notes and warnings, and offboarding processes.

### Leave Management
Multiple leave types including Annual, Sick, Casual, Maternity, Paternity, and Bereavement leave. Features include leave application and approval workflow, leave balance tracking, and leave reports.

### Attendance Management
Daily attendance marking with clock in/out functionality, bulk attendance marking for multiple employees, monthly attendance reports, and tracking for late arrivals and early departures.

### Payroll System
Complete salary component management including allowances, deductions, commissions, and loans. The system handles overtime calculation, company contributions, tax bracket configuration with progressive tax rates, and payslip generation.

### Communication
Company announcements targeted to specific employees, events and calendar management, holiday management, and company policies and documents repository.

### Reports
Monthly attendance reports, leave reports by employee and type, payroll reports with department summaries, and dashboard widgets for quick insights.

## Quick Start

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Run `composer install`
4. Run `php artisan key:generate`
5. Run `php artisan migrate --seed`
6. Run `php artisan serve`

For detailed setup instructions, see [SETUP_GUIDE.md](SETUP_GUIDE.md).

For API documentation and frontend integration, see [FRONTEND_GUIDE.md](FRONTEND_GUIDE.md).

## Default Users

After seeding, the following users are available:

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@hrms.com | password |
| Manager | manager@hrms.com | password |
| HR Officer | hr@hrms.com | password |
| Staff Member | staff@hrms.com | password |

## API Structure

The API is versioned and all endpoints are prefixed with `/api/v1/`. Authentication is required for most endpoints using Bearer tokens obtained from the login endpoint.

Key endpoint groups include authentication (`/api/v1/auth/*`), office locations, divisions, job titles, staff members, leaves, attendances, payslips, events, documents, and reports.

## Database Schema

The system includes 51 database tables covering all aspects of HR management, from basic organization structure to complex payroll calculations. Key tables include users, staff_members, office_locations, divisions, job_titles, leaves, attendances, pay_slips, and various configuration tables for tax brackets, allowances, and deductions.

## License

This project is proprietary software.
