<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Contenu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->visible(fn() => Auth::user()->is_admin)
                    ->default(fn() => Auth::id())
                    ->disabled(fn($record) => $record && Auth::user()->is_admin), // Admin ne peut pas changer le propriétaire
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
                Forms\Components\Select::make('categories')
                    ->label('Catégories')
                    ->relationship('categories', 'name', function ($query) {
                        // Afficher les catégories système et les catégories personnelles de l'utilisateur
                        return $query->where(function ($q) {
                            $q->where('is_system', true)
                                ->orWhere('user_id', Auth::id());
                        });
                    })
                    ->multiple()
                    ->preload()
                    ->options(function () {
                        return \App\Models\Category::where(function ($query) {
                            $query->where('is_system', true)
                                ->orWhere('user_id', Auth::id());
                        })->pluck('name', 'id')
                            ->map(function ($name, $id) {
                                $category = \App\Models\Category::find($id);
                                return $name . ' <span class="ml-1 inline-block h-3 w-3 rounded-full" style="background-color: ' . $category->color . '"></span>';
                            })
                            ->toArray();
                    })
                    ->allowHtml()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Couleur')
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                        Forms\Components\Hidden::make('is_system')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => Auth::user()->is_admin),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Catégories')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        $categoryRecord = $record->categories->first();
                        return $categoryRecord ? $state : '';
                    })
                    ->colors(function ($state, $record) {
                        $colors = [];
                        foreach ($record->categories as $category) {
                            $colors[] = $category->color;
                        }
                        return $colors;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->visible(fn() => Auth::user()->is_admin),  // Seulement visible pour admin
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->relationship('categories', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => Auth::user()->is_admin || $record->user_id === Auth::id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => Auth::user()->is_admin || $record->user_id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Si l'utilisateur n'est pas admin, n'afficher que ses propres notes
        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
