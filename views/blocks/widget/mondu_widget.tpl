[{$smarty.block.parent}]

[{if $oView->isMonduPayment()}]
  <script>
    var paymentUrl = "[{$oView->getPaymentPageUrl()}]";
  </script>

  [{ oxscript include=$oViewConf->getModuleUrl('oemondu','out/src/js/http_request.js') }]
  [{ oxscript include=$oViewConf->getModuleUrl('oemondu','out/src/js/mondu_checkout.js') }]

  <input id="mondu-checkout-input" type="hidden" />
[{/if}]