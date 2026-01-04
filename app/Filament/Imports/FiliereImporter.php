<?php

namespace App\Filament\Imports;

use App\Models\Filiere;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FiliereImporter extends Importer
{
    protected static ?string $model = Filiere::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nom de la filière')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('code')
                ->label('Code (ex: GL)')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            // 👇 MAGIE : On lie au département via son CODE (ex: 'INFO')
            ImportColumn::make('department') // Cherche la relation 'department'
            ->label('Code Département Parnet')
                ->relationship(resolveUsing: 'code') // Utilise la colonne 'code' de la table departments
                ->requiredMapping(),
        ];
    }

    public function resolveRecord(): Filiere
    {
        return Filiere::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your filiere import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
