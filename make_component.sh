#/bin/bash

SUBVER=`git rev-list HEAD | wc -l`

echo -n "SUBVER = "
echo $SUBVER

version=4.0.1-$SUBVER

cd src

zip -r  ../releases/com_mymuse-$version.zip * -x *build.xml* 

cp ../releases/com_mymuse-$version.zip ../releases/com_mymuse-latest.zip
cp ../releases/com_mymuse-$version.zip ../joomla/tests/_data/com_mymuse-latest.zip

echo -n "Look in releases "
echo -n "NEW VERSION =  "
echo $version
