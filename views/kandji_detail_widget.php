<div class="col-lg-4">
    <h4 data-i18n="kandji.title"></h4>
    <table id="kandji-data" class="table"></table>
</div>

<script>
$(document).on('appReady', function(){
    var kandji_tenant_address = "<?php configAppendFile(__DIR__ . '/../config.php'); echo rtrim(conf('kandji_tenant_address'), '/'); ?>";
    $.getJSON( appUrl + '/module/kandji/get_data/' + serialNumber, function( data ) {

        // Agent status badge — green for Pass, red for Alert, grey otherwise
        var statusVal = data.last_status || '';
        var badgeClass = 'default';
        if (statusVal.toLowerCase() === 'pass') { badgeClass = 'success'; }
        else if (statusVal.toLowerCase() === 'alert') { badgeClass = 'danger'; }
        var statusBadge = statusVal
            ? '<span class="label label-' + badgeClass + '">' + statusVal + '</span>'
            : '';

        $('#kandji-data')
            .append($('<tbody>')
                .append($('<tr>')
                    .append($('<th>').text(i18n.t('kandji.full_name')))
                    .append($('<td>').text(data.realname)))
                .append($('<tr>')
                    .append($('<th>').text(i18n.t('kandji.email_address')))
                    .append($('<td>').text(data.email_address)))
                .append($('<tr>')
                    .append($('<th>').text(i18n.t('kandji.blueprint_name')))
                    .append($('<td>').text(data.blueprint_name)))
                .append($('<tr>')
                    .append($('<th>').text(i18n.t('kandji.last_status')))
                    .append($('<td>').html(statusBadge || '—')))
                .append($('<tr>')
                    .append($('<th>').text(i18n.t('kandji.last_report')))
                    .append($('<td>').text(data.last_report || '—'))));
    });
});
</script>
