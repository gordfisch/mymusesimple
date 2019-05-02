#/bin/bash

SUBVER=`git rev-list HEAD | wc -l`

echo -n "SUBVER = "
echo $SUBVER

version=1.2.0-$SUBVER

cd src

zip -r  ../releases/com_mymusesimple-$version.zip * -x *build.xml* 

cp ../releases/com_mymusesimple-$version.zip ../releases/com_mymusesimple-latest.zip
cp ../releases/com_mymusesimple-$version.zip ../joomla/tests/_data/com_mymusesimple-latest.zip

echo -n "Look in releases "
echo -n "NEW VERSION =  "
echo $version
