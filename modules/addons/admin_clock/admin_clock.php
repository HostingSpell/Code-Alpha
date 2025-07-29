<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function admin_clock_config()
{
    return [
        'name' => 'Admin Clock',
        'description' => 'Displays current server time in the WHMCS admin area.',
        'version' => '1.0',
        'author' => 'Code-Alpha',
        'fields' => []
    ];
}

function admin_clock_activate()
{
    return [
        'status' => 'success',
        'description' => 'Admin Clock Activated'
    ];
}

function admin_clock_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'Admin Clock Deactivated'
    ];
}

use WHMCS\Database\Capsule;

/**
 * Calculate active time in seconds for each admin between $start and $end
 * using data from tbladminlog. This function performs read-only queries.
 *
 * @param string $start MySQL datetime
 * @param string $end   MySQL datetime
 * @return array        [adminId => seconds]
 */
function admin_clock_get_activity($start, $end)
{
    $log = Capsule::table('tbladminlog')
        ->where('logintime', '>=', $start)
        ->where('logintime', '<=', $end);

    $schema = Capsule::schema();
    if ($schema->hasColumn('tbladminlog', 'adminusername')) {
        $records = $log->get(['adminusername', 'logintime', 'logouttime', 'lastvisit']);
        $admins = Capsule::table('tbladmins')->pluck('id', 'username');
        $getAdminId = function ($row) use ($admins) {
            return $admins[$row->adminusername] ?? null;
        };
    } else {
        $idColumn = $schema->hasColumn('tbladminlog', 'adminid') ? 'adminid' : 'admin_id';
        $records = $log->get([$idColumn, 'logintime', 'logouttime', 'lastvisit']);
        $getAdminId = function ($row) use ($idColumn) {
            return $row->$idColumn ?? null;
        };
    }

    $activity = [];
    foreach ($records as $row) {
        $adminId = $getAdminId($row);
        if (!$adminId) {
            continue;
        }
        $login  = strtotime($row->logintime);
        $logout = $row->logouttime ? strtotime($row->logouttime)
                 : ($row->lastvisit ? strtotime($row->lastvisit) : $login);
        $seconds = max(0, $logout - $login);
        if (!isset($activity[$adminId])) {
            $activity[$adminId] = 0;
        }
        $activity[$adminId] += $seconds;
    }

    return $activity;
}

/**
 * Count ticket replies made by admins between $start and $end
 *
 * @param string $start MySQL datetime
 * @param string $end   MySQL datetime
 * @return array        [adminId => count]
 */
function admin_clock_get_ticket_replies($start, $end)
{
    $schema = Capsule::schema();
    $field = $schema->hasColumn('tblticketreplies', 'admin') ? 'admin' : 'adminid';

    $records = Capsule::table('tblticketreplies')
        ->where('date', '>=', $start)
        ->where('date', '<=', $end)
        ->where($field, '!=', '')
        ->select($field, Capsule::raw('COUNT(*) as total'))
        ->groupBy($field)
        ->get();

    if ($field === 'admin') {
        $map = Capsule::table('tbladmins')->pluck('id', 'username');
    } else {
        $map = Capsule::table('tbladmins')->pluck('id', 'id');
    }

    $counts = [];
    foreach ($records as $row) {
        $key = $row->$field;
        if (!isset($map[$key])) {
            continue;
        }
        $adminId = $map[$key];
        $counts[$adminId] = (int) $row->total;
    }

    return $counts;
}

/**
 * Render the Admin Clock output page
 */
function admin_clock_output($vars)
{
    $now = time();
    $timeframes = [
        'last24h'  => ['label' => 'Last 24 Hours', 'start' => $now - 86400],
        'today'    => ['label' => 'Today',        'start' => strtotime('today')],
        'last7d'   => ['label' => 'Last 7 Days',   'start' => $now - 7 * 86400],
        'thisweek' => ['label' => 'This Week',     'start' => strtotime('monday this week')],
        'last30d'  => ['label' => 'Last 30 Days',  'start' => $now - 30 * 86400],
        'thismonth'=> ['label' => 'This Month',    'start' => strtotime(date('Y-m-01 00:00:00'))],
        'last365d' => ['label' => 'Last 365 Days', 'start' => $now - 365 * 86400],
        'thisyear' => ['label' => 'This Year',     'start' => strtotime(date('Y-01-01 00:00:00'))],
    ];

    $admins = Capsule::table('tbladmins')->get(['id', 'firstname', 'lastname', 'username']);

    $activityData = [];
    $ticketData   = [];
    foreach ($timeframes as $key => $frame) {
        $start = date('Y-m-d H:i:s', $frame['start']);
        $end   = date('Y-m-d H:i:s', $now);
        $activityData[$key] = admin_clock_get_activity($start, $end);
        $ticketData[$key]   = admin_clock_get_ticket_replies($start, $end);
    }

    $display = [];
    foreach ($admins as $admin) {
        foreach ($timeframes as $key => $frame) {
            $seconds = $activityData[$key][$admin->id] ?? 0;
            $timeStr = gmdate('H:i:s', $seconds);
            $replies = $ticketData[$key][$admin->id] ?? 0;
            $display[$admin->id][$key] = $timeStr . ' / ' . $replies;
        }
    }

    return [
        'pagetitle'   => 'Admin Clock',
        'templatefile'=> 'activity',
        'requirelogin'=> true,
        'vars'        => [
            'timeframes' => $timeframes,
            'admins'     => $admins,
            'display'    => $display,
            'currentTime'=> date('Y-m-d H:i:s'),
        ]
    ];
}
?>
