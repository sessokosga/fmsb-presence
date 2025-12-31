<?php

namespace App\Filament\Resources\AttendanceSessions\RelationManagers;

use Filament\Actions\ActionGroup;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // Vos colonnes (Image, Nom, Toggle...)
                Tables\Columns\ImageColumn::make('student.avatar_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-student.png')),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Étudiant')
                    ->getStateUsing(function ($record) {
                        // On récupère l'étudiant lié
                        $student = $record->student;

                        if (! $student) return 'Étudiant introuvable';

                        // ICI : remplacez par vos vrais noms de colonnes
                        // Exemple si vous avez 'first_name' et 'last_name' :
                        return "{$student->first_name} {$student->last_name}";

                        // Ou si vous avez juste 'nom' :
                        // return $student->nom;
                    })
                    ->description(fn ($record) => $record->student->matricule ?? 'Sans matricule')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_present')
                    ->label('Présence')
                    ->onIcon('heroicon-m-check-circle') // Icône quand présent
                    ->offIcon('heroicon-m-x-circle')   // Icône quand absent
                    ->onColor('success')
                    ->offColor('danger'),

                // Les colonnes cachées...
                Tables\Columns\TextColumn::make('observation')
                    ->label('Observation')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // Votre bouton "Générer la liste" ira ici
                Actions\Action::make('fill_students')
                    ->label('Générer la liste')
                    ->icon('heroicon-o-users')
                    ->requiresConfirmation()
                    ->action(function ($livewire) {
                        $session = $livewire->getOwnerRecord(); // La séance actuelle

                        // On récupère le niveau du cours (ex: Niveau 4 pour MED4)
                        // Adaptez 'level_id' si votre colonne s'appelle autrement
                        $levelId = $session->course->level_id;

                        if (! $levelId) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur : Ce cours n\'est lié à aucun niveau.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // On récupère les étudiants de ce niveau
                        $students = \App\Models\Student::where('level_id', $levelId)->get();

                        $count = 0;
                        foreach ($students as $student) {
                            // On crée l'entrée dans la table de présence
                            $exists = \App\Models\Attendance::where('attendance_session_id', $session->id)
                                ->where('student_id', $student->id)
                                ->exists();

                            if (!$exists) {
                                \App\Models\Attendance::create([
                                    'attendance_session_id' => $session->id,
                                    'student_id' => $student->id,
                                    'is_present' => false, // Absent par défaut
                                    'status' => 'absent'
                                ]);
                                $count++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("$count étudiants ajoutés à la liste")
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('mark_all_present')
                    ->label('Tout cocher présent')
                    // 👇 Remplacez par une de ces icônes valides
                    ->icon('heroicon-o-check-badge')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (RelationManager $livewire) {
                        $session = $livewire->getOwnerRecord();
                        // On met à jour toutes les présences d'un coup
                        $session->attendances()->update([
                            'is_present' => true,
                            'status' => 'present'
                        ]);
                    }),
            ])
            ->actions([
                // 👇 1. On définit l'action d'édition ici
                Actions\EditAction::make()
                    ->label('Note / Obs'),
            ])
            // 👇 2. On dit au tableau : "Quand on clique sur la ligne, lance l'action 'edit'"
            ->recordAction('edit')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_present')
                    ->label('Filtrer par présence')
                    ->placeholder('Tous les étudiants')
                    ->trueLabel('Présents uniquement')
                    ->falseLabel('Absents uniquement'),
            ]);
    }

    /*
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student_id')
            ->columns([
                TextColumn::make('student_id')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    */
}
