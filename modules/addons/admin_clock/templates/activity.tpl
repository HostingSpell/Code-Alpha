<h2 class="text-center">{$LANG.admin_activity}</h2>
<table class="table table-striped" style="min-width:800px;">
    <thead>
        <tr>
            <th>Admin</th>
            {foreach from=$timeframes item=frame key=key}
                <th>{$frame.label}</th>
            {/foreach}
        </tr>
    </thead>
    <tbody>
    {foreach from=$admins item=admin}
        <tr>
            <td>{$admin.username|escape}</td>
            {foreach from=$timeframes key=tf item=frame}
                <td>{$display[$admin.id][$tf]|escape} {$LANG.replies}</td>
            {/foreach}
        </tr>
    {/foreach}
    </tbody>
</table>
<div class="text-center text-muted">{$LANG.current_time} {$currentTime}</div>

