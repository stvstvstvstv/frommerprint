<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Material;
use App\Models\Bom;
use App\Models\BomYieldRule;
use App\Services\BomCostCalculator;

class BomEditor extends Component
{
    public Bom $bom;

    public int $previewQuantity = 24;

    /** Non-geometric BOM items (machine, tube, graphic, other) */
    public array $items = [];

    /** Yield rule fields, editable in the form */
    public array $yieldRule = [
        'preferred_jumbo_material_id' => null,
        'paper_waste_pct'             => 1.00,
        'ink_kg_per_m2'               => null,
        'ink_material_id'             => null,
        'foil_per_roll'               => 0,
        'rolls_per_box'               => 0,
        'foil_material_id'            => null,
        'box_material_id'             => null,
    ];

    public array $costPreview = [];

    // Material option lists for selects
    public array $jumboOptions     = [];
    public array $inkOptions       = [];
    public array $packagingOptions = [];

    public function mount(Material $material)
    {
        $this->bom = $material->bomAsFinished()
            ->with([
                'items.componentMaterial',
                'yieldRule.preferredJumboMaterial',
                'yieldRule.inkMaterial',
                'yieldRule.foilMaterial',
                'yieldRule.boxMaterial',
                'finishedMaterial',
            ])
            ->firstOrFail();

        $this->loadMaterialOptions();
        $this->hydrateState();
    }

    protected function loadMaterialOptions(): void
    {
        $this->jumboOptions = Material::where('material_type', 'raw')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'width', 'length', 'gsm'])
            ->toArray();

        $this->inkOptions = Material::where('material_type', 'raw')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->packagingOptions = Material::where('material_type', 'packaging')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    protected function hydrateState(): void
    {
        // Only expose non-geometric items as editable
        $this->items = $this->bom->items
            ->filter(fn ($item) => ! in_array($item->role->value, ['paper', 'ink', 'jumbo', 'foil', 'box']))
            ->map(fn ($item) => [
                'id'            => $item->id,
                'role'          => $item->role->value,
                'material_name' => $item->componentMaterial->name,
                'quantity'      => (float) $item->quantity,
            ])
            ->values()
            ->toArray();

        $rule = $this->bom->yieldRule;

        $this->yieldRule = [
            'preferred_jumbo_material_id' => $rule?->preferred_jumbo_material_id,
            'paper_waste_pct'             => $rule?->paper_waste_pct ?? 1.00,
            'ink_kg_per_m2'               => $rule?->ink_kg_per_m2,
            'ink_material_id'             => $rule?->ink_material_id,
            'foil_per_roll'               => $rule?->foil_per_roll ?? 0,
            'rolls_per_box'               => $rule?->rolls_per_box ?? 0,
            'foil_material_id'            => $rule?->foil_material_id,
            'box_material_id'             => $rule?->box_material_id,
        ];

        $this->recalculate();
    }

    public function recalculate(): void
    {
        $bom = Bom::with([
            'items.componentMaterial',
            'yieldRule.preferredJumboMaterial',
            'yieldRule.inkMaterial',
            'yieldRule.foilMaterial',
            'yieldRule.boxMaterial',
            'finishedMaterial',
        ])->find($this->bom->id);

        // Apply live item quantities from UI
        foreach ($bom->items as $item) {
            $uiItem = collect($this->items)->firstWhere('id', $item->id);
            if ($uiItem) {
                $item->quantity = $uiItem['quantity'];
            }
        }

        // Overlay live yield rule values so the preview reflects unsaved edits
        if (! $bom->yieldRule) {
            $bom->setRelation('yieldRule', new BomYieldRule($this->yieldRule));
        } else {
            foreach ($this->yieldRule as $key => $value) {
                $bom->yieldRule->{$key} = $value;
            }
        }

        // Re-resolve FK relations that may have changed in the UI
        $bom->yieldRule->setRelation(
            'preferredJumboMaterial',
            $this->yieldRule['preferred_jumbo_material_id']
                ? Material::find($this->yieldRule['preferred_jumbo_material_id'])
                : null
        );
        $bom->yieldRule->setRelation(
            'inkMaterial',
            $this->yieldRule['ink_material_id']
                ? Material::find($this->yieldRule['ink_material_id'])
                : null
        );
        $bom->yieldRule->setRelation(
            'foilMaterial',
            $this->yieldRule['foil_material_id']
                ? Material::find($this->yieldRule['foil_material_id'])
                : null
        );
        $bom->yieldRule->setRelation(
            'boxMaterial',
            $this->yieldRule['box_material_id']
                ? Material::find($this->yieldRule['box_material_id'])
                : null
        );

        $calc = new BomCostCalculator($bom, $this->previewQuantity);
        $this->costPreview = $calc->calculate();
    }

    public function save(): void
    {
        // Save non-geometric BOM item quantities
        foreach ($this->items as $uiItem) {
            if (! isset($uiItem['id'])) {
                continue;
            }
            $bomItem = $this->bom->items->firstWhere('id', $uiItem['id']);
            if ($bomItem) {
                $bomItem->quantity = $uiItem['quantity'];
                $bomItem->save();
            }
        }

        // Upsert yield rule
        BomYieldRule::updateOrCreate(
            ['bom_id' => $this->bom->id],
            $this->yieldRule
        );

        // Reload full state
        $this->bom->load([
            'items.componentMaterial',
            'yieldRule.preferredJumboMaterial',
            'yieldRule.inkMaterial',
            'yieldRule.foilMaterial',
            'yieldRule.boxMaterial',
            'finishedMaterial',
        ]);

        $this->hydrateState();

        session()->flash('status', 'BOM saved successfully.');
    }

    public function render()
    {
        return view('livewire.bom-editor')
            ->layout('layouts.app');
    }
}
