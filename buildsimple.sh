#/bin/bash
cd /var/www/html/mymuse-git/mymusesimple
SUBVER=`git rev-list HEAD | wc -l`

echo -n "SUBVER = "
echo $SUBVER

version=3.5.0-$SUBVER



cd /var/www/html/mymuse-git
rm -f com_mymusesimple*

zip -r  com_mymusesimple-$version.zip mymusesimple



echo -n "NEW VERSION =  "
echo $version


