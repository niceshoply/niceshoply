<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SearchPlus\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\SearchPlus\Models\SearchKeyword;
use Plugin\SearchPlus\Models\Synonym;
use Plugin\SearchPlus\Services\SearchService;

class SearchPlusController extends BaseController
{
    protected string $modelClass = Synonym::class;

    public function index(): mixed
    {
        $hotWords  = SearchKeyword::query()->orderByDesc('hits')->limit(30)->get();
        $noResults = SearchKeyword::query()->where('results', 0)->orderByDesc('hits')->limit(20)->get();
        $synonyms  = Synonym::query()->orderByDesc('id')->get();
        $driver    = SearchService::getInstance()->driver();

        return nice_view('SearchPlus::console.index', compact('hotWords', 'noResults', 'synonyms', 'driver'));
    }

    public function storeSynonym(Request $request): mixed
    {
        try {
            $data = $request->validate(['terms' => 'required|string|max:255']);
            Synonym::query()->create(['terms' => $data['terms'], 'is_active' => true]);

            return json_success(__('SearchPlus::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroySynonym(int $id): mixed
    {
        Synonym::query()->whereKey($id)->delete();

        return json_success(__('SearchPlus::common.deleted'));
    }

    public function reindex(): mixed
    {
        try {
            $count = SearchService::getInstance()->reindexMeili();

            return json_success(__('SearchPlus::common.reindexed', ['count' => $count]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
