<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Organization\Services\TenantMigrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MigrationImportController extends Controller
{
    protected TenantMigrationService $migrationService;

    public function __construct(TenantMigrationService $migrationService)
    {
        $this->middleware('auth');
        $this->migrationService = $migrationService;
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property context.');

        return view('admin.migration.index', compact('property'));
    }

    public function preview(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property context.');

        $request->validate([
            'file_type' => 'required|in:guests,reservations,rooms',
            'csv_file'  => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);

        $rows = [];
        while (($data = fgetcsv($file)) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            }
        }
        fclose($file);

        $preview = $this->migrationService->dryRunImport($property, $rows, $request->file_type);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'data'    => $rows,
        ]);
    }

    public function execute(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property context.');

        $request->validate([
            'file_type' => 'required|in:guests,reservations,rooms',
            'rows'      => 'required|array',
        ]);

        $result = $this->migrationService->executeImport(
            $property,
            $request->rows,
            $request->file_type,
            auth()->user()
        );

        return redirect()->back()->with('success', "Imported {$result['imported_count']} {$request->file_type} records successfully!");
    }
}
