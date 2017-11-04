#/bin/bash
cd /var/www/html/mymuse-git/mymusesimple
SUBVER=`git rev-list HEAD | wc -l`
#git archive -o bitvolution.zip --prefix=bitvolution/ HEAD
echo -n "SUBVER = "
echo $SUBVER

version=1.0.1-$SUBVER



cd /var/www/html/mymuse-git
rm -f com_mymusesimple*

zip -r  com_mymusesimple-$version.zip mymusesimple -x *.git* *.sublime* *.buildpath* *.project* *.ext* *.settings* *buildsimple.sh* *build.xml*



echo -n "NEW VERSION =  "
echo $version


