<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->requiredMapping(),
            ImportColumn::make('last_name')
                ->requiredMapping(),
            ImportColumn::make('gender')
                ->requiredMapping(),

            ImportColumn::make('matricule')
                ->requiredMapping()
                ->rules(['required', 'unique:students,matricule']), // Le matricule est unique

            ImportColumn::make('email')
                ->rules(['nullable', 'email']),

            // Relation avec le Département via son CODE
            ImportColumn::make('department')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // Relation avec le Niveau via son CODE
            ImportColumn::make('level')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),
        ];
    }


    public function resolveRecord(): ?Student
    {
        // On identifie l'étudiant par son matricule unique
        return Student::firstOrNew([
            'matricule' => $this->data['matricule'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }
        print($body);
        return $body;
    }
}
