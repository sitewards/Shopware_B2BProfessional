{extends file="parent:frontend/checkout/confirm.tpl"}

{block name='frontend_index_header_css_screen' append}
    {block name="frontend_index_header_css_screen_b2bprofessional"}
        <link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='frontend/_resources/styles/b2bprofessional.css'}" />
        <link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='frontend/_resources/jquery_ui/css/ui-lightness/jquery-ui-1.10.4.custom.min.css'}" />
    {/block}
{/block}

{block name="frontend_index_header_javascript" append}
    {block name="frontend_index_header_javascript_b2bprofessional"}
        <script type="text/javascript" src="{link file="frontend/_resources/jquery_ui/js/jquery-ui-1.10.4.custom.js"}"></script>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('#delivery_date').datepicker({
                    {s name="B2BCheckoutDatepickerConfiguration"}prevText: '&#x3c;zurück', prevStatus: '', prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '', nextText: 'Vor&#x3e;', nextStatus: '', nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '', currentText: 'heute', currentStatus: '', todayText: 'heute', todayStatus: '', clearText: '-', clearStatus: '', closeText: 'schließen', closeStatus: '', monthNames: ['Januar','Februar','März','April','Mai','Juni', 'Juli','August','September','Oktober','November','Dezember'], monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun', 'Jul','Aug','Sep','Okt','Nov','Dez'], dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'], dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'], dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'], showMonthAfterYear: false, dateFormat:'dd.mm.yy', firstDay: 1{/s}
                });
            });
        </script>
    {/block}
{/block}

{block name='frontend_checkout_confirm_footer' prepend}
    {block name='frontend_checkout_confirm_footer_delivery_date'}
        <div class="checkout_delivery_date">
            <label for="delivery_date">{s name="B2BCheckoutDeliveryDate"}Bitte geben Sie das gewünschte Lieferdatum ein{/s}</label>
            <input type="text" id="delivery_date" name="delivery_date" value="" />
        </div>
    {/block}
{/block}