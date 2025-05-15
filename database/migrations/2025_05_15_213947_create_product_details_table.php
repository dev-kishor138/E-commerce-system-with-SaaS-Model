<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('product_details')->get()->each(function ($detail) {
            $product = DB::table('products')->where('id', $detail->product_id)->first();
            $tenant_id = $product ? DB::table('items')->where('id', $product->item_id)->value('tenant_id') : null;

            // Map product details fields to attributes
            $attributes = [
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'description',
                    'attribute_value' => $detail->description ?? '',
                    'attribute_type' => 'text',
                    'is_filterable' => false,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'ingredients',
                    'attribute_value' => $detail->ingredients ?? '',
                    'attribute_type' => 'text',
                    'is_filterable' => false,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'usage_instruction',
                    'attribute_value' => $detail->usage_instruction ?? '',
                    'attribute_type' => 'text',
                    'is_filterable' => false,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'gender',
                    'attribute_value' => $detail->gender,
                    'attribute_type' => 'enum',
                    'is_filterable' => true,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'short_description',
                    'attribute_value' => $detail->short_description ?? '',
                    'attribute_type' => 'text',
                    'is_filterable' => false,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant_id,
                    'attributable_id' => $detail->product_id,
                    'attributable_type' => 'App\Models\Product',
                    'attribute_category' => 'beauty',
                    'attribute_name' => 'product_policy',
                    'attribute_value' => $detail->product_policy ?? '',
                    'attribute_type' => 'text',
                    'is_filterable' => false,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                    'deleted_at' => $detail->deleted_at,
                ],
            ];

            foreach ($attributes as $attr) {
                if ($attr['attribute_value']) {
                    DB::table('attributes')->insert($attr);
                }
            }
        });

        // Drop product_details table
        Schema::dropIfExists('product_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate product_details and reverse migration if needed
    }
};
