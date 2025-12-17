# HRMS (Human Resource Management System) - Application Overview

## What is HRMS?

HRMS is a comprehensive backend API system designed to manage all aspects of human resources in an organization. It handles everything from employee onboarding to payroll processing, leave management, and performance tracking.

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HRMS BACKEND API                                │
│                         (Laravel 11 + PHP 8.2)                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │   Mobile    │  │   Web App   │  │   Desktop   │  │  Third-Party│       │
│  │    App      │  │  (React/Vue)│  │    App      │  │    Systems  │       │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘       │
│         │                │                │                │               │
│         └────────────────┴────────────────┴────────────────┘               │
│                                   │                                         │
│                                   ▼                                         │
│                    ┌──────────────────────────────┐                        │
│                    │      REST API Gateway        │                        │
│                    │    /api/v1/* endpoints       │                        │
│                    │   (Sanctum Authentication)   │                        │
│                    └──────────────────────────────┘                        │
│                                   │                                         │
│         ┌─────────────────────────┼─────────────────────────┐              │
│         │                         │                         │              │
│         ▼                         ▼                         ▼              │
│  ┌─────────────┐          ┌─────────────┐          ┌─────────────┐        │
│  │   Staff     │          │   Payroll   │          │   Reports   │        │
│  │ Management  │          │   System    │          │   Module    │        │
│  └─────────────┘          └─────────────┘          └─────────────┘        │
│                                                                             │
│                    ┌──────────────────────────────┐                        │
│                    │      MySQL Database          │                        │
│                    │    (51 Tables, 55 Models)    │                        │
│                    └──────────────────────────────┘                        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Core Modules Overview

### 1. Authentication & Access Control

```
┌─────────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION FLOW                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│   User                    API                    Database       │
│    │                       │                        │           │
│    │  POST /auth/login     │                        │           │
│    │──────────────────────>│                        │           │
│    │  {email, password}    │                        │           │
│    │                       │  Verify credentials    │           │
│    │                       │───────────────────────>│           │
│    │                       │                        │           │
│    │                       │  User + Roles          │           │
│    │                       │<───────────────────────│           │
│    │                       │                        │           │
│    │  {token, user}        │                        │           │
│    │<──────────────────────│                        │           │
│    │                       │                        │           │
│    │  GET /staff-members   │                        │           │
│    │  Authorization: Bearer│                        │           │
│    │──────────────────────>│                        │           │
│    │                       │  Validate token        │           │
│    │                       │───────────────────────>│           │
│    │                       │                        │           │
│    │  {data: [...]}        │                        │           │
│    │<──────────────────────│                        │           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

ROLES:
┌──────────────────┬────────────────────────────────────────────┐
│ administrator    │ Full system access                         │
├──────────────────┼────────────────────────────────────────────┤
│ manager          │ Department-level access                    │
├──────────────────┼────────────────────────────────────────────┤
│ hr_officer       │ HR operations (staff, leave, payroll)      │
├──────────────────┼────────────────────────────────────────────┤
│ staff_member     │ Self-service (own profile, leave, clock)   │
└──────────────────┴────────────────────────────────────────────┘
```

---

### 2. Organization Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                  ORGANIZATION HIERARCHY                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    ┌─────────────────┐                         │
│                    │    COMPANY      │                         │
│                    │   (Settings)    │                         │
│                    └────────┬────────┘                         │
│                             │                                   │
│           ┌─────────────────┼─────────────────┐                │
│           │                 │                 │                │
│           ▼                 ▼                 ▼                │
│    ┌─────────────┐   ┌─────────────┐   ┌─────────────┐        │
│    │   Head      │   │   Branch    │   │   Remote    │        │
│    │   Office    │   │   Office    │   │   Office    │        │
│    │  (Location) │   │  (Location) │   │  (Location) │        │
│    └──────┬──────┘   └──────┬──────┘   └──────┬──────┘        │
│           │                 │                 │                │
│     ┌─────┴─────┐     ┌─────┴─────┐     ┌─────┴─────┐         │
│     │           │     │           │     │           │         │
│     ▼           ▼     ▼           ▼     ▼           ▼         │
│  ┌──────┐  ┌──────┐ ┌──────┐  ┌──────┐ ┌──────┐  ┌──────┐    │
│  │ IT   │  │ HR   │ │Sales │  │ Ops  │ │ Dev  │  │ QA   │    │
│  │(Div) │  │(Div) │ │(Div) │  │(Div) │ │(Div) │  │(Div) │    │
│  └──┬───┘  └──┬───┘ └──┬───┘  └──┬───┘ └──┬───┘  └──┬───┘    │
│     │         │        │         │        │         │         │
│     ▼         ▼        ▼         ▼        ▼         ▼         │
│  Job Titles: Developer, Manager, Analyst, Engineer, etc.      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 3. Staff Member Lifecycle

```
┌─────────────────────────────────────────────────────────────────┐
│                   STAFF MEMBER LIFECYCLE                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐ │
│  │  HIRE    │───>│  ACTIVE  │───>│ PROMOTED │───>│TRANSFERRED│ │
│  │          │    │          │    │          │    │          │ │
│  └──────────┘    └────┬─────┘    └──────────┘    └──────────┘ │
│                       │                                        │
│                       │ (Recognition, Training, Reviews)       │
│                       │                                        │
│                       ▼                                        │
│                  ┌──────────┐                                  │
│                  │DISCIPLINE│ (Warnings, Notes)                │
│                  └────┬─────┘                                  │
│                       │                                        │
│         ┌─────────────┴─────────────┐                         │
│         │                           │                         │
│         ▼                           ▼                         │
│  ┌─────────────┐            ┌─────────────┐                   │
│  │ VOLUNTARY   │            │ INVOLUNTARY │                   │
│  │   EXIT      │            │    EXIT     │                   │
│  │(Resignation)│            │(Termination)│                   │
│  └─────────────┘            └─────────────┘                   │
│                                                                 │
│  STAFF RECORD CONTAINS:                                        │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Personal: Name, Email, Phone, Address, DOB, Gender      │  │
│  │ Employment: Staff Code, Hire Date, Status, Location     │  │
│  │ Banking: Account Name, Number, Bank, Branch             │  │
│  │ Compensation: Type (Monthly/Hourly), Base Salary        │  │
│  │ Emergency: Contact Name, Phone, Relationship            │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 4. Leave Management System

```
┌─────────────────────────────────────────────────────────────────┐
│                    LEAVE MANAGEMENT FLOW                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  LEAVE TYPES:                                                   │
│  ┌─────────┬─────────┬─────────┬─────────┬─────────┐          │
│  │ Annual  │  Sick   │ Casual  │Maternity│Paternity│          │
│  │ 20 days │ 10 days │ 5 days  │ 90 days │ 15 days │          │
│  └─────────┴─────────┴─────────┴─────────┴─────────┘          │
│                                                                 │
│  LEAVE APPLICATION WORKFLOW:                                    │
│                                                                 │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐                 │
│  │ Employee │    │    HR    │    │  Manager │                 │
│  └────┬─────┘    └────┬─────┘    └────┬─────┘                 │
│       │               │               │                        │
│       │ Apply Leave   │               │                        │
│       │──────────────>│               │                        │
│       │               │               │                        │
│       │               │ Review        │                        │
│       │               │──────────────>│                        │
│       │               │               │                        │
│       │               │   Approve/    │                        │
│       │               │   Reject      │                        │
│       │               │<──────────────│                        │
│       │               │               │                        │
│       │  Notification │               │                        │
│       │<──────────────│               │                        │
│       │               │               │                        │
│                                                                 │
│  LEAVE BALANCE CALCULATION:                                     │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Remaining = Total Allowed - Approved Leaves (This Year) │  │
│  │                                                         │  │
│  │ Example: Annual Leave                                   │  │
│  │ Total: 20 days | Used: 8 days | Remaining: 12 days     │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 5. Attendance System

```
┌─────────────────────────────────────────────────────────────────┐
│                    ATTENDANCE TRACKING                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  DAILY ATTENDANCE FLOW:                                         │
│                                                                 │
│    09:00 AM              12:00 PM              06:00 PM        │
│       │                     │                     │            │
│       ▼                     ▼                     ▼            │
│  ┌─────────┐          ┌─────────┐          ┌─────────┐        │
│  │CLOCK IN │          │  BREAK  │          │CLOCK OUT│        │
│  │         │          │         │          │         │        │
│  │ Status: │          │ Rest:   │          │ Total:  │        │
│  │ Present │          │ 60 min  │          │ 8 hours │        │
│  └─────────┘          └─────────┘          └─────────┘        │
│                                                                 │
│  ATTENDANCE STATUSES:                                           │
│  ┌─────────────┬─────────────┬─────────────┐                  │
│  │   PRESENT   │   ABSENT    │  HALF DAY   │                  │
│  │     ✓       │      ✗      │     ½       │                  │
│  └─────────────┴─────────────┴─────────────┘                  │
│                                                                 │
│  MONTHLY REPORT MOCKUP:                                         │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Employee      │ Present │ Absent │ Late │ Overtime     │  │
│  ├───────────────┼─────────┼────────┼──────┼──────────────┤  │
│  │ John Doe      │   22    │   1    │  2   │  10 hrs      │  │
│  │ Jane Smith    │   20    │   3    │  0   │   5 hrs      │  │
│  │ Bob Johnson   │   23    │   0    │  1   │  15 hrs      │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 6. Payroll System

```
┌─────────────────────────────────────────────────────────────────┐
│                    PAYROLL PROCESSING                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  SALARY CALCULATION:                                            │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │                                                         │  │
│  │   GROSS SALARY                                          │  │
│  │   ┌─────────────────────────────────────────────────┐  │  │
│  │   │ Base Salary          $5,000                     │  │  │
│  │   │ + Allowances                                    │  │  │
│  │   │   - HRA              $1,000                     │  │  │
│  │   │   - Transport        $  500                     │  │  │
│  │   │   - Medical          $  300                     │  │  │
│  │   │ + Commission         $  200                     │  │  │
│  │   │ + Overtime           $  150                     │  │  │
│  │   │ + Other Payments     $  100                     │  │  │
│  │   ├─────────────────────────────────────────────────┤  │  │
│  │   │ TOTAL GROSS          $7,250                     │  │  │
│  │   └─────────────────────────────────────────────────┘  │  │
│  │                                                         │  │
│  │   DEDUCTIONS                                            │  │
│  │   ┌─────────────────────────────────────────────────┐  │  │
│  │   │ - Tax                $  725  (10%)              │  │  │
│  │   │ - Insurance          $  200                     │  │  │
│  │   │ - Loan EMI           $  300                     │  │  │
│  │   │ - PF                 $  500                     │  │  │
│  │   ├─────────────────────────────────────────────────┤  │  │
│  │   │ TOTAL DEDUCTIONS     $1,725                     │  │  │
│  │   └─────────────────────────────────────────────────┘  │  │
│  │                                                         │  │
│  │   ═══════════════════════════════════════════════════  │  │
│  │   NET PAYABLE            $5,525                        │  │
│  │   ═══════════════════════════════════════════════════  │  │
│  │                                                         │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 7. Tax Calculation

```
┌─────────────────────────────────────────────────────────────────┐
│                    TAX BRACKET SYSTEM                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  PROGRESSIVE TAX BRACKETS:                                      │
│                                                                 │
│  Income Range          │ Tax Rate │ Fixed Amount               │
│  ──────────────────────┼──────────┼────────────────────────    │
│  $0 - $10,000          │    0%    │    $0                      │
│  $10,001 - $30,000     │    5%    │    $0                      │
│  $30,001 - $60,000     │   10%    │  $1,000                    │
│  $60,001 - $100,000    │   15%    │  $4,000                    │
│  $100,001+             │   20%    │ $10,000                    │
│                                                                 │
│  TAX CALCULATION EXAMPLE:                                       │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Annual Income: $75,000                                  │  │
│  │ Bracket: $60,001 - $100,000 (15% + $4,000 fixed)       │  │
│  │                                                         │  │
│  │ Tax = $4,000 + (($75,000 - $60,000) × 15%)             │  │
│  │     = $4,000 + ($15,000 × 0.15)                        │  │
│  │     = $4,000 + $2,250                                  │  │
│  │     = $6,250 annual tax                                │  │
│  │     = $520.83 monthly tax                              │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 8. Communication & Events

```
┌─────────────────────────────────────────────────────────────────┐
│                 COMMUNICATION MODULES                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ANNOUNCEMENTS                    EVENTS                        │
│  ┌─────────────────────┐         ┌─────────────────────┐       │
│  │ Title: Holiday      │         │ Title: Team Meeting │       │
│  │ Start: Dec 25       │         │ Date: Dec 20        │       │
│  │ End: Dec 26         │         │ Time: 10:00 AM      │       │
│  │ Target: All Staff   │         │ Attendees: IT Dept  │       │
│  └─────────────────────┘         └─────────────────────┘       │
│                                                                 │
│  COMPLAINTS                       TRAVELS                       │
│  ┌─────────────────────┐         ┌─────────────────────┐       │
│  │ From: John Doe      │         │ Employee: Jane      │       │
│  │ Against: HR Dept    │         │ Destination: NYC    │       │
│  │ Date: Dec 15        │         │ Purpose: Client     │       │
│  │ Status: Open        │         │ Status: Approved    │       │
│  └─────────────────────┘         └─────────────────────┘       │
│                                                                 │
│  HOLIDAYS CALENDAR:                                             │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ December 2024                                           │  │
│  │ ┌───┬───┬───┬───┬───┬───┬───┐                         │  │
│  │ │Sun│Mon│Tue│Wed│Thu│Fri│Sat│                         │  │
│  │ ├───┼───┼───┼───┼───┼───┼───┤                         │  │
│  │ │ 1 │ 2 │ 3 │ 4 │ 5 │ 6 │ 7 │                         │  │
│  │ │ 8 │ 9 │10 │11 │12 │13 │14 │                         │  │
│  │ │15 │16 │17 │18 │19 │20 │21 │                         │  │
│  │ │22 │23 │24 │[25]│[26]│27│28 │  [25-26] Christmas     │  │
│  │ │29 │30 │31 │   │   │   │   │                         │  │
│  │ └───┴───┴───┴───┴───┴───┴───┘                         │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 9. Documents & Letters

```
┌─────────────────────────────────────────────────────────────────┐
│                  DOCUMENT MANAGEMENT                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  DOCUMENT TYPES:                                                │
│  ┌─────────────┬─────────────┬─────────────┬─────────────┐    │
│  │   ID Proof  │  Address    │ Education   │ Experience  │    │
│  │   Passport  │   Proof     │ Certificates│  Letters    │    │
│  └─────────────┴─────────────┴─────────────┴─────────────┘    │
│                                                                 │
│  LETTER TEMPLATES:                                              │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │              JOINING LETTER                             │  │
│  │  ─────────────────────────────────────────────────────  │  │
│  │                                                         │  │
│  │  Dear {employee_name},                                  │  │
│  │                                                         │  │
│  │  We are pleased to offer you the position of           │  │
│  │  {designation} at {company_name}.                      │  │
│  │                                                         │  │
│  │  Your joining date: {join_date}                        │  │
│  │  Your salary: {salary}                                 │  │
│  │                                                         │  │
│  │  Welcome to the team!                                  │  │
│  │                                                         │  │
│  │  Regards,                                              │  │
│  │  HR Department                                         │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
│  OTHER LETTERS: Experience Certificate, NOC                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## API Endpoints Summary

```
┌─────────────────────────────────────────────────────────────────┐
│                    API ROUTES (214 Total)                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Authentication (/api/v1/auth/*)                               │
│  ├── POST /register         - Create new user                  │
│  ├── POST /login            - Get auth token                   │
│  ├── POST /logout           - Revoke token                     │
│  ├── GET  /profile          - Get current user                 │
│  └── POST /change-password  - Update password                  │
│                                                                 │
│  Staff Management (/api/v1/staff-members/*)                    │
│  ├── GET    /               - List all staff                   │
│  ├── POST   /               - Create staff member              │
│  ├── GET    /{id}           - Get staff details                │
│  ├── PUT    /{id}           - Update staff                     │
│  ├── DELETE /{id}           - Delete staff                     │
│  └── GET    /{id}/salary    - Get salary details               │
│                                                                 │
│  Leave Management (/api/v1/leaves/*)                           │
│  ├── GET    /               - List leaves                      │
│  ├── POST   /               - Apply for leave                  │
│  ├── POST   /{id}/approve   - Approve leave                    │
│  ├── POST   /{id}/reject    - Reject leave                     │
│  └── GET    /balance/{id}   - Get leave balance                │
│                                                                 │
│  Attendance (/api/v1/attendance/*)                             │
│  ├── POST   /clock-in       - Clock in                         │
│  ├── POST   /clock-out      - Clock out                        │
│  ├── POST   /bulk           - Bulk mark attendance             │
│  └── GET    /monthly-report - Get monthly report               │
│                                                                 │
│  Payroll (/api/v1/payslips/*)                                  │
│  ├── GET    /               - List payslips                    │
│  ├── POST   /generate       - Generate payslip                 │
│  └── GET    /{id}/pdf       - Download PDF                     │
│                                                                 │
│  ... and 180+ more endpoints                                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Database Schema Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                 DATABASE RELATIONSHIPS                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  users ─────────────────┐                                      │
│    │                    │                                      │
│    │ 1:1                │ 1:N                                  │
│    ▼                    ▼                                      │
│  staff_members ───── leaves                                    │
│    │                    │                                      │
│    │ N:1                │ N:1                                  │
│    ▼                    ▼                                      │
│  office_locations    leave_types                               │
│    │                                                           │
│    │ 1:N                                                       │
│    ▼                                                           │
│  divisions                                                     │
│    │                                                           │
│    │ 1:N                                                       │
│    ▼                                                           │
│  job_titles                                                    │
│                                                                 │
│  staff_members ───── attendances (1:N)                         │
│  staff_members ───── payslips (1:N)                            │
│  staff_members ───── allowances (1:N)                          │
│  staff_members ───── loans (1:N)                               │
│  staff_members ───── recognition_records (1:N)                 │
│  staff_members ───── discipline_notes (1:N)                    │
│                                                                 │
│  TOTAL: 51 Tables, 55 Models                                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Quick Start Guide

```
┌─────────────────────────────────────────────────────────────────┐
│                    GETTING STARTED                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. SETUP                                                       │
│     $ cd hrms                                                   │
│     $ composer install                                          │
│     $ cp .env.example .env                                      │
│     $ php artisan key:generate                                  │
│                                                                 │
│  2. DATABASE                                                    │
│     $ php artisan migrate --seed                                │
│                                                                 │
│  3. RUN SERVER                                                  │
│     $ php artisan serve                                         │
│     Server running on http://localhost:8000                     │
│                                                                 │
│  4. LOGIN                                                       │
│     POST /api/v1/auth/login                                     │
│     {                                                           │
│       "email": "admin@hrms.com",                                │
│       "password": "password"                                    │
│     }                                                           │
│                                                                 │
│  5. USE API                                                     │
│     GET /api/v1/staff-members                                   │
│     Authorization: Bearer {token}                               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Summary

This HRMS backend provides a complete solution for managing:

| Module | Features |
|--------|----------|
| **Staff** | 40+ fields, files, lifecycle tracking |
| **Leave** | Types, applications, approvals, balance |
| **Attendance** | Clock in/out, bulk marking, reports |
| **Payroll** | Allowances, deductions, tax, payslips |
| **Communication** | Events, announcements, complaints |
| **Documents** | Policies, letters, templates |
| **Reports** | Dashboard, attendance, leave, payroll |

**Tech Stack:** Laravel 11 + PHP 8.2 + MySQL + Sanctum + Spatie Permission

**Tests:** 39 automated tests (all passing)

**API:** 214 RESTful endpoints under `/api/v1/`
