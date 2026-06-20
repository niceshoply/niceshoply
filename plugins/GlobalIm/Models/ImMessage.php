<?php
namespace Plugin\GlobalIm\Models;

use Illuminate\Database\Eloquent\Model;

class ImMessage extends Model
{
    protected $table = 'global_im_messages';

    protected $fillable = ['channel', 'direction', 'peer_id', 'body', 'payload'];

    protected $casts = ['payload' => 'array'];
}
