<div class="col-lg-4">
    <h4 data-i18n="iru.title"></h4>
    <table id="iru-data" class="table"></table>
</div>

<script>
$(document).on('appReady', function(){
	// Get iru data
    var iru_tenant_address = "<?php configAppendFile(__DIR__ . '/../config.php'); echo rtrim(conf('iru_tenant_address'), '/'); ?>"; // Get the Iru server address
	$.getJSON( appUrl + '/module/iru/get_data/' + serialNumber, function( data ) {
            $('#iru-data')
                .append($('<tbody>')
                    .append($('<tr>')
                        .append($('<th>')
                            .text(i18n.t('iru.full_name')))
                        .append($('<td>')
                            .text(data.realname)))
                    .append($('<tr>')
                        .append($('<th>')
                            .text(i18n.t('iru.email_address')))
                        .append($('<td>')
                            .text(data.email_address)))
                    .append($('<tr>')
                        .append($('<th>')
                            .text(i18n.t('iru.blueprint_name')))
                        .append($('<td>')
                            .text(data.blueprint_name))));
    });
});
</script>
