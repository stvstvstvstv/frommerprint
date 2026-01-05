<?php

protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
{
    $schedule
        ->command('rates:fetch-bnr')
        ->dailyAt('14:00')
        ->withoutOverlapping()
        ->onFailure(fn () =>
            logger()->error('BNR exchange rate fetch failed')
        );
}
