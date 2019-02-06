<?php 

class MyMuseJjFormatCest
{


    public function _before(AcceptanceTester $I)
    {

        $this->myConfigFormat = array(
            'my_download_dir' => '/media/downloads',
            'my_preview_dir' => '/media/previews',
            'my_download_dir_format' => "1"
        );

        include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');

        $I->doAdministratorLogin();

        $I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
        $I->waitForText('Extensions: Manage', '30', array('css' => 'H1'));
        $I->searchForItem('MyMuse');
        if($I->seePageHasText('There are no extensions installed matching your query')){
              //we are good to go. MyMuse is not installed.
        }else{
              $I->uninstallExtension('mymuse');
        }
          
        $I->amOnPage('/administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
        $I->waitForText('Menus: Items (Main Menu)', '30', array('css' => 'H1'));
        if($I->seePageHasText('Single')){
            $I->clearMenus();
        }
        $I->clearTrashedMenus();

        $I->amOnPage('/administrator/index.php?option=com_users&view=users');
        $I->waitForText('Users', '30', array('css' => 'H1'));
        if($I->seePageHasText('Buyer') || $I->seePageHasText('Test-User') ){
            $I->clearUsers();
        }

        $this->mock_cd->jform_product_physical                  = "No";
        $this->mock_cd->jform_attribs_product_price_physical    = "20.00";
        $this->mock_cd->jform_attribs_product_price_mp3         = "2.00";
        $this->mock_cd->jform_attribs_product_price_mp3_all     = "10.00";
        $this->mock_cd->jform_attribs_product_price_wav         = "3.00";
        $this->mock_cd->jform_attribs_product_price_wav_all     = "15.00";

        $path = "com_mymusesimple-latest.zip";
        $I->installExtensionFromFileUpload($path, 'Extension');
        $I->changeGlobalOptions($this->mock_user_config);
        $I->changeStoreConfig($this->mock_format_config);
        $I->changeStoreConfig($this->mock_pricing_config);

        $I->createMymuseCategories();

        //basic product with tracks
        
        $id1 = $I->createMymuseProduct($this->mock_cd);

        $I->comment("Making tracks for id $id1");
        $this->mock_track->id = $id1;
        $I->createMymuseTrack($this->mock_track, $this->myConfigFormat);
        $this->mock_track1->id = $id1;
        $I->createMymuseTrack($this->mock_track1, $this->myConfigFormat);

        $I->comment("Making ALL tracks for id $id1");
        $this->mock_all_track->id = $id1;
        $I->createMymuseAllTrack($this->mock_all_track, $this->myConfigFormat);

        $I->comment("Making menu for Single Product");
        $this->mock_single_menu->jform_request_id_id = $id1;
        $I->makeMenus($this->mock_single_menu);

        $I->comment("Making menu for Cart");
        $I->makeMenus($this->mock_cart_menu);
    }

    public function MyMuseJjFormat(AcceptanceTester $I)
    {
        $I->comment("Check Multi-format");
        $I->comment('Select Home Page');
        $I->amOnPage('index.php');
        $I->see($this->mock_single_menu->jform_title);
        $I->click($this->mock_single_menu->jform_title);
        $I->see($this->mock_single_menu->jform_request_id_name);

        $I->selectFromDropdown('#variation_2_id', "2");
        $I->selectFromDropdown('#variation_4_id', "2");

        $I->placeIteminCart($this->mock_order_track);
        $I->wait(2);
        $I->placeIteminCart($this->mock_order_all_track);
        $I->wait(2);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('Please log in or register');
        $I->doFrontEndLogin();
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');      
        $I->comment("OrderID was ".$id);

        //see if it's in admin
        $I->comment("see if it's in admin");
        $I->doAdministratorLogin();
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
        $I->click($id);
        //administrator/index.php?option=com_mymuse&view=order&layout=edit&id=1007
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        $I->wait(4);



    }


}
