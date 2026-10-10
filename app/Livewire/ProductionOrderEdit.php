<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Bom;
use App\Models\BomYieldRule;
use App\Models\Material;
use App\Models\ProductionOrder;
use App\Services\BomCostCalculator;

class ProductionOrderEdit extends Component
{
    public ProductionOrder $order;

    // Finished-material combobox state
    public string  $finishedSearch        = '';
    public array   $finishedSearchResults = [];
    public bool    $showFinishedDropdown  = false;
    public ?int    $finishedMaterialId    = null;
    public ?string $selectedFinishedName  = null;
    public ?string $selectedFinishedCode  = null;
    public ?array  $selectedFinishedDims  = null;

    public ?int   $jumboMaterialId     = null;
    public int    $plannedRolls        = 1;
    public string $client              = '';
    public ?float $overrideInkKgPerM2  = null;
    public int    $overrideFoilPerRoll = 0;
    public int    $overrideRollsPerBox = 0;

    public array   $costPreview  = [];
    public array   $jumboOptions = [];
    public ?string $warning      = null;

    // -------------------------------------------------------------------------

    public function mount(ProductionOrder $order): void
    {
        abort_if(! $order->isDraft(), 403);

        $this->jumboOptions = Material::where('material_type', 'jumbo')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'width', 'gsm', 'price'])
            ->toArray();

        $this->finishedSearchResults = $this->queryFinished('');

        // Pre-populate from existing order
        $finished = $order->finishedMaterial;
        if ($finished) {
            $this->finishedMaterialId   = $finished->id;
            $this->selectedFinishedName = $finished->name;
            $this->selectedFinishedCode = $finished->code;
            $this->selectedFinishedDims = [
                'width'  => $finished->width,
                'length' => $finished->length,
                'gsm'    => $finished->gsm,
            ];
        }

        $this->jumboMaterialId     = $order->jumbo_material_id;
        $this->plannedRolls        = $order->planned_rolls;
        $this->client              = $order->client ?? '';
        $this->overrideInkKgPerM2  = $order->override_ink_kg_per_m2 ? (float) $order->override_ink_kg_per_m2 : null;
        $this->overrideFoilPerRoll = $order->override_foil_per_roll ?? 0;
        $this->overrideRollsPerBox = $order->override_rolls_per_box ?? 0;

        $this->recalculate();
    }

    // -------------------------------------------------------------------------
    // Combobox methods (identical to ProductionOrderCreate)

    public function openFinishedDropdown(): void
    {
        $this->showFinishedDropdown  = true;
        $this->finishedSearchResults = $this->queryFinished($this->finishedSearch);
    }

    public function closeFinishedDropdown(): void
    {
        $this->showFinishedDropdown = false;
    }

    public function updatedFinishedSearch(): void
    {
        $this->finishedSearchResults = $this->queryFinished($this->finishedSearch);
    }

    private function queryFinished(string $search): array
    {
        $query = Material::where('material_type', 'finished')->where('active', true);

        $terms = array_filter(explode(' ', trim($search)));
        foreach ($terms as $term) {
            $t = '%' . $term . '%';
            $query->where(function ($q) use ($t) {
                $q->where('name',    'like', $t)
                  ->orWhere('code',    'like', $t)
                  ->orWhere('saga_id', 'like', $t);
            });
        }

        return $query->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'code', 'saga_id', 'width', 'length', 'gsm'])
            ->toArray();
    }

    public function selectFinishedMaterial(int $id): void
    {
        $material = Material::findOrFail($id);

        $this->finishedMaterialId   = $material->id;
        $this->selectedFinishedName = $material->name;
        $this->selectedFinishedCode = $material->code;
        $this->selectedFinishedDims = [
            'width'  => $material->width,
            'length' => $material->length,
            'gsm'    => $material->gsm,
        ];
        $this->finishedSearch       = '';
        $this->showFinishedDropdown = false;
        $this->warning              = null;

        $bom = Bom::with('yieldRule')
            ->where('finished_material_id', $id)
            ->first();

        if ($bom?->yieldRule) {
            $rule = $bom->yieldRule;
            $this->jumboMaterialId     = $rule->preferred_jumbo_material_id;
            $this->overrideInkKgPerM2  = $rule->ink_kg_per_m2 ? (float) $rule->ink_kg_per_m2 : null;
            $this->overrideFoilPerRoll = $rule->foil_per_roll ?? 0;
            $this->overrideRollsPerBox = $rule->rolls_per_box ?? 0;
        } else {
            $this->warning = __('orders.NoBOMYieldWarning');
        }

        $this->recalculate();
    }

    public function clearFinishedMaterial(): void
    {
        $this->finishedMaterialId    = null;
        $this->selectedFinishedName  = null;
        $this->selectedFinishedCode  = null;
        $this->selectedFinishedDims  = null;
        $this->finishedSearch        = '';
        $this->showFinishedDropdown  = false;
        $this->finishedSearchResults = $this->queryFinished('');
        $this->costPreview           = [];
        $this->warning               = null;
    }

    // -------------------------------------------------------------------------

    public function updatedJumboMaterialId(): void    { $this->recalculate(); }
    public function updatedPlannedRolls(): void        { $this->recalculate(); }
    public function updatedOverrideInkKgPerM2(): void  { $this->recalculate(); }
    public function updatedOverrideFoilPerRoll(): void { $this->recalculate(); }
    public function updatedOverrideRollsPerBox(): void { $this->recalculate(); }

    public function recalculate(): void
    {
        $this->costPreview = [];

        if (! $this->finishedMaterialId || ! $this->jumboMaterialId || $this->plannedRolls < 1) {
            return;
        }

        $finished = Material::find($this->finishedMaterialId);
        $jumbo    = Material::find($this->jumboMaterialId);

        if (! $finished || ! $jumbo) {
            return;
        }

        $bom = Bom::with(['items.componentMaterial', 'yieldRule'])
            ->where('finished_material_id', $this->finishedMaterialId)
            ->first();

        if (! $bom) {
            $bom = new Bom(['setup_time_minutes' => 0]);
            $bom->setRelation('items', collect([]));
        }

        $bom->setRelation('finishedMaterial', $finished);

        $yieldRule = $bom->yieldRule ?? new BomYieldRule();
        $yieldRule->preferred_jumbo_material_id = $jumbo->id;
        $yieldRule->ink_kg_per_m2  = $this->overrideInkKgPerM2;
        $yieldRule->foil_per_roll  = $this->overrideFoilPerRoll;
        $yieldRule->rolls_per_box  = $this->overrideRollsPerBox;

        $yieldRule->setRelation('preferredJumboMaterial', $jumbo);
        $yieldRule->setRelation('inkMaterial',
            ($this->overrideInkKgPerM2 && $yieldRule->ink_material_id)
                ? Material::find($yieldRule->ink_material_id) : null);
        $yieldRule->setRelation('tubeMaterial',
            $yieldRule->tube_material_id
                ? Material::find($yieldRule->tube_material_id) : null);
        $yieldRule->setRelation('foilMaterial',
            ($this->overrideFoilPerRoll > 0 && $yieldRule->foil_material_id)
                ? Material::find($yieldRule->foil_material_id) : null);
        $yieldRule->setRelation('boxMaterial',
            ($this->overrideRollsPerBox > 0 && $yieldRule->box_material_id)
                ? Material::find($yieldRule->box_material_id) : null);

        $bom->setRelation('yieldRule', $yieldRule);

        $this->costPreview = (new BomCostCalculator($bom, $this->plannedRolls))->calculate();
    }

    // -------------------------------------------------------------------------

    private function validationRules(): array
    {
        return [
            'finishedMaterialId'  => 'required|exists:materials,id',
            'jumboMaterialId'     => 'required|exists:materials,id',
            'plannedRolls'        => 'required|integer|min:1',
            'client'              => 'nullable|string|max:255',
            'overrideInkKgPerM2'  => 'nullable|numeric|min:0',
            'overrideFoilPerRoll' => 'required|integer|min:0',
            'overrideRollsPerBox' => 'required|integer|min:0',
        ];
    }

    public function save(): void
    {
        abort_if(! $this->order->isDraft(), 403);
        $this->validate($this->validationRules());

        $finished = Material::findOrFail($this->finishedMaterialId);
        $jumbo    = Material::findOrFail($this->jumboMaterialId);

        $this->order->update([
            'finished_material_id'       => $finished->id,
            'jumbo_material_id'          => $jumbo->id,
            'planned_rolls'              => $this->plannedRolls,
            'client'                     => $this->client ?: null,
            'snapshot_finished_width_mm' => $finished->width,
            'snapshot_finished_length_m' => $finished->length,
            'snapshot_jumbo_width_mm'    => $jumbo->width,
            'snapshot_jumbo_gsm'         => $jumbo->gsm,
            'snapshot_jumbo_cost_per_m'  => $jumbo->price,
            'override_ink_kg_per_m2'     => $this->overrideInkKgPerM2,
            'override_foil_per_roll'     => $this->overrideFoilPerRoll,
            'override_rolls_per_box'     => $this->overrideRollsPerBox,
        ]);

        session()->flash('status', __('orders.updated', ['id' => $this->order->id]));
        $this->redirect(route('production-orders.index'));
    }

    public function confirmOrder(): void
    {
        abort_if(! $this->order->isDraft(), 403);
        $this->validate($this->validationRules());

        $finished = Material::findOrFail($this->finishedMaterialId);
        $jumbo    = Material::findOrFail($this->jumboMaterialId);

        $this->order->update([
            'finished_material_id'       => $finished->id,
            'jumbo_material_id'          => $jumbo->id,
            'planned_rolls'              => $this->plannedRolls,
            'client'                     => $this->client ?: null,
            'snapshot_finished_width_mm' => $finished->width,
            'snapshot_finished_length_m' => $finished->length,
            'snapshot_jumbo_width_mm'    => $jumbo->width,
            'snapshot_jumbo_gsm'         => $jumbo->gsm,
            'snapshot_jumbo_cost_per_m'  => $jumbo->price,
            'override_ink_kg_per_m2'     => $this->overrideInkKgPerM2,
            'override_foil_per_roll'     => $this->overrideFoilPerRoll,
            'override_rolls_per_box'     => $this->overrideRollsPerBox,
            'status'                     => ProductionOrder::STATUS_IN_PRODUCTION,
            'confirmed_total_cost'       => $this->costPreview['total_cost'] ?? null,
            'started_at'                 => now(),
        ]);

        session()->flash('status', __('orders.confirmed', ['id' => $this->order->id]));
        $this->redirect(route('production-orders.index'));
    }

    public function render()
    {
        return view('livewire.production-order-edit')
            ->layout('layouts.app');
    }
}
