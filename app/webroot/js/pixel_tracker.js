var casper = require('casper').create({
    verbose: true,
    logLevel: "debug"
});
var utils = require('utils');
var result = "";

var headers = function (headers) {
    var length = headers.length;
    var params = {};
    for (var i = 0; i < length; i++) {
        params[headers[i]['name']] = headers[i]['value'];
    }
    return params;
}

casper.urlList = {};
casper.redirectList = {};
casper.output = {};
casper.on("resource.requested", function (resource, request) {
    var res = resource.url.match(/bs.servin/g);
    var res1 = resource.url.match(/insight.adsrvr/g);

    var params = headers(resource.headers);
    if (typeof (params["Referer"]) != 'undefined') {
        var r = params["Referer"];
        while (typeof (casper.redirectList[r]) != 'undefined') {
            r = casper.redirectList[r];
        }
        if (typeof (casper.urlList[r]) == "undefined") {
            casper.urlList[r] = [];
        }
        if (res || res1) {
            var pa = {};
            var p = resource.url.split(/[\s?&]+/);
            for (pp = 0; pp < p.length; pp++) {
                var ppp = p[pp].split("=");
                pa[ppp.shift()] = ppp.join('=');
            }
            pa['url'] = resource.url;
            casper.urlList[r].push(pa);
        }
    }
});

casper.on('resource.received', function (resource) {
    if (resource.redirectURL != null && resource.redirectURL != '') {
        casper.redirectList[resource.redirectURL] = resource.url;
    }
});

casper.start().then(function () {
    this.open('http://adpixel.dev/users/getPages', {
        method: 'get',
        headers: {
            'Accept': 'application/json'
        }
    });
});

casper.then(function () {
    var json_string = JSON.parse( this.getPageContent() );
    casper.each( json_string, function( casper, link ) {
        casper.thenOpen( link.Pages.url, function (response) {
            if(response.status == 404){
                casper.output[link.Pages.id] = "Not Found";
            }
            else{
                var vendor = JSON.parse( link.Pages.row_data ).vendor;
                var pixelIdCode = JSON.parse( link.Pages.row_data ).pixel_id_code;
                var splittedPixelCode = pixelIdCode.split(/src\s*=\s*"(HTTPS?:){0,1}(.+?)"/i);
                casper.output[link.Pages.id] = "Not Firing";
                if ( vendor.toLowerCase() == "sizmek" || vendor.toLowerCase() == "the trade desk" ) {
                    casper.each( casper.urlList[link.Pages.url], function( casper1, par ) {
                        var webUrl = par.url.replace(/https?:/i, '');
                        var excelUrl = splittedPixelCode[2].replace(/&amp;/g, '&');
                        if( webUrl == excelUrl ) {
                            if ( vendor.toLowerCase() == "sizmek" ) {
                                result = ( par.ActivityID != "undefined" && par.ActivityID == link.Pages.pixel_code ) ? "Firing" :"Not Firing";
                            } else {
                                var act = ( par.ct ).split(":");
                                result = ( par.ct != "undefined" && act[1]== link.Pages.pixel_code ) ? "Firing" : "Not Firing";
                            }
                            casper.output[link.Pages.id] = result;
                        }
                    });
                }
            }
        });
    });
});

casper.run(function() {
    utils.dump(casper.output);
    this.open('http://adpixel.dev/users/postPages', {
        method: 'post',
        headers: {
            'Accept': 'application/json'
        },
        data: {'data1': JSON.stringify(casper.output)}
    });
    this.echo('Script completed');
    this.exit();
});
