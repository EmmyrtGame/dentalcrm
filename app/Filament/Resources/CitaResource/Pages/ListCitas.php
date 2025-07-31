<?php

namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use App\Filament\Resources\CitaResource\Widgets\CitaCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCitas extends ListRecords
{
    protected static string $resource = CitaResource::class;
    protected static ?string $breadcrumb = 'Calendario';

    // Añadir listeners
    protected $listeners = [
        'refreshTable' => 'refreshTable',
    ];

    public function refreshTable(): void
    {
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CitaCalendarWidget::class,
        ];
    }
}
