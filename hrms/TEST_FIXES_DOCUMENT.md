# HRMS Backend Test Fixes Documentation

## Overview
This document tracks the fixes made to ensure all automated tests pass for the HRMS backend application.

## Test Failures Analysis

### 1. LeaveBalanceTest (4 failures)
**Root Cause:** The test file uses `'days' => 20` when creating LeaveType, but the migration column is `days_allowed`.

**Fix:** Update LeaveBalanceTest to use `days_allowed` instead of `days`.

### 2. AttendanceFactory (5 failures)
**Root Cause:** 
- Factory generates `status` values including 'late' which is not in the migration enum
- Migration enum: `['present', 'absent', 'half_day']`
- Date format mismatch in assertDatabaseHas assertions

**Fix:** 
- Update AttendanceFactory to only use valid enum values
- Update AttendanceTest to handle date format properly

### 3. AuthTest (2 failures)
**Root Cause:**
- `test_user_can_register`: The `staff_member` role doesn't exist because RefreshDatabase clears seeded data
- `test_user_cannot_login_with_invalid_credentials`: Controller returns 422 (ValidationException) but test expects 401

**Fix:**
- Add role seeding in setUp method
- Update test to expect 422 status code (matching actual API behavior)

### 4. LeaveFactory (3 failures)
**Root Cause:** The factory generates `$endDate` with upper bound '+7 days' relative to now, not relative to `$startDate`. When `$startDate` lands beyond now+7 days, faker throws "Start date must be anterior to end date" error.

**Fix:** Generate `$endDate` relative to `$startDate` to ensure it's always after start date.

## Fixes Applied

### Fix 1: LeaveBalanceTest
- Changed `'days' => 20` to `'days_allowed' => 20`
- Changed `$this->leaveType->days` to `$this->leaveType->days_allowed`

### Fix 2: AttendanceFactory
- Changed status enum to only include valid values: `['present', 'absent', 'half_day']`
- Removed 'late' from randomElement array

### Fix 3: AuthTest
- Added setUp method to seed required roles
- Changed expected status code from 401 to 422 for invalid credentials test

### Fix 4: LeaveFactory
- Fixed date generation to compute `$endDate` relative to `$startDate`

### Fix 5: AttendanceTest
- Fixed date format assertions to use Carbon for proper comparison

## Verification
After applying all fixes, run `php artisan test` to verify all 39 tests pass.
