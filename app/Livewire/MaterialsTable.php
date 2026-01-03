<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Material;

class MaterialsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $search = '';
    public string $type = '';

    // Form state
    public ?int $editingId = null;

    public string $code = '';
    public string $name = '';
    public string $material_type = '';
    public int|string|null $unit_id = null;
    public bool $active = true;

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
    }

    // Delete
    public function delete(int $id)
    {
        Material::findOrFail($id)->delete();
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
                )
            )
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.materials-table', [
            'materials' => $materials,
        ]);
    }
}