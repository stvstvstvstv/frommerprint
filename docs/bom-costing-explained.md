# BOM Structure, Consumption & Costing

## Overview

Each finished product (a label roll) has a **Bill of Materials (BOM)** that defines what goes into making it and how costs are calculated. The system uses two distinct approaches depending on the component type:

- **Geometric components** — consumption is calculated mathematically from the physical dimensions of the finished roll and the jumbo paper it is cut from. You do not enter quantities manually.
- **Non-geometric components** — consumption is a fixed quantity per finished roll, entered manually.

---

## Database Structure

Three tables make up a BOM:

```
boms
  └── finished_material_id   FK → materials (the finished roll product)
  └── setup_time_minutes     one-off machine preparation time
  └── active

bom_items  (one row per non-geometric component)
  └── bom_id
  └── component_material_id  FK → materials
  └── role                   graphic | machine | other
  └── quantity               units of component consumed per finished roll
  └── waste_factor_pct       optional extra % to add for waste/spoilage
  └── sort_order

bom_yield_rules  (one row per BOM — the geometric configuration)
  └── bom_id
  └── preferred_jumbo_material_id   FK → materials (the jumbo paper to cut from)
  └── paper_waste_pct               production waste allowance for paper (%)
  └── ink_kg_per_m2                 ink consumption per m² of roll surface
  └── ink_material_id               FK → materials (which ink)
  └── tube_material_id              FK → materials (which tube)
  └── foil_per_roll                 how many finished rolls per foil wrap
  └── foil_material_id              FK → materials (inner packaging)
  └── rolls_per_box                 how many foil packs per box
  └── box_material_id               FK → materials (outer packaging)
```

---

## Component Categories

### Geometric Components (driven by YieldRule)

These five components are **never** entered as BomItems. Their consumption is always derived from the order quantity and physical dimensions:

| Component | Role enum | Consumption basis |
|-----------|-----------|-------------------|
| Jumbo paper | `jumbo` / `paper` | Area of paper consumed to cut the order |
| Ink | `ink` | Surface area of finished rolls × ink density |
| Tube | `tube` | 1 tube per finished roll |
| Foil (inner pack) | `foil` | 1 foil pack per N finished rolls |
| Box (outer pack) | `box` | 1 box per N foil packs |

If any of these roles are assigned to a BomItem by mistake, the calculator silently skips them.

### Non-Geometric Components (BomItems)

These are entered manually with a quantity-per-roll:

| Role | Typical use |
|------|-------------|
| `graphic` | Printing plate, artwork setup charge |
| `machine` | Machine running time (minutes or hours per roll) |
| `other` | Glue, labels, misc consumables |

---

## Prices

Every material price is normalised before it is used, by `BomCostCalculator::unitCost()`:

```
unit_cost = price / pricing_per                      (e.g. 25 RON per 10 packs = 2.50 per pack)
unit_cost_RON = unit_cost × rate_to_base             (latest exchange rate on or before the costing date)
```

- All costs and totals are in the base currency (`config('currency.base')`, RON).
- The costing date is today for previews, and the start date when an order moves to In Production.
- The pricing unit is what one `unit_cost` buys: m² for jumbo, kg for ink, hour for machines, piece/pack for tube, foil and box.
- **Ink must be priced per kg**, because ink consumption is entered in kg/m².

A material that can't be priced (no price, no exchange rate on or before the costing date, or ink not priced per kg) is left out of the total and listed under `problems` in the result. The screens show these as a red warning, and an order with problems can't be started.

---

## Consumption Calculations

All calculations are performed by `App\Services\BomCostCalculator` for a given order of `N` planned rolls.

---

### 1. Paper (Jumbo)

The jumbo roll is wider than the finished roll. Multiple finished rolls are cut side by side in a single pass of the slitter (a "shaft pass").

```
rolls_per_shaft = floor(jumbo_width_mm / finished_width_mm)

shafts_needed   = ceil(N / rolls_per_shaft)

gross_area_m²   = shafts_needed × (jumbo_width_mm / 1000) × finished_length_m

net_area_m²     = gross_area_m² × (1 + paper_waste_pct / 100)

cost            = net_area_m² × jumbo_price_per_m²
```

**Example:** Finished roll 57 mm wide, 30 m long. Jumbo 1 410 mm wide, 1.20 RON/m². Waste 1%.

```
rolls_per_shaft = floor(1410 / 57)  = 24
shafts_needed   = ceil(1000 / 24)   = 42
gross_area_m²   = 42 × 1.410 × 30  = 1 776.6 m²
net_area_m²     = 1 776.6 × 1.01   = 1 794.4 m²
cost            = 1 794.4 × 1.20   = 2 153.2 RON
```

Note: any leftover capacity on the last shaft pass is wasted — this is a natural production reality, not a bug.

---

### 2. Ink

Ink consumption is proportional to the printed surface area of every finished roll.

```
roll_area_m²    = (finished_width_mm / 1000) × finished_length_m

ink_kg_per_roll = ink_kg_per_m² × roll_area_m²

total_ink_kg    = ink_kg_per_roll × N

cost            = total_ink_kg × ink_price_per_kg
```

**Example:** 57 mm × 30 m roll, ink density 0.004 kg/m², ink costs 25 RON/kg, order of 1 000 rolls.

```
roll_area_m²    = 0.057 × 30        = 1.71 m²
ink_kg_per_roll = 0.004 × 1.71      = 0.00684 kg
total_ink_kg    = 0.00684 × 1 000   = 6.84 kg
cost            = 6.84 × 25         = 171.0 RON
```

---

### 3. Tube

One tube per finished roll, no waste factor.

```
quantity = N

cost     = N × tube_price_per_piece
```

---

### 4. Foil (Inner Packaging)

`foil_per_roll` defines how many finished rolls are wrapped in one foil sheet/bag.

```
foil_packs = ceil(N / foil_per_roll)

cost       = foil_packs × foil_price_per_pack
```

---

### 5. Box (Outer Packaging)

`rolls_per_box` defines how many foil packs fit in one box. If foil packaging is not used, it falls back to counting finished rolls directly.

```
units_for_box = foil_packs   (or N if no foil)

box_count     = ceil(units_for_box / rolls_per_box)

cost          = box_count × box_price_per_box
```

---

### 6. Non-Geometric BomItems (Graphic, Machine, Other)

Each BomItem has a `quantity` that represents the amount of that component consumed **per finished roll**. An optional `waste_factor_pct` adds extra for spoilage.

```
effective_qty = item_quantity × N × (1 + waste_factor_pct / 100)

cost          = effective_qty × component_material_price
```

**Example:** Graphic plate costs 50 RON/unit, quantity 0.001 per roll (i.e. 1 plate per 1 000 rolls), no waste, order of 1 000 rolls.

```
effective_qty = 0.001 × 1 000 × 1.0  = 1.0
cost          = 1.0 × 50             = 50.0 RON
```

---

### 7. Setup Cost

Setup is a one-off cost for the full production run, not multiplied by order quantity. It uses the hourly rate of the first `machine`-role BomItem to convert setup minutes into a cost.

```
cost = setup_time_minutes × (machine_hourly_rate / 60)
```

**Example:** 20 minutes setup, machine rate 120 RON/hour.

```
cost = 20 × (120 / 60) = 40.0 RON
```

---

## Total Cost Summary

The calculator returns:

```
total_cost = paper_cost
           + ink_cost
           + tube_cost
           + non_geometric_item_costs   (graphic, machine, other)
           + setup_cost
           + foil_cost
           + box_cost

unit_cost  = total_cost / N
```

---

## Production Order Overrides

When creating or editing a production order, the user can override three YieldRule values on a per-order basis without touching the BOM:

| Override field | What it replaces |
|----------------|-----------------|
| `override_ink_kg_per_m2` | `bom_yield_rules.ink_kg_per_m2` |
| `override_foil_per_roll` | `bom_yield_rules.foil_per_roll` |
| `override_rolls_per_box` | `bom_yield_rules.rolls_per_box` |

The jumbo selection on the production order also overrides the `preferred_jumbo_material_id` from the YieldRule. This allows running the same product on a different width jumbo without changing the BOM.

---

## What the YieldRule Does Not Cover

The following are **not** handled by the current system and would need extension if required:

- **Multi-colour ink** — only a single ink material and a single `kg/m²` coefficient. Multiple colours must be combined into one coefficient.
- **Actual vs planned quantity variance** — the system costs at `planned_rolls`. If actual output differs, there is no mechanism to record or reconcile the difference.
