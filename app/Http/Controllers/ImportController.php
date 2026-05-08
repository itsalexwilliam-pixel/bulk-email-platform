<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index()
    {
        $groups = Group::orderBy('name')->get();
        return view('import.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file'             => ['required', 'file'],
            'name_column'          => ['nullable', 'string'],
            'first_name_column'    => ['nullable', 'string'],
            'last_name_column'     => ['nullable', 'string'],
            'email_column'         => ['required', 'string'],
            'business_name_column' => ['nullable', 'string'],
            'website_column'       => ['nullable', 'string'],
            'groups'               => ['nullable', 'array'],
            'groups.*'             => ['exists:groups,id'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'CSV file appears to be empty.'])->withInput();
        }

        // Strip BOM and whitespace from headers
        $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
        $headers = array_map('trim', $headers);

        $nameCol          = trim((string) $request->name_column);
        $firstNameCol     = trim((string) $request->first_name_column);
        $lastNameCol      = trim((string) $request->last_name_column);
        $emailCol         = trim((string) $request->email_column);
        $businessNameCol  = trim((string) $request->business_name_column);
        $websiteCol       = trim((string) $request->website_column);

        // Resolve name strategy: single column OR first+last
        $nameIndex      = $nameCol !== '' ? array_search($nameCol, $headers, true) : false;
        $firstNameIndex = $firstNameCol !== '' ? array_search($firstNameCol, $headers, true) : false;
        $lastNameIndex  = $lastNameCol !== '' ? array_search($lastNameCol, $headers, true) : false;

        $emailIndex        = array_search($emailCol, $headers, true);
        $businessNameIndex = $businessNameCol !== '' ? array_search($businessNameCol, $headers, true) : false;
        $websiteIndex      = $websiteCol !== '' ? array_search($websiteCol, $headers, true) : false;

        // Must have either a name column OR at least first_name column
        $hasName      = $nameIndex !== false;
        $hasFirstName = $firstNameIndex !== false;

        if (!$hasName && !$hasFirstName) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'No name column found in CSV. Headers detected: ' . implode(', ', $headers)])->withInput();
        }

        if ($emailIndex === false) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Email column not found in CSV. Headers detected: ' . implode(', ', $headers)])->withInput();
        }

        $accountId = (int) ($request->user()?->account_id ?? 0);
        $total = 0;
        $imported = 0;
        $skipped = 0;
        $fileEmails = [];
        $failedRows = [];   // [['row' => N, 'email' => '...', 'name' => '...', 'reasons' => [...]]]
        $rowNumber = 1;     // 1-based, header is row 0

        while (($row = fgetcsv($handle)) !== false) {
            // Skip blank rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $rowNumber++;
            $total++;
            $reasons = [];

            // Build name from single column or first+last
            if ($hasName) {
                $name = trim((string)($row[$nameIndex] ?? ''));
            } else {
                $firstName = trim((string)($row[$firstNameIndex] ?? ''));
                $lastName  = $lastNameIndex !== false ? trim((string)($row[$lastNameIndex] ?? '')) : '';
                $name      = trim($firstName . ' ' . $lastName);
            }

            $email        = strtolower(trim((string)($row[$emailIndex] ?? '')));
            $businessName = $businessNameIndex !== false ? trim((string)($row[$businessNameIndex] ?? '')) : null;
            $website      = $websiteIndex !== false ? trim((string)($row[$websiteIndex] ?? '')) : null;

            // Add https:// if website has no scheme
            if ($website && !preg_match('/^https?:\/\//i', $website)) {
                $website = 'https://' . $website;
            }

            $validator = Validator::make(
                ['name' => $name, 'email' => $email, 'website' => $website ?: null],
                [
                    'name'    => ['required', 'string', 'max:255'],
                    'email'   => ['required', 'email', 'max:255'],
                    'website' => ['nullable', 'url', 'max:255'],
                ]
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $msg) {
                    $reasons[] = $msg;
                }
            }

            if ($email !== '' && in_array($email, $fileEmails, true)) {
                $reasons[] = 'Duplicate email in this file';
            }

            if ($email !== '' && empty($reasons) && Contact::where('email', $email)->exists()) {
                $reasons[] = 'Email already exists in contacts';
            }

            if (!empty($reasons)) {
                $skipped++;
                $failedRows[] = [
                    'row'     => $rowNumber,
                    'name'    => $name !== '' ? $name : '—',
                    'email'   => $email !== '' ? $email : '—',
                    'reasons' => $reasons,
                ];
                continue;
            }

            $contact = Contact::create([
                'account_id'    => $accountId,
                'name'          => $name,
                'business_name' => $businessName ?: null,
                'email'         => $email,
                'website'       => $website ?: null,
            ]);

            $contact->groups()->sync($request->groups ?? []);

            $fileEmails[] = $email;
            $imported++;
        }

        fclose($handle);

        return view('import.result', [
            'total'      => $total,
            'imported'   => $imported,
            'skipped'    => $skipped,
            'failedRows' => $failedRows,
        ]);
    }
}
