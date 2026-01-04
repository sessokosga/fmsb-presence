<?php

namespace App\Filament\Imports;

use App\Models\Course;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CourseImporter extends Importer
{
    protected static ?string $model = Course::class;

    // app/Filament/Imports/CourseImporter.php

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nom du cours')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('code')
                ->label('Code UE (ex: INF101)')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('credits')
                ->numeric()
                ->rules(['integer', 'min:1']),

            ImportColumn::make('hours')
                ->numeric()
                ->label('Volume Horaire'),

            // 👇 Relation Filière (via Code ex: 'GL')
            ImportColumn::make('filiere')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // 👇 Relation Niveau (via Code ex: 'L1')
            ImportColumn::make('level')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),
            ImportColumn::make('semester')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // 👇 Relation Enseignant (via Nom ou Matricule selon votre préférence)
            // Ici je suppose qu'on met le nom exact dans le CSV
            ImportColumn::make('teacher')
                ->label('Enseignant (Email)')
                ->relationship(resolveUsing: 'email'),
        ];
    }

    public function resolveRecord(): ?Course
    {
        return Course::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your course import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }
        print ($body);
        return $body;
    }
}
