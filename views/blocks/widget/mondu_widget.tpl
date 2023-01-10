[{$smarty.block.parent}]

[{if $oView->isMonduPayment()}]
  <script>
    var widgetUrl = "[{$oViewConf->getWidgetUrl()}]";
    var paymentUrl = "[{$oView->getPaymentPageUrl()}]";
  </script>

  [{ oxstyle  include=$oViewConf->getModuleUrl('oemondu','out/src/css/widget.css') }]
  [{ oxscript include=$oViewConf->getModuleUrl('oemondu','out/src/js/http_request.js') }]
  [{ oxscript include=$oViewConf->getModuleUrl('oemondu','out/src/js/mondu_checkout.js') }]

  <input id="mondu-checkout-input" type="hidden" />
  <div id="mondu-checkout-widget"></div>
[{/if}]