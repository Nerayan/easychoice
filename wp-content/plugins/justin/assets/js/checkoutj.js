"use strict";
!(function (e) {
    let i,
        n = e("#justin_shipping_method_fields"),
        s = function () {
            n.addClass("justin-state-loading");
        },
        t = function () {
            n.removeClass("justin-state-loading");
        };
    e(".woocommerce-shipping-fields").css("display", "none");
    let o = function () {
        let i = e(".shipping_method").length > 1 ? e(".shipping_method:checked").val() : e(".shipping_method").val();
        return console.log("currentShipping :", i), i && i.match(/^justin_shipping_method.+/i);
    };
    e(function () {
        e("#justin_shipping_method_fields").css("display", "none"),
            // e(document.body).bind("update_checkout", function (e, i) {
            e(document.body).on("update_checkout", function (e, i) {
                s();
            }),
            // e(document.body).bind("updated_checkout", function (n, s) {
            e(document.body).on("updated_checkout", function (n, s) {
                "UA" === (i = e("#billing_country").length ? e("#billing_country").val() : "UA") && o()
                    ? (e("#justin_shipping_method_fields").css("display", "block"), e(".woocommerce-shipping-fields").css("display", "none"))
                    : (e("#justin_shipping_method_fields").css("display", "none"), e(".woocommerce-shipping-fields").css("display", "block")),
                    o() &&
                        "true" === woo_justin_globals.disableDefaultBillingFields &&
                        (e("#billing_address_1_field").css("display", "none"),
                        e("#billing_address_2_field").css("display", "none"),
                        e("#billing_city_field").css("display", "none"),
                        e("#billing_state_field").css("display", "none"),
                        e("#billing_postcode_field").css("display", "none")),
                    t();
            }),
            e("#justin_shipping_method_city").on("change", function () {
                let i = e("#justin_shipping_method_warehouse"),
                    n = e("#justin_shipping_method_city").val();
                s(),
                    JustinShipRouter.getWarehouses({
                        cityRef: n,
                        success: function (s) {
                            t();
                            try {
                                let t = s.data;
                                for (let s = 0; s < t.length; s++) {
                                    let o = t[s];
                                    // console.log(o),
                                        o.locality == n
                                            ? i.append(
                                                  e("<option></option>")
                                                      .attr("value", o.description)
                                                      .text(o.description + " (" + o.adress + " )")
                                              ) : '';
                                            // : console.log(o.locality, n);
                                }
                            } catch (e) {
                                console.log(e + "Не вдалось знайти " + s);
                            }
                        },
                    });
            }),
            "function" == typeof e.fn.select2 &&
                e(".justin-select").select2({
                    sorter: function (i) {
                        return (
                            i.sort(function (n, s) {
                                let t = e(".select2-search__field");
                                if (0 === t.length || "" === t.val()) return i;
                                let o = n.text.toLowerCase(),
                                    l = s.text.toLowerCase(),
                                    c = t.val().toLowerCase();
                                return o.indexOf(c) < l.indexOf(c) ? -1 : o.indexOf(c) > l.indexOf(c) ? 1 : 0;
                            }),
                            i
                        );
                    },
                });
                // e("#billing_address_1_field").css("display", "none !important");
    });
})(jQuery);
