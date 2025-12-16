# HRMS Backend Feature Prompts (Laravel)

> **Tech Stack:** Laravel 11 + PHP 8.2 + MySQL + Sanctum + Spatie Permission
>
> **Reference:** Demo project patterns (NOT code) - understand features, write original
>
> **CRITICAL:** Use ORIGINAL variable names, table names, method names - NO copying from demo
>
> **Naming Convention:** Use clear, descriptive names (e.g., `staff_member` not `employee`, `office_location` not `branch`)

---

## Prompt Set 1: Project Setup & Authentication

### Features: Laravel Installation, Sanctum Auth, Spatie Permission Setup, Login Flow

```
Set up Laravel 11 HRMS backend project with authentication.
IMPORTANT: Write ORIGINAL code - only take CONCEPT reference from demo, NO variable/table names.

**Feature 1: Laravel Project Setup**
- Install Laravel 11: composer create-project laravel/laravel hrms
- Configure MySQL database in .env
- Install dependencies: Sanctum, Spatie Permission
- Configure app timezone and locale
- Create base folder structure

**Feature 2: Sanctum Authentication**
- php artisan install:api (Sanctum)
- Configure User model with HasApiTokens, HasRoles (Spatie)
- Create AccessController with signIn, signUp, signOut
- Password hashing with Hash::make()
- Personal access token generation

**Feature 3: Spatie Permission Setup**
- composer require spatie/laravel-permission
- php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
- Create roles: administrator, manager, hr_officer, staff_member
- Define abilities: view_staff, create_staff, edit_staff, delete_staff
- AccessSeeder with default roles and abilities

**Feature 4: Login Flow**
- Login controller: validate email/password, issue token
- Register: create user + assign role
- Forgot password: send reset link via email
- Reset password: verify token, update password
- Logout: revoke current token

Routes (routes/api.php):
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/forgot-password
POST /api/auth/reset-password
```

---

## Prompt Set 2: Organization Structure

### Features: Office Locations, Divisions, Job Titles, File Categories

```
Implement organization structure management for HRMS.
IMPORTANT: Use ORIGINAL table/model names - NOT demo names.

**Feature 1: Office Location Management**
- Migration: office_locations (title, address, contact_phone, contact_email, is_active, tenant_id, author_id)
- Model: OfficeLocation with fillable, relationships
- OfficeLocationController: index, create, store, edit, update, destroy
- Resource route: Route::resource('office-locations', OfficeLocationController::class)
- Blade views: index, create, edit

**Feature 2: Division Management**
- Migration: divisions (title, office_location_id, notes, is_active, tenant_id, author_id)
- Model: Division belongsTo OfficeLocation
- DivisionController with standard CRUD
- fetchDivisions endpoint for AJAX filtering by location

**Feature 3: Job Title Management**
- Migration: job_titles (title, division_id, notes, is_active, tenant_id, author_id)
- Model: JobTitle belongsTo Division
- JobTitleController with CRUD
- fetchJobTitles endpoint for AJAX filtering

**Feature 4: File Categories**
- Migration: file_categories (title, notes, is_mandatory, is_active)
- Model: FileCategory
- FileCategoryController with CRUD
- Used when uploading staff member files
```

---

## Prompt Set 3: Staff Member Management

### Features: Staff Member CRUD, Personal Info, Employment Details, Staff Files

```
Implement comprehensive Staff Member management module.
IMPORTANT: Use ORIGINAL field names - conceptually similar to demo but unique.

**Feature 1: StaffMember Model (Complex - 40+ fields)**
- Migration: staff_members with fields:
  - user_id, full_name, personal_email, mobile_number, birth_date, gender, home_address
  - nationality, passport_number, country_code, region, city_name, postal_code
  - staff_code, biometric_id
  - office_location_id, division_id, job_title_id, hire_date
  - bank_account_name, bank_account_number, bank_name, bank_branch
  - compensation_type, base_salary, employment_status, tenant_id, author_id
- Model relationships: belongsTo User, OfficeLocation, Division, JobTitle

**Feature 2: StaffMemberController**
- index: list with datatable, filters (location, division)
- create: form with location/division/job_title dropdowns  
- store: validate, create User + StaffMember, attach role
- show: full profile view
- edit/update: update details
- destroy: soft delete or mark inactive
- gridView: card view alternative

**Feature 3: Staff Files**
- Migration: staff_files (staff_member_id, file_category_id, file_path)
- Upload files during staff creation
- View/download files on profile
- Remove files

**Feature 4: AJAX Helpers**
- fetchDivisions: POST, filter by office_location_id  
- fetchJobTitles: POST, filter by division_id
- Used in create/edit forms for cascading dropdowns
```

---

## Prompt Set 4: Staff Lifecycle - Recognition & Advancement

### Features: Recognition Categories, Recognition Records, Role Upgrades, Location Transfers

```
Implement staff recognition and career progression.
IMPORTANT: Original naming - NOT demo variable names.

**Feature 1: Recognition Categories**
- Migration: recognition_categories (title, notes, is_active, tenant_id, author_id)
- Model: RecognitionCategory
- RecognitionCategoryController with CRUD
- Examples: Star Performer, Team Champion, Innovation Leader

**Feature 2: Recognition Records**  
- Migration: recognition_records (staff_member_id, recognition_category_id, recognition_date, reward, notes, tenant_id, author_id)
- Model: RecognitionRecord belongsTo StaffMember, RecognitionCategory
- RecognitionRecordController with CRUD
- History per staff member

**Feature 3: Role Upgrades (Promotions)**
- Migration: role_upgrades (staff_member_id, new_job_title_id, upgrade_title, effective_date, notes, tenant_id, author_id)
- Model: RoleUpgrade belongsTo StaffMember, JobTitle
- RoleUpgradeController with CRUD
- Update staff member job_title on upgrade

**Feature 4: Location Transfers**
- Migration: location_transfers (staff_member_id, new_office_location_id, new_division_id, effective_date, notes, tenant_id, author_id)
- Model: LocationTransfer belongsTo StaffMember, OfficeLocation, Division
- LocationTransferController with CRUD
- Update staff member location/division on transfer
```

---

## Prompt Set 5: Staff Lifecycle - Discipline & Exit

### Features: Discipline Notes, Exit Categories, Offboarding, Voluntary Exits

```
Implement disciplinary actions and staff exits.
IMPORTANT: Original naming - unique table/field names.

**Feature 1: Discipline Notes (Warnings)**
- Migration: discipline_notes (staff_member_id, issued_to_user_id, subject, issue_date, details, tenant_id, author_id)
- Model: DisciplineNote belongsTo StaffMember
- DisciplineNoteController with CRUD
- History tracking

**Feature 2: Exit Categories (Termination Types)**
- Migration: exit_categories (title, notes, is_active)
- Model: ExitCategory  
- ExitCategoryController with CRUD
- Examples: Voluntary, Involuntary, Retirement, Contract End

**Feature 3: Offboarding (Terminations)**
- Migration: offboardings (staff_member_id, exit_category_id, exit_date, notice_date, details, tenant_id, author_id)
- Model: Offboarding belongsTo StaffMember, ExitCategory
- OffboardingController with CRUD
- Update staff status on offboarding

**Feature 4: Voluntary Exits (Resignations)**
- Migration: voluntary_exits (staff_member_id, notice_date, exit_date, reason, approval_status, tenant_id, author_id)
- Model: VoluntaryExit belongsTo StaffMember
- Status: pending, approved, declined
- VoluntaryExitController with CRUD and approval workflow
```

---

## Prompt Set 6: Employee Lifecycle - Travels & Complaints

### Features: Travels (Trips), Complaints, Announcements, Holidays

```
Implement business travel and grievance management.

**Feature 1: Travels (Trips)**
- Migration: travels (employee_id, start_date, end_date, purpose, place, description, status, workspace, created_by)
- Model: Travel belongsTo Employee
- Status: Pending, Approved, Rejected
- TravelController with CRUD and approval

**Feature 2: Complaints**
- Migration: complaints (complaint_from, complaint_against, title, complaint_date, description, workspace, created_by)
- complaint_from: employee filing
- complaint_against: employee/department
- ComplaintController with CRUD

**Feature 3: Announcements**
- Migration: announcements (title, start_date, end_date, description, workspace, created_by)
- announcement_employees pivot table for targeting
- AnnouncementController with CRUD
- getemployee endpoint for multi-select

**Feature 4: Holidays**
- Migration: holidays (name, date, description, workspace, created_by)
- HolidayController with CRUD
- Holiday calendar view
- Import holidays from file
```

---

## Prompt Set 7: Leave Management

### Features: Leave Types, Leave Applications, Leave Approvals, Leave Reports

```
Implement comprehensive leave management system.

**Feature 1: Leave Types**
- Migration: leave_types (name, days, description, workspace, created_by)
- Model: LeaveType
- LeaveTypeController with CRUD
- Examples: Casual, Sick, Annual, Maternity

**Feature 2: Leave Model**
- Migration: leaves (employee_id, user_id, leave_type_id, applied_on, start_date, end_date, total_leave_days, leave_reason, remark, status, workspace, created_by)
- Model: Leave belongsTo Employee, LeaveType
- Status: Pending, Approved, Rejected
- Auto-calculate total_leave_days

**Feature 3: LeaveController**
- index: list leaves with filters
- create/store: apply for leave
- action: show approval form
- changeaction: approve/reject with remark
- jsoncount: AJAX to get leave balance

**Feature 4: Leave Reports**
- Monthly leave summary by employee
- Leave balance tracking
- Route: report/leave
- Filter by branch, department, month
```

---

## Prompt Set 8: Attendance Management

### Features: Attendance Records, Clock In/Out, Bulk Attendance, Attendance Reports

```
Implement attendance tracking and management.

**Feature 1: Attendance Model**
- Migration: attendances (employee_id, date, status, clock_in, clock_out, late, early_leaving, overtime, total_rest, workspace, created_by)
- Model: Attendance belongsTo Employee (via User)
- Status: Present, Absent, Half Day

**Feature 2: AttendanceController**
- index: list attendance with date filters
- create/store: mark single attendance
- edit/update: modify attendance
- attendance: employee self clock-in/out

**Feature 3: Bulk Attendance**
- BulkAttendance: form to mark multiple employees
- BulkAttendanceData: process bulk submission
- Select date, show all employees, mark present/absent

**Feature 4: Attendance Import/Report**
- Import from Excel/CSV file
- fileImportExport: export attendance data
- Monthly attendance report
- Route: report/monthly/attendance
```

---

## Prompt Set 9: Payroll Setup

### Features: Payslip Types, Allowance Options, Loan Options, Deduction Options

```
Implement payroll configuration modules.

**Feature 1: Payslip Types**
- Migration: payslip_types (name, description, status, workspace, created_by)
- Model: PayslipType
- PayslipTypeController with CRUD
- Used for salary type categorization

**Feature 2: Allowance Options**
- Migration: allowance_options (name, description, status, workspace, created_by)
- Model: AllowanceOption
- AllowanceOptionController with CRUD
- Examples: HRA, Medical, Transport

**Feature 3: Loan Options**
- Migration: loan_options (name, description, status, workspace, created_by)
- Model: LoanOption
- LoanOptionController with CRUD
- Examples: Personal Loan, Advance Salary

**Feature 4: Deduction Options**
- Migration: deduction_options (name, description, status, workspace, created_by)
- Model: DeductionOption
- DeductionOptionController with CRUD
- Examples: Tax, Insurance, PF
```

---

## Prompt Set 10: Salary Components

### Features: Allowances, Commissions, Loans, Saturation Deductions

```
Implement employee-specific salary components.

**Feature 1: Allowances**
- Migration: allowances (employee_id, allowance_option_id, title, type, amount, workspace, created_by)
- type: fixed or percentage
- AllowanceController with CRUD
- Create from employee salary page

**Feature 2: Commissions**
- Migration: commissions (employee_id, title, type, amount, start_date, end_date, workspace, created_by)
- Date range for commission validity
- CommissionController with CRUD

**Feature 3: Loans**
- Migration: loans (employee_id, loan_option_id, title, type, amount, start_date, end_date, reason, workspace, created_by)
- LoanController with CRUD
- EMI deduction tracking

**Feature 4: Saturation Deductions**
- Migration: saturation_deductions (employee_id, deduction_option_id, title, type, amount, workspace, created_by)
- SaturationDeductionController with CRUD
- Recurring deductions from salary
```

---

## Prompt Set 11: Payroll Processing

### Features: Other Payments, Overtime, Company Contributions, Set Salary

```
Implement additional payroll components and salary setup.

**Feature 1: Other Payments**
- Migration: other_payments (employee_id, title, type, amount, workspace, created_by)
- OtherPaymentController with CRUD
- Bonuses, special payments

**Feature 2: Overtime**
- Migration: overtimes (employee_id, title, number_of_days, hours, rate, start_date, end_date, workspace, created_by)
- Calculate: number_of_days * hours * rate
- OvertimeController with CRUD

**Feature 3: Company Contributions**
- Migration: company_contributions (employee_id, title, type, amount, workspace, created_by)
- Employer-side contributions (PF, gratuity)
- CompanyContributionController with CRUD

**Feature 4: Set Salary (Salary Structure)**
- SetSalaryController: view employee salary structure
- employeeBasicSalary: show/edit basic salary
- employeeUpdateSalary: update salary
- Dashboard showing all salary components
```

---

## Prompt Set 12: Payslip Generation

### Features: Payslip Model, PaySlipController, Payslip PDF, Payslip Email

```
Implement payslip generation and distribution.

**Feature 1: PaySlip Model**
- Migration: pay_slips (employee_id, net_payable, basic_salary, salary_month, status, allowance, commission, loan, saturation_deduction, other_payment, overtime, company_contribution, tax_bracket, workspace, created_by)
- JSON fields store component snapshots
- Model: PaySlip belongsTo Employee

**Feature 2: PaySlipController**
- index: list payslips by month
- create: generate payslip for month
- search_json: AJAX search
- store: calculate and save payslip

**Feature 3: Payslip Calculation (Employee.php)**
- get_net_salary(): calculate net pay
  - Sum: basic + allowances + commissions + overtime + other_payment + company_contribution
  - Minus: loans + deductions
  - Apply tax bracket percentage
- Store calculated values in PaySlip

**Feature 4: Payslip PDF/Email**
- pdf: generate payslip PDF using DomPDF
- payslipPdf: download PDF
- send: email payslip to employee
- Template with company header, earnings, deductions, net pay
```

---

## Prompt Set 13: Tax Management

### Features: Tax Brackets, Tax Rebates, Tax Thresholds, Allowance Tax

```
Implement tax calculation modules.

**Feature 1: Tax Brackets**
- Migration: tax_brackets (name, from, to, fixed_amount, percentage, workspace, created_by)
- TaxBracketController with CRUD
- Apply bracket based on total earnings

**Feature 2: Tax Rebates**
- Migration: tax_rebates (name, amount, description, workspace, created_by)
- TaxRebateController with CRUD
- Reduce taxable income

**Feature 3: Tax Thresholds**
- Migration: tax_thresholds (name, threshold_amount, description, workspace, created_by)
- TaxThresholdController with CRUD
- Minimum income before tax applies

**Feature 4: Allowance Tax**
- Migration: allowance_taxes (allowance_id, tax_percentage, workspace, created_by)
- AllowanceTaxController with CRUD
- Tax on specific allowances
```

---

## Prompt Set 14: Events & Calendar

### Features: Events, Event Employees, Calendar View, Event Notifications

```
Implement events and calendar management.

**Feature 1: Events Model**
- Migration: events (title, start_date, end_date, color, description, workspace, created_by)
- Migration: event_employees (event_id, employee_id)
- Model: Event with many-to-many employees

**Feature 2: EventController**
- index: list events
- create/store: create event with employee selection
- showData: event details
- getdepartment/getemployee: AJAX for filtering

**Feature 3: Calendar View**
- FullCalendar.js integration
- Display events, holidays, leaves
- Color coding by type
- Month/Week/Day views

**Feature 4: Event Notifications**
- Notify selected employees on event creation
- Email notifications
- Dashboard widget for upcoming events
```

---

## Prompt Set 15: Company Policies & Documents

### Features: Company Policies, Documents, Document Controller, Policy Acknowledgment

```
Implement document and policy management.

**Feature 1: Company Policies**
- Migration: company_policies (name, document, description, workspace, created_by)
- CompanyPolicyController with CRUD
- File upload for policy documents
- description route for viewing

**Feature 2: Documents Model**
- Migration: documents (name, document_type_id, document, description, workspace, created_by)
- Model: Document belongsTo DocumentType
- Central document repository

**Feature 3: DocumentController**
- index: list all documents
- create/store: upload new document
- description: view document details
- download: download document file
- destroy: delete document

**Feature 4: Policy Display**
- List company policies for employees
- Mark as read/acknowledged
- Policy version tracking
```

---

## Prompt Set 16: Letter Templates

### Features: Joining Letter, Experience Certificate, NOC

```
Implement HR letter generation.

**Feature 1: Joining Letter**
- Migration: joining_letters (lang, content, created_by)
- Store template with placeholders
- JoiningLetter model
- Placeholders: {employee_name}, {designation}, {join_date}, etc.

**Feature 2: Experience Certificate**
- Migration: experience_certificates (lang, content, created_by)
- Template for employment verification
- Include: dates, designation, performance

**Feature 3: NOC (No Objection Certificate)**
- Migration: noc_certificates (lang, content, created_by)
- Template with placeholders
- Generate on request

**Feature 4: Letter Generation**
- joiningletterPdf: generate PDF with employee data
- ExpCertificatePdf: generate experience letter
- NocPdf: generate NOC
- Replace placeholders with actual employee data
```

---

## Prompt Set 17: IP Restriction & Settings

### Features: IP Restriction, Company Settings, HRM Settings, Email Config

```
Implement security and configuration modules.

**Feature 1: IP Restriction**
- Migration: ip_restricts (ip, workspace, created_by)
- IpRestrictController with CRUD
- Middleware to check IP on clock-in
- Whitelist allowed IPs

**Feature 2: Company Settings**
- employee_prefix: #EMP format
- company_start_time, company_end_time
- ip_restrict: on/off toggle
- Store in settings table

**Feature 3: HRM Module Settings**
- HrmController::setting() to save settings
- Configure: work hours, overtime rules
- Leave policy defaults
- Attendance thresholds

**Feature 4: Email Configuration**
- MAIL_MAILER, MAIL_HOST settings
- Email templates for notifications
- Test email functionality
```

---

## Prompt Set 18: Reports

### Features: Monthly Attendance Report, Leave Report, Payroll Report, Dashboard

```
Implement reporting modules.

**Feature 1: Monthly Attendance Report**
- ReportController::monthlyAttendance
- Filter by branch, department, employee
- Show: present days, absent, late, overtime
- Export to Excel

**Feature 2: Leave Report**
- ReportController::leave
- Filter by month, year, leave type
- Show: taken, pending, balance
- Employee-wise breakdown

**Feature 3: Payroll Report**
- ReportController::Payroll
- Filter by month
- Show: gross, deductions, net
- Department/branch summary

**Feature 4: Dashboard Widgets**
- Total employees count
- This month attendance summary
- Pending leave requests
- Recent announcements
- Upcoming events
```

---

## Prompt Set 19: DataTables Integration

### Features: Employee DataTable, Attendance DataTable, Leave DataTable, Payslip DataTable

```
Implement server-side DataTables for lists.

**Feature 1: Employee DataTable**
- EmployeeDataTable class
- Columns: ID, Name, Email, Branch, Department, Status
- Actions: View, Edit, Delete
- Server-side processing

**Feature 2: Attendance DataTable**
- AttendanceDataTable class
- Columns: Employee, Date, Clock In, Clock Out, Status
- Filter by date range
- Bulk actions

**Feature 3: Leave DataTable**
- LeaveDataTable class
- Columns: Employee, Type, From, To, Days, Status
- Color coding by status
- Quick approve/reject

**Feature 4: Payslip DataTable**
- PayslipDataTable class
- Columns: Employee, Month, Basic, Net, Status
- Generate/View/Download actions
- Bulk generate for month
```

---

## Prompt Set 20: Import/Export Features

### Features: Employee Import, Attendance Import, Holiday Import, Data Export

```
Implement bulk data import/export functionality.

**Feature 1: Employee Import**
- fileImportExport: show import form
- fileImport: process uploaded Excel/CSV
- Sample template download
- Validation and error reporting

**Feature 2: Attendance Import**
- attendance.import routes
- Map columns to fields
- Handle date formats
- Bulk insert with validation

**Feature 3: Holiday Import**
- holiday.import routes
- Upload holiday calendar
- Parse and insert
- Handle duplicates

**Feature 4: Data Export**
- Export to Excel using Laravel Excel
- Employee list export
- Attendance report export
- Payroll summary export
```

---

## Prompt Set 21: API Layer (for Mobile/Frontend)

### Features: API Resources, API Controllers, API Routes, API Auth

```
Implement API layer for mobile/SPA integration.

**Feature 1: API Resources**
- EmployeeResource: transform employee data
- LeaveResource, AttendanceResource
- PayslipResource
- Consistent JSON structure

**Feature 2: API Controllers**
- Separate controllers in app/Http/Controllers/Api/
- Return JSON responses
- Handle API-specific logic

**Feature 3: API Routes**
- routes/api.php structure
- Version prefix: /api/v1/
- Sanctum protected routes
- Public endpoints (job listings)

**Feature 4: API Authentication**
- Login returns token
- Token in Authorization header
- Token refresh mechanism
- Logout revokes token
```

---

## Prompt Set 22: Seeders & Testing

### Features: Database Seeders, Factory Classes, Feature Tests, Unit Tests

```
Implement seeding and testing infrastructure.

**Feature 1: Database Seeders**
- UserSeeder: admin, employees
- RolePermissionSeeder: all roles/permissions
- BranchDepartmentSeeder
- DemoDataSeeder

**Feature 2: Factory Classes**
- EmployeeFactory
- LeaveFactory, AttendanceFactory
- Use Faker for realistic data

**Feature 3: Feature Tests**
- AuthTest: login, register, logout
- EmployeeTest: CRUD operations
- LeaveTest: apply, approve, reject
- AttendanceTest: mark, bulk

**Feature 4: Unit Tests**
- Employee::get_net_salary() test
- Tax calculation tests
- Leave balance calculation
- Validation rule tests
```

---

## Usage Instructions

1. **Start with Prompt Set 1** to initialize Laravel project with Spatie Permission
2. **Progress sequentially** through each phase
3. **CRITICAL: Write ORIGINAL code** - understand concepts from demo, use unique names
4. **Key naming changes applied:**
   - `employee` -> `staff_member`
   - `branch` -> `office_location`  
   - `department` -> `division`
   - `designation` -> `job_title`
   - `workspace` -> `tenant_id`
   - `created_by` -> `author_id`
5. **Test each module** before moving to next
6. **Use `php artisan` commands** for migrations and testing

---

## Feature Summary

| Phase | Prompt Sets | Features |
|-------|-------------|----------|
| Project Setup | 1-2 | 8 |
| Staff Management | 3-6 | 16 |
| Time Off & Attendance | 7-8 | 8 |
| Compensation System | 9-13 | 20 |
| Communication | 14-16 | 12 |
| Admin & Reports | 17-18 | 8 |
| Advanced | 19-22 | 16 |

**Total: 22 Prompt Sets | 88 Features (Laravel + Spatie Permission)**
