<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];  
    }

    public function getHeaderWidgets():array
    {
        return[
            OrderStats::class
        ];
     }

    // public function getFooterWidgets():array
    // {
    //     return[
    //         OrderStats::class
    //     ];
    //  }

    public function getTabs():array
    {
        return[
            null=>Tab::make('All'),
            'new '=>Tab::make()->query(fn ($query)=> $query->where('status', 'new')),
            'processing '=>Tab::make()->query(fn ($query)=> $query->where('status', 'processing')),
            'shipped '=>Tab::make()->query(fn ($query)=> $query->where('status', 'shipped')),
            'delivered '=>Tab::make()->query(fn ($query)=> $query->where('status', 'delivered')),
            'cancelled '=>Tab::make()->query(fn ($query)=> $query->where('status', 'cancelled')),
        ];
     }
}
