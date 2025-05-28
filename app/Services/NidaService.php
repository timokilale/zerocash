<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NidaService
{
    private $apiUrl;
    private $apiKey;
    private $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.nida.api_url', 'https://api.nida.go.tz/v1');
        $this->apiKey = config('services.nida.api_key', '');
        $this->timeout = config('services.nida.timeout', 30);
    }

    /**
     * Fetch customer details from NIDA by NIDA number.
     */
    public function fetchCustomerDetails(string $nidaNumber): array
    {
        try {
            // For demo purposes, we'll simulate NIDA API response
            // In production, replace this with actual NIDA API call
            return $this->simulateNidaResponse($nidaNumber);
            
            // Actual API call would look like this:
            /*
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get($this->apiUrl . '/citizen/' . $nidaNumber);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch data from NIDA',
                'details' => $response->body(),
            ];
            */
        } catch (\Exception $e) {
            Log::error('NIDA API Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'NIDA service unavailable',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate NIDA number format.
     */
    public function validateNidaFormat(string $nidaNumber): bool
    {
        // NIDA format: YYYYMMDD-XXXXX-XXXXX-XX
        $pattern = '/^\d{8}-\d{5}-\d{5}-\d{2}$/';
        return preg_match($pattern, $nidaNumber) === 1;
    }

    /**
     * Extract date of birth from NIDA number.
     */
    public function extractDateOfBirth(string $nidaNumber): ?string
    {
        if (!$this->validateNidaFormat($nidaNumber)) {
            return null;
        }

        $datePart = substr($nidaNumber, 0, 8);
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
            return $date->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Simulate NIDA API response for demo purposes.
     */
    private function simulateNidaResponse(string $nidaNumber): array
    {
        if (!$this->validateNidaFormat($nidaNumber)) {
            return [
                'success' => false,
                'error' => 'Invalid NIDA number format',
            ];
        }

        // Extract info from NIDA number
        $dateOfBirth = $this->extractDateOfBirth($nidaNumber);
        
        // Generate sample data based on NIDA number
        $sampleNames = [
            'John Doe', 'Jane Smith', 'Michael Johnson', 'Sarah Wilson',
            'David Brown', 'Lisa Davis', 'Robert Miller', 'Emily Garcia',
            'James Rodriguez', 'Maria Martinez', 'William Anderson', 'Jennifer Taylor'
        ];
        
        $sampleAddresses = [
            'Dar es Salaam, Kinondoni',
            'Arusha, Arusha City',
            'Mwanza, Nyamagana',
            'Dodoma, Dodoma Urban',
            'Mbeya, Mbeya City',
            'Morogoro, Morogoro Urban'
        ];

        // Use NIDA number to generate consistent sample data
        $nameIndex = hexdec(substr(md5($nidaNumber), 0, 2)) % count($sampleNames);
        $addressIndex = hexdec(substr(md5($nidaNumber), 2, 2)) % count($sampleAddresses);
        
        $fullName = $sampleNames[$nameIndex];
        $nameParts = explode(' ', $fullName);

        return [
            'success' => true,
            'data' => [
                'nida_number' => $nidaNumber,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'full_name' => $fullName,
                'date_of_birth' => $dateOfBirth,
                'gender' => (hexdec(substr(md5($nidaNumber), 4, 1)) % 2) ? 'Male' : 'Female',
                'address' => $sampleAddresses[$addressIndex],
                'nationality' => 'Tanzanian',
                'marital_status' => ['Single', 'Married', 'Divorced', 'Widowed'][hexdec(substr(md5($nidaNumber), 5, 1)) % 4],
                'verified_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Create customer from NIDA data.
     */
    public function createCustomerFromNida(string $nidaNumber, array $additionalData = []): array
    {
        $nidaResponse = $this->fetchCustomerDetails($nidaNumber);
        
        if (!$nidaResponse['success']) {
            return $nidaResponse;
        }

        $nidaData = $nidaResponse['data'];
        
        try {
            // Generate username and password
            $username = \App\Models\User::generateUsername($nidaData['first_name'], $nidaData['last_name']);
            $password = \App\Models\User::generatePassword();

            // Create user
            $user = \App\Models\User::create([
                'username' => $username,
                'email' => $additionalData['email'] ?? $username . '@zerocash.com',
                'password' => \Hash::make($password),
                'first_name' => $nidaData['first_name'],
                'last_name' => $nidaData['last_name'],
                'phone' => $additionalData['phone'] ?? '+255700000000',
                'address' => $nidaData['address'],
                'nida' => $nidaNumber,
                'date_of_birth' => $nidaData['date_of_birth'],
                'role' => 'customer',
                'status' => 'active',
                'password_auto_generated' => true,
                'nida_verified' => true,
                'nida_data' => $nidaData,
            ]);

            // Create default savings account
            $defaultAccountType = \App\Models\AccountType::where('code', 'SAV')->first();
            $defaultBranch = \App\Models\Branch::first();

            if ($defaultAccountType && $defaultBranch) {
                $account = \App\Models\Account::create([
                    'user_id' => $user->id,
                    'account_type_id' => $defaultAccountType->id,
                    'branch_id' => $defaultBranch->id,
                    'balance' => 0,
                    'status' => 'active',
                ]);

                // Create welcome notification
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Welcome to ZeroCash Banking',
                    'message' => "Welcome! Your account has been created. Your account number is: {$account->account_number}. Your temporary password is: {$password}",
                    'type' => 'account',
                    'data' => [
                        'account_number' => $account->account_number,
                        'temporary_password' => $password,
                    ],
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'user' => $user,
                        'account' => $account,
                        'temporary_password' => $password,
                        'nida_data' => $nidaData,
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create default account',
            ];

        } catch (\Exception $e) {
            Log::error('Error creating customer from NIDA: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to create customer',
                'details' => $e->getMessage(),
            ];
        }
    }
}
