<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $settings = CompanySetting::all();

        return response()->json([
            'data' => $settings,
        ]);
    }

    public function show(string $key): JsonResponse
    {
        $setting = CompanySetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'data' => $setting,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:company_settings,key'],
            'value' => ['required'],
            'type' => ['required', 'in:string,integer,float,boolean,array,json'],
            'description' => ['nullable', 'string'],
        ]);

        $setting = CompanySetting::setValue(
            $validated['key'],
            $validated['value'],
            $validated['type'],
            $validated['description'] ?? null
        );

        return response()->json([
            'message' => 'Setting created successfully',
            'data' => $setting,
        ], 201);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required'],
            'type' => ['sometimes', 'in:string,integer,float,boolean,array,json'],
            'description' => ['nullable', 'string'],
        ]);

        $setting = CompanySetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting = CompanySetting::setValue(
            $key,
            $validated['value'],
            $validated['type'] ?? $setting->type,
            $validated['description'] ?? $setting->description
        );

        return response()->json([
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    public function destroy(string $key): JsonResponse
    {
        $setting = CompanySetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'message' => 'Setting deleted successfully',
        ]);
    }

    public function getValue(string $key): JsonResponse
    {
        $value = CompanySetting::getValue($key);

        return response()->json([
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['required'],
            'settings.*.type' => ['required', 'in:string,integer,float,boolean,array,json'],
        ]);

        $updated = [];
        foreach ($validated['settings'] as $settingData) {
            $updated[] = CompanySetting::setValue(
                $settingData['key'],
                $settingData['value'],
                $settingData['type']
            );
        }

        return response()->json([
            'message' => count($updated) . ' settings updated successfully',
            'data' => $updated,
        ]);
    }

    public function hrmSettings(Request $request): JsonResponse
    {
        $hrmKeys = [
            'employee_prefix',
            'company_start_time',
            'company_end_time',
            'ip_restrict',
            'work_hours_per_day',
            'overtime_rate',
            'late_deduction_rate',
            'leave_policy_default',
        ];

        $settings = CompanySetting::whereIn('key', $hrmKeys)->get();

        return response()->json([
            'data' => $settings,
        ]);
    }

    public function updateHrmSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_prefix' => ['nullable', 'string', 'max:20'],
            'company_start_time' => ['nullable', 'date_format:H:i:s'],
            'company_end_time' => ['nullable', 'date_format:H:i:s'],
            'ip_restrict' => ['nullable', 'boolean'],
            'work_hours_per_day' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'late_deduction_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updated = [];
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'float' : 'string');
                $updated[] = CompanySetting::setValue($key, $value, $type);
            }
        }

        return response()->json([
            'message' => 'HRM settings updated successfully',
            'data' => $updated,
        ]);
    }
}
