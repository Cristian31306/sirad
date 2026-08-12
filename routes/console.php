<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('radicados:check-vencimientos')->dailyAt('08:00');

// Sincronizar festivos del año actual y el próximo automáticamente cada mes
Schedule::command('sirad:sync-festivos')->monthly();
Schedule::command('sirad:sync-festivos '.(date('Y') + 1))->monthly();
