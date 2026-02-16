<?php

use App\Models\Setting;

function app_settings(): Setting
{
    return Setting::singleton();
}
if (!function_exists('canMenu')) {
    function canMenu(string $perm): bool
    {
        $u = auth()->user();
        if (!$u) return false;
        if ($u->hasRole('admin')) return true;
        return $u->canPerm($perm);
    }
}
