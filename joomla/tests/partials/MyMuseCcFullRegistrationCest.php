<?php 

class MyMuseCcFullRegistrationCest
{
	public function _before(AcceptanceTester $I)
	{
		include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');
	}
/*
        $I->click("Edit Profile");
        $I->waitForElement(array('css' => '#jform_profile_country'));
        $I->scrollTo(array('css' => '#jform_profile_country'));
        $I->selectOptionInChosenByIdUsingJs('jform_profile_country', 'United Kingdom');
        $I->wait(1); 
        $I->selectOptionInChosenByIdUsingJs('jform_profile_region', 'Scotland');
        $I->click("Submit");
        */
	// tests
public function MyMuseCcFullRegistration(AcceptanceTester $I)
    {
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
    }

}
