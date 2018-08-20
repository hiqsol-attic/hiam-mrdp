<?php

namespace hiam\mrdp\tests\acceptance;

use hiam\mrdp\tests\_support\AcceptanceTester;

class ConfirmEmailsCest
{
    public function ensureIGetAnErrorWhenITryToClientConfirmEmail(AcceptanceTester $I)
    {
        $I->wantTo('check I get an error when I try to visit the client confirm page without token');
        $I->amOnPage('/registration/client-confirm-email');
        $I->waitForElement('.ui-pnotify-text', 10);
        $I->see('Failed confirm email. Please start over.', ['css' => '.ui-pnotify-text']);
    }

//    public function ensureIGetAnErrorWhenITryToContactConfirmEmail(AcceptanceTester $I)
//    {
//        $I->wantTo('check I get an error when I try to visit the contact confirm page without token');
//        $I->amOnPage('/registration/contact-confirm-email');
//        $I->waitForElement('.ui-pnotify-text', 10);
//        $I->see('Failed confirm email. Please start over.', ['css' => '.ui-pnotify-text']);
//    }
}
