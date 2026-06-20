<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductIo\Services;

use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;

class ProductIOService
{
    public const HEADER = ['spu_code', 'sku_code', 'name', 'price', 'quantity', 'active'];

    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 流式导出所有商品 SKU 为 CSV。
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'products_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM，避免 Excel 中文乱码
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADER);

            Product::query()
                ->with(['skus', 'translation'])
                ->chunk(200, function ($products) use ($out) {
                    foreach ($products as $product) {
                        $name = optional($product->translation)->name ?? '';
                        $skus = $product->skus;
                        if ($skus->isEmpty()) {
                            fputcsv($out, [$product->spu_code, '', $name, $product->price, '', (int) $product->active]);

                            continue;
                        }
                        foreach ($skus as $sku) {
                            fputcsv($out, [
                                $product->spu_code,
                                $sku->code,
                                $name,
                                $sku->price,
                                $sku->quantity,
                                (int) $product->active,
                            ]);
                        }
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * 导入 CSV，按 sku_code 回填价格/库存(可选上下架)。
     *
     * @return array{updated:int, skipped:int, errors:array<int,string>}
     */
    public function import(string $path, bool $applyActive = false): array
    {
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => ['cannot open file']];
        }

        $rowNo  = 0;
        $colMap = null;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNo++;
            // 去除首行 BOM
            if ($rowNo === 1 && isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            }

            // 解析表头
            if ($colMap === null) {
                $colMap = array_flip(array_map('trim', $row));
                if (! isset($colMap['sku_code'])) {
                    fclose($handle);

                    return ['updated' => 0, 'skipped' => 0, 'errors' => ['missing sku_code column']];
                }

                continue;
            }

            $skuCode = trim($row[$colMap['sku_code']] ?? '');
            if ($skuCode === '') {
                $skipped++;

                continue;
            }

            /** @var Sku|null $sku */
            $sku = Sku::query()->where('code', $skuCode)->first();
            if (! $sku) {
                $skipped++;
                $errors[] = "row {$rowNo}: sku_code '{$skuCode}' not found";

                continue;
            }

            $dirty = false;
            if (isset($colMap['price']) && is_numeric($row[$colMap['price']] ?? null)) {
                $sku->price = round((float) $row[$colMap['price']], 2);
                $dirty = true;
            }
            if (isset($colMap['quantity']) && is_numeric($row[$colMap['quantity']] ?? null)) {
                $sku->quantity = (int) $row[$colMap['quantity']];
                $dirty = true;
            }
            if ($dirty) {
                $sku->save();
            }

            if ($applyActive && isset($colMap['active'])) {
                $active = (int) ($row[$colMap['active']] ?? 1);
                Product::query()->where('id', $sku->product_id)->update(['active' => $active === 1]);
            }

            $updated++;
        }

        fclose($handle);

        return ['updated' => $updated, 'skipped' => $skipped, 'errors' => array_slice($errors, 0, 50)];
    }
}
