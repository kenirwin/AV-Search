#!/bin/bash

cd /docs/lib/find/av/
rm feature_film_lookup.html.bak
mv feature_film_lookup.html feature_film_lookup.html.bak
php feature_film_lookup.php > feature_film_lookup.html
