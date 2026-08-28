<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'category', 'youtube_url', 'youtube_video_id', 'youtube_found_at', 'ai_verified', 'ai_accuracy', 'ai_explanation'];

    public function videoCandidates()
    {
        return $this->hasMany(VideoCandidate::class)->orderBy('created_at', 'desc')->limit(5);
    }
}
