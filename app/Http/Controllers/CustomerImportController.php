<?php

namespace App\Http\Controllers;

use App\Imports\CustomersImport;
use App\Exports\CustomerImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class CustomerImportController extends Controller
{
    // Show import form
    public function showForm()
    {
        return view('customers.import');
    }

    // Handle file upload and import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ], [
            'file.mimes' => 'Only Excel files (.xlsx, .xls) are allowed.'
        ]);

        try {
            $import = new CustomersImport(auth()->id());
            Excel::import($import, $request->file('file'));

            $message = "Imported: {$import->getImportedCount()} rows, Skipped: {$import->getSkippedCount()} rows.";
            return redirect()->route('customers.import.form')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return redirect()->back()->withErrors($errors);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    // Download Excel template
    public function downloadTemplate()
    {
        return Excel::download(new CustomerImportTemplateExport, 'customer_template.xlsx');
    }
}
