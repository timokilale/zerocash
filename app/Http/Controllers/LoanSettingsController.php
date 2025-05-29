<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanSetting;

class LoanSettingsController extends Controller
{
    /**
     * Display loan settings.
     */
    public function index()
    {
        $settings = LoanSetting::orderBy('category')->orderBy('key')->get()->groupBy('category');

        return view('banking.loan-settings.index', compact('settings'));
    }

    /**
     * Update loan settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            $updatedSettings = [];

            foreach ($request->settings as $key => $value) {
                $setting = LoanSetting::where('key', $key)->first();

                if ($setting) {
                    $originalValue = $setting->value;

                    // Handle array values (from hidden input + checkbox)
                    if (is_array($value)) {
                        $value = end($value); // Take the last value (checkbox value if checked)
                    }

                    // Cast value based on type
                    $castedValue = $this->castValueForStorage($value, $setting->type);
                    $setting->update(['value' => $castedValue]);

                    // Track changes for success message
                    if ($originalValue !== $castedValue) {
                        $displayValue = $setting->type === 'boolean'
                            ? ($castedValue ? 'Enabled' : 'Disabled')
                            : $castedValue . ($setting->key === 'min_balance_percentage' || $setting->key === 'max_debt_to_income_percentage' || $setting->key === 'max_loan_to_balance_multiplier' ? '%' : '');

                        $updatedSettings[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $displayValue;
                    }
                }
            }

            $successMessage = 'Loan settings updated successfully!';
            if (!empty($updatedSettings)) {
                $successMessage .= ' Changes: ' . implode(', ', $updatedSettings);
            }

            return redirect()->route('loan-settings.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Cast value for storage based on type.
     */
    private function castValueForStorage($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return is_array($value) ? json_encode($value) : $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Get loan eligibility preview for a customer.
     */
    public function eligibilityPreview(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1000',
            'term_months' => 'required|integer|min:1|max:60',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $eligibilityService = new \App\Services\LoanEligibilityService();

        $eligibility = $eligibilityService->checkEligibility(
            $user,
            $request->amount,
            $request->term_months
        );

        return response()->json($eligibility);
    }
}
