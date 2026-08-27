<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'category', 'youtube_url', 'youtube_video_id', 'youtube_found_at', 'ai_verified', 'ai_accuracy', 'ai_explanation'];
}
