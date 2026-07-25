<?php

declare(strict_types=1);

namespace BezhanSalleh\FilamentShield\Tests\Fixtures;

use BezhanSalleh\FilamentShield\Tests\Fixtures\Pages\Reports;
use BezhanSalleh\FilamentShield\Tests\Fixtures\Resources\ArticleResource;
use BezhanSalleh\FilamentShield\Tests\Fixtures\Widgets\RevenueChart;
use Filament\Panel;
use Filament\PanelProvider;

class CataloguePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('catalogue')
            ->path('catalogue')
            ->resources([ArticleResource::class])
            ->pages([Reports::class])
            ->widgets([RevenueChart::class]);
    }
}
