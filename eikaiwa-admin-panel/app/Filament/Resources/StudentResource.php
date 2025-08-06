<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                    ->query(fn (Builder $query): Builder => $query->where('remaining_tickets', '>', 0)
                    ),

                Tables\Filters\Filter::make('no_tickets')
                    ->label('チケットなし')
                    ->query(fn (Builder $query): Builder => $query->where('remaining_tickets', 0)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('詳細'),

                Tables\Actions\Action::make('add_tickets')
                    ->label('チケット付与')
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('ticket_count')
                            ->label('付与するチケット数')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1)
                            ->helperText('1〜100枚の範囲で入力してください')
                            ->suffixIcon('heroicon-m-ticket'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('チケット付与の確認')
                    ->modalDescription(fn (Student $record): string => "生徒: {$record->name}\n".
                        "現在のチケット数: {$record->remaining_tickets}枚\n".
                        'チケットを付与してもよろしいですか？'
                    )
                    ->modalSubmitActionLabel('付与する')
                    ->modalCancelActionLabel('キャンセル')
                    ->action(function (Student $record, array $data): void {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                $record->addTickets(
                                    count: $data['ticket_count'],
                                    addedBy: auth()->user(),
                                    notes: '管理画面から付与'
                                );
                            });

                            Notification::make()
                                ->title('チケット付与完了')
                                ->body("{$data['ticket_count']}枚のチケットを付与しました")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('エラーが発生しました')
                                ->body('チケット付与に失敗しました。もう一度お試しください。')
                                ->danger()
                                ->send();

                            Log::error('チケット付与エラー', [
                                'student_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }),
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
