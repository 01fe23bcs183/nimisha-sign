# HRMS Frontend Integration Guide

This guide provides comprehensive API documentation for integrating frontend applications with the HRMS backend.

## Base URL

All API endpoints are prefixed with `/api/v1/`. For local development, the full base URL is `http://localhost:8000/api/v1/`.

## Authentication

The API uses Laravel Sanctum for token-based authentication. All protected endpoints require a Bearer token in the Authorization header.

### Headers Required

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Authentication Endpoints

#### Register a New User

```
POST /api/v1/auth/register
```

Request body:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response (201 Created):
```json
{
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "created_at": "2025-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

#### Login

```
POST /api/v1/auth/login
```

Request body:
```json
{
    "email": "admin@hrms.com",
    "password": "password"
}
```

Response (200 OK):
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "System Administrator",
        "email": "admin@hrms.com",
        "roles": [{"name": "administrator"}]
    },
    "token": "2|xyz789...",
    "token_type": "Bearer"
}
```

#### Logout

```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Response (200 OK):
```json
{
    "message": "Logged out successfully"
}
```

#### Get Current User Profile

```
GET /api/v1/auth/profile
Authorization: Bearer {token}
```

Response (200 OK):
```json
{
    "user": {
        "id": 1,
        "name": "System Administrator",
        "email": "admin@hrms.com",
        "roles": [{"name": "administrator"}],
        "staff_member": null
    }
}
```

#### Update Profile

```
PUT /api/v1/auth/profile
Authorization: Bearer {token}
```

Request body:
```json
{
    "name": "Updated Name",
    "email": "newemail@example.com"
}
```

#### Change Password

```
PUT /api/v1/auth/change-password
Authorization: Bearer {token}
```

Request body:
```json
{
    "current_password": "oldpassword",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

## Organization Structure

### Office Locations

#### List All Office Locations

```
GET /api/v1/office-locations
Authorization: Bearer {token}
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Headquarters",
            "address": "123 Main Street",
            "contact_phone": "+1-555-0100",
            "contact_email": "hq@company.com",
            "is_active": true
        }
    ]
}
```

#### Create Office Location

```
POST /api/v1/office-locations
Authorization: Bearer {token}
```

Request body:
```json
{
    "title": "New Branch",
    "address": "456 Oak Avenue",
    "contact_phone": "+1-555-0200",
    "contact_email": "branch@company.com",
    "is_active": true
}
```

#### Get Single Office Location

```
GET /api/v1/office-locations/{id}
```

#### Update Office Location

```
PUT /api/v1/office-locations/{id}
```

#### Delete Office Location

```
DELETE /api/v1/office-locations/{id}
```

### Divisions

#### List All Divisions

```
GET /api/v1/divisions
```

Query parameters:
- `office_location_id` - Filter by office location

#### Create Division

```
POST /api/v1/divisions
```

Request body:
```json
{
    "title": "Engineering",
    "office_location_id": 1,
    "notes": "Software development team",
    "is_active": true
}
```

#### Fetch Divisions by Office Location (AJAX)

```
POST /api/v1/fetch-divisions
```

Request body:
```json
{
    "office_location_id": 1
}
```

### Job Titles

#### List All Job Titles

```
GET /api/v1/job-titles
```

Query parameters:
- `division_id` - Filter by division

#### Create Job Title

```
POST /api/v1/job-titles
```

Request body:
```json
{
    "title": "Senior Developer",
    "division_id": 1,
    "notes": "Senior software developer position",
    "is_active": true
}
```

#### Fetch Job Titles by Division (AJAX)

```
POST /api/v1/fetch-job-titles
```

Request body:
```json
{
    "division_id": 1
}
```

## Staff Management

### Staff Members

#### List All Staff Members

```
GET /api/v1/staff-members
```

Query parameters:
- `office_location_id` - Filter by office location
- `division_id` - Filter by division
- `employment_status` - Filter by status (active, inactive, terminated, on_leave)

#### Create Staff Member

```
POST /api/v1/staff-members
```

Request body:
```json
{
    "user_id": 5,
    "full_name": "John Doe",
    "personal_email": "john.doe@example.com",
    "mobile_number": "+1-555-1234",
    "birth_date": "1990-05-15",
    "gender": "male",
    "home_address": "123 Test Street",
    "staff_code": "EMP001",
    "office_location_id": 1,
    "division_id": 1,
    "job_title_id": 1,
    "hire_date": "2024-01-15",
    "compensation_type": "monthly",
    "base_salary": 5000,
    "employment_status": "active"
}
```

#### Get Staff Member

```
GET /api/v1/staff-members/{id}
```

#### Update Staff Member

```
PUT /api/v1/staff-members/{id}
```

#### Delete Staff Member

```
DELETE /api/v1/staff-members/{id}
```

## Leave Management

### Leave Types

#### List All Leave Types

```
GET /api/v1/leave-types
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Annual Leave",
            "days_allowed": 20,
            "description": "Paid annual vacation leave",
            "is_paid": true,
            "is_active": true
        }
    ]
}
```

### Leaves

#### List All Leaves

```
GET /api/v1/leaves
```

Query parameters:
- `staff_member_id` - Filter by staff member
- `leave_type_id` - Filter by leave type
- `status` - Filter by status (pending, approved, rejected)

#### Apply for Leave

```
POST /api/v1/leaves
```

Request body:
```json
{
    "staff_member_id": 1,
    "leave_type_id": 1,
    "start_date": "2025-01-15",
    "end_date": "2025-01-20",
    "leave_reason": "Family vacation"
}
```

#### Approve Leave

```
POST /api/v1/leaves/{id}/approve
```

Request body:
```json
{
    "remark": "Approved as requested"
}
```

#### Reject Leave

```
POST /api/v1/leaves/{id}/reject
```

Request body:
```json
{
    "remark": "Insufficient leave balance"
}
```

#### Get Leave Balance

```
GET /api/v1/leaves/balance/{staff_member_id}
```

Response:
```json
{
    "staff_member_id": 1,
    "balances": [
        {
            "leave_type": "Annual Leave",
            "days_allowed": 20,
            "days_taken": 5,
            "days_pending": 2,
            "days_remaining": 13
        }
    ]
}
```

## Attendance Management

### Attendances

#### List Attendances

```
GET /api/v1/attendances
```

Query parameters:
- `staff_member_id` - Filter by staff member
- `date` - Filter by specific date
- `from_date` - Filter from date
- `to_date` - Filter to date

#### Mark Attendance

```
POST /api/v1/attendances
```

Request body:
```json
{
    "staff_member_id": 1,
    "date": "2025-01-15",
    "status": "present",
    "clock_in": "09:00:00",
    "clock_out": "18:00:00"
}
```

#### Clock In

```
POST /api/v1/attendances/clock-in
```

Request body:
```json
{
    "staff_member_id": 1
}
```

#### Clock Out

```
POST /api/v1/attendances/clock-out
```

Request body:
```json
{
    "staff_member_id": 1
}
```

#### Bulk Attendance

```
POST /api/v1/attendances/bulk
```

Request body:
```json
{
    "date": "2025-01-15",
    "attendances": [
        {"staff_member_id": 1, "status": "present"},
        {"staff_member_id": 2, "status": "absent"},
        {"staff_member_id": 3, "status": "present"}
    ]
}
```

#### Monthly Report

```
GET /api/v1/attendances/monthly-report
```

Query parameters:
- `staff_member_id` - Staff member ID
- `month` - Month (1-12)
- `year` - Year

## Payroll

### Allowance Options

```
GET /api/v1/allowance-options
POST /api/v1/allowance-options
GET /api/v1/allowance-options/{id}
PUT /api/v1/allowance-options/{id}
DELETE /api/v1/allowance-options/{id}
```

### Deduction Options

```
GET /api/v1/deduction-options
POST /api/v1/deduction-options
GET /api/v1/deduction-options/{id}
PUT /api/v1/deduction-options/{id}
DELETE /api/v1/deduction-options/{id}
```

### Tax Brackets

```
GET /api/v1/tax-brackets
POST /api/v1/tax-brackets
GET /api/v1/tax-brackets/{id}
PUT /api/v1/tax-brackets/{id}
DELETE /api/v1/tax-brackets/{id}
```

### Payslips

#### List Payslips

```
GET /api/v1/payslips
```

Query parameters:
- `staff_member_id` - Filter by staff member
- `salary_month` - Filter by month (YYYY-MM format)

#### Generate Payslip

```
POST /api/v1/payslips
```

Request body:
```json
{
    "staff_member_id": 1,
    "salary_month": "2025-01"
}
```

#### Get Payslip

```
GET /api/v1/payslips/{id}
```

## Events & Calendar

### Events

```
GET /api/v1/events
POST /api/v1/events
GET /api/v1/events/{id}
PUT /api/v1/events/{id}
DELETE /api/v1/events/{id}
```

Create event request body:
```json
{
    "title": "Company Meeting",
    "start_date": "2025-01-20",
    "end_date": "2025-01-20",
    "color": "#3788d8",
    "description": "Monthly all-hands meeting",
    "staff_member_ids": [1, 2, 3]
}
```

### Holidays

```
GET /api/v1/holidays
POST /api/v1/holidays
GET /api/v1/holidays/{id}
PUT /api/v1/holidays/{id}
DELETE /api/v1/holidays/{id}
```

## Documents & Policies

### Document Types

```
GET /api/v1/document-types
POST /api/v1/document-types
GET /api/v1/document-types/{id}
PUT /api/v1/document-types/{id}
DELETE /api/v1/document-types/{id}
```

### Company Policies

```
GET /api/v1/company-policies
POST /api/v1/company-policies
GET /api/v1/company-policies/{id}
PUT /api/v1/company-policies/{id}
DELETE /api/v1/company-policies/{id}
```

### Documents

```
GET /api/v1/documents
POST /api/v1/documents
GET /api/v1/documents/{id}
PUT /api/v1/documents/{id}
DELETE /api/v1/documents/{id}
```

## Reports

### Dashboard

```
GET /api/v1/reports/dashboard
```

Response:
```json
{
    "total_employees": 50,
    "attendance_today": {
        "present": 45,
        "absent": 3,
        "on_leave": 2
    },
    "pending_leaves": 5,
    "upcoming_events": []
}
```

### Monthly Attendance Report

```
GET /api/v1/reports/monthly-attendance
```

Query parameters:
- `month` - Month (1-12)
- `year` - Year
- `office_location_id` - Filter by office location

### Leave Report

```
GET /api/v1/reports/leave
```

Query parameters:
- `month` - Month (1-12)
- `year` - Year
- `leave_type_id` - Filter by leave type

### Payroll Report

```
GET /api/v1/reports/payroll
```

Query parameters:
- `month` - Month (YYYY-MM format)
- `office_location_id` - Filter by office location

## Error Handling

The API returns consistent error responses:

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Authentication Error (401)

```json
{
    "message": "Unauthenticated."
}
```

### Authorization Error (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Not Found Error (404)

```json
{
    "message": "Resource not found."
}
```

### Server Error (500)

```json
{
    "message": "Server Error"
}
```

## Pagination

List endpoints return paginated results:

```json
{
    "data": [...],
    "links": {
        "first": "http://localhost:8000/api/v1/staff-members?page=1",
        "last": "http://localhost:8000/api/v1/staff-members?page=5",
        "prev": null,
        "next": "http://localhost:8000/api/v1/staff-members?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

Query parameters for pagination:
- `page` - Page number
- `per_page` - Items per page (default: 15)

## Rate Limiting

The API implements rate limiting to prevent abuse. Default limits are 60 requests per minute for authenticated users. When rate limited, you'll receive a 429 response with a `Retry-After` header.

## CORS

The API supports Cross-Origin Resource Sharing (CORS) for frontend applications. Configure allowed origins in the Laravel CORS configuration if needed.

## Best Practices

1. Always include the `Accept: application/json` header to ensure JSON responses.
2. Store tokens securely and never expose them in client-side code.
3. Implement token refresh logic for long-running sessions.
4. Handle all error responses gracefully in your frontend.
5. Use pagination for large data sets to improve performance.
6. Cache frequently accessed data like office locations and leave types.
