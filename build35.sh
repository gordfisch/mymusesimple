#/bin/bash
# Create repository
# svn import com_mymuse_j3 svn://host.arboreta.ca/mymuse3/trunk
svn update /home/gord/workspace/MyMuse3.5
SUBVER=`svnversion /home/gord/workspace/MyMuse3.5`

echo -n "SUBVER = "
echo $SUBVER


version=3.4.0-$SUBVER
cd /var/www/html/mymuse35
rm -fR com_mymuse_J3*
rm -fR *.zip


#svn export svn://host.arboreta.ca/mymuse1.5/branches/stable1.0.110535
svn export svn://host.arboreta.ca/mymuse3/trunk com_mymuse_J3

zip -r  com_mymuse_J3-$version.zip com_mymuse_J3
cp com_mymuse_J3-$version.zip com_mymuse_J3-latest.zip


cd com_mymuse_J3/modules

zip -r mod_mymuse_latest-J3-3.2.0.zip mod_mymuse_latest
mv mod_mymuse_latest-J3-3.2.0.zip ../../
echo "created mod_mymuse_latest_J3"

zip -r mod_mymuse_minicart-J3-3.3.0.zip mod_mymuse_minicart
mv mod_mymuse_minicart-J3-3.3.0.zip ../../
echo "created mod_mymuse_minicart_J3"

zip -r mod_mymuse_jplayer-J3-3.4.0.zip mod_mymuse_jplayer
mv mod_mymuse_jplayer-J3-3.4.0.zip ../../
echo "created mod_mymuse_jplayer_J3"

cd ../plugins

zip -r audio_jplayer_J3-3.4.0.zip audio_jplayer
mv audio_jplayer_J3-3.4.0.zip ../../
echo "created audio jplayer_J3"

zip -r finder_mymuse_J3-3.0.0.zip finder_mymuse
mv finder_mymuse_J3-3.0.0.zip ../../
echo "created finder_mymuse_J3"


zip -r payment_moneybookers_J3-3.2.0.zip payment_moneybookers
mv payment_moneybookers_J3-3.2.0.zip ../../
echo "created payment_moneybookers_J3"

zip -r payment_monsterpay_J3-3.2.0.zip payment_monsterpay
mv payment_monsterpay_J3-3.2.0.zip ../../
echo "created payment_monsterpay_J3"

zip -r payment_payfast_J3-3.2.0.zip payment_payfast
mv payment_payfast_J3-3.2.0.zip ../../
echo "created payment_payfast_J3"

zip -r payment_pesapal_J3-3.2.0.zip payment_pesapal
mv payment_pesapal_J3-3.2.0.zip ../../
echo "created payment_pesapal_J3"

zip -r payment_offline_J3-3.2.0.zip payment_offline
mv payment_offline_J3-3.2.0.zip ../../
echo "created payment_offline_J3"

zip -r payment_paypal_J3-3.3.2.zip payment_paypal
mv payment_paypal_J3-3.3.2.zip ../../
echo "created payment_paypal_J3"

zip -r payment_paypalpro_J3-3.3.2.zip payment_paypalpro
mv payment_paypalpro_J3-3.3.2.zip ../../
echo "created payment_paypalpro_J3"

zip -r payment_paypalproexpress_J3-3.3.2.zip payment_paypalproexpress
mv payment_paypalproexpress_J3-3.3.2.zip ../../
echo "created payment_paypalproexpress_J3"

zip -r payment_virtualmerchant_J3-3.2.0.zip payment_virtualmerchant
mv payment_virtualmerchant_J3-3.2.0.zip ../../
echo "created payment_virtualmerchant_J3"

zip -r payment_payunity_J3-3.3.2.zip payment_payunity
mv payment_payunity_J3-3.3.2.zip ../../
echo "created payment_payunity_J3"

zip -r preorder_J3-3.0.0.zip preorder
mv preorder_J3-3.0.0.zip ../../
echo "created preorder_J3"


zip -r searchmymuse_J3-3.0.0.zip searchmymuse
mv searchmymuse_J3-3.0.0.zip ../../
echo "created search_J3"

zip -r shipping_price_J3-3.3.0.zip shipping_price
mv shipping_price_J3-3.3.0.zip ../../
echo "created shipping_price_J3"

zip -r shipping_standard_J3-3.2.0.zip shipping_standard
mv shipping_standard_J3-3.2.0.zip ../../
echo "created shipping_standard_J3"

zip -r shoppergroupview_J3-3.0.0.zip shoppergroupview
mv shoppergroupview_J3-3.0.0.zip ../../
echo "created shoppergrouviewp_J3"

zip -r user_mymuse_J3-3.2.10.zip user_mymuse
mv user_mymuse_J3-3.2.10.zip ../../
echo "created user_mymuse_J3"

zip -r user_mymusenoreg_J3-3.2.10.zip user_mymusenoreg
mv user_mymusenoreg_J3-3.2.10.zip ../../
echo "created user_mymusenoreg_J3"

zip -r user_redirectonlogin_J3-3.0.0.zip user_redirectonlogin
mv user_redirectonlogin_J3-3.0.0.zip ../../
echo "created user_redirectonlogin_J3"

zip -r user_redirectonprofile_J3-3.4.0.zip user_redirectonprofile
mv user_redirectonprofile_J3-3.4.0.zip ../../
echo "created user_redirectonprofile_J3"


zip -r video_jplayer_J3-3.4.0.zip video_jplayer
mv video_jplayer_J3-3.4.0.zip ../../
echo "created video_jplayer_J3"

zip -r mymuse_vote_J3-3.2.0.zip mymuse_vote
mv mymuse_vote_J3-3.2.0.zip ../../
echo "created mymuse_vote_J3"

zip -r mymuse_socialshare_J3-3.2.10.zip mymuse_socialshare
mv mymuse_socialshare_J3-3.2.10.zip ../../
echo "created mymuse_socialshare_J3"

zip -r mymuse_discount_J3-3.3.2.zip mymuse_discount
mv mymuse_discount_J3-3.3.2.zip ../../
echo "created mymuse_discount_J3"

zip -r mymuse_licenceprice_J3-3.4.0.zip mymuse_licenceprice
mv mymuse_licenceprice_J3-3.4.0.zip ../../
echo "created mymuse_licenceprice_J3"

zip -r mymuse_shortcode_J3-3.3.2.zip mymuse_shortcode
mv mymuse_shortcode_J3-3.3.2.zip ../../
echo "created mymuse_shortcode_J3"

zip -r audio_amplitude_J3-3.4.0.zip audio_amplitude
mv audio_amplitude_J3-3.4.0.zip ../../
echo "created audio_amplitude_J3"

echo -n "NEW VERSION = J3 "
echo $version


# branch creation
#svn copy svn://host.arboreta.ca/mymuse1.5/trunk svn://host.arboreta.ca/mymuse16/branches/stable1.0.110535
# to merge, switch to branch in eclipse and do merge.
# svn import mymuse1.6 svn://host.arboreta.ca/mymuse1.6/trunk  -m "Initial import"

#log
#svn log svn://host.arboreta.ca/mymuse3/trunk

