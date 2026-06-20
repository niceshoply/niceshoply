<?php
namespace Plugin\TaxEngine\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $table = 'tax_engine_rules';

    protected $fillable = ['country_code', 'region_code', 'name', 'tax_type', 'rate', 'include_in_price', 'active'];

    protected $casts = ['rate' => 'float', 'include_in_price' => 'boolean', 'active' => 'boolean'];
}
