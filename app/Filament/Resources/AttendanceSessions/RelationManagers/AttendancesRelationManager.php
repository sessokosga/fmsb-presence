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
                // 1. Nom complet (Prénom + Nom) + Matricule en description
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Étudiant')
                    ->getStateUsing(fn ($record) => "{$record->student->first_name} {$record->student->last_name}")
                    ->description(fn ($record) => $record->student->matricule ?? 'Sans matricule')
                    ->searchable(['student.first_name', 'student.last_name', 'student.matricule'])
                    ->sortable(),

                // 2. NOUVEAU : La Filière (ex: GL)
                Tables\Columns\TextColumn::make('student.filiere.code')
                    ->label('Filière')
                    ->badge()
                    ->color('info') // Bleu
                    ->sortable()
                    ->searchable(),

                // 3. NOUVEAU : Le Niveau (ex: L3)
                Tables\Columns\TextColumn::make('student.level.code')
                    ->label('Niveau')
                    ->badge()
                    ->color('warning') // Orange/Jaune
                    ->sortable(),

                // 4. Le toggle pour marquer présent/absent
                Tables\Columns\ToggleColumn::make('is_present')
                    ->label('Présence')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignCenter(), // Centré pour faire plus propre sans la photo
            ])
            ->headerActions([
                // 👇 LE BOUTON MIS À JOUR AVEC LA NOUVELLE LOGIQUE
                Actions\Action::make('fill_students')
                    ->label('Générer la liste (Filière + Rattrapages)')
                    ->icon('heroicon-o-user-group')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Importer les étudiants')
                    ->modalDescription('Ceci importera les étudiants de la filière standard ainsi que ceux inscrits en rattrapage.')
                    ->action(function ($livewire) {
                        $session = $livewire->getOwnerRecord();
                        $course = $session->course;

                        if (! $course) {
                            \Filament\Notifications\Notification::make()->title('Erreur : Session sans cours lié.')->danger()->send();
                            return;
                        }

                        // 1. GROUPE A : Les étudiants "Standards" (Même Filière + Même Niveau)
                        $etudiantsStandard = \App\Models\Student::query()
                            ->where('filiere_id', $course->filiere_id)
                            ->where('level_id', $course->level_id)
                            ->get();

                        // 2. GROUPE B : Les étudiants en "Rattrapage" (Table pivot course_student)
                        // (Assure-toi que la relation 'etudiantsRattrapage' existe bien dans le modèle Course)
                        $etudiantsRattrapage = $course->etudiantsRattrapage()->get();

                        // 3. FUSION : On combine les deux listes (merge évite les doublons d'IDs)
                        $tousLesEtudiants = $etudiantsStandard->merge($etudiantsRattrapage);

                        if ($tousLesEtudiants->isEmpty()) {
                            \Filament\Notifications\Notification::make()->title('Aucun étudiant trouvé (ni filière, ni rattrapage).')->warning()->send();
                            return;
                        }

                        $count = 0;
                        foreach ($tousLesEtudiants as $student) {
                            // firstOrCreate vérifie si l'étudiant est déjà là pour éviter les doublons
                            $attendance = \App\Models\Attendance::firstOrCreate([
                                'attendance_session_id' => $session->id,
                                'student_id' => $student->id,
                            ], [
                                'is_present' => false,
                                'status' => 'absent'
                            ]);

                            if ($attendance->wasRecentlyCreated) {
                                $count++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title($count . ' étudiants ajoutés avec succès.')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('reset_list')
                    ->label('Vider la liste')
                    ->icon('heroicon-o-trash')
                    ->color('danger') // Rouge pour signaler le danger
                    ->requiresConfirmation()
                    ->modalHeading('Vider la liste de présence ?')
                    ->modalDescription('Attention, vous allez supprimer tous les étudiants de cette liste ainsi que leur statut (présent/absent). Cette action est irréversible.')
                    ->modalSubmitActionLabel('Oui, tout supprimer')
                    ->action(function ($livewire) {
                        // 1. On récupère la session
                        $session = $livewire->getOwnerRecord();

                        // 2. On compte pour l'info
                        $count = $session->attendances()->count();

                        // 3. On supprime tout via la relation (ça vide la table attendances pour cette session seulement)
                        $session->attendances()->delete();

                        // 4. Notification
                        \Filament\Notifications\Notification::make()
                            ->title("Liste vidée ($count étudiants retirés)")
                            ->success()
                            ->send();
                    }),

                // Bouton pour tout valider d'un coup
                Actions\Action::make('mark_all_present')
                    ->label('Tout cocher présent')
                    ->icon('heroicon-o-check-badge')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function ($livewire) {
                        $session = $livewire->getOwnerRecord();
                        $session->attendances()->update([
                            'is_present' => true,
                            'status' => 'present'
                        ]);
                        \Filament\Notifications\Notification::make()->title('Mise à jour effectuée')->success()->send();
                    }),
            ])
            ->recordActions(ActionGroup::make([
                Actions\EditAction::make()->label('Note / Obs'),
                Actions\DeleteAction::make()->label('Retirer'),
            ]))
            ->recordAction('edit')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_present')
                    ->label('Filtrer par présence')
                    ->trueLabel('Présents uniquement')
                    ->falseLabel('Absents uniquement'),
            ]);
    }

//    public function table(Table $table): Table
//    {
//        return $table
//            ->columns([
//                // Photo de l'étudiant
//                Tables\Columns\ImageColumn::make('student.avatar_url')
//                    ->label('Photo')
//                    ->circular()
//                    ->defaultImageUrl(url('/images/placeholder-student.png')),
//
//                // Nom complet (Prénom + Nom) + Matricule en petit
//                Tables\Columns\TextColumn::make('student.name')
//                    ->label('Étudiant')
//                    ->getStateUsing(fn ($record) => "{$record->student->first_name} {$record->student->last_name}")
//                    ->description(fn ($record) => $record->student->matricule ?? 'Sans matricule')
//                    ->searchable(['student.first_name', 'student.last_name', 'student.matricule'])
//                    ->sortable(),
//
//                // Le toggle pour marquer présent/absent rapidement
//                Tables\Columns\ToggleColumn::make('is_present')
//                    ->label('Présence')
//                    ->onIcon('heroicon-m-check-circle')
//                    ->offIcon('heroicon-m-x-circle')
//                    ->onColor('success')
//                    ->offColor('danger'),
//            ])
//            ->headerActions([
//                // 👇 LE BOUTON MIS À JOUR AVEC LA NOUVELLE LOGIQUE
//                Actions\Action::make('fill_students')
//                    ->label('Générer la liste (Filière + Rattrapages)')
//                    ->icon('heroicon-o-user-group')
//                    ->color('primary')
//                    ->requiresConfirmation()
//                    ->modalHeading('Importer les étudiants')
//                    ->modalDescription('Ceci importera les étudiants de la filière standard ainsi que ceux inscrits en rattrapage.')
//                    ->action(function ($livewire) {
//                        $session = $livewire->getOwnerRecord();
//                        $course = $session->course;
//
//                        if (! $course) {
//                            \Filament\Notifications\Notification::make()->title('Erreur : Session sans cours lié.')->danger()->send();
//                            return;
//                        }
//
//                        // 1. GROUPE A : Les étudiants "Standards" (Même Filière + Même Niveau)
//                        $etudiantsStandard = \App\Models\Student::query()
//                            ->where('filiere_id', $course->filiere_id)
//                            ->where('level_id', $course->level_id)
//                            ->get();
//
//                        // 2. GROUPE B : Les étudiants en "Rattrapage" (Table pivot course_student)
//                        // (Assure-toi que la relation 'etudiantsRattrapage' existe bien dans le modèle Course)
//                        $etudiantsRattrapage = $course->etudiantsRattrapage()->get();
//
//                        // 3. FUSION : On combine les deux listes (merge évite les doublons d'IDs)
//                        $tousLesEtudiants = $etudiantsStandard->merge($etudiantsRattrapage);
//
//                        if ($tousLesEtudiants->isEmpty()) {
//                            \Filament\Notifications\Notification::make()->title('Aucun étudiant trouvé (ni filière, ni rattrapage).')->warning()->send();
//                            return;
//                        }
//
//                        $count = 0;
//                        foreach ($tousLesEtudiants as $student) {
//                            // firstOrCreate vérifie si l'étudiant est déjà là pour éviter les doublons
//                            $attendance = \App\Models\Attendance::firstOrCreate([
//                                'attendance_session_id' => $session->id,
//                                'student_id' => $student->id,
//                            ], [
//                                'is_present' => false,
//                                'status' => 'absent'
//                            ]);
//
//                            if ($attendance->wasRecentlyCreated) {
//                                $count++;
//                            }
//                        }
//
//                        \Filament\Notifications\Notification::make()
//                            ->title($count . ' étudiants ajoutés avec succès.')
//                            ->success()
//                            ->send();
//                    }),
//                Actions\Action::make('reset_list')
//                    ->label('Vider la liste')
//                    ->icon('heroicon-o-trash')
//                    ->color('danger') // Rouge pour signaler le danger
//                    ->requiresConfirmation()
//                    ->modalHeading('Vider la liste de présence ?')
//                    ->modalDescription('Attention, vous allez supprimer tous les étudiants de cette liste ainsi que leur statut (présent/absent). Cette action est irréversible.')
//                    ->modalSubmitActionLabel('Oui, tout supprimer')
//                    ->action(function ($livewire) {
//                        // 1. On récupère la session
//                        $session = $livewire->getOwnerRecord();
//
//                        // 2. On compte pour l'info
//                        $count = $session->attendances()->count();
//
//                        // 3. On supprime tout via la relation (ça vide la table attendances pour cette session seulement)
//                        $session->attendances()->delete();
//
//                        // 4. Notification
//                        \Filament\Notifications\Notification::make()
//                            ->title("Liste vidée ($count étudiants retirés)")
//                            ->success()
//                            ->send();
//                    }),
//
//                // Bouton pour tout valider d'un coup
//                Actions\Action::make('mark_all_present')
//                    ->label('Tout cocher présent')
//                    ->icon('heroicon-o-check-badge')
//                    ->color('gray')
//                    ->requiresConfirmation()
//                    ->action(function ($livewire) {
//                        $session = $livewire->getOwnerRecord();
//                        $session->attendances()->update([
//                            'is_present' => true,
//                            'status' => 'present'
//                        ]);
//                        \Filament\Notifications\Notification::make()->title('Mise à jour effectuée')->success()->send();
//                    }),
//            ])
//            ->recordActions(ActionGroup::make([
//                Actions\EditAction::make()->label('Note / Obs'),
//                Actions\DeleteAction::make()->label('Retirer'), // Utile si on veut enlever un étudiant de la liste manuellement
//            ]))
//            ->recordAction('edit')
//            ->filters([
//                Tables\Filters\TernaryFilter::make('is_present')
//                    ->label('Filtrer par présence')
//                    ->trueLabel('Présents uniquement')
//                    ->falseLabel('Absents uniquement'),
//            ]);
//    }


}
