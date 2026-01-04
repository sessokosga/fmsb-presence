<?php

namespace App\Filament\Resources\Filieres;

use App\Filament\Resources\Filieres\Pages\CreateFiliere;
use App\Filament\Resources\Filieres\Pages\EditFiliere;
use App\Filament\Resources\Filieres\Pages\ListFilieres;
use App\Filament\Resources\Filieres\Schemas\FiliereForm;
use App\Filament\Resources\Filieres\Tables\FilieresTable;
use App\Models\Filiere;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables;

class FiliereResource extends Resource
{
    protected static ?string $model = Filiere::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required()
                    ->label('Département'),
                TextInput::make('name')
                    ->required()
                    ->label('Nom de la filière'),
                TextInput::make('code')
                    ->label('Code (ex: GL)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department.name') // Affiche le nom du dept
                ->sortable()
                    ->label('Département'),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Filière'),


                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions(ActionGroup::make([
                Actions\EditAction::make(),
            ]))
            ->toolbarActions(ActionGroup::make([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]));
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
            'index' => ListFilieres::route('/'),
            'create' => CreateFiliere::route('/create'),
            'edit' => EditFiliere::route('/{record}/edit'),
        ];
    }
}
