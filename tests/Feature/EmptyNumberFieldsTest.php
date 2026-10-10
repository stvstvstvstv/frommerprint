<?php

namespace Tests\Feature;

use App\Livewire\ProductionOrderCreate;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * An emptied number box arrives as null (wire:model.number). It must stay
 * empty on later requests instead of crashing or reverting to a default.
 */
class EmptyNumberFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleared_quantity_stays_empty_and_cannot_be_saved(): void
    {
        $component = Livewire::actingAs(User::factory()->create())
            ->test(ProductionOrderCreate::class)
            ->assertSet('plannedRolls', 100)
            ->set('plannedRolls', null);

        // A later request must still see it empty, not the default of 100
        $component->call('recalculate')
            ->assertSet('plannedRolls', null)
            ->assertSet('costPreview', []);

        $component->call('save')->assertHasErrors(['plannedRolls' => 'required']);

        $this->assertSame(0, ProductionOrder::count());
    }

    public function test_cleared_foil_and_box_counts_mean_none(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ProductionOrderCreate::class)
            ->set('overrideFoilPerRoll', null)
            ->set('overrideRollsPerBox', null)
            ->call('save')
            ->assertHasNoErrors(['overrideFoilPerRoll', 'overrideRollsPerBox']);
    }
}
