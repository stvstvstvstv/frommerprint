<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Material;
use App\Models\Bom;
use App\Services\BomCostCalculator;

class BomEditor extends Component
{
    public Bom $bom;

    public int $previewQuantity = 24;

    public array $items = [];
    public array $packaging = [];

    public array $costPreview = [];

    public function mount(Material $material)
    {
        $this->bom = $material->bomAsFinished()
            ->with([
                'items.componentMaterial',
                'packagingRule.innerPackMaterial',
                'packagingRule.outerPackMaterial',
            ])
            ->firstOrFail();

        $this->hydrateState();
    }

    protected function hydrateState(): void
    {
        $this->items = $this->bom->items->map(fn ($item) => [
            'id' => $item->id,
            'role' => $item->role->value,
            'material_id' => $item->component_material_id,
            'material_name' => $item->componentMaterial->name,
            'quantity' => (float) $item->quantity,
        ])->toArray();

        $this->packaging = [
            'inner_material_name' => $this->bom->packagingRule?->innerPackMaterial?->name,
            'inner_qty' => $this->bom->packagingRule?->inner_pack_qty,
            'outer_material_name' => $this->bom->packagingRule?->outerPackMaterial?->name,
            'outer_qty' => $this->bom->packagingRule?->outer_pack_qty,
        ];

        $this->recalculate();
    }

    public function recalculate(): void
    {
        $bom = Bom::with([
            'items.componentMaterial',
            'packagingRule.innerPackMaterial',
            'packagingRule.outerPackMaterial',
        ])->find($this->bom->id);

        // Override quantities from UI state
        foreach ($bom->items as $item) {
            $uiItem = collect($this->items)->firstWhere('id', $item->id);
            if ($uiItem) {
                $item->quantity = $uiItem['quantity'];
            }
        }

        $calc = new BomCostCalculator($bom, $this->previewQuantity);
        $this->costPreview = $calc->calculate();
    }

    public function render()
    {
        return view('livewire.bom-editor')
            ->layout('layouts.app');
    }

    public function save(): void
    {
        foreach ($this->items as $uiItem) {
            if (! isset($uiItem['id'])) {
                continue;
            }

            $bomItem = $this->bom->items->firstWhere('id', $uiItem['id']);

            if (! $bomItem) {
                continue;
            }

            $bomItem->quantity = $uiItem['quantity'];
            $bomItem->save();
        }

        // Reload BOM + relations to stay consistent
        $this->bom->load([
            'items.componentMaterial',
            'packagingRule.innerPackMaterial',
            'packagingRule.outerPackMaterial',
        ]);

        $this->hydrateState();

        session()->flash('status', 'BOM saved successfully.');
    }
}
