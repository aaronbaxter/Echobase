osmconvert --parameter-file="prm/osmconvert_params" -b="-180,0,-90,90" planet-140820.osm.pbf -o="tmp/osm_01_01.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="-180,-90,-90,0" planet-140820.osm.pbf -o="tmp/osm_01_02.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="-90,0,0,90" planet-140820.osm.pbf -o="tmp/osm_02_01.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="-90,-90,0,0" planet-140820.osm.pbf -o="tmp/osm_02_02.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="0,0,90,90" planet-140820.osm.pbf -o="tmp/osm_03_01.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="0,-90,90,0" planet-140820.osm.pbf -o="tmp/osm_03_02.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="90,0,180,90" planet-140820.osm.pbf -o="tmp/osm_04_01.pbf"
osmconvert --parameter-file="prm/osmconvert_params" -b="90,-90,180,0" planet-140820.osm.pbf -o="tmp/osm_04_02.pbf"