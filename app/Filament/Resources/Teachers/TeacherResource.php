<?php

namespace App\Filament\Resources\Teachers;

use App\Filament\Imports\TeacherImporter;
use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Tables\TeachersTable;
use App\Models\Teacher;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Actions\ImportAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Identité de l\'Enseignant')
                    ->schema([
                        Components\Select::make('title')
                            ->label('Grade / Titre')
                            ->options([
                                'Pr' => 'Professeur (Pr)',
                                'Dr' => 'Docteur (Dr)',
                                'M.' => 'Monsieur',
                                'Mme' => 'Madame',
                            ])
                            ->default('Dr')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Components\TextInput::make('name')
                            ->label('Nom et Prénom')
                            ->required()
                            ->placeholder('ex: ARABO')
                            ->columnSpan(3),

                        Components\TextInput::make('specialty')
                            ->label('Spécialité')
                            ->placeholder('ex: Cardiologie')
                            ->columnSpan(2),

                        Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->columnSpan(1),

                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Enseignant')
                    ->formatStateUsing(function ($record) {
                        // Affiche : "Dr ARABO"
                        return $record->title . ' ' . $record->name;
                    })
                    // Permet de chercher en tapant "Dr" OU "Arabo"
                    ->searchable(['title', 'name'])
                    ->sortable() // Trie alphabétiquement par le nom
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('specialty')
                    ->label('Spécialité')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope'),
            ])
            ->recordAction(Actions\EditAction::class)
            ->toolbarActions(ActionGroup::make([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]))
            ->headerActions([
//                Actions\CreateAction::make(),
                ImportAction::make()
                    ->importer(TeacherImporter::class)
                    ->label('Importer des Enseignants')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
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
            'index' => ListTeachers::route('/'),
            'create' => CreateTeacher::route('/create'),
            'edit' => EditTeacher::route('/{record}/edit'),
        ];
    }
}
