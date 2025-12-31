<?php

namespace App\Filament\Imports;

use App\Models\AcademicYear;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class AcademicYearImporter extends Importer
{
    protected static ?string $model = AcademicYear::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->label('Année Académique')
                // On nettoie la donnée (ex: " 2024-2025 " -> "2024-2025")
                ->castStateUsing(fn ($state) => trim($state))
                ->rules(['required', 'max:255']),

            // Si vous avez des dates de début/fin dans votre table
            ImportColumn::make('start_date')
                ->ignoreBlankState(),

            ImportColumn::make('end_date')
                ->ignoreBlankState(),
        ];
    }

    public function resolveRecord(): AcademicYear
    {
        return AcademicYear::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your academic year import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
