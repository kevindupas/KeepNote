<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Contenu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn($record) => $record && $record->is_system && !Auth::user()->is_admin),
                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur')
                    ->required()
                    ->disabled(fn($record) => $record && $record->is_system && !Auth::user()->is_admin),
                Forms\Components\Toggle::make('is_system')
                    ->label('Catégorie système')
                    ->default(false)
                    ->disabled() // Toujours disabled, même pour admin
                    ->visible(fn() => Auth::user()->is_admin), // Visible uniquement pour admin
                Forms\Components\Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->disabled() // Désactivé pour tout le monde
                    ->visible(fn() => Auth::user()->is_admin) // Visible uniquement pour admin
                    ->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->label('Couleur')
                    ->formatStateUsing(fn(string $state): string => "<span style=\"color: {$state};\">⬤</span> {$state}")
                    ->html(),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('Système')
                    ->boolean()
                    ->visible(fn() => Auth::user()->is_admin), // Visible uniquement pour admin
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Créée par')
                    ->visible(fn() => Auth::user()->is_admin), // Visible uniquement pour admin
                Tables\Columns\TextColumn::make('notes_count')
                    ->label('Notes')
                    ->state(function (Category $record) {
                        // Pour les admins, afficher le nombre total de notes
                        if (Auth::user()->is_admin) {
                            return $record->notes()->count();
                        }

                        // Pour les étudiants, afficher uniquement leurs notes
                        return $record->notes()->where('user_id', Auth::id())->count();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_system')
                    ->label('Catégories système')
                    ->query(fn(Builder $query): Builder => $query->where('is_system', true)),
                Tables\Filters\Filter::make('personal')
                    ->label('Mes catégories')
                    ->query(fn(Builder $query): Builder => $query->where('is_system', false)->where('user_id', Auth::id())),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn($record) =>
                        // L'admin peut modifier toutes les catégories
                        Auth::user()->is_admin ||
                            // Les utilisateurs ne peuvent modifier que leurs propres catégories non-système
                            (!$record->is_system && $record->user_id === Auth::id())
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn($record) =>
                        // Seul l'admin peut supprimer des catégories système
                        ($record->is_system && Auth::user()->is_admin) ||
                            // Les utilisateurs ne peuvent supprimer que leurs propres catégories non-système
                            (!$record->is_system && $record->user_id === Auth::id())
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Si l'utilisateur n'est pas admin, n'afficher que les catégories système et ses propres catégories
        if (!Auth::user()->is_admin) {
            $query->where(function ($query) {
                $query->where('is_system', true)
                    ->orWhere('user_id', Auth::id());
            });
        }

        return $query;
    }
}
