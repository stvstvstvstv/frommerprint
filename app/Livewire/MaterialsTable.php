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
    // public bool $editMode = false;
    public bool $isDirty = false;

    // pricing fields
    public ?float $price = null;
    public ?int $currencyId = null;
    public ?int $pricingUnitId = null;
    public int $pricingPer = 1;

    // Form state
    public ?int $editingId = null;

    public string $code = '';
    public string $name = '';
    public string $material_type = '';
    public string $material_name = '';
    public int|string|null $unit_id = null;
    public bool $active = true;
    public bool $showForm = false;
    public ?int $deleteId = null;

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetInspector();
    }

    public function updatedType()
    {
        $this->resetPage();
        $this->resetInspector();
    }

    public function updatedPage(): void
    {
        $this->resetInspector();
    }

    // Validation rules
    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:materials,code,' . $this->editingId,
            'name' => 'required|string|max:255',
            'material_type' => 'required|in:raw,finished,packaging, box,machine,tube,foil,ink,paper,other',
            'unit_id' => 'required|exists:units,id',
            'active' => 'boolean',

            // pricing (now mandatory for create/edit)
            'price' => 'nullable|numeric|min:0',
            'currencyId' => 'required|exists:currencies,id',
            'pricingPer' => 'required|integer|min:1',
            'pricingUnitId' => 'required|exists:units,id',
        ];
    }


    // Create or update
    public function save(): void
    {
        $data = $this->validate();

        Material::updateOrCreate(
            ['id' => $this->editingId],
            [
                'code' => $data['code'],
                'name' => $data['name'],
                'material_type' => $data['material_type'],
                'unit_id' => $data['unit_id'],
                'active' => $data['active'] ?? true,

                // pricing
                'price' => $data['price'],
                'currency_id' => $data['currencyId'],
                'pricing_unit_id' => $data['pricingUnitId'],
                'pricing_per' => $data['pricingPer'],
            ]
        );

        $this->resetForm();
    }

    // Load material for editing
    public function edit(int $id): void
    {
        $material = Material::findOrFail($id);

        $this->editingId = $material->id;

        // core
        $this->code = $material->code;
        $this->name = $material->name;
        $this->material_type = $material->material_type;
        $this->unit_id = $material->unit_id;
        $this->active = (bool) $material->active;

        // pricing
        $this->price = $material->price;
        $this->currencyId = $material->currency_id;
        $this->pricingUnitId = $material->pricing_unit_id;
        $this->pricingPer = $material->pricing_per ?? 1;

        $this->showForm = true;
        $this->editMode = false; // pricing edit mode will be removed next step
    }

    // Reset form state
    public function resetForm(): void
    {
        $this->editingId = null;

        $this->code = '';
        $this->name = '';
        $this->material_type = '';
        $this->material_name = '';
        $this->unit_id = null;
        $this->active = true;

        $this->price = null;
        $this->currencyId = null;
        $this->pricingUnitId = null;
        $this->pricingPer = 1;
        $this->isDirty = false;

        $this->showForm = false;
    }

    public function clearPricingState(): void
    {
        $this->price = null;
        $this->currency = 'EUR';
        $this->pricingUnitId = null;
        $this->pricingPer = 1;
    }

    protected function resetInspector(): void
    {
        $this->selectedMaterialId = null;

        $this->price = null;
        $this->currencyId = null;
        $this->pricingUnitId = null;
        $this->pricingPer = 1;

        $this->material_type = '';
    }

    // Create
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }


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
                       ->orWhere('dimensions', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.materials-table', [
            'materials' => $materials,
            'units' => Unit::orderBy('code')->get(),
            'currencies' => \App\Models\Currency::orderBy('code')->get(),
        ]);
    }

    // Material Selection code
    // public function selectMaterial(int $id): void
    // {
    //     $material = Material::findOrFail($id);
    //     $this->selectedMaterialId = $material->id;

    //     $this->material_name = $material->name;

    //     // inspector context
    //     $this->material_type = $material->material_type;

    //     $this->editMode = false;

    //     $this->price = $material->price;
    //     $this->currencyId = $material->currency_id;
    //     $this->pricingUnitId = $material->pricing_unit_id;
    //     $this->pricingPer = $material->pricing_per ?? 1;
    // }

    public function selectMaterial(int $id): void
    {
        // If editing and dirty, ask before switching
        if ($this->showForm && $this->isDirty) {
            $this->dispatch('confirm-material-switch', newId: $id);
            return;
        }

        $this->selectMaterialNow($id);
    }

    public function selectMaterialNow(int $id): void
    {
        $material = Material::findOrFail($id);

        $this->selectedMaterialId = $material->id;
        $this->material_type = $material->material_type;
        $this->material_name = $material->name;

        $this->price = $material->price;
        $this->currencyId = $material->currency_id;
        $this->pricingUnitId = $material->pricing_unit_id;
        $this->pricingPer = $material->pricing_per ?? 1;

        // close edit panel
        $this->showForm = false;
        $this->isDirty = false;
    }

    // Track for changes
    public function updated($property): void
    {
        if ($this->showForm && $property !== 'search' && $property !== 'type') {
            $this->isDirty = true;
        }
    }
}