<?php
namespace Plugin\TaxEngine\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\TaxEngine\Models\TaxRule;

class TaxEngineController extends BaseController
{
    public function index(): mixed
    {
        $rules = TaxRule::query()->orderBy('country_code')->orderBy('region_code')->get();

        return nice_view('TaxEngine::console.index', compact('rules'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'country_code'    => 'required|string|max:8',
                'region_code'     => 'nullable|string|max:32',
                'name'            => 'required|string|max:128',
                'tax_type'        => 'required|string|max:32',
                'rate'            => 'required|numeric|min:0|max:100',
                'include_in_price'=> 'nullable|boolean',
                'active'          => 'nullable|boolean',
            ]);
            TaxRule::query()->create([
                'country_code'     => strtoupper($data['country_code']),
                'region_code'      => $data['region_code'] ? strtoupper($data['region_code']) : null,
                'name'             => $data['name'],
                'tax_type'         => strtolower($data['tax_type']),
                'rate'             => $data['rate'],
                'include_in_price' => $request->boolean('include_in_price'),
                'active'           => $request->boolean('active', true),
            ]);

            return json_success(__('TaxEngine::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        TaxRule::query()->whereKey($id)->delete();

        return json_success(__('TaxEngine::common.deleted'));
    }
}
