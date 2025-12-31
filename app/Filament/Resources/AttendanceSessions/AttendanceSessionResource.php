<?php

namespace App\Filament\Resources\AttendanceSessions;

use App\Filament\Resources\AttendanceSessions\Pages\CreateAttendanceSession;
use App\Filament\Resources\AttendanceSessions\Pages\EditAttendanceSession;
use App\Filament\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Resources\AttendanceSessions\Schemas\AttendanceSessionForm;
use App\Filament\Resources\AttendanceSessions\Tables\AttendanceSessionsTable;
use App\Models\AttendanceSession;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informations Générales')
                    ->schema([
                        // 1 part : Année Académique (Par défaut l'actuelle)
                        Select::make('academic_year_id')
                            ->label('Année')
                            ->relationship('academicYear', 'name')
                            ->default(fn() => \App\Models\AcademicYear::where('is_current', true)->first()?->id)
                            ->required()
                            ->columnSpan(1),

                        // 2 parts : Unité d'Enseignement (UE)
                        // On peut aussi mettre le premier cours par défaut pour éviter le vide
                        Select::make('course_id')
                            ->label('Unité d\'Enseignement (UE)')
                            ->relationship('course', 'code')
                            ->default(fn() => \App\Models\Course::first()?->id)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Select::make('teacher_id')
                            ->label('Enseignant Principal')
                            ->relationship('teacher', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->title} {$record->name}")
                            ->searchable()
                            ->preload()
                            ->live() // 👈 IMPORTANT : Déclenche la mise à jour immédiate
                            ->afterStateUpdated(function (Set $set) {
                                // Optionnel : Si on change de prof principal, on vide la liste des moniteurs pour éviter les conflits
                                $set('monitor_ids', []);
                            })
                            ->required(),

                        // Dans le schema du formulaire
                        Select::make('session_type')
                            ->label('Type de séance')
                            ->options([
                                'CM' => 'Cours Magistral (CM)',
                                'TD' => 'Travaux Dirigés (TD)',
                                'TP' => 'Travaux Pratiques (TP)',
                                'TPE' => 'Travail Personnel (TPE)',
                                'CC' => 'Contrôle Continu (CC)',
                            ])
                            ->default('CM')
                            ->required()
                            ->native(false) // Plus joli visuellement
                            ->columnSpan(2),

                        // 1 part : La Date (Aujourd'hui)
                        DatePicker::make('session_date')
                            ->label('Date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Horaires et Lieu')
                    ->schema([
                        // Heure de début par défaut : 07:30
                        TimePicker::make('start_time')
                            ->label('Heure Début')
                            ->default('07:30')
                            ->required(),

                        // Heure de fin par défaut : 10:00
                        TimePicker::make('end_time')
                            ->label('Heure Fin')
                            ->default('10:00')
                            ->required(),

                        // Lieu par défaut (Amphi 700 ou autre)
                        TextInput::make('location')
                            ->label('Salle / Amphi')
                            ->placeholder('ex: Amphi 700')
                            ->default('Amphi 700'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Fieldset::make('Détails Académiques')
                    ->schema([
                        // Remplacement du select statique par la relation
                        Select::make('semester_id')
                            ->label('Semestre')
                            ->relationship('semester', 'name') // Suppose que votre table semestres a une colonne 'name' (ex: "Semestre 1")
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('week_number')
                            ->label('N° Semaine')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(20),

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
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->title} {$record->name}")
                            ->multiple()
                            ->preload()
                            ->live(),
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
                    ->formatStateUsing(fn ($record) => $record->teacher
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
