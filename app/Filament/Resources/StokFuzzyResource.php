<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokFuzzyResource\Pages;
use App\Filament\Resources\StokFuzzyResource\RelationManagers;
use App\Models\StokFuzzy;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

class StokFuzzyResource extends Resource
{
    protected static ?string $model = StokFuzzy::class;

    protected static ?string $navigationLabel= 'Perencanaan Stok';

    protected static ?string $modelLabel = 'Perencanaan Stok ';

    protected static ?string $navigationGroup = 'Jojo Laundry';

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode')
                ->schema([
                    TextInput::make('periode')
                        ->label('Periode')
                        ->type('month')
                        ->required()
                        ->default(now()->format('Y-m'))
                        ->extraAttributes(['class' => 'month-picker'])
                        ->afterStateHydrated(function ($component, $state) {
                            if ($state) {
                                $date = Carbon::parse($state)->startOfMonth();
                                $component->state($date->format('Y-m'));
                            }
                        })
                        ->beforeStateDehydrated(function ($component, $state) {
                            if ($state) {
                                return Carbon::parse($state)->startOfMonth()->format('Y-m');
                            }
                        })
                ])->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Hasil Prediksi')
                ->schema([
                    Forms\Components\Placeholder::make('prediksi_detergen')
                        ->label('Prediksi Stok Detergen')
                        ->content(function ($record) {
                            if (!$record) return '-';
                            $prediksi = $record->getPrediksiStok();
                            return number_format($prediksi['detergen'], 2) . ' Liter';
                        }),
                    Forms\Components\Placeholder::make('prediksi_pewangi')
                        ->label('Prediksi Stok Pewangi')
                        ->content(function ($record) {
                            if (!$record) return '-';
                            $prediksi = $record->getPrediksiStok();
                            return number_format($prediksi['pewangi'], 2) . ' Liter';
                        }),
                    Forms\Components\Placeholder::make('prediksi_parfum')
                        ->label('Prediksi Stok Parfum')
                        ->content(function ($record) {
                            if (!$record) return '-';
                            $prediksi = $record->getPrediksiStok();
                            return number_format($prediksi['parfum'], 2) . ' Liter';
                        }),
                ])
                ->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Detergen')
                ->schema([
                    Forms\Components\TextInput::make('detergen_stok_minimum')
                        ->label('Stok Minimum (Liter)')
                        ->numeric()
                        ->suffix('L')
                        ->required(),
                    Forms\Components\TextInput::make('detergen_stok_sekarang')
                        ->label('Stok Sekarang (Liter)')
                        ->numeric()
                        ->suffix('L')
                        ->required(),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('detergen_interval_rendah_min')
                                ->label('Interval Rendah Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('detergen_interval_rendah_max')
                                ->label('Interval Rendah Max')
                                ->numeric()
                                ->required(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('detergen_interval_sedang_min')
                                ->label('Interval Sedang Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('detergen_interval_sedang_max')
                                ->label('Interval Sedang Max')
                                ->numeric()
                                ->required(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('detergen_interval_tinggi_min')
                                ->label('Interval Tinggi Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('detergen_interval_tinggi_max')
                                ->label('Interval Tinggi Max')
                                ->numeric()
                                ->required(),
                        ]),
                ])->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Pewangi')
                ->schema([
                    Forms\Components\TextInput::make('pewangi_stok_minimum')
                        ->label('Stok Minimum (Liter)')
                        ->numeric()
                        ->suffix('L')
                        ->required(),
                    Forms\Components\TextInput::make('pewangi_stok_sekarang')
                        ->label('Stok Sekarang (Liter)')
                        ->numeric()
                        ->suffix('L')
                        ->required(),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('pewangi_interval_rendah_min')
                                ->label('Interval Rendah Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('pewangi_interval_rendah_max')
                                ->label('Interval Rendah Max')
                                ->numeric()
                                ->required(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('pewangi_interval_sedang_min')
                                ->label('Interval Sedang Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('pewangi_interval_sedang_max')
                                ->label('Interval Sedang Max')
                                ->numeric()
                                ->required(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('pewangi_interval_tinggi_min')
                                ->label('Interval Tinggi Min')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('pewangi_interval_tinggi_max')
                                ->label('Interval Tinggi Max')
                                ->numeric()
                                ->required(),
                        ]),
                ])->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Parfum')
                ->schema([
                    Forms\Components\TextInput::make('parfum_stok_minimum')
                    ->label('Stok Minimum (Liter)')
                    ->numeric()
                    ->suffix('L')
                    ->required(),
                Forms\Components\TextInput::make('parfum_stok_sekarang')
                    ->label('Stok Sekarang (Liter)')
                    ->numeric()
                    ->suffix('L')
                    ->required(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('parfum_interval_rendah_min')
                            ->label('Interval Rendah Min')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('parfum_interval_rendah_max')
                            ->label('Interval Rendah Max')
                            ->numeric()
                            ->required(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('parfum_interval_sedang_min')
                            ->label('Interval Sedang Min')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('parfum_interval_sedang_max')
                            ->label('Interval Sedang Max')
                            ->numeric()
                            ->required(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('parfum_interval_tinggi_min')
                            ->label('Interval Tinggi Min')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('parfum_interval_tinggi_max')
                            ->label('Interval Tinggi Max')
                            ->numeric()
                            ->required(),
                    ]),
            ])->columnSpan(['lg' => 1]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')
                ->label('Periode')
                ->formatStateUsing(function ($state) {
                    return Carbon::parse($state)->locale('id')->isoFormat('MMMM Y');
                })
                ->searchable(),
                
                //Tables\Columns\TextColumn::make('detergen_stok_sekarang')
                    //->label('Stok Detergen saat ini')
                    //->formatStateUsing(fn ($state) => number_format($state, 2) . ' L')
                    //->searchable(),
               // Tables\Columns\TextColumn::make('pewangi_stok_sekarang')
                    //->label('Stok Pewangi saat ini')
                    //->formatStateUsing(fn ($state) => number_format($state, 2) . ' L')
                   // ->searchable(),
                //Tables\Columns\TextColumn::make('parfum_stok_sekarang')
                    //->label('Stok Parfum saat ini')
                    //->formatStateUsing(fn ($state) => number_format($state, 2) . ' L')
                    //->searchable(),
                Tables\Columns\TextColumn::make('prediksi_detergen')
                    ->label('Prediksi Detergen')
                    ->getStateUsing(function ($record) {
                        $prediksi = $record->getPrediksiStok();
                        return number_format($prediksi['detergen'], 2) . ' L';
                    }),
                Tables\Columns\TextColumn::make('prediksi_pewangi')
                    ->label('Prediksi Pewangi')
                    ->getStateUsing(function ($record) {
                        $prediksi = $record->getPrediksiStok();
                        return number_format($prediksi['pewangi'], 2) . ' L';
                    }),
                Tables\Columns\TextColumn::make('prediksi_parfum')
                    ->label('Prediksi Parfum')
                    ->getStateUsing(function ($record) {
                        $prediksi = $record->getPrediksiStok();
                        return number_format($prediksi['parfum'], 2) . ' L';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStokFuzzies::route('/'),
            'create' => Pages\CreateStokFuzzy::route('/create'),
            'edit' => Pages\EditStokFuzzy::route('/{record}/edit'),
        ];
    }
}