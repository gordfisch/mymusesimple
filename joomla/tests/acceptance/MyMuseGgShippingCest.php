<?php 

class MyMuseGgShippingCest
{
    public function _before(AcceptanceTester $I)
    {
    	include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');
        $this->mock_stock_config_default = $this->mock_stock_config;
        $I->doAdministratorLogin();

        $I->comment("Check if MyMuse needs un install");
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

        $I->amOnPage('/administrator/index.php?option=com_users&view=users');
        $I->waitForText('Users', '30', array('css' => 'H1'));
        if($I->seePageHasText('Buyer') || $I->seePageHasText('Test-User') ){
            $I->clearUsers();
        }


        $path = "com_mymuse-latest.zip";
        $I->installExtensionFromFileUpload($path, 'Extension');

        $I->createMymuseCategories();

        //basic product with both CD and tracks
        $id1 = $I->createMymuseProduct($this->mock_cd);

        $I->comment("Making menu for Single Product");
        $this->mock_single_menu->jform_request_id_id = $id1;
        $I->makeMenus($this->mock_single_menu);

        //make a cart menu
        $I->comment("Making menu for Cart");
        $I->createMenuItem2('My Cart', 'MyMuse', 'Shopping Cart');

        //make a list my orders menu
        $I->comment("Making menu for List My Orders");
        $I->createMenuItem2('List My Orders', 'MyMuse', 'List My Orders');
        
        //make an edit profile menu
        $I->comment("Making menu for Edit Profile");
        $I->createMenuItem2('Edit Profile', 'Users', 'Edit User Profile');


        $I->changeGlobalOptions($this->mock_user_config);
        $I->changeStoreConfig($this->mock_regFull_config);

        $I->disablePlugin("User - MyMuse No Registration Profile");
        $I->enablePlugin("User - MyMuse Profile");

        $I->fillFullRegForm($this->mock_user);
        $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);

 
    }

    // tests
    public function MyMuseGgShipping(AcceptanceTester $I)
    {
    	

        //check SHIPPING STANDARD
        $I->comment("Enable Plugin: MyMuse Shipping Standard)");
        $I->enablePlugin("MyMuse Shipping Standard");
        $I->disablePlugin("MyMuse Shipping by Price");
        $I->disablePlugin("MyMuse Shipping USPS");
        $I->click('Clear');
        

        $shipping['plugin'] = 'MyMuse Shipping Standard';
        $shipping['test_country'] = 'United States';
        $shipping['test_region'] = 'Vermont';
        $shipping['test_postal_code'] = '05682';
        $shipping['mycost']['option1']['quantity1']    = '$5.00';
        $shipping['mytotal']['option1']['quantity1']   = '$25.00';
        $shipping['mycost']['option2']['quantity1']    = '$20.00';
        $shipping['mytotal']['option2']['quantity1']   = '$40.00';
        $shipping['mycost']['option1']['quantity2']    = '$6.00';
        $shipping['mytotal']['option1']['quantity2']   = '$46.00';
        $shipping['mycost']['option2']['quantity2']    = '$22.00';
        $shipping['mytotal']['option2']['quantity2']   = '$62.00';

        $this->_shipping($I, $shipping);


        //check SHIPPING BY PRICE
        $I->comment("Enable Plugin: MyMuse Shipping by Price");
        $I->disablePlugin("MyMuse Shipping Standard");
        $I->enablePlugin("MyMuse Shipping by Price");
        $I->disablePlugin("MyMuse Shipping USPS");
        $I->click('Clear');
        

        $shipping['plugin'] = 'MyMuse Shipping by Price';
        $shipping['test_country'] = 'United States';
        $shipping['test_region'] = 'Vermont';
        $shipping['test_postal_code'] = '05682';
        $shipping['mycost']['option1']['quantity1']    = '$5.00';
        $shipping['mytotal']['option1']['quantity1']   = '$25.00';
        $shipping['mycost']['option2']['quantity1']    = '$15.00';
        $shipping['mytotal']['option2']['quantity1']   = '$35.00';
        $shipping['mycost']['option1']['quantity2']    = '$9.00';
        $shipping['mytotal']['option1']['quantity2']   = '$49.00';
        $shipping['mycost']['option2']['quantity2']    = '$28.00';
        $shipping['mytotal']['option2']['quantity2']   = '$68.00';

        $this->_shipping($I, $shipping);

        //check SHIPPING USPS
        $I->comment("Enable Plugin: MyMuse Shipping USPS");
        $I->disablePlugin("MyMuse Shipping Standard");
        $I->disablePlugin("MyMuse Shipping by Price");
        $I->enablePlugin("MyMuse Shipping USPS");
        $I->click('Clear');

        $shipping['plugin'] = 'MyMuse Shipping USPS';
        $shipping['test_country'] = 'Canada';
        $shipping['test_region'] = 'Quebec';
        $shipping['test_postal_code'] = 'H4V 2K1';
        $shipping['option1'] = "Media Mail Parcel";
        $shipping['option2'] = "Priority Mail Medium Flat";

        $shipping['mycost']['option1']['quantity1']    = '$2.75';
        $shipping['mytotal']['option1']['quantity1']   = '$22.75';
        $shipping['mycost']['option2']['quantity1']    = '$14.35';
        $shipping['mytotal']['option2']['quantity1']   = '$34.35';
        $shipping['mycost']['option1']['quantity2']    = '$3.27';
        $shipping['mytotal']['option1']['quantity2']   = '$43.27';
        $shipping['mycost']['option2']['quantity2']    = '$14.35';
        $shipping['mytotal']['option2']['quantity2']   = '$54.35';


        $this->_shipping($I, $shipping);

    }

    protected function _shipping(&$I, $shipping)
    {
        $I->comment('***************** Start shipping function for '.$shipping['plugin']);
        $this->mock_shipping_config->select[1]['value']             = "No";
        $I->changeStoreConfig($this->mock_shipping_config);
        $I->clearCart();
        $I->placeIteminCart($this->mock_order_cd);
        $I->wait(2);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
    
        $I->comment("Check Shipping Option 1)");
        $I->selectOption(array('id' => 'shipmethodid1'), '1');
        $I->click(['id' => 'shipping']);
        $I->waitForElement(array('css' => '#order_shipping_cost'));
        if($shipping['plugin'] == 'MyMuse Shipping USPS'){
            $I->see($shipping['option1'], ['css' => '#order_shipping_name']);
        }else{
            $I->see($shipping['mycost']['option1']['quantity1'], ['css' => '#order_shipping_cost']);
            $I->see($shipping['mytotal']['option1']['quantity1'], ['css' => '#mytotal']);
        }
        $I->comment("Change Plugins configuration: make shipping 2 enabled)");
        $I->amOnPage('administrator/index.php?option=com_plugins&view=plugins&filter_folder=mymuse');
        $I->waitForElement(array('css' => '#pluginList'));
        $I->click($shipping['plugin']);
        $I->click("Ship Method 2");
        $I->waitForElement(['css' => '#jform_params_ship_2_active-lbl']);
        $I->selectOptionInRadioField('2. Published', 'Yes');
        $I->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'plugin.save\');"]']);


        $I->comment("Check Shipping Option 2)");
        $I->amOnPage('index.php');
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->selectOption(array('id' => 'shipmethodid2'), '2');
        $I->click(['id' => 'shipping']);
        $I->waitForElement(array('css' => '#order_shipping_cost'));
        if($shipping['plugin'] == 'MyMuse Shipping USPS'){
            $I->see($shipping['option2'], ['css' => '#order_shipping_name']);
        }else{
            $I->see($shipping['mycost']['option2']['quantity1'], ['css' => '#order_shipping_cost']);
            $I->see($shipping['mytotal']['option2']['quantity1'], ['css' => '#mytotal']);
        }


        //check with quantity 2
        $I->click("My Cart");
        $I->fillField(array('xpath' => '//*[@name="quantity[1]"]'), '2');
        $I->click('Update');
        $I->click(['id' => 'checkout']);
        $I->selectOption(array('id' => 'shipmethodid1'), '1');
        $I->click(['id' => 'shipping']);
        $I->waitForElement(array('css' => '#order_shipping_cost'));
        if($shipping['plugin'] == 'MyMuse Shipping USPS'){
            $I->see($shipping['option1'], ['css' => '#order_shipping_name']);
        }else{
            $I->see($shipping['mycost']['option1']['quantity2'], ['css' => '#order_shipping_cost']);
            $I->see($shipping['mytotal']['option1']['quantity2'], ['css' => '#mytotal']);
        }

        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->selectOption(array('id' => 'shipmethodid2'), '2');
        $I->click(['id' => 'shipping']);
        if($shipping['plugin'] == 'MyMuse Shipping USPS'){
            $I->see($shipping['option2'], ['css' => '#order_shipping_name']);
        }else{
            $I->see($shipping['mycost']['option2']['quantity2'], ['css' => '#order_shipping_cost']);
            $I->see($shipping['mytotal']['option2']['quantity2'], ['css' => '#mytotal']);
        }
        $I->wait(2);


        //check ADD SHIPPING AUTOMATICALLY
         $I->comment("ADD SHIPPING AUTOMATICALLY");
         $this->mock_shipping_config->select[1]['value']             = "Yes";
         $I->changeStoreConfig($this->mock_shipping_config);

         $I->comment("Change Plugins configuration: make shipping 2 enabled");
         $I->amOnPage('administrator/index.php?option=com_plugins&view=plugins&filter_folder=mymuse');
         $I->click($shipping['plugin']);
         $I->click("Ship Method 2");
         $I->waitForElement(['css' => '#jform_params_ship_2_active-lbl']);
         $I->selectOptionInRadioField('2. Published', 'No');
         $I->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'plugin.save\');"]']);

         $I->clearCart();
         $I->placeIteminCart($this->mock_order_cd);

         $I->amOnPage('index.php');
         $I->click("My Cart");
         $I->click(['id' => 'checkout']);
         $I->see('Shipping added');



        //let's try with different country that's blocked
        $this->mock_shipping_config->select[1]['value']             = "No";
        $I->changeStoreConfig($this->mock_shipping_config);
        $I->comment("Change Plugins configuration)");
        $I->amOnPage('administrator/index.php?option=com_plugins&view=plugins&filter_folder=mymuse');
        $I->click($shipping['plugin']);
        $I->waitForElement(array('css' => '.page-title'));
        $I->click("Ship Method 1");
        $I->selectMultipleOptionsInChosen('1. Countries', array('United States'));
        $I->click("Ship Method 2");
        $I->waitForElement(array('css' => '#jform_params_ship_2_active'));
        $I->selectOptionInRadioField('2. Published', 'No');
        $I->click("//fieldset[@id='jform_params_ship_2_active']/label[contains(normalize-space(string(.)), 'No')]");
        $I->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'plugin.save\');"]']);
        $I->see("Plugin saved");

        $I->amOnPage('index.php');
        $I->click("Edit Profile");
        $I->waitForElement(array('css' => '#jform_profile_country'));
        $I->scrollTo(array('css' => '#jform_profile_country'));
        $I->selectOptionInChosenByIdUsingJs('jform_profile_country', $shipping['test_country']); 
        $I->selectOptionInChosenByIdUsingJs('jform_profile_region', $shipping['test_region']);
        $I->selectOptionInChosenByIdUsingJs('jform_profile_postal_code', $shipping['test_postal_code']);
        $I->click("Submit");

        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see("Sorry, no shipping options are available!");



    }
}
