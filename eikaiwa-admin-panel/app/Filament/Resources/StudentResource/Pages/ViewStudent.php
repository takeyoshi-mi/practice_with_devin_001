<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
            ]);
    }
}
