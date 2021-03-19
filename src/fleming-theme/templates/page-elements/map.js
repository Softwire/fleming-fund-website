var mapConfig;
var highlightedRegion;
var focusedRegion;

var map;

function getCountryFillMap() {
    var fillMap = {};
    $.each(mapConfig.countries, function (code, country) {
        var region = mapConfig.regions[country.region];
        fillMap[code] = region.colourScheme;
    });
    return fillMap;
}

function getCountryFillOpacityMap() {
    var fillOpacityMap = {};
    $.each(mapConfig.countries, function (code, country) {
        fillOpacityMap[code] = (country.isPartner ? '0.5' : '1');
    });
    return fillOpacityMap;
}

function getCountryStyleMap() {
    var styleMap = {};
    $.each(mapConfig.countries, function (code, country) {
        styleMap[code] = 'cursor: pointer';
    });
    return styleMap;
}

function getCountryClassMap() {
    var classMap = {};
    $.each(mapConfig.countries, function (code, country) {
        var region = mapConfig.regions[country.region];
        classMap[code]
            = 'jvectormap-region jvectormap-element '
            + (country.isPartner ? 'partner ' : '')
            + region.colourScheme
            + ((highlightedRegion === 'all') || (highlightedRegion === country.region) ? '' : ' not-highlighted');
    });
    return classMap;
}

function refocusMap() {
    if (map !== undefined && focusedRegion !== undefined) {
        var focusedCountryCodes = mapConfig.regions[focusedRegion].countries;
        map.updateSize();
        map.setFocus({
            regions: focusedCountryCodes
        });
        if (mapConfig.zoomAwayFromFocus > 1) {
            map.setScale(map.scale / mapConfig.zoomAwayFromFocus, map.width / 2, map.height / 2, !1, false);
        }

        if (mapConfig.rightBound) {
            var shiftX =  (map.width - mapConfig.rightBound) / 2;
            map.transX -= shiftX / map.scale;
        }
        if (mapConfig.bottomBound) {
            var shiftY =  (map.height - mapConfig.bottomBound) / 2;
            map.transY -= shiftY / map.scale;
        }
        map.applyTransform();

        $('.jvectormap-region').each(function() {
            var country = $(this);
            var countryCode = country.data('code');
            if ($.inArray(countryCode, focusedCountryCodes) > -1) {
                country.removeClass('not-highlighted');
            } else {
                country.addClass('not-highlighted');
            }
        });
    }
}

function focusMapOn(region) {
    focusedRegion = region;
    if (mapConfig.regions[region] === undefined || mapConfig.regions[region].countries.length === 0) {
        focusedRegion = 'all';
    } else {
        focusedRegion = region;
    }
    refocusMap();
}

function setRightBound(bound) {
    mapConfig.rightBound = bound;
    refocusMap();
}

function setBottomBound(bound) {
    mapConfig.bottomBound = bound;
    refocusMap();
}

function forceRedrawMap() {
    $('.map').css('opacity', '0.999');
    setTimeout(function () {
        $('.map').css('opacity', '1');
    }, 10);
}

function init(config, mapElementID) {
    mapConfig = config;
    highlightedRegion = config.currentRegion;
    focusedRegion = config.currentRegion;

    $(function () { // initialise map only after the DOM is ready and inline scripts finished
        $('.map-container').show();
        map = $('#' + mapElementID).vectorMap({
            map: 'world_mill',
            backgroundColor: '#e5e5e5',
            regionStyle: {
                initial: {
                    fill: 'white',
                    "fill-opacity": 1,
                    stroke: 'white',
                    "stroke-width": '0.5px',
                    "stroke-opacity": 1,
                    "stroke-linecap": 'round',
                    "stroke-linejoin": 'round'
                },
                hover: {
                    cursor: 'normal'
                }
            },
            focusOn: {
                regions: mapConfig.regions[focusedRegion].countries
            },
            series: {
                regions: [
                    {attribute: 'class', values: getCountryClassMap()},
                    {attribute: 'fill', values: getCountryFillMap()},
                    {attribute: 'stroke', values: getCountryFillMap()},
                    {attribute: 'fill-opacity', values: getCountryFillOpacityMap()},
                    {attribute: 'style', values: getCountryStyleMap()},
                ],
            },
            markerStyle: {
                initial: {
                    r: 15
                }
            },
            markers: mapConfig.markers,
            labels: {
                markers: {
                    render: function (markerIndex) {
                        return parseInt(markerIndex) + 1;
                    },
                    offsets: function () {
                        return [-24, 0];
                    }
                }
            },
            zoomOnScroll: false,
            panOnDrag: config.interactive,
            bindTouchEvents: config.interactive,
            zoomButtons: false, // we implement our own
            zoomMax: 50,
            onRegionTipShow: function (e, tip, code) {
                var country = mapConfig.countries[code];
                if (country) {
                    tip.html(country.name);
                } else {
                    e.preventDefault();
                }
            },
            onRegionClick: function (e, code) {
                var country = mapConfig.countries[code];
                if (country) {
                    window.location.href = country.URL;
                }
            },
            onViewportChange: function() {
                setTimeout(forceRedrawMap, 700);
            }
        }).vectorMap('get', 'mapObject');

        $('#' + mapElementID + '-zoom-in').click(
            function () {
                map.setScale(map.scale * map.params.zoomStep, map.width / 2, map.height / 2, !1, map.params.zoomAnimate)
            }
        );
        $('#' + mapElementID + '-zoom-out').click(
            function () {
                map.setScale(map.scale / map.params.zoomStep, map.width / 2, map.height / 2, !1, map.params.zoomAnimate)
            }
        );

        function hideTip() {
            $('.jvectormap-tip').hide();
        }

        $(document).scroll(hideTip);
        $('.jvectormap-tip').bind('mouseover', hideTip);

        $(window).resize(function () {
            refocusMap();
        });

        refocusMap();
    });
}

module.exports = {
    focusMapOn,
    init,
    setRightBound,
    setBottomBound
};
