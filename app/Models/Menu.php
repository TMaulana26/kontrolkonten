<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class Menu extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LogsActivity;

    public array $translatable = ['name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['route', 'icon', 'order', 'status'])
            ->setDescriptionForEvent(fn(string $eventName) => "Menu has been {$eventName}")
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $translations = [
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
        ];

        if (in_array($eventName, ['created', 'updated'])) {
            $attributes = $activity->properties->get('attributes', []);
            $activity->properties = $activity->properties->put('attributes', array_merge($attributes, $translations));
        }

        if ($eventName === 'updated') {
            $oldTranslations = [
                'name' => $this->getOriginal('name'),
                'description' => $this->getOriginal('description'),
            ];

            $oldAttributes = $activity->properties->get('old', []);
            $activity->properties = $activity->properties->put('old', array_merge($oldAttributes, $oldTranslations));
        }
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'route',
        'icon',
        'order',
        'status',
    ];
}
