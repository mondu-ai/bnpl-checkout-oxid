[{$smarty.block.parent}]

[{if $oView->isMonduPayment()}]
  [{capture assign="orderShipping"}]
    window.onload = function () {
      const shipForm = document.querySelector('form#sendorder');
      const resetShippingForm = document.querySelector('form#resetorder');

      if (shipForm) {
        shipForm.addEventListener('submit', () => {
          event.preventDefault();
          const isConfirmed = confirm("[{oxmultilang ident="MONDU_WILL_CREATE_INVOICE"}]");

          if(isConfirmed) {
            event.target.submit();
          }
        });
      }

      if (resetShippingForm) {
        resetShippingForm.addEventListener('submit', () => {
          event.preventDefault();
          const isConfirmed = confirm("[{oxmultilang ident="MONDU_WILL_CANCEL_INVOICE"}]");

          if(isConfirmed) {
            event.target.submit();
          }
        });
      }
    };
  [{/capture}]

  [{oxscript add=$orderShipping}]
[{/if}]