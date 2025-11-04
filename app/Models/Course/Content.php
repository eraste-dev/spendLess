<?php

namespace App\Models\Course;

use App\Enum\MediaTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    protected $guarded = ['id'];

    use HasFactory;

    protected $casts = [
        'type' => MediaTypeEnum::class
    ];

    protected static function boot()
    {
        parent::boot();

        // Order contents with serial number by default
        static::addGlobalScope('serial_number', function ($builder) {
            $builder->orderBy('serial_number');
        });
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mediaPath(): Attribute
    {
        $media = asset($this->media->src ?? 'media/dummy-image.jpg');

        if ($this->media && Storage::exists($this->media->src)) {
            $media = Storage::url($this->media->src);
        }

        return Attribute::make(
            get: fn () => $media
        );
    }
}