<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student_id')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('nickname')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('remaining_tickets')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id')
                    ->label('生徒ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('nickname')
                    ->label('ニックネーム')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('氏名')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('メール')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                    
                Tables\Columns\TextColumn::make('remaining_tickets')
                    ->label('残チケット')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    }),
                    
                Tables\Columns\TextColumn::make('currentPlan.plan_name')
                    ->label('現在のプラン')
                    ->placeholder('プランなし')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_tickets')
                    ->label('チケット保有者')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('remaining_tickets', '>', 0)
                    ),
                    
                Tables\Filters\Filter::make('no_tickets')
                    ->label('チケットなし')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('remaining_tickets', 0)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('詳細'),
            ])
            ->bulkActions([
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->searchPlaceholder('ID、名前、メールで検索...')
            ->extremePaginationLinks()
            ->striped();
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
