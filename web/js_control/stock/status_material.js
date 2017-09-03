$(function() {
    // control search
    $("#btnSearch").click(function(e) {
        e.preventDefault();
        var keysearch = $("#keySearch").val().trim();
        if (keysearch.length == 0)
            keysearch = "_";

        window.location.href = WEB_URL+"stock/status/material?key="+keysearch;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control clear search
    $("#btnClearSearch").click(function(e) {
        window.location.href = WEB_URL+"stock/status/material";
    });
});