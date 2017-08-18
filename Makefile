mz-docs:
	php -q ./bin/mk_mapzen_docs.php > docs/index.md
	php -q ./bin/mk_mapzen_docs.php --page methods --endpoint "$(ENDPOINT)" --api_key "$(API_KEY)" --access_token "$(ACCESS_TOKEN)" --examples > docs/methods.md
	php -q ./bin/mk_mapzen_docs.php --page formats > docs/formats.md
	php -q ./bin/mk_mapzen_docs.php --page pagination > docs/pagination.md
	php -q ./bin/mk_mapzen_docs.php --page errors > docs/errors.md

templates:
	php -q ./bin/compile-templates.php

secret:
	php -q ./bin/generate_secret.php

setup:
	if test ! -f www/include/secrets.php; then cp www/include/secrets.php.example www/include/secrets.php; fi
	ubuntu/setup-ubuntu.sh
	ubuntu/setup-flamework.sh
	ubuntu/setup-certified.sh
	sudo ubuntu/setup-certified-ca.sh
	sudo ubuntu/setup-certified-certs.sh
	bin/configure_secrets.sh .
	ubuntu/setup-db.sh wof_api wof_api

mapzen:	styleguide mapzenjs refill

styleguide:
	if test -e www/css/mapzen.styleguide.css; then cp www/css/mapzen.styleguide.css www/css/mapzen.styleguide.css.bak; fi
	curl -s -o www/css/mapzen.styleguide.css https://mapzen.com/common/styleguide/styles/styleguide.css
	curl -s -o www/javascript/mapzen.styleguide.min.js https://mapzen.com/common/styleguide/scripts/mapzen-styleguide.min.js

tangram:
	curl -s -o www/javascript/tangram.js https://mapzen.com/tangram/tangram.debug.js
	curl -s -o www/javascript/tangram.min.js https://mapzen.com/tangram/tangram.min.js

refill:
	curl -s -o www/tangram/refill-style.zip https://mapzen.com/carto/refill-style/refill-style.zip

mapzenjs:
	curl -s -o www/css/mapzen.js.css https://mapzen.com/js/mapzen.css
	curl -s -o www/javascript/mapzen.js https://mapzen.com/js/mapzen.js
	curl -s -o www/javascript/mapzen.min.js https://mapzen.com/js/mapzen.min.js

vizjs:
	curl -L -s -o www/javascript/viz-lite.js https://github.com/mdaines/viz.js/releases/download/v1.8.0/viz-lite.js

chrome:
	curl -s -o www/css/mapzen.whosonfirst.chrome.css https://raw.githubusercontent.com/whosonfirst/css-mapzen-whosonfirst/master/css/mapzen.whosonfirst.chrome.css
	curl -s -o www/javascript/mapzen.whosonfirst.chrome.js https://raw.githubusercontent.com/whosonfirst/js-mapzen-whosonfirst/master/src/mapzen.whosonfirst.chrome.js
	curl -s -o www/javascript/mapzen.whosonfirst.chrome.init.js https://raw.githubusercontent.com/whosonfirst/js-mapzen-whosonfirst/master/src/mapzen.whosonfirst.chrome.init.js

whosonfirst:
	curl -s -o www/include/lib_whosonfirst_placetypes.php https://raw.githubusercontent.com/whosonfirst/flamework-whosonfirst/master/www/include/lib_whosonfirst_placetypes.php
	curl -s -o www/include/lib_whosonfirst_placetypes_spec.php https://raw.githubusercontent.com/whosonfirst/flamework-whosonfirst/master/www/include/lib_whosonfirst_placetypes_spec.php
	curl -s -o www/include/lib_whosonfirst_sources.php https://raw.githubusercontent.com/whosonfirst/flamework-whosonfirst/master/www/include/lib_whosonfirst_sources.php
	curl -s -o www/include/lib_whosonfirst_sources_spec.php https://raw.githubusercontent.com/whosonfirst/flamework-whosonfirst/master/www/include/lib_whosonfirst_sources_spec.php
	curl -s -o www/include/lib_whosonfirst_uri.php https://raw.githubusercontent.com/whosonfirst/flamework-whosonfirst/master/www/include/lib_whosonfirst_uri.php

dev-to-master:
	git checkout master
	git pull origin dev
	git push origin master
	git checkout dev

d2m: dev-to-master
