<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('anpec_surveys')) {
            Schema::create('anpec_surveys', function (Blueprint $table) {
                $table->id();
                $table->string('title', 160);
                $table->text('description')->nullable();
                $table->json('questions');
                $table->boolean('is_active')->default(false)->index();
                $table->string('demo_key', 80)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('anpec_survey_responses')) {
            Schema::create('anpec_survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained('anpec_surveys')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->json('answers');
                $table->timestamps();
                $table->unique(['survey_id', 'customer_id'], 'anpec_survey_customer_unique');
                $table->index('customer_id');
            });
        }

        DB::table('anpec_surveys')->insertOrIgnore([
            'title' => 'Encuesta de ejemplo: tu tienda',
            'description' => 'Ayúdanos a conocer las necesidades de tu tienda. Esta encuesta es un ejemplo para la demostración.',
            'questions' => json_encode([
                ['id' => 'q1', 'text' => '¿Qué apoyo necesitas para tu tienda?', 'options' => ['Mejores precios', 'Más variedad de productos', 'Entregas más frecuentes'], 'allow_other' => true],
                ['id' => 'q2', 'text' => '¿Qué productos te gustaría encontrar con mayor facilidad?', 'options' => ['Abarrotes', 'Bebidas', 'Productos de limpieza'], 'allow_other' => true],
                ['id' => 'q3', 'text' => '¿Cómo prefieres recibir información de promociones?', 'options' => ['En la app', 'Por WhatsApp', 'Por correo electrónico'], 'allow_other' => true],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'is_active' => true,
            'demo_key' => 'store-needs-demo-v1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('anpec_survey_responses');
        Schema::dropIfExists('anpec_surveys');
    }
};
