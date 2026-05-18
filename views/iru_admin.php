<?php $this->view('partials/head');
  // Add local config
  configAppendFile(__DIR__ . '/../config.php');
?>

<div class="container">
    <div class="row"><span id="iru_pull_all"></span></div>
    <div class="col-lg-6">
        <div id="GetAllIru-Progress" class="progress hide">
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
                <span id="Progress-Bar-Percent"></span>
            </div>
        </div>
        <br id="Progress-Space" class="hide">
        <div id="Iru-System-Status"></div>
    </div>
</div>  <!-- /container -->

<script>
var iru_pull_all_running = 0;

$(document).on('appReady', function(e, lang) {

    // Generate pull all button and header
    $('#iru_pull_all').html('<h3 class="col-lg-6" >&nbsp;&nbsp;'+i18n.t('iru.title_admin')+'&nbsp;&nbsp;<button id="GetAllIru" class="btn btn-default btn-xs hide">'+i18n.t("iru.pull_in_all")+'</button></h3>');

    // Get Iru server URL
    var iru_api_endpoint = "<?php echo rtrim(conf('iru_api_endpoint'), '/'); ?>";

    // Check if Iru lookups are enabled
    if ("<?php echo conf('iru_enable'); ?>" == true) {
        var iru_enabled = i18n.t('yes');
        var iru_enabled_int = 1;
        $('#GetAllIru').removeClass('hide');
    } else {
        var iru_enabled = i18n.t('no');
        var iru_enabled_int = 0;
    }

    iru_pull_all_running = 0;

    // Check if Iru API password is set
    if (parseInt("<?php echo strlen(conf('iru_api_key')); ?>") > 0) {
        var iru_api_key = i18n.t('yes');
    } else {
        var iru_api_key = i18n.t('no');
    }

    // Build table
    var irurows = '<table class="table table-striped table-condensed"><tbody>'
    irurows = irurows + '<tr><th>'+i18n.t('iru.lookups_enabled')+'</th><td>'+iru_enabled+'</td></tr>';
    irurows = irurows + '<tr><th>'+i18n.t('iru.api_endpoint')+'</th><td><a target="_blank" href="'+iru_api_endpoint+'">'+iru_api_endpoint+'</a></td></tr>';
    irurows = irurows + '<tr><th>'+i18n.t('iru.token_set')+'</th><td>'+iru_api_key+'</td></tr>';

    $('#Iru-System-Status').html(irurows+'</tbody></table>') // Close table framework and assign to HTML ID

    $('#GetAllIru').click(function (e) {
        // Disable button and unhide progress bar
        $('#GetAllIru').html(i18n.t('iru.processing')+'...');
        $('#Progress-Bar-Percent').text('0%');
        $('#GetAllIru-Progress').removeClass('hide');
        $('#Progress-Space').removeClass('hide');
        $('#GetAllIru').addClass('disabled');
        iru_pull_all_running = 1;

        // Get JSON of all serial numbers
        $.getJSON(appUrl + '/module/iru/pull_all_iru_data', function (processdata) {

            // Set count of serial numbers to be processed
            var progressmax = (processdata.length);
            var progessvalue = 0;;
            $('.progress-bar').attr('aria-valuemax', progressmax);

            var serial_index = 0;
            var serial = processdata[0]

            // Process the serial numbers
            process_serial(serial,progessvalue,progressmax,processdata,serial_index)
        });
    });
});

// Process each Iru request one at a time
function process_serial(serial,progessvalue,progressmax,processdata,serial_index){

        // Get JSON for each serial number
        request = $.ajax({
        url: appUrl + '/module/iru/pull_all_iru_data/'+processdata[serial_index],
        type: "get",
        success: function (obj, resultdata) {

            // Calculate progress bar's percent
            var processpercent = Math.round((((progessvalue+1)/progressmax)*100));
            progessvalue++
            $('.progress-bar').css('width', (processpercent+'%')).attr('aria-valuenow', processpercent);
            $('#Progress-Bar-Percent').text(progessvalue+"/"+progressmax);

            // Cleanup and reset when done processing serials
            if ((progessvalue) == progressmax) {
                // Make button clickable again and hide process bar elements
                $('#GetAllIru').html(i18n.t('iru.pull_in_all'));
                $('#GetAllIru').removeClass('disabled');
                iru_pull_all_running = 0;
                $("#Progress-Space").fadeOut(1200, function() {
                    $('#Progress-Space').addClass('hide')
                    var progresselement = document.getElementById('Progress-Space');
                    progresselement.style.display = null;
                    progresselement.style.opacity = null;
                });
                $("#GetAllIru-Progress").fadeOut( 1200, function() {
                    $('#GetAllIru-Progress').addClass('hide')
                    var progresselement = document.getElementById('GetAllIru-Progress');
                    progresselement.style.display = null;
                    progresselement.style.opacity = null;
                    $('.progress-bar').css('width', 0+'%').attr('aria-valuenow', 0);
                });

                return true;
            }

            // Go to the next serial
            serial_index++

            // Get next serial
            serial = processdata[serial_index];

            // Run function again with new serial
            process_serial(serial,progessvalue,progressmax,processdata,serial_index)
        },
        statusCode: {
            500: function() {
                iru_pull_all_running = 0;
                alert("An internal server occurred. Please refresh the page and try again.");
            }
        }
    });
}

// Warning about leaving page if Iru pull all is running
window.onbeforeunload = function() {
    if (iru_pull_all_running == 1) {
        return i18n.t('iru.leave_page_warning');
    } else {
        return;
    }
};

</script>

<?php $this->view('partials/foot'); ?>
