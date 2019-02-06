<?php 
use \Codeception\Util\Locator;
class MyMuseEeStockCest
{
    var $id = 1;

    public function _before(AcceptanceTester $I)
    {
    	include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');
        $this->mock_stock_config_default = $this->mock_stock_config;
        $this->myConfigStd = array(
            'my_download_dir' => '/images/A_MyMuseDownloads',
            'my_preview_dir' => '/images/A_MyMusePreviews',
            'my_download_dir_format' => "0"
        );

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
        $this->id = $id1;

        $I->comment("Making tracks for id $id1");
        $this->mock_track->id = $id1;
        $I->createMymuseTrack($this->mock_track, $this->myConfigStd);

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

        $this->mock_user->jform_profile_country                 = 'United Kingdom';
        $this->mock_user->jform_profile_region                  = 'Scotland';
        $this->mock_user->jform_profile_postal_code             = 'EH10 4BF';
        $I->fillFullRegForm($this->mock_user);

        $I->comment("Enable Plugin: MyMuse Shipping Standard)");
        $I->enablePlugin("MyMuse Shipping Standard");
        $I->disablePlugin("MyMuse Shipping by Price");
        $I->disablePlugin("MyMuse Shipping USPS");
        $I->click('Clear');

        $I->changeStoreConfig($this->mock_shipping_config);
       
        $this->mock_delay_config->select[0]['value'] = "6000";
        $I->changeStoreConfig($this->mock_delay_config);

    }

    // tests
    public function MyMuseEeStock(AcceptanceTester $I)
    {
    	


        //USE STOCK
        $I->comment('******** Check USE STOCK');
        $this->mock_stock_config->select[0]['value']             = "Yes";
        $I->changeStoreConfig($this->mock_stock_config);

        $I->comment('Get current Stock');
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id=1');
        $I->waitForElement(array('css' => '#jform_product_in_stock'));
        $current_stock = $I->grabValueFrom('Product in Stock');
        $I->comment("Current Stock ".$current_stock);

        $I->amOnPage('index.php');
        if($I->seePageHasText('Log in')){
            $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);
        }

        $I->amOnPage('index.php');
        if($I->seePageHasText('Log in')){
            $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);
        }

        $I->amOnPage('index.php');

        $I->click("My Cart");
        if($I->seePageHasText('Your cart is empty')){
            $I->placeIteminCart($this->mock_order_cd);
        }
        $I->wait(4);
        $I->click("My Cart");

        $I->waitForElement(array('css' => '#quantity1'));
        $quantity_purchased = $I->grabValueFrom(['name' => 'quantity[1]']);

        $I->comment("Quantity purchased = ".$quantity_purchased);
        $I->click(['id' => 'checkout']);
        $I->selectOption(array('id' => 'shipmethodid1'), '1');
        $I->click(['id' => 'shipping']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');      
        $I->comment("OrderID was ".$id);

        //confirm the order
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
        $I->wait(2);
        $I->click($id);
        //administrator/index.php?option=com_mymuse&view=order&layout=edit&id=1007
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);

        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id=1');
        $I->waitForElement(array('css' => '#jform_product_in_stock'));
        $new_stock = $I->grabValueFrom('Product in Stock');
        $I->comment("New Stock ".$new_stock);
        $I->assertEquals($new_stock, $current_stock - $quantity_purchased);


 

        //checK CHECK STOCK
        $I->comment('******** Check CHECK STOCK');
        $this->mock_stock_config->select[1]['value']             = "Yes";
        $I->changeStoreConfig($this->mock_stock_config);

        $I->comment('Get current Stock');
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id=1');
        $I->waitForElement(array('css' => '#jform_product_in_stock'));
        $current_stock = $I->grabValueFrom('Product in Stock');
        $I->comment("Current Stock ".$current_stock);

        $I->doFrontendLogout();
        $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);

        $I->amOnPage('index.php');
        $I->placeIteminCart($this->mock_order_cd);
        $I->wait(4);
        $I->click("My Cart");
        $I->fillField(array('id' => 'quantity1'), $current_stock + 1);
        $I->click("Update");
        $I->see("Exceeds Available Stock. Available stock: ".$current_stock);
        $I->wait(1);
        


        //check ADD ZERO STOCK
        $I->comment("******** Check ADD ZERO STOCK");
        $this->mock_stock_config->select[2]['value']             = "Yes";
        $I->changeStoreConfig($this->mock_stock_config);


        $I->comment('Update current Stock');
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id=1');
        $I->waitForElement(array('css' => '#jform_product_in_stock'));
        $I->fillField(array('id' => 'jform_product_in_stock'),   $this->mock_cd->jform_product_in_stock);
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.save\');"]']);
        $current_stock = $this->mock_cd->jform_product_in_stock;


        $I->amOnPage('index.php');
        //$I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->fillField(array('id' => 'quantity1'), $current_stock + 1);
        $I->click("Update");
        $I->see("Exceeds Available Stock. Available stock: ".$current_stock);
        $I->see('Backordered');
        $I->click(['id' => 'checkout']);
        $I->selectOption(array('id' => 'shipmethodid1'), '1');
        $I->click(['id' => 'shipping']);
        $I->waitForElement(array('css' => '#order_shipping_cost'));

        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');      
        $I->comment("OrderID was ".$id);

        //confirm the order
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
        $I->see("Backordered item");
        $I->click($id);
        //administrator/index.php?option=com_mymuse&view=order&layout=edit&id=1007
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        $I->see("Exceeds Available Stock. Available stock: ".$current_stock);

        $I->comment('Update current Stock');
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id=1');
        $I->waitForElement(array('css' => '#jform_product_in_stock'));
        $current_stock = $current_stock + 1;
        $I->fillField(array('id' => 'jform_product_in_stock'),  $current_stock);
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.save\');"]']);


        //ship the order
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
        $I->see("Backordered item");
        $I->click($id);
        //administrator/index.php?option=com_mymuse&view=order&layout=edit&id=1007
        $I->see('Order Summary');
        $I->scrollTo(array('css' => '#shipit'));
        $I->click(["id" => "shipit"]);
        $I->see("Item Backorder removed");


        //Check SPECIAL STATUS
        $this->mock_stock_config->select[1]['value'] = "Yes";
        $this->mock_stock_config->select[2]['value'] = "Yes";
        $I->changeStoreConfig($this->mock_stock_config);
 

        $I->comment('Check SPECIAL STATUS: COMING SOON');
        $this->mock_product_config->id                            = $this->id;
        $this->mock_product_config->tab                           = "Details";
        $this->mock_product_config->select[0]['option']           = "jform_attribs_special_status";
        $this->mock_product_config->select[0]['value']            = "Coming Soon";
        $this->mock_product_config->select[0]['type']             = "select";
        $I->editMymuseProductField($this->mock_product_config);
        $I->clearCart();
      
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Coming Soon");
        $I->placeIteminCart($this->mock_order_cd);
        $I->see("Coming Soon", '#my_content');


        $I->comment('Check SPECIAL STATUS: OUT OF STOCK');
        $this->mock_product_config->select[0]['value']            = "Out of Stock";
        $I->editMymuseProductField($this->mock_product_config);
      
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Out of Stock");
        $I->placeIteminCart($this->mock_order_cd);
        $I->see("Out of Stock", '#my_content');


        $I->comment('Check SPECIAL STATUS: NO LONGR AVAILABLE');
        $this->mock_product_config->select[0]['value']            = "No Longer Available";
        $I->editMymuseProductField($this->mock_product_config);
      
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("No Longer Available");
        $I->placeIteminCart($this->mock_order_cd);
        $I->see("No Longer Available", '#my_content');


        $I->comment('Check SPECIAL STATUS: PRE ORDER');
        $this->mock_product_config->select[0]['value']            = "Pre Order";
        $I->editMymuseProductField($this->mock_product_config);
        
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Pre Order");
        $I->placeIteminCart($this->mock_order_cd);
        $I->see("Exceeds Available Stock", '#my_content');




        //Try with DIGITAL PRODUCTS
        $I->comment('Check SPECIAL STATUS: DIGITAL: COMING SOON');
        $this->mock_product_config->id                            = $this->id;
        $this->mock_product_config->tab                           = "Details";
        $this->mock_product_config->select[0]['option']           = "jform_attribs_special_status";
        $this->mock_product_config->select[0]['value']            = "Coming Soon";
        $this->mock_product_config->select[0]['type']             = "select";
        $I->editMymuseProductField($this->mock_product_config);

        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Coming Soon");
        $I->placeIteminCart($this->mock_order_track);
        $I->see("Coming Soon", '#my_content');


        $I->comment('Check SPECIAL STATUS: DIGITAL: OUT OF STOCK');
        $this->mock_product_config->select[0]['value']            = "Out of Stock";
        $I->editMymuseProductField($this->mock_product_config);
      
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Out of Stock");
        $I->placeIteminCart($this->mock_order_track);
        $I->see("Out of Stock", '#my_content');


        $I->comment('Check SPECIAL STATUS: DIGITAL: NO LONGR AVAILABLE');
        $this->mock_product_config->select[0]['value']            = "No Longer Available";
        $I->editMymuseProductField($this->mock_product_config);
      
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("No Longer Available");
        $I->placeIteminCart($this->mock_order_track);
        $I->see("No Longer Available", '#my_content');


        $I->comment('Check SPECIAL STATUS: DIGITAL: PRE ORDER');
        $this->mock_product_config->select[0]['value']            = "Pre Order";
        $I->editMymuseProductField($this->mock_product_config);
        

        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Pre Order");
        $I->placeIteminCart($this->mock_order_track);
        $I->see("Added Are You My Sister Song", '#my_content');

        //change the product_made_date to zero or in the future.
        $this->mock_product_config->tab                           = "Recording Details";
        $this->mock_product_config->select[0]['option']           = "jform_product_made_date";
        $this->mock_product_config->select[0]['value']            = "2040-02-08 ";
        $this->mock_product_config->select[0]['type']             = "text";
        $I->editMymuseProductField($this->mock_product_config);

        $I->clearCart();
        $I->amOnPage('index.php');
        $I->click("Single");
        $I->see("Pre Order");
        $I->placeIteminCart($this->mock_order_track);
        $I->see("Pre Ordered", '#my_content');
        $I->wait(4);

        $I->click("My Cart");
        $I->see("Pre Ordered", '.mymuse_msg');

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

        $I->amOnPage('administrator/index.php?option=com_mymuse&view=product&layout=edit&id='.$this->id);
        $I->click('Alert Customers');
        $I->see('Sent notice to order with id '.$this->id);

        $this->mock_delay_config->select[0]['value'] = "3000";
        $I->changeStoreConfig($this->mock_delay_config);

    }

}
