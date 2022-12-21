[{$smarty.block.parent}]

[{if $oView->isMonduPayment()}]
  [{capture assign="orderShipping"}]
    window.onload = function () {
      const shipButton = document.querySelector('input#shippNowButton');
      const resetButton = document.querySelector('input#resetShippingDateButton');

      if (shipButton) {
        shipButton.addEventListener('mousedown', (event) => {
          event.preventDefault();
          const isConfirmed = confirm("[{oxmultilang ident="MONDU_WILL_CREATE_INVOICE"}]");

          if(isConfirmed) {
            event.target.click();
          }
        });
      }

      if (resetButton) {
        resetButton.addEventListener('mousedown', (event) => {
          event.preventDefault();
          const isConfirmed = confirm("[{oxmultilang ident="MONDU_WILL_CANCEL_INVOICE"}]");

          if(isConfirmed) {
            event.target.click();
          }
        });
      }
    };
  [{/capture}]

  [{oxscript add=$orderShipping}]
[{/if}]