<?php

declare(strict_types=1);

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\FilamentShield as FilamentShieldManager;
use BezhanSalleh\FilamentShield\Tests\Fixtures\CataloguePanelProvider;
use BezhanSalleh\FilamentShield\Tests\Fixtures\Pages\Reports;
use BezhanSalleh\FilamentShield\Tests\Fixtures\Widgets\RevenueChart;
use Filament\Facades\Filament;
use Filament\PanelRegistry;

describe('permission catalogue through the real transformers', function () {
    beforeEach(function () {
        $this->app->register(CataloguePanelProvider::class);
        $this->app->forgetInstance(PanelRegistry::class);

        declareAppModel('App\Models\Blog\Article');

        Filament::setCurrentPanel('catalogue');
    });

    it('builds the catalogue from real panel entities', function () {
        expect(FilamentShield::getEntitiesPermissions())
            ->toContain('ViewAny:Article')
            ->toContain('View:Reports')
            ->toContain('View:RevenueChart');
    });

    it('does not leak page or widget class names', function () {
        expect(FilamentShield::getEntitiesPermissions())
            ->not->toContain(Reports::class)
            ->not->toContain(RevenueChart::class);
    });

    it('includes custom permissions from the config', function () {
        config()->set('filament-shield.custom_permissions', ['Impersonate:User' => 'Impersonate User']);

        expect(FilamentShield::getEntitiesPermissions())->toContain('Impersonate:User');
    });
});

describe('catalogue assembly from transformed entities', function () {
    beforeEach(function () {
        $this->shield = new class extends FilamentShieldManager
        {
            public function getAllResourcePermissionsWithLabels(): array
            {
                return [
                    'View:Article' => 'View',
                    'Update:Article' => 'Update',
                ];
            }

            public function getPages(): ?array
            {
                return [
                    'App\Filament\Pages\Settings' => [
                        'pageFqcn' => 'App\Filament\Pages\Settings',
                        'permissions' => ['View:Settings' => 'Settings'],
                    ],
                ];
            }

            public function getWidgets(): ?array
            {
                return [
                    'App\Filament\Widgets\IncomeWidget' => [
                        'widgetFqcn' => 'App\Filament\Widgets\IncomeWidget',
                        'permissions' => ['View:IncomeWidget' => 'Income Widget'],
                    ],
                ];
            }

            public function getCustomPermissions(bool $localized = false): ?array
            {
                return ['Impersonate:User' => 'Impersonate User'];
            }
        };
    });

    it('assembles the keys of every entity type in merge order', function () {
        expect($this->shield->getEntitiesPermissions())
            ->toBe([
                'View:Article',
                'Update:Article',
                'View:Settings',
                'View:IncomeWidget',
                'Impersonate:User',
            ]);
    });

    it('keeps transformed entity class names out of the catalogue', function () {
        expect($this->shield->getEntitiesPermissions())
            ->not->toContain('App\Filament\Pages\Settings')
            ->not->toContain('App\Filament\Widgets\IncomeWidget');
    });

    it('skips entities without transformed permissions', function () {
        $shield = new class extends FilamentShieldManager
        {
            public function getAllResourcePermissionsWithLabels(): array
            {
                return ['View:Article' => 'View'];
            }

            public function getPages(): ?array
            {
                return ['App\Filament\Pages\Settings' => ['pageFqcn' => 'App\Filament\Pages\Settings']];
            }

            public function getWidgets(): ?array
            {
                return null;
            }

            public function getCustomPermissions(bool $localized = false): ?array
            {
                return null;
            }
        };

        expect($shield->getEntitiesPermissions())->toBe(['View:Article']);
    });
});
