<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Models\Transaksi;
use App\Enums\OrderStatus;
use App\Models\Item;
use App\Exports\TransaksiExport;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Tables\Actions\ExportAction;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Divider;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationLabel= 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi ';

    protected static ?string $navigationGroup = 'Jojo Laundry';

    protected static ?string $navigationIcon = 'iconpark-transactionorder-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema(static::getDetailsFormSchema())
                        ->columns(2),

                    Forms\Components\Section::make()
                        ->schema(static::getShippingAndServiceSchema())
                        ->columns(2),

                    Forms\Components\Section::make('Detail Item Pemesanan')
                        ->headerActions([
                            Action::make('reset')
                                ->modalHeading('Are you sure?')
                                ->modalDescription('All existing items will be removed from the order.')
                                ->requiresConfirmation()
                                ->color('danger')
                                ->action(fn (Forms\Set $set) => $set('items', [])),
                        ])
                        ->schema([
                            static::getItemsRepeater(),
                        ]),                         
                ])
                ->columnSpan(['lg' => fn (?transaksi $record) => $record === null ? 3 : 2]),
                
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('total_harga')
                            ->label('Total Harga')
                            ->content(fn (Transaksi $record): string => 'Rp ' . number_format($record->total_harga, 0, ',', '.')),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn (transaksi $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diperbarui pada')
                            ->content(fn (transaksi $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?transaksi $record) => $record === null),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('number')
                    ->label('No Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelanggan.Nama')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->searchable()
                    ->sortable()
                    ->money('IDR')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total')
                            ->query(function ($query) {
                                return $query->where('status', '!=', 'batal');
                            }),
                    ]),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Layanan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => 'Regular (3 Hari)',
                        'express' => 'Express (1 Hari)',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Pengambilan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'delivery' => 'Delivery',
                        'pickup' => 'PickUp',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y, H:i')
                    ->date()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->label('Ubah'),
                Tables\Actions\ViewAction::make()
                ->label('Lihat'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required(),
                        Forms\Components\Select::make('format')
                            ->label('Format')
                            ->options([
                                'xlsx' => 'Excel (XLSX)',
                                'csv' => 'CSV',
                            ])
                            ->default('xlsx')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $export = new TransaksiExport($data['start_date'], $data['end_date']);
    
                        return match ($data['format']) {
                            'xlsx' => Excel::download($export, 'transaksi_export_' . now()->format('YmdHis') . '.xlsx'),
                            'csv' => Excel::download($export, 'transaksi_export_' . now()->format('YmdHis') . '.csv'),
                            default => null,
                        };
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), 
                ]),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
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
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'view' => Pages\ViewTransaksi::route('/{record}'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
            
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;
    
        return (string) $modelClass::whereNotIn('status', ['selesai', 'batal'])->count();
    }

    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('number')
                ->label('No Transaksi')
                ->default('Jojo-' . random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(transaksi::class, 'number', ignoreRecord: true),

            Forms\Components\Select::make('pelanggan_id')
                ->relationship('pelanggan', 'Nama')
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('Nama')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('Alamat')
                        ->label('Alamat')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('No_hp')
                        ->maxLength(255)
                        ->numeric(),

                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading('Create customer')
                        ->modalSubmitActionLabel('Create customer')
                        ->modalWidth('lg');
                }),
                Forms\Components\ToggleButtons::make('status')
                ->inline()
                ->options(OrderStatus::class)
                ->required(),

        ];
    }
    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->label('items')
                    ->options(Item::query()->pluck('Nama', 'id'))
                    ->required()
                    ->exists('items', 'id')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $item = Item::find($state);
                        $set('harga_item', $item?->Harga ?? 0);
                        $set('satuan_berat', $item?->Satuan_berat ?? 'Kg');
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('Jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->step(0.1)
                    ->rules([
                        'numeric',
                        'min:0.1',  
                        'max:99.9'  
                    ])
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required()
                    ->reactive() 
                    ->afterStateUpdated(function ($state, $context, Forms\Set $set, ?Model $record) {
                        if ($record) {
                            $record->transaksi?->updateTotalPrice();
                        }
                    })
                    ->suffix(fn (Forms\Get $get) => $get('satuan_berat')),

                    Forms\Components\Hidden::make('satuan_berat')
                        ->default('Kg'),

                //Forms\Components\TextInput::make('harga_item')
                    //->label('Harga per 1 Item')
                    //->disabled()
                    //->dehydrated()
                    //->numeric()
                    //->required()
                    //->columnSpan([
                        //'md' => 3,
                    //]),
                ])
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    
                    $data['transaksi_id'] = auth()->id();
                    return $data;
                })
            ->orderColumn('sort')
            ->hiddenLabel()
            ->columns([
                'md' => 10, 
            ])
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set, ?Model $record) {
                if ($record) {
                    $record->updateTotalPrice();
                }
            })
            ->required();
    }

    public static function getShippingAndServiceSchema(): array
    {
        return [
            Forms\Components\Section::make('Detail Layanan Dan Pengiriman')
                ->schema([
                    Forms\Components\Select::make('shipping_method')
                        ->label('Metode Pengiriman')
                        ->options([
                            'pickup' => 'Pick Up (Gratis)',
                            'delivery' => 'Delivery (Rp 5.000)',
                        ])
                        ->required()
                        ->default('pickup')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, ?Model $record) {
                            $shippingPrice = $state === 'delivery' ? 5000 : 0;
                            $set('shipping_price', $shippingPrice);
                            
                            if ($record) {
                                $record->updateTotalPrice();
                            }
                        })
                        ->columnSpan(['md' => 6]),

                    Forms\Components\Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'regular' => 'Regular (3 Hari Kerja)',
                            'express' => 'Express (1 Hari Kerja)',
                        ])
                        ->required()
                        ->default('regular')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, ?Model $record) {
                            if ($record) {
                                $record->updateTotalPrice();
                            }
                        })
                        ->columnSpan(['md' => 6]),

                    Forms\Components\Hidden::make('shipping_price')
                        ->default(0),
                ])
                ->columns(12)
        ];
    }

}
