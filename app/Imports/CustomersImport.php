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
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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
        // Trim all strings
        $row = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        $username = $row['username'] ?? '';

        // 1. Duplicate check
        if (Customer::where('username', $username)->exists()) {
            $this->skippedCount++;
            $this->errors[] = "Username '{$username}' already exists – skipped.";
            return null;
        }

        // 2. Internet Plan Lookup
        $planId = null;
        $rateLimit = null;
        if (!empty($row['internet_plan'])) {
            $searchTerm = $row['internet_plan'];
            $plan = InternetPlan::where('rate_limit', $searchTerm)->first()
                ?? InternetPlan::whereRaw('LOWER(rate_limit) = ?', [strtolower($searchTerm)])->first()
                ?? InternetPlan::where('name', $searchTerm)->first()
                ?? InternetPlan::whereRaw('LOWER(name) = ?', [strtolower($searchTerm)])->first()
                ?? InternetPlan::where('rate_limit', 'LIKE', '%' . $searchTerm . '%')->first();

            if ($plan) {
                $planId = $plan->id;
                $rateLimit = $plan->rate_limit;
            } else {
                $this->skippedCount++;
                $this->errors[] = "Internet plan '{$searchTerm}' not found.";
                return null;
            }
        }

        // 3. Branch Lookup
        $branchId = null;
        if (!empty($row['branch'])) {
            $branch = Branch::where('name', $row['branch'])->first();
            if ($branch) {
                $branchId = $branch->id;
            } else {
                $this->skippedCount++;
                $this->errors[] = "Branch '{$row['branch']}' not found.";
                return null;
            }
        }

        // 4. Parse Dates (Now validation won't block this!)
        $expireDate = $this->parseDate($row['expire_date'] ?? null);
        $registeredAt = $this->parseDate($row['registered_at'] ?? null) ?? now();

        // 5. Create and Save Customer
        $customer = new Customer([
            'name'             => $row['name'] ?? '',
            'username'         => $username,
            'password'         => $row['password'] ?? '',
            'email'            => $row['email'] ?? null,
            'contact_number'   => $row['contact_number'] ?? null,
            'address'          => $row['address'] ?? null,
            'mac_address'      => $row['mac_address'] ?? null,
            'expire_date'      => $expireDate,
            'registered_at'    => $registeredAt,
            'status'           => $row['status'] ?? 'active',
            'remarks'          => $row['remarks'] ?? null,
            'internet_plan_id' => $planId,
            'branch_id'        => $branchId,
            'user_id'          => $this->userId,
        ]);

        $customer->save();

        // 6. RADIUS Records
        $this->createRadiusRecords($customer, $row['password'], $rateLimit);

        $this->importedCount++;
        return $customer;
    }

    protected function parseDate($value)
    {
        if (empty($value)) return null;

        // Excel serial number (e.g., 46200 for 2026-06-22)
        if (is_numeric($value) && $value > 0 && $value < 50000) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        if (is_string($value)) {
            // DD/MM/YYYY
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
                return Carbon::createFromFormat('d/m/Y', $value);
            }
            // YYYY-MM-DD
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                Log::warning("Could not parse date: '{$value}'");
                return null;
            }
        }

        return null;
    }

    protected function createRadiusRecords($customer, $plainPassword, $rateLimit)
    {
        try {
            RadCheck::updateOrCreate(
                ['username' => $customer->username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $plainPassword]
            );

            RadReply::updateOrCreate(
                ['username' => $customer->username, 'attribute' => 'Framed-Pool'],
                ['op' => ':=', 'value' => 'PPPoE-Pool']
            );

            if ($rateLimit) {
                RadReply::updateOrCreate(
                    ['username' => $customer->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $rateLimit]
                );
            }
        } catch (\Exception $e) {
            Log::error("RADIUS failed for {$customer->username}: " . $e->getMessage());
            $this->errors[] = "RADIUS failed for {$customer->username}.";
        }
    }

    /**
     * FIXED: Removed 'date' rule so Excel serials and DD/MM/YYYY pass through.
     */
    public function rules(): array
    {
        return [
            '*.name'     => 'required|string|max:255',
            '*.username' => 'required|string|max:255',
            '*.password' => 'required|string|min:4',
            '*.email'    => 'nullable|email',
            '*.status'   => 'nullable|in:active,grace,expired,suspended,discontinued',
            '*.expire_date'    => 'nullable', // Changed from 'date'
            '*.registered_at'  => 'nullable', // Changed from 'date'
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
