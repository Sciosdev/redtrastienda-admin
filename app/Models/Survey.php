<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $table = 'anpec_surveys';

    protected $fillable = ['title', 'description', 'questions', 'is_active', 'demo_key'];

    protected $casts = ['id' => 'integer', 'questions' => 'array', 'is_active' => 'boolean'];

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'survey_id');
    }
}
