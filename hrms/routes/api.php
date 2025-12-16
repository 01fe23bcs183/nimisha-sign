<?php

use App\Http\Controllers\Api\V1\AccessController;
use App\Http\Controllers\Api\V1\AllowanceController;
use App\Http\Controllers\Api\V1\AllowanceOptionController;
use App\Http\Controllers\Api\V1\AllowanceTaxController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\CommissionController;
use App\Http\Controllers\Api\V1\CompanyContributionController;
use App\Http\Controllers\Api\V1\CompanyPolicyController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\DeductionOptionController;
use App\Http\Controllers\Api\V1\DisciplineNoteController;
use App\Http\Controllers\Api\V1\DivisionController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentTypeController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\ExitCategoryController;
use App\Http\Controllers\Api\V1\ExperienceCertificateController;
use App\Http\Controllers\Api\V1\FileCategoryController;
use App\Http\Controllers\Api\V1\HolidayController;
use App\Http\Controllers\Api\V1\IpRestrictController;
use App\Http\Controllers\Api\V1\JobTitleController;
use App\Http\Controllers\Api\V1\JoiningLetterController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\LoanOptionController;
use App\Http\Controllers\Api\V1\LocationTransferController;
use App\Http\Controllers\Api\V1\NocCertificateController;
use App\Http\Controllers\Api\V1\OffboardingController;
use App\Http\Controllers\Api\V1\OfficeLocationController;
use App\Http\Controllers\Api\V1\OtherPaymentController;
use App\Http\Controllers\Api\V1\OvertimeController;
use App\Http\Controllers\Api\V1\PaySlipController;
use App\Http\Controllers\Api\V1\PayslipTypeController;
use App\Http\Controllers\Api\V1\RecognitionCategoryController;
use App\Http\Controllers\Api\V1\RecognitionRecordController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleUpgradeController;
use App\Http\Controllers\Api\V1\SaturationDeductionController;
use App\Http\Controllers\Api\V1\StaffFileController;
use App\Http\Controllers\Api\V1\StaffMemberController;
use App\Http\Controllers\Api\V1\TaxBracketController;
use App\Http\Controllers\Api\V1\TaxRebateController;
use App\Http\Controllers\Api\V1\TaxThresholdController;
use App\Http\Controllers\Api\V1\TravelController;
use App\Http\Controllers\Api\V1\VoluntaryExitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AccessController::class, 'signUp']);
        Route::post('login', [AccessController::class, 'signIn']);
        Route::post('forgot-password', [AccessController::class, 'forgotPassword']);
        Route::post('reset-password', [AccessController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AccessController::class, 'signOut']);
            Route::get('profile', [AccessController::class, 'profile']);
            Route::put('profile', [AccessController::class, 'updateProfile']);
            Route::post('change-password', [AccessController::class, 'changePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('office-locations', OfficeLocationController::class);

        Route::apiResource('divisions', DivisionController::class);
        Route::post('divisions/fetch-by-location', [DivisionController::class, 'fetchByLocation']);

        Route::apiResource('job-titles', JobTitleController::class);
        Route::post('job-titles/fetch-by-division', [JobTitleController::class, 'fetchByDivision']);

        Route::apiResource('file-categories', FileCategoryController::class);

        Route::apiResource('staff-members', StaffMemberController::class);
        Route::get('staff-members/{staffMember}/salary', [StaffMemberController::class, 'salary']);
        Route::put('staff-members/{staffMember}/salary', [StaffMemberController::class, 'updateSalary']);

        Route::prefix('staff-members/{staffMember}')->group(function () {
            Route::apiResource('files', StaffFileController::class)->parameters(['files' => 'staffFile']);
            Route::get('files/{staffFile}/download', [StaffFileController::class, 'download']);
        });

        Route::apiResource('recognition-categories', RecognitionCategoryController::class);
        Route::apiResource('recognition-records', RecognitionRecordController::class);

        Route::apiResource('role-upgrades', RoleUpgradeController::class);
        Route::apiResource('location-transfers', LocationTransferController::class);

        Route::apiResource('discipline-notes', DisciplineNoteController::class);

        Route::apiResource('exit-categories', ExitCategoryController::class);
        Route::apiResource('offboardings', OffboardingController::class);

        Route::apiResource('voluntary-exits', VoluntaryExitController::class);
        Route::post('voluntary-exits/{voluntaryExit}/approve', [VoluntaryExitController::class, 'approve']);
        Route::post('voluntary-exits/{voluntaryExit}/decline', [VoluntaryExitController::class, 'decline']);

        Route::apiResource('travels', TravelController::class);
        Route::post('travels/{travel}/approve', [TravelController::class, 'approve']);
        Route::post('travels/{travel}/reject', [TravelController::class, 'reject']);

        Route::apiResource('complaints', ComplaintController::class);
        Route::post('complaints/{complaint}/resolve', [ComplaintController::class, 'resolve']);

        Route::apiResource('announcements', AnnouncementController::class);

        Route::apiResource('holidays', HolidayController::class);
        Route::post('holidays/import', [HolidayController::class, 'import']);

        Route::apiResource('leave-types', LeaveTypeController::class);

        Route::apiResource('leaves', LeaveController::class);
        Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve']);
        Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject']);
        Route::get('staff-members/{staffMember}/leave-balance', [LeaveController::class, 'balance']);

        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);
        Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore']);
        Route::get('attendance/monthly-report', [AttendanceController::class, 'monthlyReport']);

        Route::apiResource('payslip-types', PayslipTypeController::class);
        Route::apiResource('allowance-options', AllowanceOptionController::class);
        Route::apiResource('loan-options', LoanOptionController::class);
        Route::apiResource('deduction-options', DeductionOptionController::class);

        Route::apiResource('allowances', AllowanceController::class);
        Route::get('staff-members/{staffMember}/allowances', [AllowanceController::class, 'byStaffMember']);

        Route::apiResource('commissions', CommissionController::class);
        Route::get('staff-members/{staffMember}/commissions', [CommissionController::class, 'byStaffMember']);

        Route::apiResource('loans', LoanController::class);
        Route::get('staff-members/{staffMember}/loans', [LoanController::class, 'byStaffMember']);
        Route::post('loans/{loan}/payment', [LoanController::class, 'recordPayment']);

        Route::apiResource('saturation-deductions', SaturationDeductionController::class);
        Route::get('staff-members/{staffMember}/saturation-deductions', [SaturationDeductionController::class, 'byStaffMember']);

        Route::apiResource('other-payments', OtherPaymentController::class);
        Route::get('staff-members/{staffMember}/other-payments', [OtherPaymentController::class, 'byStaffMember']);

        Route::apiResource('overtimes', OvertimeController::class);
        Route::get('staff-members/{staffMember}/overtimes', [OvertimeController::class, 'byStaffMember']);

        Route::apiResource('company-contributions', CompanyContributionController::class);
        Route::get('staff-members/{staffMember}/company-contributions', [CompanyContributionController::class, 'byStaffMember']);

        Route::apiResource('pay-slips', PaySlipController::class);
        Route::post('pay-slips/bulk-generate', [PaySlipController::class, 'bulkGenerate']);
        Route::post('pay-slips/{paySlip}/mark-paid', [PaySlipController::class, 'markAsPaid']);
        Route::get('staff-members/{staffMember}/pay-slips', [PaySlipController::class, 'byStaffMember']);

        Route::apiResource('tax-brackets', TaxBracketController::class);
        Route::post('tax-brackets/calculate', [TaxBracketController::class, 'calculate']);

        Route::apiResource('tax-rebates', TaxRebateController::class);
        Route::apiResource('tax-thresholds', TaxThresholdController::class);
        Route::apiResource('allowance-taxes', AllowanceTaxController::class);

        Route::apiResource('events', EventController::class);
        Route::get('events/calendar', [EventController::class, 'calendar']);

        Route::apiResource('document-types', DocumentTypeController::class);

        Route::apiResource('company-policies', CompanyPolicyController::class);
        Route::post('company-policies/{companyPolicy}/acknowledge', [CompanyPolicyController::class, 'acknowledge']);
        Route::get('company-policies/{companyPolicy}/download', [CompanyPolicyController::class, 'download']);

        Route::apiResource('documents', DocumentController::class);
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);

        Route::apiResource('joining-letters', JoiningLetterController::class);
        Route::get('joining-letters/{joiningLetter}/generate/{staffMember}', [JoiningLetterController::class, 'generate']);
        Route::get('joining-letters/{joiningLetter}/pdf/{staffMember}', [JoiningLetterController::class, 'pdf']);

        Route::apiResource('experience-certificates', ExperienceCertificateController::class);
        Route::get('experience-certificates/{experienceCertificate}/generate/{staffMember}', [ExperienceCertificateController::class, 'generate']);
        Route::get('experience-certificates/{experienceCertificate}/pdf/{staffMember}', [ExperienceCertificateController::class, 'pdf']);

        Route::apiResource('noc-certificates', NocCertificateController::class);
        Route::get('noc-certificates/{nocCertificate}/generate/{staffMember}', [NocCertificateController::class, 'generate']);
        Route::get('noc-certificates/{nocCertificate}/pdf/{staffMember}', [NocCertificateController::class, 'pdf']);

        Route::apiResource('ip-restricts', IpRestrictController::class);
        Route::get('ip-restricts/check', [IpRestrictController::class, 'checkIp']);

        Route::prefix('settings')->group(function () {
            Route::get('/', [CompanySettingController::class, 'index']);
            Route::post('/', [CompanySettingController::class, 'store']);
            Route::get('hrm', [CompanySettingController::class, 'hrmSettings']);
            Route::put('hrm', [CompanySettingController::class, 'updateHrmSettings']);
            Route::post('bulk', [CompanySettingController::class, 'bulkUpdate']);
            Route::get('{key}', [CompanySettingController::class, 'show']);
            Route::put('{key}', [CompanySettingController::class, 'update']);
            Route::delete('{key}', [CompanySettingController::class, 'destroy']);
            Route::get('{key}/value', [CompanySettingController::class, 'getValue']);
        });

        Route::prefix('reports')->group(function () {
            Route::get('dashboard', [ReportController::class, 'dashboard']);
            Route::get('monthly-attendance', [ReportController::class, 'monthlyAttendance']);
            Route::get('leave', [ReportController::class, 'leaveReport']);
            Route::get('payroll', [ReportController::class, 'payrollReport']);
        });
    });
});
