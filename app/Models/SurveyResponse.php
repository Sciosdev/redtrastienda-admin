<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $table = 'anpec_survey_responses';

    protected $fillable = ['survey_id', 'customer_id', 'answers'];

    protected $casts = ['id' => 'integer', 'survey_id' => 'integer', 'customer_id' => 'integer', 'answers' => 'array'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
