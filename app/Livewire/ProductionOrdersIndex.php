<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Bom;
use App\Models\BomYieldRule;
use App\Models\Material;
use App\Models\ProductionOrder;
use App\Services\BomCostCalculator;

class ProductionOrdersIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function startProduction(int $id): void
    {
        $order = ProductionOrder::findOrFail($id);
        abort_if(! $order->isDraft(), 403);

        $totalCost = $this->computeCost($order);

        $order->update([
            'status'               => ProductionOrder::STATUS_IN_PRODUCTION,
            'confirmed_total_cost' => $totalCost,
            'started_at'           => now(),
        ]);

        // TODO: reserve raw material stock (jumbo, ink, foil, box)

        session()->flash('status', __('orders.confirmed', ['id' => $id]));
    }

    public function complete(int $id): void
    {
        $order = ProductionOrder::findOrFail($id);
        abort_if(! $order->isInProduction(), 403);

        $order->update([
            'status'       => ProductionOrder::STATUS_COMPLETE,
            'completed_at' => now(),
        ]);

        // TODO: add finished product to stock

        session()->flash('status', __('orders.completed', ['id' => $id]));
    }

    public function cancel(int $id): void
    {
        $order = ProductionOrder::findOrFail($id);
        abort_if($order->isCancelled() || $order->isComplete(), 403);

        $order->update(['status' => ProductionOrder::STATUS_CANCELLED]);

        session()->flash('status', __('orders.cancelled', ['id' => $id]));
    }

    // -------------------------------------------------------------------------

    private function computeCost(ProductionOrder $order): float
    {
        $finished = Material::find($order->finished_material_id);
        $jumbo    = Material::find($order->jumbo_material_id);

        if (! $finished || ! $jumbo) {
            return 0.0;
        }

        $bom = Bom::with(['items.componentMaterial', 'yieldRule'])
            ->where('finished_material_id', $order->finished_material_id)
            ->first();

        if (! $bom) {
            $bom = new Bom(['setup_time_minutes' => 0]);
            $bom->setRelation('items', collect([]));
        }

        $bom->setRelation('finishedMaterial', $finished);

        $yieldRule = $bom->yieldRule ?? new BomYieldRule();
        $yieldRule->preferred_jumbo_material_id = $jumbo->id;
        if ($order->override_ink_kg_per_m2)  $yieldRule->ink_kg_per_m2  = $order->override_ink_kg_per_m2;
        $yieldRule->foil_per_roll = $order->override_foil_per_roll;
        $yieldRule->rolls_per_box = $order->override_rolls_per_box;

        $yieldRule->setRelation('preferredJumboMaterial', $jumbo);
        $yieldRule->setRelation('inkMaterial',
            ($yieldRule->ink_kg_per_m2 && $yieldRule->ink_material_id)
                ? Material::find($yieldRule->ink_material_id) : null);
        $yieldRule->setRelation('tubeMaterial',
            $yieldRule->tube_material_id
                ? Material::find($yieldRule->tube_material_id) : null);
        $yieldRule->setRelation('foilMaterial',
            ($yieldRule->foil_per_roll > 0 && $yieldRule->foil_material_id)
                ? Material::find($yieldRule->foil_material_id) : null);
        $yieldRule->setRelation('boxMaterial',
            ($yieldRule->rolls_per_box > 0 && $yieldRule->box_material_id)
                ? Material::find($yieldRule->box_material_id) : null);

        $bom->setRelation('yieldRule', $yieldRule);

        return (new BomCostCalculator($bom, $order->planned_rolls))->calculate()['total_cost'];
    }

    public function render()
    {
        $orders = ProductionOrder::with(['finishedMaterial', 'jumboMaterial'])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.production-orders-index', ['orders' => $orders])
            ->layout('layouts.app');
    }
}
