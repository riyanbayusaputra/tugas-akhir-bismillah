<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Store;
use Filament\Forms\Set;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Tables\Table;
use function Livewire\after;
use Filament\Resources\Resource;
use App\Services\MidtransService;
use Barryvdh\DomPDF\Facade as PDF;

use App\Services\OrderStatusService;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Umum')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('No. Pesanan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Tanggal Pesan')
                                    ->disabled()
                                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d M Y H:i')),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->disabled(),
                            ]),
                        Forms\Components\Section::make('Informasi User')
                            ->schema([
                                Forms\Components\TextInput::make('user.email')
                                    ->label('Email User')
                                    ->formatStateUsing(fn($record, $state) => $record->user?->email ?? '-')
                                    ->disabled(),
                                Forms\Components\TextInput::make('user.name')
                                    ->label('Nama User')
                                    ->formatStateUsing(fn($record, $state) => $record->user?->name ?? '-')
                                    ->disabled(),
                            ]),
                        Forms\Components\Section::make('Penerima')
                            ->schema([
                                Forms\Components\TextInput::make('recipient_name')
                                    ->label('Nama Penerima')
                                    ->disabled(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->disabled(),
                                Forms\Components\Textarea::make('shipping_address')
                                    ->label('Alamat Pengiriman')
                                    ->disabled(),

                                Forms\Components\TextInput::make('provinsi_name')
                                    ->label('Provinsi')
                                    ->disabled(),
                                Forms\Components\TextInput::make('kabupaten_name')
                                    ->label('Kabupaten')
                                    ->disabled(),
                                Forms\Components\TextInput::make('kecamatan_name')
                                    ->label('Kecamatan')
                                    ->disabled(),
                            
                                Forms\Components\Textarea::make('noted')
                                    ->label('Catatan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('delivery_date')
                                    ->label('Tanggal')
                                    ->disabled(),
                                Forms\Components\TextInput::make('delivery_time')
                                    ->label('Waktu pemakaian/pengiriman')
                                    ->disabled(),
                            ]),
                          Forms\Components\Section::make('Produk Dipesan')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('product_name')
                                        ->label('Nama Produk')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->disabled(),
                                    // Forms\Components\TextInput::make('price')
                                    //     ->label('Harga')
                                    //     ->numeric()
                                    //     ->disabled()
                                    //     ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                                ])
                                ->disabled() // agar user tidak bisa edit
                                ->columns(3)
                                ->columnSpan('full'),
                        ])

                                        

                    ]),


                        Forms\Components\Group::make()
                        ->schema([
                        Forms\Components\Section::make('Detail Harga')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->disabled()
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Biaya Pengiriman')
                                    ->disabled(
                                        fn(Forms\Get $get) =>
                                        $get('payment_status') == OrderStatusService::PAYMENT_PAID
                                    )
                                    ->numeric(),
                                    

                                    
                              
                                    Forms\Components\TextInput::make('price_adjustment')
                                    ->label('Biaya tambahan ketika custom')
                                    ->disabled(fn (Forms\Get $get) => !$get('is_custom_catering'))
                                    ->numeric(),
                                
                                
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total Pembayaran')
                                    ->disabled()
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Forms\Components\Section::make('Status Order')
                            ->schema([
                                Forms\Components\TextInput::make('payment_gateway_transaction_id')
                                    ->label('Url Pembayaran')
                                    ->disabled()
                                    ->visible(
                                        fn() =>
                                        Store::first()->is_use_payment_gateway == true
                                    ),
                                Forms\Components\FileUpload::make('payment_proof')
                                    ->label('Bukti Pembayaran')
                                    ->image()
                                    ->disk('public')
                                    ->directory('payment-proofs')
                                    ->visible(
                                        fn($record) =>
                                        $record?->payment_gateway_transaction_id == null &&
                                        Store::first()->is_use_payment_gateway == false &&
                                        $record?->payment_proof !== null
                                    )
                                    ->disabled(),
                                Forms\Components\Select::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        OrderStatusService::PAYMENT_UNPAID => OrderStatusService::getPaymentStatusLabel(OrderStatusService::PAYMENT_UNPAID),
                                        OrderStatusService::PAYMENT_PAID => OrderStatusService::getPaymentStatusLabel(OrderStatusService::PAYMENT_PAID),
                                    ])
                                    ->required()
                                    ->live()
                                    ->disabled(
                                        fn($record) =>
                                        $record?->snap_token != null
                                    ),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        OrderStatusService::STATUS_CHECKING => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_CHECKING),
                                        OrderStatusService::STATUS_PENDING => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_PENDING),
                                        OrderStatusService::STATUS_PROCESSING => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_PROCESSING),
                                        OrderStatusService::STATUS_SHIPPED => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_SHIPPED),
                                        OrderStatusService::STATUS_COMPLETED => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_COMPLETED),
                                        OrderStatusService::STATUS_CANCELLED => OrderStatusService::getStatusLabel(OrderStatusService::STATUS_CANCELLED),
                                    ])
                                    ->required()
                                    ->live(),
                            ]),
                            Forms\Components\Repeater::make('customCatering')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('menu_description')
                                    ->label('Deskripsi Menu'),
                            ])
                            

                            
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disabled()
                            ->visible(fn($record) => $record?->is_custom_catering == true),
                                
                                            
                                    ])
                                
                                  
                                  
                     
       


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->created_at->format('d M Y (H:i)')),
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->phone),
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderStatusService::PAYMENT_UNPAID => 'danger',
                        OrderStatusService::PAYMENT_PAID => 'success',
                    })
                    ->formatStateUsing(fn($state) => OrderStatusService::getPaymentStatusLabel($state)),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderStatusService::STATUS_CHECKING => 'gray',
                        OrderStatusService::STATUS_PENDING => 'warning',
                        OrderStatusService::STATUS_PROCESSING => 'info',
                        OrderStatusService::STATUS_SHIPPED => 'primary',
                        OrderStatusService::STATUS_COMPLETED => 'success',
                        OrderStatusService::STATUS_CANCELLED => 'danger',
                    })
                    ->formatStateUsing(fn($state) => OrderStatusService::getStatusLabel($state)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                // Tables\Filters\TernaryFilter::make('is_custom_catering')
                //     ->label('Custom Catering')
                //     ->placeholder('Semua Pesanan')
                //     ->trueLabel('Hanya Custom Catering')
                //     ->falseLabel('Tanpa Custom Catering'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
               Tables\Actions\Action::make('generate_invoice')
        ->label('Invoice')
        ->icon('heroicon-o-document-text')
        ->color('success')
        ->requiresConfirmation()
        ->visible(fn($record) => $record->payment_status == OrderStatusService::PAYMENT_PAID)
        ->action(function (Order $record) {
            $pdf = app('dompdf.wrapper');
        $pdf->loadView('livewire.invoice', ['order' => $record]);


        return response()->streamDownload(
                fn() => print($pdf->output()),
                'invoice-' . $record->order_number . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
            
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
        ];
    }
}
