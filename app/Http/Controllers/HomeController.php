<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Material;
use App\Models\ProductionOrder;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Testing dashboard: exposes every screen in the app plus a few
     * counts so it's obvious what data exists. Roles/navigation later.
     */
    public function index()
    {
        $materialCounts = Material::selectRaw('material_type, count(*) as c')
            ->groupBy('material_type')
            ->pluck('c', 'material_type');

        $orderCounts = ProductionOrder::selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $boms = Bom::with(['finishedMaterial', 'yieldRule.preferredJumboMaterial'])
            ->withCount('items')
            ->get()
            ->sortBy(fn ($bom) => $bom->finishedMaterial?->name)
            ->values();

        $recentOrders = ProductionOrder::with('finishedMaterial')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'materialCounts'  => $materialCounts,
            'orderCounts'     => $orderCounts,
            'boms'            => $boms,
            'recentOrders'    => $recentOrders,
            'currencyCount'   => Currency::count(),
            'latestRateDate'  => ExchangeRate::max('rate_date'),
            'userCount'       => User::count(),
            'orderStatuses'   => [
                ProductionOrder::STATUS_DRAFT,
                ProductionOrder::STATUS_IN_PRODUCTION,
                ProductionOrder::STATUS_COMPLETE,
                ProductionOrder::STATUS_CANCELLED,
            ],
        ]);
    }
}
