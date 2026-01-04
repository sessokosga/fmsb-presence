<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Imports\DepartmentImporter;
use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
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

use Filament\Forms;
use Filament\Tables;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $modelLabel = 'Département';
    protected static ?string $pluralModelLabel = 'Départements';
    protected static ?string $navigationLabel = 'Départements';
    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Configuration du Département')
                    ->description('Gérez les informations d\'identification et le rattachement à la faculté')
                    ->schema([
                        // Liste déroulante des facultés
                        Forms\Components\Select::make('faculty_id')
                            ->label('Faculté parente')
                            ->relationship('faculty', 'code')
                            ->default(fn() => \App\Models\Faculty::first()?->id) // Sélection par défaut
                            ->searchable()
                            ->preload()
                            ->columnSpan(1)
                            ->required(),

                        // Nom du département
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du Département')
                            ->placeholder('ex: Sciences Biomédicales')
                            ->required()
                            ->columnSpan(2)
                            ->maxLength(255),

                        // Code du département
                        Forms\Components\TextInput::make('code')
                            ->label('Code (SBM)')
                            ->placeholder('SBM')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1)
                            ->maxLength(10),
                    ])
                    ->columns(4) // Aligne les 3 champs sur une seule ligne à l'intérieur
                    ->columnSpanFull(), // Force la section à prendre toute la largeur de la page
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('faculty.code')
                    ->label('Faculté')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Département')
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->copyable() // Permet de cliquer pour copier le code
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                // Permet de filtrer la liste par Faculté
                Tables\Filters\SelectFilter::make('faculty')
                    ->relationship('faculty', 'name'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(DepartmentImporter::class)
                    ->label('Importer Départements')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
            ])
            ->recordActions([ActionGroup::make([
                EditAction::make(),
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
            "index" => ListDepartments::route("/"),
            "create" => CreateDepartment::route("/create"),
            "edit" => EditDepartment::route("/{record}/edit"),
        ];
    }
}
