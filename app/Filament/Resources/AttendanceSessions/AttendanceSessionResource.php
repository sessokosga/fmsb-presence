<?php

namespace App\Filament\Resources\AttendanceSessions;

use App\Filament\Resources\AttendanceSessions\Pages\CreateAttendanceSession;
use App\Filament\Resources\AttendanceSessions\Pages\EditAttendanceSession;
use App\Filament\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Resources\AttendanceSessions\Schemas\AttendanceSessionForm;
use App\Filament\Resources\AttendanceSessions\Tables\AttendanceSessionsTable;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AttendanceSessionResource extends Resource
{
    protected static ?string $model = AttendanceSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static ?string $modelLabel = 'Séance de présence';
    protected static ?string $pluralModelLabel = 'Séances de présence';
    protected static ?string $navigationLabel = 'Présences'; // Plus court pour le menu

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Sélection du Cours')
                    ->description('Choisissez le département et la filière pour filtrer les cours.')
                    ->schema([
                        // 1. DÉPARTEMENT
                        Select::make('department_id')
                            ->label('Département')
                            ->options(\App\Models\Department::all()->mapWithKeys(function ($department) {
                                return [$department->id => "{$department->code} - {$department->name}"];
                            }))
                            ->searchable()
                            ->live()
                            // 👇 LA CORRECTION EST ICI : On remplit le champ quand on charge une session existante
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record && $record->course) {
                                    // On remonte : Session -> Course -> Filiere -> Department
                                    $component->state($record->course->filiere->department_id);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('filiere_id', null);
                                $set('course_id', null);
                                if ($state) {
                                    $firstFiliere = \App\Models\Filiere::where('department_id', $state)->first();
                                    if ($firstFiliere) {
                                        $set('filiere_id', $firstFiliere->id);
                                        $firstCourse = \App\Models\Course::where('filiere_id', $firstFiliere->id)->first();
                                        if ($firstCourse) {
                                            $set('course_id', $firstCourse->id);
                                            if ($firstCourse->teacher_id) $set('teacher_id', $firstCourse->teacher_id);
                                        }
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->columnSpan(1),

                        // 2. FILIÈRE
                        Select::make('filiere_id')
                            ->label('Filière')
                            ->options(function (Get $get, $record) {
                                // Astuce : Si on est en train d'éditer, on prend le dept du record, sinon celui du formulaire
                                $deptId = $get('department_id');

                                // Si le formulaire est vide mais qu'on a un record, on le récupère du record
                                if (!$deptId && $record && $record->course) {
                                    $deptId = $record->course->filiere->department_id;
                                }

                                if (!$deptId) return [];

                                return \App\Models\Filiere::where('department_id', $deptId)
                                    ->get()
                                    ->mapWithKeys(fn($f) => [$f->id => "{$f->code} - {$f->name}"]);
                            })
                            ->searchable()
                            ->live()
                            // 👇 LA CORRECTION EST ICI AUSSI
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record && $record->course) {
                                    $component->state($record->course->filiere_id);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('course_id', null);
                                if ($state) {
                                    $firstCourse = \App\Models\Course::where('filiere_id', $state)->first();
                                    if ($firstCourse) {
                                        $set('course_id', $firstCourse->id);
                                        if ($firstCourse->teacher_id) $set('teacher_id', $firstCourse->teacher_id);
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->disabled(fn(Get $get) => !$get('department_id'))
                            ->columnSpan(1),

                        // 3. COURS (UE)
                        Select::make('course_id')
                            ->label('Unité d\'Enseignement (UE)')
                            ->options(function (Get $get, $record) {
                                $filiereId = $get('filiere_id');

                                // Récupération de secours pour l'édition
                                if (!$filiereId && $record && $record->course) {
                                    $filiereId = $record->course->filiere_id;
                                }

                                if (!$filiereId) return [];

                                return \App\Models\Course::where('filiere_id', $filiereId)
                                    ->get()
                                    ->mapWithKeys(fn($c) => [$c->id => "{$c->code} - {$c->name}"]);
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $course = \App\Models\Course::find($state);
                                if ($course && $course->teacher_id) {
                                    $set('teacher_id', $course->teacher_id);
                                }
                            })
                            ->required()
                            ->disabled(fn(Get $get) => !$get('filiere_id'))
                            ->columnSpan(2),
                        Placeholder::make('warning_course_change')
                            ->label('⚠️ Attention')
                            ->content('Vous avez modifié le cours. En sauvegardant, la liste de présence actuelle sera automatiquement supprimée pour éviter les incohérences.')
                            ->visible(function (Get $get, $record) {
                                // On n'affiche ça que si on est en mode édition (record existe)
                                if (!$record) return false;

                                // On récupère la nouvelle valeur choisie
                                $newCourseId = $get('course_id');

                                // On récupère l'ancienne valeur en base
                                $oldCourseId = $record->course_id;

                                // On affiche si c'est différent ET qu'il y a déjà des présences enregistrées
                                return ($newCourseId != $oldCourseId) && $record->attendances()->exists();
                            })
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-danger-600 bg-danger-50 p-4 rounded-lg border border-danger-200']), // Style rouge
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Détails de la séance')
                    ->schema([
                        // L'enseignant (Pré-rempli mais modifiable)
                        Select::make('teacher_id')
                            ->label('Enseignant responsable')
                            ->relationship('teacher', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->title} {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),


                        Select::make('monitor_ids')
                            ->label('Moniteurs / Suppléants')
                            ->relationship(
                                'monitors',
                                'name',
                                // 👇 C'est ici qu'on filtre la liste
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    // On récupère l'ID du prof principal sélectionné au-dessus
                                    $teacherId = $get('teacher_id');

                                    // On retourne tous les profs SAUF celui qui est déjà principal
                                    if ($teacherId) {
                                        return $query->where('teachers.id', '!=', $teacherId);
                                    }
                                    return $query;
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->title} {$record->name}")
                            ->multiple()
                            ->preload()
                            ->columnSpan(2)
                            ->live(),

                        Select::make('session_type')
                            ->label('Type')
                            ->options([
                                'CM' => 'Cours Magistral',
                                'TD' => 'Travaux Dirigés',
                                'TP' => 'Travaux Pratiques',
                                'CC' => 'Contrôle Continu',
                            ])
                            ->default('CM')
                            ->native(false)
                            ->required(),


                        // ... Vos champs Date, Heure, Salle (inchangés) ...
                        Select::make('academic_year_id')
                            ->label("Année Académique")
                            ->relationship('academic_year', 'name')
                            ->default(fn () => AcademicYear::first()->id)
                            ->required(),
                        DatePicker::make('session_date')->default(now())->required(),
                        TimePicker::make('start_time')->default('07:30')->required(),
                        TimePicker::make('end_time')->default('10:00')->required(),
                        TextInput::make('location')->default('Amphi 700'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('course.code')
                    ->label('UE')
                    ->badge()
                    ->color('info'),
                TextColumn::make('course.name')
                    ->label('Module')
                    ->searchable(),
                // Dans les colonnes du tableau (table)
                TextColumn::make('session_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'CM',
                        'warning' => 'TD',
                        'success' => 'TP',
                        'gray' => 'TPE',
                    ])
                    ->sortable(),

// Ajoutons aussi l'enseignant car il est sur votre PDF
                TextColumn::make('teacher.name')
                    ->label('Enseignant')
                    ->formatStateUsing(fn($record) => $record->teacher
                        ? "{$record->teacher->title} {$record->teacher->name}"
                        : 'Non assigné'
                    )
                    ->searchable(['teacher.name', 'teacher.title']),
                TextColumn::make('start_time')
                    ->label('Début')
                    ->time('H:i'),
                TextColumn::make('location')
                    ->label('Lieu')
                    ->icon('heroicon-o-map-pin'),
            ])
            ->recordAction(ViewAction::class)
            ->headerActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceSessions::route('/'),
            'create' => CreateAttendanceSession::route('/create'),
            'edit' => EditAttendanceSession::route('/{record}/edit'),
        ];
    }
}
