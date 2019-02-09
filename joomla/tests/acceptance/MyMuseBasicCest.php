<?php 

class MyMuseBasicCest
{
    public function _before(AcceptanceTester $I)
    {

        $this->myConfigStd = array(
            'my_download_dir' => '/images/mymuse/downloads',
            'my_preview_dir' => '/images/mymuse/previews',
            'my_download_dir_format' => "1",
            'my_price_by_product' => 0,
            'my_formats' => array('mp3')
        );

        include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');

        $I->doAdministratorLogin();

        $I->wait(3);
        if($I->seePageHasText('would like your permission')){
            $I->click('Never');
            $I->wait(3);
        }else{
            $I->comment("No Stats");
        }
        if($I->seePageHasText('Read Messages')){
            $I->click('Read Messages');
            $I->wait(3);
            $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'hideAll\');"]']);
        }


        $I->comment("Check if MyMuse needs uninstall");
        $I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
        $I->waitForText('Extensions: Manage', '30', array('css' => 'H1'));
        $I->searchForItem('MyMuse Simple');
        if($I->seePageHasText('There are no extensions installed matching your query')){
            //we are good to go. MyMuse is not installed.
        }else{
            $I->uninstallExtension('MyMuse Simple');
        }
        
        $I->amOnPage('/administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
        $I->waitForText('Menus: Items (Main Menu)', '30', array('css' => 'H1'));
        if($I->seePageHasText('Single')){
            $I->clearMenus();
        }

        $I->amOnPage('/administrator/index.php?option=com_users&view=users');
        $I->waitForText('Users', '30', array('css' => 'H1'));
        if($I->seePageHasText('Buyer') || $I->seePageHasText('Test-User') ){
            $I->clearUsers();
        }


        $path = "com_mymusesimple-latest.zip";
        $I->installExtensionFromFileUpload($path, 'Extension');

        $I->createMymuseCategories();

        //basic product with  tracks
        $id1 = $I->createMymuseProduct($this->mock_cd);

        $I->comment("Making tracks for id $id1");
        $this->mock_track->id = $id1;
        $I->createMymuseTrack($this->mock_track, $this->myConfigStd);


        $I->comment("Making menu for Single Product");
        $this->mock_single_menu->jform_request_id_id = $id1;
        $I->makeMenus($this->mock_single_menu);

        //make a cart menu
        $I->comment("Making menu for Cart");
        $I->makeMenus($this->mock_cart_menu);
        //$I->createMenuItem('My Cart', 'MyMuse', 'Shopping Cart');

       //make a list my orders menu
        $I->comment("Making menu for List My Orders");
        $I->createMenuItem2('List My Orders', 'MyMuse Simple', 'List My Orders');

        //make an edit profile menu
        $I->comment("Making menu for Edit Profile");
        $I->createMenuItem2('Edit Profile', 'Users', 'Edit User Profile');
    

    }

    // tests

    public function MyMuseBasic(AcceptanceTester $I)
    {
        
    //orderOne

        $I->placeIteminCart($this->mock_order_track);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);

        if($I->seePageHasText('Please log in or register')){
            $I->doFrontEndLogin();
        }
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
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        $I->wait(1);

        //See if it's in the front end
        $I->amOnPage('index.php');
        if($I->seePageHasText('Log in')){
            $I->doFrontEndLogin();
        }
        $I->click("List My Orders");
        $I->waitForText('Your Order History', 30);
        $I->see($id);

        $I->doFrontendLogout();

//FULL REGISTRATION

        $I->doAdministratorLogin();
        $I->changeGlobalOptions($this->mock_user_config);
        $I->changeStoreConfig($this->mock_regFull_config);
        $I->disablePlugin("User - MyMuse No Registration Profile");
        $I->enablePlugin("User - MyMuse Profile");


        $I->placeIteminCart($this->mock_order_track);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        if($I->seePageHasText('Please log in or register')){
            $I->fillFullRegForm($this->mock_user);
            $I->wait(2);
            $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);
        }
        
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
        
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        $I->wait(1);


        //NO REGISTRATION
        //$I->doAdministratorLogin();
        $I->comment("NO REGISTRATION)");
        $I->changeGlobalOptions($this->mock_user_default_config);

        $I->changeStoreConfig($this->mock_noReg_config);

        $I->comment("Change Plugins configuration)");
        $I->enablePlugin("User - MyMuse No Registration Profile");
        $I->disablePlugin("User - MyMuse Profile");


        $I->placeIteminCart($this->mock_order_track);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->wait(2);

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
