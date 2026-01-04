<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Imports\CourseImporter;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                // PREMIÈRE LIGNE : Détails du Module + Enseignant
                Section::make('Détails du Module')
                    ->description('Identifiants, responsable et poids de l\'Unité d\'Enseignement')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom de l\'UE')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('code')
                            ->label('Code UE')
                            ->required()
                            ->unique(ignoreRecord: true),

                        // 👇 AJOUT : L'Enseignant Responsable
                        Select::make('teacher_id')
                            ->label('Enseignant Responsable')
                            ->relationship('teacher', 'name')
                            // Affiche "Pr. Fouda" au lieu de juste "Fouda"
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->title} {$record->name}")
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(), // Prend toute la largeur pour bien lire le nom

                        TextInput::make('credits')
                            ->label('Crédits (ECTS)')
                            ->numeric(),

                        TextInput::make('hours')
                            ->label('Volume Horaire (H)')
                            ->numeric()
                            ->suffix('heures'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // DEUXIÈME LIGNE : Rattachement
                Section::make('Rattachement Académique')
                    ->description('Localisation du cours dans le cursus')
                    ->schema([
                        // Le département est commenté car géré via la filière désormais
                        // Select::make('department_id')...

                        Select::make('filiere_id')
                            ->relationship('filiere', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Filière'),

                        Select::make('level_id')
                            ->relationship('level', 'code')
                            ->default(fn () => \App\Models\Level::first()?->id)
                            ->preload()
                            ->required(),

                        Select::make('semester_id')
                            ->relationship('semester', 'code')
                            ->default(fn () => \App\Models\Semester::where('is_active', true)->first()?->id
                                ?? \App\Models\Semester::first()?->id)
                            ->preload()
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(CourseImporter::class)
                    ->label('Importer des Cours')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
            ])
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Module')
                    ->searchable()
                    ->wrap(),

                // 👇 AJOUT : Affichage du responsable dans la liste
                TextColumn::make('teacher.name')
                    ->label('Responsable')
                    ->formatStateUsing(fn ($record) => $record->teacher
                        ? "{$record->teacher->title} {$record->teacher->name}"
                        : '-')
                    ->searchable(['teacher.name', 'teacher.title'])
                    ->color('gray')
                    ->toggleable(), // Permet de masquer la colonne si besoin

                TextColumn::make('credits')
                    ->label('Crédits')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true), // Masqué par défaut pour alléger

                TextColumn::make('hours')
                    ->label('Vol. H')
                    ->suffix(' h')
                    ->alignCenter(),

                TextColumn::make('filiere.code') // Ajout utile pour voir la filière
                ->label('Filière')
                    ->badge()
                    ->color('info'),

                TextColumn::make('level.code')
                    ->label('Niv.')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('semester.code')
                    ->label('Sem.')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('filiere')->relationship('filiere', 'name'),
                SelectFilter::make('level')->relationship('level', 'code'),
                SelectFilter::make('semester')->relationship('semester', 'code'),
                SelectFilter::make('teacher')->relationship('teacher', 'name')->label('Enseignant'),
            ]);
    }

//
//    public static function form(Schema $form): Schema
//    {
//        return $form
//            ->schema([
//                // PREMIÈRE LIGNE : Détails du Module
//                Section::make('Détails du Module')
//                    ->description('Identifiants et poids de l\'Unité d\'Enseignement')
//                    ->schema([
//                        TextInput::make('name')
//                            ->label('Nom de l\'UE')
//                            ->required()
//                            ->columnSpan(2), // Le nom prend 2 colonnes sur 3
//
//                        TextInput::make('code')
//                            ->label('Code UE')
//                            ->required()
//                            ->unique(ignoreRecord: true),
//
//                        TextInput::make('credits')
//                            ->label('Crédits (ECTS)')
//                            ->numeric(),
//
//                        TextInput::make('hours')
//                            ->label('Volume Horaire (H)')
//                            ->numeric()
//                            ->suffix('heures'),
//                    ])
//                    ->columns(3) // À l'intérieur de la section, on garde 3 colonnes
//                    ->columnSpanFull(), // FORCE LA SECTION SUR TOUTE LA LARGEUR
//
//                // DEUXIÈME LIGNE : Rattachement
//                Section::make('Rattachement Académique')
//                    ->description('Localisation du cours dans le cursus')
//                    ->schema([
////                        Select::make('department_id')
////                            ->relationship('department', 'name')
////                            ->default(fn () => \App\Models\Department::first()?->id)
////                            ->preload()
////                            ->required(),
//                        Select::make('filiere_id')
//                            ->relationship('filiere', 'name')
//                            ->searchable()
//                            ->preload()
//                            ->required()
//                            ->label('Filière'),
//
//                        Select::make('level_id')
//                            ->relationship('level', 'code')
//                            ->default(fn () => \App\Models\Level::first()?->id)
//                            ->preload()
//                            ->required(),
//
//                        Select::make('semester_id')
//                            ->relationship('semester', 'code')
//                            ->default(fn () => \App\Models\Semester::where('is_active', true)->first()?->id
//                                ?? \App\Models\Semester::first()?->id)
//                            ->preload()
//                            ->required(),
//                    ])
//                    ->columns(3)
//                    ->columnSpanFull(), // FORCE LA SECTION SUR UNE NOUVELLE LIGNE
//            ]);
//    }
//
//
//    public static function table(Table $table): Table
//    {
//        return $table
//            ->headerActions([
//                ImportAction::make()
//                    ->importer(CourseImporter::class)
//                    ->label('Importer des Cours')
//                    ->icon('heroicon-o-arrow-up-tray')
//                    ->color('primary'),
//            ])
//            ->columns([
//                TextColumn::make('code')
//                    ->label('Code')
//                    ->sortable()
//                    ->searchable(),
//                TextColumn::make('name')
//                    ->label('Module')
//                    ->searchable()
//                    ->wrap(), // Permet au texte long de passer à la ligne
//                TextColumn::make('credits')
//                    ->label('Crédits')
//                    ->numeric()
//                    ->alignCenter(),
//                TextColumn::make('hours')
//                    ->label('Vol. Horaire')
//                    ->suffix(' h')
//                    ->alignCenter(),
//                TextColumn::make('level.code')
//                    ->label('Niveau')
//                    ->badge()
//                    ->color('warning'),
//                TextColumn::make('semester.code')
//                    ->label('Sem.')
//                    ->badge()
//                    ->color('success'),
//            ])
//            ->filters([
//                SelectFilter::make('filiere')->relationship('filiere', 'name'),
//                SelectFilter::make('level')->relationship('level', 'code'),
//                SelectFilter::make('semester')->relationship('semester', 'code'),
//            ]);
//    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
