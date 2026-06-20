<?php
namespace Plugin\ProductFeed\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLog extends Model
{
    protected $table = 'product_feed_logs';

    protected $fillable = ['channel', 'format', 'file_path', 'item_count'];
}
