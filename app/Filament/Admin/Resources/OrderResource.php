<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Admin\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Admin\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Filament\Admin\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;

use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order information')->schema([
                        Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user', 'name')
                        ->preload()
                        ->searchable()
                        ->required(),

                        Select::make('payment_method')
                        ->options([
                            'orange'=>'Orange ',
                            'mtn'=>'MTN',
                            'cod'=>'cash on delivery'
                        ])->required(),

                        Select::make('payment_status')
                        ->options([
                            'pending'=>'Pending ',
                            'paid'=>'Paid',
                            'failed'=>'Failed',
                        ])
                        ->default('pending')
                          ->required(),

                          ToggleButtons::make('status')
                          ->inline()
                          ->default('new')
                          ->required()
                          ->options([
                            'new'=>'New',
                            'processing'=>'Processing',
                            'shipped'=>'Shipped',
                            'delivered'=>'Delivered',
                            'cancelled'=>'Cancelled',
                          ])
                          ->colors([
                            'new'=>'info',
                            'processing'=>'warning',
                            'shipped'=>'success',
                            'delivered'=>'success',
                            'cancelled'=>'danger',
                          ])
                          ->icons([
                            'new'=>'heroicon-m-sparkles',
                            'processing'=>'heroicon-m-arrow-path',
                            'shipped'=>'heroicon-m-truck',
                            'delivered'=>'heroicon-m-check-badge',
                            'cancelled'=>'heroicon-m-x-circle',
                          ]),

                          Select::make('currency')
                          ->options([
                          'fcfa'=>'FCFA',
                          'usd'=>'USD',
                          'eur'=>'EUR',
                          'gbp'=>'GBP',
                          ])
                          ->default('fcfa')
                          ->required(),

                          Select::make('shipping_method')
                          ->options([
                          'fedex'=>'FedEx',
                          'ups'=>'UPS',
                          'eur'=>'EUR',
                          'gbp'=>'GBP',
                          ])
                          ->default('fedex'),

                            Textarea::make('notes')
                            ->columnSpanFull()
                      ])->columns(2),

                      Section::make('Order Items')->schema([
                        Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                             ->required()
                             ->distinct()
                             ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                             ->columnSpan(4)
                             ->reactive()
                             ->afterStateUpdated(fn ($state, Set $set) => $set('unit_amount', Product::find($state)
                             ?->price ?? 0))
                             ->afterStateUpdated(fn ($state, Set $set) => $set('total_amount', Product::find($state)
                             ?->price ?? 0)),
                             
                             TextInput::make('quantity')
                             ->numeric()
                             ->required()
                             ->default(1)
                             ->minValue(1)
                             ->columnSpan(2)
                             ->reactive()
                             ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total_amount',$state * $get('unit_amount'))),
                             
                             
                             TextInput::make('unit_amount')
                             ->numeric()
                             ->required()
                             ->disabled(1)
                             ->dehydrated()
                             ->columnSpan(3),
                             
                             TextInput::make('total_amount')
                             ->numeric()
                             ->required()
                             ->dehydrated()
                             ->columnSpan(3) 
                             
                        ])->columns(12),
                        Placeholder::make('grand_total_placeholder')
                        ->label('Grand Total')
                        ->content(function (Get $get, Set $set){
                            $total = 0;

                            if(!$repeaters = $get('items')){
                                return $total;
                            } 
                            foreach ($repeaters as $key => $repeater) {
                                $total +=$get("items.{$key}.total_amount");   
                            }

                            $set('grand_total', $total);
                            
                            return Number::currency($total, 'XAF');
                        }),

                        Hidden::make('grand_total')
                            ->default(0),
                      ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                ->label('Customer')
                ->sortable()
                ->searchable(),

                TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->money('XAF'),

                TextColumn::make('payment_method')
                ->sortable()      
                ->searchable(),

                TextColumn::make('payment_status')
                ->sortable()      
                ->searchable(),

                TextColumn::make('currency')
                ->sortable()      
                ->searchable(),

                TextColumn::make('shipping_method')
                ->sortable()      
                ->searchable(),

                SelectColumn::make('status')
                ->options([
                    'new'=>'New',
                    'processing'=>'Processing',
                    'shipped'=>'Shipped',
                    'delivered'=>'Delivered',
                    'cancelled'=>'Cancelled',
                ])
                ->sortable()      
                ->searchable(),

                
                TextColumn::make('created_at')
                ->sortable()      
                ->toggleable()      
                ->dateTime(),

                TextColumn::make('updated_at')
                ->sortable()      
                ->toggleable()      
                ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string{
    return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string{
    return static::getModel()::count() > 10 ? 'danger': 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
