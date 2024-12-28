<?php

use App\Console\Commands\FetchArticles;
use Illuminate\Support\Facades\Schedule;

Schedule::command(FetchArticles::class)->everyMinute();
