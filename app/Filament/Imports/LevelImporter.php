<?php

namespace App\Filament\Imports;

use App\Models\Level;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LevelImporter extends Importer
{
    protected static ?string $model = Level::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => trim($state)),
            ImportColumn::make('code')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => strtoupper(trim($state))),
        ];
    }

    public function resolveRecord(): Level
    {
        return Level::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your level import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
