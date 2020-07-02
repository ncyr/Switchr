// DataTables options (admin)
$(document).ready( function () {
    $('.data-table').DataTable({
        'ordering'  : true,
        'order'     : [ 1, 'desc' ],
        'serverSide': false,
        'lengthChange': false,

        'columnDefs': [ {
            'targets': 'dis',
            'orderable'  : false,
        } ]
    });
} );

// DataTables options (customer)
$(document).ready( function () {
    $('.cus-data-table').DataTable({
        'ordering'  : true,
        'order'     : [ 0, 'desc' ],
        'serverSide': false,
        'lengthChange': false,

        'columnDefs': [ {
            'targets': 'dis',
            'orderable'  : false,
        } ]
    });
} );

/*!
 * Required Star jQuery Plugin v1.0
 * @link https://github.com/juno/jquery-requiredstar-plugin
 * @author Junya Ogura <http://sooey.com/>
 */
(function($) {
  $.fn.requiredStar = function(options) {
    var opts = $.extend({}, $.fn.requiredStar.defaults, options);

    return this.each(function() {
      if ($(this).is('.' + opts.requiredClass)) {
        updateValidity($(this), opts); // update validity with default value
        var c = function() { updateValidity($(this), opts); };
        $(this).keyup(c).change(c);
        $(this).mouseup(c);
        $(this).mouseenter(c);
      }
    });
  };

  $.fn.requiredStar.defaults = {
    requiredClass: 'required',
    validClass: 'valid'
  };

  function updateValidity(e, opts) {
    e.is('.' + opts.requiredClass) && !e.val() ? e.removeClass(opts.validClass) : e.addClass(opts.validClass);
  }
})(jQuery);

// Execute
$(function() {
  $('input, textarea').requiredStar();
});



// Stops form submission and raises dialog box if a required field is not filled
function checkValid() {
    var r = $('.required').get();
    var v = $('.valid').get();
    if (r.length != v.length) {
        $( "#dialog" ).dialog( "open" );
        return false;
    } else { return true;}
}

// To be used for Reset button
function checkValidAll() {
    $(function() {
        $('input, textarea').requiredStar();
    });
}

function showHidden() {
    var amountRadio  = $("[type=radio]:eq(0)");
    var percentRadio = $("[type=radio]:eq(1)");
    var amountBox    = $("[name='amount_off']");
    var percentBox   = $("[name='percent_off']");

    percentRadio.show();
    percentBox.show();
    $("#percent").show();
    amountRadio.show();
    amountBox.show();
    $("#amount").show();
}

// Dialog (pop-up) box for required fields
$(function() {
    $( "#dialog" ).dialog({
        autoOpen: false,
        draggable: false,
        buttons: [ { text: "Ok", click: function() { $( this ).dialog( "close" ); } } ],
        show: {
            effect: "fade",
            duration: 400
        },
        hide: {
            effect: "fade",
            duration: 400
        }
    });
    $( "#opener" ).click(function() {
        $( "#dialog" ).dialog( "open" );
    });
});

function radioOnClick() {
    var amountRadio  = $("[type=radio]:eq(0)");
    var percentRadio = $("[type=radio]:eq(1)");
    var amountBox    = $("[name='amount_off']");
    var percentBox   = $("[name='percent_off']");

    if ($(amountRadio).is(':checked')) {
        $(percentBox).addClass( "valid" );
        percentRadio.hide();
        percentBox.hide();
        $("#percent").hide();

    }

    else if ($(percentRadio).is(':checked')) {
        $(amountBox).addClass( "valid" );
        amountRadio.hide();
        amountBox.hide();
        $("#amount").hide();

    }
}

function durationBox() {
    if ( $('[name="duration"]').val() == 'repeating' ) {
        $('[name="duration_months"]').css("visibility", "visible");
        $('[name="duration_months"]').removeClass( "valid" );
    } else {
        $('[name="duration_months"]').css("visibility", "hidden");
        $('[name="duration_months"]').addClass( "valid" );
    }
}
