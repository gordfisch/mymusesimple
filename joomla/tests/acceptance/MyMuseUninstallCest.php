<?php 

class MyMuseUninstallCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function MyMuseUninstall(AcceptanceTester $I)
    {
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

        $I->amOnPage('/administrator/index.php?option=com_users&view=users');
        $I->waitForText('Users', '30', array('css' => 'H1'));
        if($I->seePageHasText('Buyer') || $I->seePageHasText('Test-User') ){
            $I->clearUsers();
        }
    }
}
