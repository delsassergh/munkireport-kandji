<h2>Iru  <a data-i18n="iru.recheck" class="btn btn-default btn-xs" href="<?php echo url('module/iru/recheck_iru/' . $serial_number);?>"></a><span id="iru_view_in"></span></h2>

<div id="iru-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>


<script>
$(document).on('appReady', function(){
    $.getJSON( appUrl + '/module/iru/get_data/' + serialNumber, function(data) {
        formatted_data = []
        formatted_data.push(data)
        data = formatted_data
        // Check if we have data
        if( ! data[0]['device_id']){
            $('#iru-msg').text(i18n.t('no_data'));
        }else{

            // Hide
            $('#iru-msg').text('');
            $('#iru-msg').removeClass('hide');

            var iru_tenant_address = "<?php configAppendFile(__DIR__ . '/../config.php'); echo rtrim(conf('iru_tenant_address'), '/'); ?>"; // Get the Iru server address

            // Generate buttons and tabs
            $('#iru_view_in').html('<a data-i18n-"iru.view_in_iru" class="btn btn-default btn-xs" href="'+iru_tenant_address+'/devices/'+data[0]['device_id']+'" target="_blank" title="'+i18n.t('iru.view_in_iru')+'">View in Iru</a>'); // View in Iru button

            var skipThese = ['id','serial_number'];
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    // Skip skipThese
                    if(skipThese.indexOf(prop) == -1){
                        // Do nothing for empty values to blank them
                        if (d[prop] == '' || d[prop] == null){
                            rows = rows

                        // Format date
                        } else if((prop == "last_check_in" || prop == "last_enrollment" || prop == "first_enrollment") && d[prop] > 0){
                            var date = new Date(d[prop] * 1000);
                            rows = rows + '<tr><th>'+i18n.t('iru.'+prop)+'</th><td><span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span></td></tr>';
                        // Else, build out rows from entries
                        } else {
                            rows = rows + '<tr><th>'+i18n.t('iru.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                        }
                    }
                }

                $('#iru-tab')
                    .append($('<div style="max-width:1000px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(rows))))
            })
        }
    });
});

// Make button groups active
$(".btn-group > .btn").click(function(){
    $(this).addClass("active").siblings().removeClass("active");
});

</script>
