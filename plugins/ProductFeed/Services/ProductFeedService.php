<?php
namespace Plugin\ProductFeed\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use NiceShoply\Common\Models\Product;
use Plugin\ProductFeed\Models\FeedLog;

class ProductFeedService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (int) plugin_setting('product_feed', 'enabled', 0) === 1;
    }

    protected function baseUrl(): string
    {
        $url = trim((string) plugin_setting('product_feed', 'base_url', ''));
        if ($url !== '') {
            return rtrim($url, '/');
        }

        return rtrim((string) config('app.url'), '/');
    }

    public function generate(string $channel = 'google'): FeedLog
    {
        $items = $this->collectItems();
        $dir   = 'feeds';
        Storage::disk('public')->makeDirectory($dir);

        if ($channel === 'csv') {
            $path = $dir.'/products.csv';
            $body = $this->buildCsv($items);
            $format = 'csv';
        } elseif ($channel === 'meta') {
            $path = $dir.'/meta_catalog.xml';
            $body = $this->buildMetaXml($items);
            $format = 'xml';
        } else {
            $path = $dir.'/google_shopping.xml';
            $body = $this->buildGoogleXml($items);
            $format = 'xml';
            $channel = 'google';
        }

        Storage::disk('public')->put($path, $body);

        return FeedLog::query()->create([
            'channel'    => $channel,
            'format'     => $format,
            'file_path'  => $path,
            'item_count' => count($items),
        ]);
    }

    protected function collectItems(): array
    {
        $items = [];
        $base  = $this->baseUrl();

        Product::query()->with(['masterSku', 'translation'])->where('active', 1)->chunk(200, function ($products) use (&$items, $base) {
            foreach ($products as $product) {
                $sku = $product->masterSku;
                if (! $sku) {
                    continue;
                }
                $name  = $product->translation->name ?? $product->name ?? ('Product #'.$product->id);
                $price = (float) ($sku->price ?? 0);
                $link  = $base.'/products/'.$product->id;
                $image = $product->image ? image_origin($product->image) : '';
                $items[] = [
                    'id'          => (string) ($sku->code ?: $product->id),
                    'title'       => $name,
                    'description' => strip_tags((string) ($product->translation->content ?? '')),
                    'link'        => $link,
                    'image'       => $image,
                    'price'       => number_format($price, 2, '.', '').' '.strtoupper(setting_currency_code()),
                    'availability'=> ((int) ($sku->quantity ?? 0)) > 0 ? 'in stock' : 'out of stock',
                    'brand'       => system_setting('site_name', 'NiceShoply'),
                ];
            }
        });

        return $items;
    }

    protected function buildGoogleXml(array $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>';
        $xml .= '<title>'.htmlspecialchars(system_setting('site_name', 'Store')).'</title>';
        $xml .= '<link>'.htmlspecialchars($this->baseUrl()).'</link>';
        foreach ($items as $item) {
            $xml .= '<item>';
            $xml .= '<g:id>'.htmlspecialchars($item['id']).'</g:id>';
            $xml .= '<g:title>'.htmlspecialchars($item['title']).'</g:title>';
            $xml .= '<g:description>'.htmlspecialchars(mb_substr($item['description'], 0, 5000)).'</g:description>';
            $xml .= '<g:link>'.htmlspecialchars($item['link']).'</g:link>';
            if ($item['image']) {
                $xml .= '<g:image_link>'.htmlspecialchars($item['image']).'</g:image_link>';
            }
            $xml .= '<g:price>'.htmlspecialchars($item['price']).'</g:price>';
            $xml .= '<g:availability>'.htmlspecialchars($item['availability']).'</g:availability>';
            $xml .= '<g:brand>'.htmlspecialchars($item['brand']).'</g:brand>';
            $xml .= '</item>';
        }
        $xml .= '</channel></rss>';

        return $xml;
    }

    protected function buildMetaXml(array $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">';
        $xml .= '<title>'.htmlspecialchars(system_setting('site_name', 'Store')).'</title>';
        $xml .= '<link href="'.htmlspecialchars($this->baseUrl()).'"/>';
        foreach ($items as $item) {
            $xml .= '<entry>';
            $xml .= '<g:id>'.htmlspecialchars($item['id']).'</g:id>';
            $xml .= '<g:title>'.htmlspecialchars($item['title']).'</g:title>';
            $xml .= '<g:description>'.htmlspecialchars(mb_substr($item['description'], 0, 5000)).'</g:description>';
            $xml .= '<g:link>'.htmlspecialchars($item['link']).'</g:link>';
            if ($item['image']) {
                $xml .= '<g:image_link>'.htmlspecialchars($item['image']).'</g:image_link>';
            }
            $xml .= '<g:price>'.htmlspecialchars($item['price']).'</g:price>';
            $xml .= '<g:availability>'.htmlspecialchars($item['availability']).'</g:availability>';
            $xml .= '</entry>';
        }
        $xml .= '</feed>';

        return $xml;
    }

    protected function buildCsv(array $items): string
    {
        $lines = ['id,title,description,link,image,price,availability,brand'];
        foreach ($items as $item) {
            $lines[] = implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', [
                $item['id'], $item['title'], mb_substr($item['description'], 0, 500),
                $item['link'], $item['image'], $item['price'], $item['availability'], $item['brand'],
            ]));
        }

        return implode("\n", $lines);
    }

    public function publicUrl(FeedLog $log): string
    {
        return Storage::disk('public')->url($log->file_path);
    }
}
