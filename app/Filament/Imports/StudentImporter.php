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

    // app/Filament/Imports/StudentImporter.php

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('matricule')
                ->requiredMapping()
                ->rules(['required', 'unique:students,matricule']),

            ImportColumn::make('first_name')
                ->label('Prénom')
                ->requiredMapping(),

            ImportColumn::make('last_name')
                ->label('Nom')
                ->requiredMapping(),

            ImportColumn::make('gender')
                ->label('Genre (M/F)')
                ->rules(['in:M,F']),

            ImportColumn::make('email')
                ->rules(['email']),

            ImportColumn::make('phone'),

            // 👇 Relation Département (Optionnel si la filière suffit, mais bon pour la cohérence)
            ImportColumn::make('department')
                ->relationship(resolveUsing: 'code'),

            // 👇 TRES IMPORTANT : La Filière (via Code ex: 'GL')
            ImportColumn::make('filiere')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // 👇 TRES IMPORTANT : Le Niveau (via Code ex: 'L1')
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
