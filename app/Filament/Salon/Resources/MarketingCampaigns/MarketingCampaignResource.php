<?php

namespace App\Filament\Salon\Resources\MarketingCampaigns;

use App\Filament\Salon\Resources\MarketingCampaigns\Pages\ManageMarketingCampaigns;
use App\Models\MarketingCampaign;
use App\Jobs\ProcessCampaignMessages;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MarketingCampaignResource extends Resource
{
    protected static ?string $model = MarketingCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $pluralLabel = 'الحملات التسويقية';
    protected static ?string $label = 'حملة تسويقية';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان الحملة')
                    ->placeholder('مثال: عرض عيد الفطر 🌙')
                    ->required(),
                Textarea::make('message')
                    ->label('نص الرسالة (WhatsApp)')
                    ->placeholder('اكتبي رسالتك هنا...')
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'scheduled' => 'مجدولة',
                        'processing' => 'قيد الإرسال',
                        'completed' => 'مكتملة',
                        'failed' => 'فشلت',
                    ])
                    ->default('draft')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                DateTimePicker::make('scheduled_at')
                    ->label('وقت الجدولة (اختياري)')
                    ->helperText('اتركيه فارغاً للإرسال اليدوي فوراً'),
                
                Placeholder::make('metrics')
                    ->label('إحصائيات الإرسال')
                    ->content(fn ($record) => $record ? "تم إرسال {$record->sent_count} من {$record->total_recipients} رسالة" : 'سيتم حساب المستهدفين عند بدء الإرسال')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'scheduled' => 'مجدولة',
                        'processing' => 'جارٍ الإرسال',
                        'completed' => 'مكتملة',
                        'failed' => 'فشلت',
                        default => $state,
                    }),
                TextColumn::make('sent_count')
                    ->label('المرسل')
                    ->suffix(fn ($record) => " / {$record->total_recipients}")
                    ->color('info')
                    ->weight('bold'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('send')
                    ->label('إرسال الآن')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => in_array($record->status, ['processing', 'completed']))
                    ->action(function ($record) {
                        $record->update(['status' => 'scheduled']);
                        ProcessCampaignMessages::dispatch($record);
                        
                        Notification::make()
                            ->title('بدأت الحملة!')
                            ->body('يتم الآن معالجة إرسال الرسائل في الخلفية.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMarketingCampaigns::route('/'),
        ];
    }
}
