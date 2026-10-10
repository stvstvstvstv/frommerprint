<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

/**
 * Parses width (mm), length (m), and gsm from material names.
 *
 * Supported patterns in name:
 *   "57mm x 30m/12/48gmp"  (finished rolls — main pattern)
 *   "1410mm x 48gsm"       (jumbos — already seeded, skipped if populated)
 *   "78mmx10m"             (compact form, no gsm)
 *
 * Only fills fields that are currently null; never overwrites existing data.
 */
class MaterialDimensionSeeder extends Seeder
{
    public function run(): void
    {
        $updated = 0;
        $skipped = 0;
        $noMatch = 0;

        Material::all()->each(function (Material $material) use (&$updated, &$skipped, &$noMatch) {
            // Already fully populated — nothing to do
            if ($material->width !== null && $material->length !== null) {
                $skipped++;
                return;
            }

            // Extract width_mm and length_m
            // Matches: "57mm x 30m", "1410mmx13.5m", "78mm×10m" etc.
            if (! preg_match('/(\d+(?:\.\d+)?)\s*mm\s*[xX×]\s*(\d+(?:\.\d+)?)\s*m\b/i', $material->name, $dim)) {
                $noMatch++;
                $this->command->warn("  No match: [{$material->material_type}] {$material->name}");
                return;
            }

            $width  = (float) $dim[1];
            $length = (float) $dim[2];

            // Extract gsm — matches "/48gmp" or "48gsm" anywhere in the string
            $gsm = null;
            if (preg_match('/(\d+)\s*(?:gmp|gsm)/i', $material->name, $gsmMatch)) {
                $gsm = (float) $gsmMatch[1];
            }

            $changes = [];
            if ($material->width  === null) $changes['width']  = $width;
            if ($material->length === null) $changes['length'] = $length;
            if ($material->gsm    === null && $gsm !== null) $changes['gsm'] = $gsm;

            if (empty($changes)) {
                $skipped++;
                return;
            }

            $material->update($changes);
            $updated++;

            $this->command->line(sprintf(
                '  Updated #%-4d %-30s  w=%-6s l=%-6s g=%s',
                $material->id,
                substr($material->name, 0, 30),
                $changes['width']  ?? '(kept)',
                $changes['length'] ?? '(kept)',
                isset($changes['gsm']) ? $changes['gsm'] : '(kept)',
            ));
        });

        $this->command->newLine();
        $this->command->info("Done — updated: {$updated} | already set: {$skipped} | no match: {$noMatch}");
    }
}
