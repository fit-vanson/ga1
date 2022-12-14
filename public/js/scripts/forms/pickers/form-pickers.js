/*=========================================================================================
    File Name: pickers.js
    Description: Pick a date/time Picker, Date Range Picker JS
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
    Author: Pixinvent
    Author URL: hhttp://www.themeforest.net/user/pixinvent
==========================================================================================*/
(function (window, document, $) {
  'use strict';

  /*******  Flatpickr  *****/
  var
    rangePickr = $('.flatpickr-range')


  // Range
  if (rangePickr.length) {
    rangePickr.flatpickr({
      mode: 'range',
      dateFormat:'d-m-Y'
    });
  }


})(window, document, jQuery);
