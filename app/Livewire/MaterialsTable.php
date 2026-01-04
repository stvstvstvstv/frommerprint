<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Material;
use App\Models\Unit;

class MaterialsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $search = '';
    public string $type = '';

    // Selected material for details or editing
    public ?int $selectedMaterialId = null;
    public bool $editMode = false;

    // pricing fields
    public ?float $price = null;
    public string $currency = 'EUR';
    public ?int $pricingUnitId = null;
    public int $pricingPer = 1;

    // Form state
    public ?int $editingId = null;

    public string $code = '';
    public string $name = '';
    public string $material_type = '';
    public int|string|null $unit_id = null;
    public bool $active = true;
    public bool $showForm = false;
    public ?int $deleteId = null;

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    // Validation rules
    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:materials,code,' . $this->editingId,
            'name' => 'required|string|max:255',
            'material_type' => 'required|in:raw,finished,packaging',
            'unit_id' => 'required|exists:units,id',
            'active' => 'boolean',
        ];
    }

        protected function pricingRules(): array
        {
            return [
                'price' => 'nullable|numeric|min:0',
                'currency' => 'required|size:3',
                'pricingPer' => 'required|integer|min:1',
                'pricingUnitId' => 'required|exists:units,id',
            ];
        }


    // Create or update
    public function save()
    {
        $data = $this->validate();

        Material::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->resetForm();
    }

    // Save pricing information (or cancel)
    public function savePricing(): void
    {
        $this->validate($this->pricingRules());

        $material = Material::findOrFail($this->selectedMaterialId);

        $material->update([
            'price' => $this->price,
            'currency' => $this->currency,
            'pricing_unit_id' => $this->pricingUnitId,
            'pricing_per' => $this->pricingPer,
        ]);

        $this->editMode = false;
    }

    public function cancelPricingEdit(): void
    {
        $this->editMode = false;
    }

    // Load material for editing
    public function edit(int $id)
    {
        $material = Material::findOrFail($id);

        $this->editingId = $material->id;
        $this->code = $material->code;
        $this->name = $material->name;
        $this->material_type = $material->material_type;
        $this->unit_id = $material->unit_id;
        $this->active = (bool) $material->active;

        $this->showForm = true;
    }

    // Reset form state
    public function resetForm()
    {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->material_type = '';
        $this->unit_id = null;
        $this->active = true;

        $this->showForm = false;
    }

    public function clearPricingState(): void
    {
        $this->price = null;
        $this->currency = 'EUR';
        $this->pricingUnitId = null;
        $this->pricingPer = 1;
    }

    // Create
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    // Delete
    //public function delete(int $id)
    //{
    //    Material::findOrFail($id)->delete();
    //}

    //public function confirmDelete(int $id)
    //{
    //    $this->deleteId = $id;
    //}

    //public function deleteConfirmed()
    //{
    //    Material::findOrFail($this->deleteId)->delete();
    //    $this->deleteId = null;
    //}

    // Render table
    public function render()
    {
        $materials = Material::with('unit')
            ->when($this->type !== '', fn ($q) =>
                $q->where('material_type', $this->type)
            )
            ->when($this->search !== '', fn ($q) =>
                $q->where(fn ($qq) =>
                    $qq->where('code', 'like', '%' . $this->search . '%')
                       ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.materials-table', [
            'materials' => $materials,
            'units' => Unit::orderBy('code')->get(),
        ]);
    }

    // Material Selection code
    public function selectMaterial(int $id): void
    {
        $material = Material::findOrFail($id);

        $this->selectedMaterialId = $material->id;
        $this->editMode = false;

        $this->price = $material->price;
        $this->currency = $material->currency ?? 'EUR';
        $this->pricingUnitId = $material->pricing_unit_id;
        $this->pricingPer = $material->pricing_per ?? 1;
    }
}