#!/bin/bash

PHPWEBSERVEROK="$( php -r "if (version_compare(phpversion(), '5.4', '<')) { echo 'nok'; } else { echo 'ok'; }" )"

if [ "$PHPWEBSERVEROK" != "ok" ]; then
    echo "No PHP webserver available before version 5.4..."
    exit
fi

DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

php -S localhost:8081 -t "$DIR/.."&

echo "Waiting until PHP webserver is ready on port 8081"
while [[ -z `curl -s 'http://localhost:8081' ` ]]
do
    echo -n "."
    sleep 2s
done

echo "PHP webserver is up"