<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                // SECTION 1 : ÉTAT-CIVIL
                Section::make('Identité de l\'Étudiant')
                    ->description('Informations personnelles de base')
                    ->schema([
                        TextInput::make('matricule')
                            ->label('Matricule')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required(),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required(),
                        Select::make('gender')
                            ->label('Genre')
                            ->options([
                                'M' => 'Masculin',
                                'F' => 'Féminin',
                            ])->required(),
                        DatePicker::make('birth_date')
                            ->label('Date de naissance')
                            ->native(false) // Utilise un calendrier plus joli
                            ->displayFormat('d/m/Y'),
                    ])->columns(2)->columnSpanFull(),

                // SECTION 2 : CURSUS ACTUEL
                Section::make('Affectation Académique')
                    ->description('Où se trouve cet étudiant cette année ?')
                    ->schema([
                        Select::make('department_id')
                            ->label('Département')
                            ->relationship('department', 'name')
                            ->default(fn () => \App\Models\Department::first()?->id)
                            ->preload()
                            ->required(),
                        Select::make('level_id')
                            ->label('Niveau')
                            ->relationship('level', 'code')
                            ->default(fn () => \App\Models\Level::first()?->id)
                            ->preload()
                            ->required(),
                    ])->columns(2)->columnSpanFull(),

                // SECTION 3 : CONTACT (À ajouter après la section Affectation)
                Section::make('Coordonnées')
                    ->description('Informations de contact de l\'étudiant')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->label('Adresse Email')
                            ->placeholder('exemple@univ.cm'),

                        TextInput::make('phone')
                            ->tel()
                            ->label('Numéro de téléphone')
                            ->placeholder('6xx xxx xxx'),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Matricule mis en avant en gras
                TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Nom complet combiné
                TextColumn::make('full_name')
                    ->label('Nom Complet')
                    ->state(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name']),

                // Badge pour le Genre
                TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'M' => 'info',
                        'F' => 'danger',
                        default => 'gray',
                    }),

                // Département avec recherche
                TextColumn::make('department.name')
                    ->label('Département')
                    ->sortable()
                    ->searchable(),

                // Niveau affiché sous forme de badge coloré
                TextColumn::make('level.code')
                    ->label('Niveau')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ])
            ->filters([
                // Filtrer par département
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->label('Filtrer par Département'),

                // Filtrer par niveau
                SelectFilter::make('level')
                    ->relationship('level', 'code')
                    ->label('Filtrer par Niveau'),
            ])


            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
