<?php

namespace App\Filament\Resources\AcademicYears;

use App\Filament\Resources\AcademicYears\Pages\CreateAcademicYear;
use App\Filament\Resources\AcademicYears\Pages\EditAcademicYear;
use App\Filament\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Filament\Resources\AcademicYears\Schemas\AcademicYearForm;
use App\Filament\Resources\AcademicYears\Tables\AcademicYearsTable;
use App\Models\AcademicYear;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Configuration de l\'Année')
                    ->schema([
                        // Libellé (ex: 2025-2026) - 2 parts
                        TextInput::make('name')
                            ->label('Libellé')
                            ->placeholder('2025-2026')
                            ->required()
                            ->columnSpan(2),

                        // Date de début - 1 part
                        DatePicker::make('start_date')
                            ->label('Début')
                            ->required()
                            ->columnSpan(1),

                        // Date de fin - 1 part
                        DatePicker::make('end_date')
                            ->label('Fin')
                            ->columnSpan(1),

                        // Toggle Actuel - 1 part
                        Toggle::make('is_current')
                            ->label('Actuelle')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Année Académique')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('start_date')
                    ->label('Date de Début')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Date de Fin')
                    ->date('d M Y')
                    ->placeholder('Non définie'),

                // Badge pour identifier l'année active
                IconColumn::make('is_current')
                    ->label('Statut Actuel')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('is_current')
                    ->label('Année active uniquement'),
            ])
            ->recordAction(EditAction::class)
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                // Action personnalisée pour définir l'année actuelle en un clic
                Action::make('set_active')
                    ->label('Activer')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        \App\Models\AcademicYear::where('is_current', true)->update(['is_current' => false]);
                        $record->update(['is_current' => true]);
                    })
                    ->hidden(fn ($record) => $record->is_current),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicYears::route('/'),
            'create' => CreateAcademicYear::route('/create'),
            'edit' => EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
