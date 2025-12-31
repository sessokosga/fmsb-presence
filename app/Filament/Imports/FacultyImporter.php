<?php

namespace App\Filament\Imports;

use App\Models\Faculty;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FacultyImporter extends Importer
{
    protected static ?string $model = Faculty::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping(),
            ImportColumn::make('code')->requiredMapping()->castStateUsing(fn ($state) => strtoupper(trim($state))),
        ];
    }

    public function resolveRecord(): ?Faculty
    {
        return Faculty::firstOrNew(['code' => $this->data['code']]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your faculty import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
