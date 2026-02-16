<?php

use App\Models\Setting;

function app_settings(): Setting {
    return Setting::singleton();
}
