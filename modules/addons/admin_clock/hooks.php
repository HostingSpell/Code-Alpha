<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function admin_clock_widget($vars)
{
    $time  = date('Y-m-d H:i:s');
    $title = 'Server Clock';
    $link  = 'addonmodules.php?module=admin_clock';
    $content  = '<div style="text-align:center;font-size:18px;"><strong>' . $time . '</strong></div>';
    $content .= '<div class="text-center" style="margin-top:5px;"><a href="' . $link . '" class="btn btn-sm btn-default">View Admin Activity</a></div>';
    return ['title' => $title, 'content' => $content];
}

add_hook('AdminHomeWidgets', 1, 'admin_clock_widget');
?>
