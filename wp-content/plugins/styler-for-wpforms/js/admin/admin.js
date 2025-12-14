(function ($) {
  $(document).ready(function () {


    function addpendStylerButton() {

      // Get the formId from localized data.
      var formId = window.sfwfPowerUpsFormBuilderData.formId;

      // Get the builder url.
      var builderUrl = window.sfwfPowerUpsFormBuilderData.ultimatePageUrl + "&formId=" + formId;

      // Create the new button element with the specified class and data attribute
      var stylerBtn = $("<a>", {
        class: "wpforms-panel-styler-button sfwf-custom-btn",
        "data-panel": "styler",
        href: builderUrl,
      });

      // Create the <i> element with the specified class
      var icon = $("<i>", {
        class: "fa fa-paint-brush",
      });

      // Create the <span> element with the text "Styler"
      var span = $("<span>").text("Styler");

      // Append the <i> and <span> elements to the new button
      stylerBtn.append(icon).append(span);

      // Append the new button after the existing button with the specified class
      $(".wpforms-panel-providers-button").last().after(stylerBtn);

      return stylerBtn;

    }

    // Call the function to add the new button
    addpendStylerButton();

    function addAddressFieldSchemeLink() {
      // Get the power ups link from the localized data
      const articlesUrls = window.sfwfPowerUpsFormBuilderData.articlesUrls;
      const addressFieldSchemeLink = articlesUrls.power_ups.address_scheme;

      // Create the <a> tag
      const linkElement = $('<a>', {
        href: addressFieldSchemeLink,
        target: '_blank',
        text: 'Add New Address Field Schemes',
        class: 'after-label-description sfwf-address-field-scheme-link',
      });

      // Find the target div and append the link under the label
      $('.wpforms-field-option-row-scheme > label').append(linkElement);
    }

    // call the function to add power ups link in address field scheme
    addAddressFieldSchemeLink();


  });
})(jQuery);
