<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCandidate extends Model
{
    protected $fillable = ['product_id', 'video_id', 'title', 'channel', 'published_at', 'description_snippet', 'raw_payload'];
}
