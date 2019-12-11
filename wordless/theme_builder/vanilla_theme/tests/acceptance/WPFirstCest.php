<?php

class WPFirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function homePagePostWithContent(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Hello world!');
    }

    public function loginAsUser(AcceptanceTester $I)
    {
        // create a user that can create posts
        $user_id = $I->haveUserInDatabase( 'user', 'administrator', [ 'user_pass' => 'user' ] );

        // login to get the cookies set
        $I->loginAs( 'user', 'user' );
    }

    public function addPostAndVisitItsUrl(AcceptanceTester $I)
    {
        $id = $I->haveOnePost();
        $I->seePostInDatabase(['ID' => $id]);
        $post = get_post($id);
        $I->amOnPage('/' . $post->post_name);
        $I->see($post->post_title, 'h3');
    }
}
