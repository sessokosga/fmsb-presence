<?php

namespace App\Filament\Resources\AttendanceSessions\Pages;

use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAttendanceSession extends EditRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // On récupère l'enregistrement qui vient d'être sauvegardé
        $record = $this->getRecord();

        // On vérifie si la colonne 'course_id' a été modifiée durant cette sauvegarde
        if ($record->wasChanged('course_id')) {

            // Si oui, on compte combien on en supprime (pour l'info)
            $count = $record->attendances()->count();

            if ($count > 0) {
                // On supprime toutes les présences liées à l'ancien cours
                $record->attendances()->delete();

                Notification::make()
                    ->title('Liste réinitialisée')
                    ->body("Le cours a changé. La liste des $count étudiants précédents a été effacée.")
                    ->warning()
                    ->send();
            }

            $this->dispatch('$refresh');
        }
    }
}
