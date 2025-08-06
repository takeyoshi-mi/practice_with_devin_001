<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('add_tickets')
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
                ->modalDescription(fn (): string => "生徒: {$this->record->name}\n".
                    "現在のチケット数: {$this->record->remaining_tickets}枚\n".
                    'チケットを付与してもよろしいですか？'
                )
                ->modalSubmitActionLabel('付与する')
                ->modalCancelActionLabel('キャンセル')
                ->action(function (array $data): void {
                    try {
                        DB::transaction(function () use ($data) {
                            $this->record->addTickets(
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

                        $this->record->refresh();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('エラーが発生しました')
                            ->body('チケット付与に失敗しました。もう一度お試しください。')
                            ->danger()
                            ->send();

                        Log::error('チケット付与エラー', [
                            'student_id' => $this->record->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('基本情報')
                    ->schema([
                        Infolists\Components\TextEntry::make('student_id')
                            ->label('生徒ID')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('nickname')
                            ->label('ニックネーム'),

                        Infolists\Components\TextEntry::make('name')
                            ->label('氏名')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('メールアドレス')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),

                        Infolists\Components\TextEntry::make('remaining_tickets')
                            ->label('残チケット数')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state === 0 => 'danger',
                                $state < 5 => 'warning',
                                default => 'success',
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('プラン情報')
                    ->schema([
                        Infolists\Components\TextEntry::make('currentPlan.plan_name')
                            ->label('プラン名')
                            ->placeholder('プランなし'),

                        Infolists\Components\TextEntry::make('currentPlan.start_date')
                            ->label('開始日')
                            ->date('Y年m月d日'),

                        Infolists\Components\TextEntry::make('currentPlan.finish_date')
                            ->label('終了日')
                            ->date('Y年m月d日')
                            ->placeholder('無期限'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Infolists\Components\Section::make('チケット履歴')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('ticketHistories')
                            ->label(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('付与日時')
                                    ->dateTime('Y年m月d日 H:i'),

                                Infolists\Components\TextEntry::make('addedByUser.name')
                                    ->label('付与者'),

                                Infolists\Components\TextEntry::make('ticket_count')
                                    ->label('数量')
                                    ->badge()
                                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'
                                    )
                                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? '+' : '').$state.'枚'
                                    ),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('備考')
                                    ->placeholder('-'),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
