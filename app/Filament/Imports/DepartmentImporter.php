<?php

namespace App\Filament\Imports;

use App\Models\Department;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DepartmentImporter extends Importer
{
    protected static ?string $model = Department::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping(),

            ImportColumn::make('code')
                ->requiredMapping(),

            // On lie la faculté via son CODE
            ImportColumn::make('faculty')
                ->relationship(resolveUsing: 'code') // 👈 On cherche par 'code' au lieu de 'name'
                ->requiredMapping()
                ->label('Code Faculté'),
        ];
    }

    public function resolveRecord(): ?Department
    {
        // On identifie le département par son propre code unique
        return Department::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your department import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
