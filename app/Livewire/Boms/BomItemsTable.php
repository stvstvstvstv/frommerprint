<?php

namespace App\Livewire\Boms;

use Livewire\Component;
use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Material;

class BomItemsTable extends Component
{
    public Bom $bom;

    public $items = [];

    public $newItem = [
        'component_material_id' => null,
        'role' => '',
        'quantity' => null,
        'waste_factor_pct' => null,
        'notes' => '',
    ];

    protected function rules()
    {
        return [
            'items.*.role' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.waste_factor_pct' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',

            'newItem.component_material_id' => 'required|exists:materials,id',
            'newItem.role' => 'required|string|max:255',
            'newItem.quantity' => 'required|numeric|min:0',
            'newItem.waste_factor_pct' => 'nullable|numeric|min:0|max:100',
            'newItem.notes' => 'nullable|string',
        ];
    }

    public function mount(Bom $bom)
    {
        $this->bom = $bom;
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = $this->bom
            ->items()
            ->with('componentMaterial')
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function addItem()
    {
        $this->validateOnly('newItem');

        $nextOrder = BomItem::where('bom_id', $this->bom->id)->max('sort_order') + 1;

        BomItem::create([
            'bom_id' => $this->bom->id,
            'component_material_id' => $this->newItem['component_material_id'],
            'role' => $this->newItem['role'],
            'quantity' => $this->newItem['quantity'],
            'waste_factor_pct' => $this->newItem['waste_factor_pct'],
            'notes' => $this->newItem['notes'],
            'sort_order' => $nextOrder,
        ]);

        $this->reset('newItem');
        $this->loadItems();
    }

    public function updateItem($index)
    {
        $this->validateOnly("items.$index");

        $item = BomItem::findOrFail($this->items[$index]['id']);
        $item->update([
            'role' => $this->items[$index]['role'],
            'quantity' => $this->items[$index]['quantity'],
            'waste_factor_pct' => $this->items[$index]['waste_factor_pct'],
            'notes' => $this->items[$index]['notes'],
        ]);
    }

    public function deleteItem($itemId)
    {
        BomItem::where('id', $itemId)->delete();
        $this->loadItems();
    }

    public function render()
    {
        return view('livewire.boms.bom-items-table', [
            'materials' => Material::orderBy('name')->get(),
        ]);
    }
}