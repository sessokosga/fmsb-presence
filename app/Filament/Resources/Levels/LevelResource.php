<?php

namespace App\Filament\Resources\Levels;

use App\Filament\Imports\LevelImporter;
use App\Filament\Resources\Levels\Pages\CreateLevel;
use App\Filament\Resources\Levels\Pages\EditLevel;
use App\Filament\Resources\Levels\Pages\ListLevels;
use App\Filament\Resources\Levels\Schemas\LevelForm;
use App\Filament\Resources\Levels\Tables\LevelsTable;
use App\Models\Level;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Resources\LevelResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $modelLabel = 'Niveau';
    protected static ?string $pluralModelLabel = 'Niveaux';
    protected static ?string $navigationLabel = 'Niveaux';
    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->placeholder('Licence 1')
                            ->required(),

                        Forms\Components\TextInput::make('code')
                            ->label('Code court')
                            ->placeholder('L1')
                            ->required(),
                    ])
                    ->columns(2)
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(LevelImporter::class)
                    ->label('Importer des Niveaux')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
            ])
            ->toolbarActions(ActionGroup::make([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom'),
                Tables\Columns\TextColumn::make('code')->label('Code')->badge(), // Le format badge est joli pour les codes
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
            "index" => ListLevels::route("/"),
            "create" => CreateLevel::route("/create"),
            "edit" => EditLevel::route("/{record}/edit"),
        ];
    }
}
