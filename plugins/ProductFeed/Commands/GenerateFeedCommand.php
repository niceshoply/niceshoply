<?php
namespace Plugin\ProductFeed\Commands;

use Illuminate\Console\Command;
use Plugin\ProductFeed\Services\ProductFeedService;

class GenerateFeedCommand extends Command
{
    protected $signature = 'product:feed {channel?}';

    protected $description = 'Generate product feed (google/meta/csv)';

    public function handle(): int
    {
        $channel = $this->argument('channel') ?: (string) plugin_setting('product_feed', 'default_channel', 'google');
        $log     = ProductFeedService::getInstance()->generate($channel);
        $this->info("Generated {$log->channel} feed: {$log->item_count} items → {$log->file_path}");

        return self::SUCCESS;
    }
}
