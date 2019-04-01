document.addEventListener('DOMContentLoaded', function() {
  var blockObject = {
    cards: document.querySelectorAll('.lmfwc-card'),
    toggleButton: document.querySelectorAll('.lmfwc-card button.button-secondary'),
    init: function() {
      var that = this;

      for (var i = 0; i < this.toggleButton.length; i++) {
        this.toggleButton[i].addEventListener('click', function() {
          that.toggleCards(this);
        });
      }
    },
    toggleCards: function(button) {
      var clickedCard = button.parentNode;
      var clickedCardOrder = clickedCard.dataset.order;
      var clickedCardTransform = clickedCard.style.transform;
      var clickedCardForm = clickedCard.querySelector('form');
      var clickedCardContent = clickedCard.querySelector('.lmfwc-card-content');
      var firstCard = document.querySelector('.lmfwc-card[data-order="1"]');

      // Hide active card content
      if (clickedCardContent.clientHeight > 0) {
        clickedCardContent.style.height = '0px';
        clickedCard.style.width = 'calc(50% - 1em)';

      // Display clicked card content
      } else {
        // Reset cards
        for (var i = 0; i < this.cards.length; i++) {
          this.cards[i].style.width = 'calc(50% - 1em)';
          this.cards[i].style.zIndex = 'unset';
          this.cards[i].querySelector('.lmfwc-card-content').style.height = '0px';
        }

        // Change the two card's positions
        clickedCard.style.webkitTransform = 'translate(0%, 0%)';
        clickedCard.style.mozTransform = 'translate(0%, 0%)';
        clickedCard.style.msTransform = 'translate(0%, 0%)';
        clickedCard.style.oTransform = 'translate(0%, 0%)';
        clickedCard.style.transform = 'translate(0%, 0%)';
        firstCard.style.WebkitTransform = clickedCardTransform;
        firstCard.style.MozTransform = clickedCardTransform;
        firstCard.style.MsTransform = clickedCardTransform;
        firstCard.style.OTransform = clickedCardTransform;
        firstCard.style.transform = clickedCardTransform;
        firstCard.dataset.order = clickedCardOrder;
        clickedCard.dataset.order = 1;
        clickedCard.style.zIndex = 1;

        // Expand the clicked card
        clickedCard.style.width = 'calc(100% - 1em)';
        clickedCardContent.style.height = clickedCardForm.clientHeight + 'px';
      }
    },
    resetCards: function() {

    }
  }

  blockObject.init();
})