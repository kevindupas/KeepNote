<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

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
                    ->disabled(fn($record) => $record && Auth::user()->is_admin),
                Forms\Components\Select::make('note_id')
                    ->relationship('note', 'title', function (Builder $query) {
                        // Si l'utilisateur n'est pas admin, n'afficher que ses propres notes
                        if (!Auth::user()->is_admin) {
                            $query->where('user_id', Auth::id());
                        }
                    })
                    ->searchable()
                    ->label('Note associée (optionnelle)')
                    ->placeholder('Aucune note associée')
                    ->nullable(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_completed')
                    ->label('Terminée')
                    ->default(false),
                Forms\Components\Repeater::make('subtasks')
                    ->label('Sous-tâches')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required(),
                        Forms\Components\Toggle::make('is_completed')
                            ->label('Terminée')
                            ->default(false),
                    ])
                    ->defaultItems(0)
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
                Tables\Columns\TextColumn::make('note.title')
                    ->label('Note associée')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Terminée')
                    ->boolean(),
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
                    ->visible(fn() => Auth::user()->is_admin),
                Tables\Filters\SelectFilter::make('note_id')
                    ->label('Note')
                    ->relationship('note', 'title', function (Builder $query) {
                        // Si l'utilisateur n'est pas admin, n'afficher que ses propres notes
                        if (!Auth::user()->is_admin) {
                            $query->where('user_id', Auth::id());
                        }
                    }),
                Tables\Filters\Filter::make('is_completed')
                    ->label('Tâches terminées')
                    ->query(fn(Builder $query) => $query->where('is_completed', true)),
                Tables\Filters\Filter::make('is_not_completed')
                    ->label('Tâches non terminées')
                    ->query(fn(Builder $query) => $query->where('is_completed', false)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle')
                    ->label(fn(Task $record): string => $record->is_completed ? 'Marquer comme non terminée' : 'Marquer comme terminée')
                    ->icon(fn(Task $record): string => $record->is_completed ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(Task $record): string => $record->is_completed ? 'danger' : 'success')
                    ->action(function (Task $record): void {
                        $record->update(['is_completed' => !$record->is_completed]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('completeAll')
                        ->label('Marquer comme terminées')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn(Collection $records) => $records->each->update(['is_completed' => true])),
                    Tables\Actions\BulkAction::make('incompleteAll')
                        ->label('Marquer comme non terminées')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn(Collection $records) => $records->each->update(['is_completed' => false])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Si l'utilisateur n'est pas admin, n'afficher que ses propres tâches
        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
