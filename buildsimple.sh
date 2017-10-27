#/bin/bash
cd /var/www/html/mymuse-git/mymusesimple
SUBVER=`git rev-list HEAD | wc -l`
#git archive -o bitvolution.zip --prefix=bitvolution/ HEAD
echo -n "SUBVER = "
echo $SUBVER

version=3.5.0-$SUBVER



cd /var/www/html/mymuse-git
rm -f com_mymusesimple*

zip -r  com_mymusesimple-$version.zip mymusesimple -x *.git



echo -n "NEW VERSION =  "
echo $version


