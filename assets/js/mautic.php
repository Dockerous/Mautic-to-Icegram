<?php
header('Content-Type: application/javascript');
require_once plugins_url( 'admin/options.php', __FILE__ );
require_once plugins_url( 'phpuri/phpuri.php', __FILE__ );
$mtc_to_ig_options = new MTI_Base_Options("mtc_to_ig");
$url = $mtc_to_ig_options->get_url;
$base = phpUri::parse($url);
$add_base = $base->scheme.$base->authority;
?>

    /** This section is only needed once per page if manually copying **/
    if (typeof MauticSDKLoaded == 'undefined') {
        var MauticSDKLoaded = true;
        var head            = document.getElementsByTagName('head')[0];
        var script          = document.createElement('script');
        script.type         = 'text/javascript';
        script.src          = '<?php echo  $url; ?>/media/js/mautic-form.js';
        script.onload       = function() {
            MauticSDK.onLoad();
            jQuery("body").trigger('mautic_loaded');
            console.log('mautic_loaded triggered');
        };
        head.appendChild(script);
        var MauticDomain = '<?php echo  $add_base; ?>';
        var MauticLang   = {
            'submittingMessage': "Please wait..."
        }
    }
