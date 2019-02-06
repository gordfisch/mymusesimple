<?php 

class MyMuseGgNoRegistrationCest
{
	public function _before(AcceptanceTester $I)
	{
		include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');
	}

	// tests
	public function MyMuseGgNoRegistration(AcceptanceTester $I)
	{
        $I->doAdministratorLogin();
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
