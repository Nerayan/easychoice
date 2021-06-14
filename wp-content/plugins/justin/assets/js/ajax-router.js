"use strict";
!(function (o) {
    window.JustinShipRouter = {
        loadCities: function (s) {
            o.ajax({
                method: "POST",
                url: woo_justin_globals.ajaxUrl,
                data: { action: "woo_justin_load_cities", body: s.data },
                dataType: "json",
                success: function (o) {
                    s.success(o);
                },
            });
        },
        loadWarehouses: function (s) {
            o.ajax({
                method: "POST",
                url: woo_justin_globals.ajaxUrl,
                data: { action: "woo_justin_load_warehouses", body: s.data },
                dataType: "json",
                success: function (o) {
                    s.success(o);
                },
            });
        },
        getCities: function (s) {
            o.ajax({
                method: "POST",
                url: woo_justin_globals.ajaxUrl,
                data: { action: "woo_justin_get_cities", body: { ref: s.areaRef } },
                dataType: "json",
                success: function (o) {
                    s.success(o);
                },
            });
        },
        getWarehouses: function (s) {
            o.ajax({
                method: "POST",
                url: woo_justin_globals.ajaxUrl,
                data: { action: "woo_justin_get_warehouses", body: { ref: s.cityRef } },
                dataType: "json",
                success: function (o) {
                    s.success(o);
                },
            });
        },
    };
})(jQuery);
