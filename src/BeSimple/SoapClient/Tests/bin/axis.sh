#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

VERSION_AXIS=1.5.1
ZIP_AXIS=axis2-$VERSION_AXIS-bin.zip
if [[ "$VERSION_AXIS" > "1.5.1" ]]; then
    PATH_AXIS=http://archive.apache.org/dist/axis/axis2/java/core/$VERSION_AXIS/$ZIP_AXIS
else
    PATH_AXIS=http://archive.apache.org/dist/ws/axis2/${VERSION_AXIS//./_}/$ZIP_AXIS
fi

if [ ! -f "$DIR/$ZIP_AXIS" ]; then
    curl -O -s $PATH_AXIS
fi

VERSION_RAMPART=1.5
ZIP_RAMPART=rampart-dist-$VERSION_RAMPART-bin.zip
PATH_RAMPART=http://archive.apache.org/dist/axis/axis2/java/rampart/$VERSION_RAMPART/$ZIP_RAMPART

if [ ! -f "$DIR/$ZIP_RAMPART" ]; then
    curl -O -s $PATH_RAMPART
fi

unzip -o -qq "$DIR/$ZIP_AXIS"

AXIS_DIR=$DIR/axis2-$VERSION_AXIS

unzip -o -qq -j "$DIR/$ZIP_RAMPART" '*/lib/*.jar' -d $AXIS_DIR/lib
unzip -o -qq -j "$DIR/$ZIP_RAMPART" '*/modules/*.mar' -d $AXIS_DIR/repository/modules

cp -r $DIR/../AxisInterop/axis_services/* $AXIS_DIR/repository/services

$AXIS_DIR/bin/axis2server.sh&

echo "Waiting until Axis is ready on port 8080"
while [[ -z `curl -s 'http://localhost:8080/axis2/services/' ` ]]
do
    echo -n "."
    sleep 2s
done

echo "Axis is up"