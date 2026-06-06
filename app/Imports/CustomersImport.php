<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\InternetPlan;
use App\Models\Branch;
use App\Models\RadCheck;
use App\Models\RadReply;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected $userId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        // Skip duplicate username
        if (Customer::where('username', $row['username'])->exists()) {
            $this->skippedCount++;
            $this->errors[] = "Username '{$row['username']}' already exists – skipped.";
            return null;
        }

        // Lookup internet plan ID - ROBUST VERSION
        $planId = null;
        $rateLimit = null;
        if (!empty($row['internet_plan'])) {
            $searchTerm = trim($row['internet_plan']);
            Log::info("Looking for internet plan: '{$searchTerm}'");

            $plan = InternetPlan::where('rate_limit', $searchTerm)->first()
                ?? InternetPlan::whereRaw('LOWER(rate_limit) = ?', [strtolower($searchTerm)])->first()
                ?? InternetPlan::where('name', $searchTerm)->first()
                ?? InternetPlan::whereRaw('LOWER(name) = ?', [strtolower($searchTerm)])->first()
                ?? InternetPlan::where('rate_limit', 'LIKE', '%' . $searchTerm . '%')->first();

            if ($plan) {
                $planId = $plan->id;
                $rateLimit = $plan->rate_limit; // store for RADIUS reply
                Log::info("Found plan: ID {$planId}, Name: {$plan->name}, Rate: {$plan->rate_limit}");
            } else {
                $this->skippedCount++;
                $availablePlans = InternetPlan::select('id', 'name', 'rate_limit')->get()->toArray();
                $this->errors[] = "Internet plan '{$searchTerm}' not found. Available plans: " . json_encode($availablePlans);
                Log::warning("Plan not found: '{$searchTerm}'");
                return null;
            }
        }

        // Lookup branch ID by name
        $branchId = null;
        if (!empty($row['branch'])) {
            $branch = Branch::where('name', $row['branch'])->first();
            if ($branch) {
                $branchId = $branch->id;
            } else {
                $this->skippedCount++;
                $this->errors[] = "Branch '{$row['branch']}' not found – row skipped.";
                return null;
            }
        }

        // Create customer (without saving yet)
        $customer = new Customer([
            'name'             => $row['name'],
            'username'         => $row['username'],
            'password'         => $row['password'], // will be hashed by mutator
            'email'            => $row['email'] ?? null,
            'contact_number'   => $row['contact_number'] ?? null,
            'address'          => $row['address'] ?? null,
            'mac_address'      => $row['mac_address'] ?? null,
            'expire_date'      => isset($row['expire_date']) ? Carbon::parse($row['expire_date']) : null,
            'registered_at'    => isset($row['registered_at']) ? Carbon::parse($row['registered_at']) : now(),
            'status'           => $row['status'] ?? 'active',
            'remarks'          => $row['remarks'] ?? null,
            'internet_plan_id' => $planId,
            'branch_id'        => $branchId,
            'user_id'          => $this->userId,
        ]);

        // Save customer to database
        $customer->save();

        // After saving, create RADIUS records
        $this->createRadiusRecords($customer, $row['password'], $rateLimit);

        $this->importedCount++;
        return $customer;
    }

    /**
     * Create RADIUS radcheck and radreply entries for the customer.
     *
     * @param Customer $customer
     * @param string $plainPassword
     * @param string|null $rateLimit
     */
    protected function createRadiusRecords($customer, $plainPassword, $rateLimit)
    {
        try {
            // 1. Add radcheck: Cleartext-Password (or Encrypted-Password depending on your RADIUS setup)
            RadCheck::updateOrCreate(
                ['username' => $customer->username, 'attribute' => 'Cleartext-Password'],
                [
                    'op'    => ':=',
                    'value' => $plainPassword
                ]
            );

            RadReply::updateOrCreate(
                    [
                        'username'  => $customer->username,
                        'attribute' => 'Framed-Pool',
                    ],
                    [
                        'op'    => ':=',
                        'value' => 'PPPoE-Pool',
                    ]
                );

            // 2. Add radreply: Mikrotik-Rate-Limit (or other vendor-specific attribute)
            if ($rateLimit) {
                RadReply::updateOrCreate(
                    ['username' => $customer->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    [
                        'op'    => ':=',
                        'value' => $rateLimit
                    ]
                );
            }


            Log::info("RADIUS records created for username: {$customer->username}");
        } catch (\Exception $e) {
            Log::error("Failed to create RADIUS records for {$customer->username}: " . $e->getMessage());
            // Optionally add to errors array
            $this->errors[] = "RADIUS setup failed for {$customer->username}: " . $e->getMessage();
        }
    }

    public function rules(): array
    {
        return [
            '*.name'     => 'required|string|max:255',
            '*.username' => 'required|string|max:255',
            '*.password' => 'required|string|min:4',
            '*.email'    => 'nullable|email',
            '*.status'   => 'nullable|in:active,grace,expired,suspended,discontinued',
            '*.expire_date' => 'nullable|date',
            '*.registered_at' => 'nullable|date',
        ];
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
