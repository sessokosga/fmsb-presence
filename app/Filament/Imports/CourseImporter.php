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

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping(),

            ImportColumn::make('code')
                ->requiredMapping(),
            ImportColumn::make('credits')
                ->requiredMapping(),
            ImportColumn::make('hours')
                ->requiredMapping(),

            // Relation Département (via Code)
            ImportColumn::make('department')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // Relation Niveau (via Code)
            ImportColumn::make('level')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),

            // Relation Semestre (via Code)
            ImportColumn::make('semester')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping(),
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
