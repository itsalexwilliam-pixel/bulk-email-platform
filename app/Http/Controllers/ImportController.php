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
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
            'name_column' => ['required', 'string'],
            'email_column' => ['required', 'string'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:groups,id'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        $headers = fgetcsv($handle);
        if (!$headers) {
            return back()->withErrors(['csv_file' => 'CSV file appears to be empty.'])->withInput();
        }

        $nameIndex = array_search($request->name_column, $headers, true);
        $emailIndex = array_search($request->email_column, $headers, true);

        if ($nameIndex === false || $emailIndex === false) {
            return back()->withErrors(['csv_file' => 'Selected columns were not found in CSV headers.'])->withInput();
        }

        $total = 0;
        $imported = 0;
        $skipped = 0;
        $fileEmails = [];

        while (($row = fgetcsv($handle)) !== false) {
            $total++;

            $name = trim((string)($row[$nameIndex] ?? ''));
            $email = trim((string)($row[$emailIndex] ?? ''));

            $validator = Validator::make(
                ['name' => $name, 'email' => $email],
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                ]
            );

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            $emailKey = strtolower($email);
            if (in_array($emailKey, $fileEmails, true)) {
                $skipped++;
                continue;
            }

            if (Contact::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $contact = Contact::create([
                'name' => $name,
                'email' => $email,
            ]);

            $contact->groups()->sync($request->groups ?? []);

            $fileEmails[] = $emailKey;
            $imported++;
        }

        fclose($handle);

        return view('import.result', [
            'total' => $total,
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }
}
