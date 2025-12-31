<?php

namespace App\Filament\Imports;

use App\Models\Semester;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SemesterImporter extends Importer
{
    protected static ?string $model = Semester::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => trim($state)),

            ImportColumn::make('code')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => strtoupper(trim($state))),

            ImportColumn::make('is_active')
                ->label('Actif')
                ->boolean() // Indique à Filament que c'est un booléen
                ->rules(['required', 'boolean'])
                ->castStateUsing(function ($state) {
                    // Gère les variantes de saisie dans le CSV
                    return in_array(strtolower(trim($state)), ['1', 'true', 'oui', 'yes', 'on']);
                }),
        ];
    }

    public function resolveRecord(): ?Semester
    {
        return Semester::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your semester import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
