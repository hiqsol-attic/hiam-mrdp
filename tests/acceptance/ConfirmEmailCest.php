<?php

namespace hiam\mrdp\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;

class ConfirmEmailCest
{
    public function ensureIGetErrorsWhenRequiredParamsIsNotPassed(AcceptanceTester $I)
    {
        $I->wantTo('check when url without required params has an validation error.');
        $I->amOnPage('/registration/confirm');
        $I->see('Id cannot be blank');
        $I->see('Client cannot be blank');
        $I->see('Email cannot be blank');
        $I->see('What cannot be blank');
        $I->see('Salt cannot be blank');
        $I->see('Hash cannot be blank');
    }
}
