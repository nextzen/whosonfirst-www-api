var mapzen = mapzen || {};
mapzen.places = mapzen.places || {};

mapzen.places.map = (function(){

	var maps = {};

	var self = {

		'get_map': function(map_id){

			if (! maps[map_id]){

				var api_key = document.body.getAttribute("data-mapzen-api-key");
				var abs_root_url = document.body.getAttribute("data-abs-root-url");

				L.Mapzen.apiKey = api_key;

				var map = L.Mapzen.map(map_id, {
					zoomControl: false,
					maxZoom: 20,
					// https://github.com/mapzen/mapzen.js/blob/master/src/js/components/tangram.js
					tangramOptions: {
						//scene: L.Mapzen.BasemapStyles.Refill,
						scene: abs_root_url + "tangram/refill-style.zip",
						tangramURL: abs_root_url + "javascript/tangram.min.js"
					}
				});

				L.control.zoom({
					position: 'topleft'
				}).addTo(map);

				maps[map_id] = map;
			}

			return maps[map_id];
		},

		'get_marker': function(feature, latlon){

			var props = feature['properties'];
			var label = props['wof:name'];

			if (! latlon) {
				var lat = props['geom:latitude'];
				var lon = props['geom:longitude'];
				latlon = [lat, lon];
			}

			var abs_root_url = document.body.getAttribute("data-abs-root-url");
			var icon = L.icon({
				iconUrl: abs_root_url + 'images/pin.png',
				iconSize: [32, 47],
				iconAnchor: [16, 46],
			});
			var m = L.marker(latlon, {
				icon: icon
			});
			m.bindTooltip(label);

			return m;
		},

		'feature_handler': function(feature, layer){

			var props = feature['properties'];
			var wofid = props["wof:id"];

			layer.on('click', function (e){

				var enc_id = encodeURIComponent(wofid);

				var abs_root_url = document.body.getAttribute("data-abs-root-url");
				var url = abs_root_url + "id/" + enc_id + "/";

				location.href = url;
			});
		},

		'draw_nearby_map': function(map_id, cb){

			var map_el = document.getElementById(map_id);
			var map = self.get_map(map_id);

			if (map_el.getAttribute("data-wof-id")){
				return self.draw_place_map(map_id, cb);
			}

			map.setView([37.7749, -122.4194], 12);

			cb(map);
		},

		'draw_place_map': function(map_id, cb){

			var map_el = document.getElementById(map_id);
			var map = self.get_map(map_id);

			var placetype = map_el.getAttribute("data-wof-placetype");

			var lat = map_el.getAttribute("data-geom-latitude");
			var lon = map_el.getAttribute("data-geom-longitude");

			lat = parseFloat(lat);
			lon = parseFloat(lon);

			var bbox = map_el.getAttribute("data-geom-bbox");
			if (bbox) {
				bbox = bbox.split(",");

				var min_lon = parseFloat(bbox[0]);
				var min_lat = parseFloat(bbox[1]);
				var max_lon = parseFloat(bbox[2]);
				var max_lat = parseFloat(bbox[3]);
			}

			if ((min_lat == max_lat) && (min_lon == max_lon)){

				var zoom = 12;

				if (placetype == "venue"){
					zoom = 17;
				}

				map.setView([lat, lon], zoom);
			}

			else {
				var sw = L.latLng(min_lat, min_lon);
				var ne = L.latLng(max_lat, max_lon);

				var bounds = L.latLngBounds(sw, ne);
				var opts = { "padding": [100, 100] };

				map.fitBounds(bounds, opts);
			}

			// this doesn't work well enough to use yet...

			/*
			var abs_root_url = document.body.getAttribute("data-abs-root-url");

			var opts = {
				"url": abs_root_url + "pelias/v1",
				"focus": false,
				"panToPoint": false,
			};

			var geocoder = L.Mapzen.geocoder(opts);
			geocoder.addTo(map);
			*/

			cb(map);
		},

		'add_geojson_to_map': function(map, geojson, more){

			if (! more){
				more = {};
			}

			var args = {
				"pointToLayer": self.get_marker,
				"onEachFeature": self.feature_handler
			};

			// console.log("[map][geojson] ADD", geojson, args);

			var layer = L.geoJSON(geojson, args);

			// http://leafletjs.com/reference-1.1.0.html#layergroup-setzindex
			// https://github.com/Leaflet/Leaflet/issues/3427 (sigh...)

			if (more["z-index"]){
				var z = layer.setZIndex(more["z-index"]);
			}

			return layer.addTo(map);
		},

		'add_geojson_clusters_to_map': function(map, geojson, more){

			var cluster = L.markerClusterGroup({
				maxClusterRadius: 120,
				showCoverageOnHover: false
			});
			var feature, layer;

			for (var i = 0; i < geojson.features.length; i++) {
				feature = geojson.features[i];
				marker = self.get_marker(feature);
				self.feature_handler(feature, marker);
				cluster.addLayer(marker);
			}

			map.addLayer(cluster);
		},

		'get_place_coords': function(place){

			var lat = null;
			var lon = null;

			if ('lbl:latitude' in place){
				lat = place['lbl:latitude'];
			} else if ('geom:latitude' in place){
				lat = place['geom:latitude'];
			}

			if ('lbl:longitude' in place){
				lon = place['lbl:longitude'];
			} else if ('geom:longitude' in place){
				lon = place['geom:longitude'];
			}

			return [lon, lat];
		}
	};

	return self;

})();
