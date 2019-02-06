<?php 

class AdminLoginCest
{
    public function login(AcceptanceTester $I)
    {
        $I->doAdministratorLogin();
    }
}
