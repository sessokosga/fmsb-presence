<?php

namespace App\Filament\Resources\Faculties;

use App\Filament\Imports\FacultyImporter;
use App\Filament\Resources\Faculties\Pages\CreateFaculty;
use App\Filament\Resources\Faculties\Pages\EditFaculty;
use App\Filament\Resources\Faculties\Pages\ListFaculties;
use App\Filament\Resources\Faculties\Schemas\FacultyForm;
use App\Filament\Resources\Faculties\Tables\FacultiesTable;
use App\Models\Faculty;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Models\Level;
use Filament\Forms;
use Filament\Tables;

class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make("name")
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true) // Empêche d'avoir deux fois le même code
                ->maxLength(10),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(FacultyImporter::class)
                    ->label('Importer des Facultés')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Code')->badge(), // Le format badge est joli pour les codes
                Tables\Columns\TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([ActionGroup::make([ EditAction::make()])])
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
            "index" => ListFaculties::route("/"),
            "create" => CreateFaculty::route("/create"),
            "edit" => EditFaculty::route("/{record}/edit"),
        ];
    }
}
