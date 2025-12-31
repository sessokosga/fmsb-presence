<?php

namespace App\Filament\Imports;

use App\Models\Teacher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping(),

            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email']),

            ImportColumn::make('phone')
                ->requiredMapping() // On le rend obligatoire pour la différenciation
                ->rules(['required']),

            ImportColumn::make('specialty')
                ->ignoreBlankState(),
        ];
    }

    public function resolveRecord(): ?Teacher
    {
        // Recherche un enseignant qui possède cet email OU ce numéro de téléphone
        // Cela évite de créer un doublon si l'un des deux change
        return Teacher::where('email', $this->data['email'])
            ->orWhere('phone', $this->data['phone'])
            ->first() ?? new Teacher();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your teacher import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
