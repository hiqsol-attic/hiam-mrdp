<?php

namespace hiam\mrdp\tests\acceptance;

use hiam\tests\_support\AcceptanceTester;

class ConfirmEmailCest
{
    public function ensureIAmGetWrongEmailToConfirmError(AcceptanceTester $I)
    {
        $I->wantTo('check wrong email to confirm is get error.');
        $I->amOnPage('/registration/confirm?' . http_build_query([
                'email' => 'test@test.com', 'client' => 'test_client', 'id' => 1234567, 'salt' => 'test_salt',
                'hash' => 'test_hash', 'what' => 'contactConfirmEmail',
            ]));
        $I->see('wrong email to confirm');
    }

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
