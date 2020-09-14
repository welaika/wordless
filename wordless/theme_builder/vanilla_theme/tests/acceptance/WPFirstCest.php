<?php

class WPFirstCest
{
    public function _before(AcceptanceTester $I)
    {
        // $userId = $I->haveUserInDatabase('user', 'editor', ['user_pass' => 'password', 'user_email' => 'user@example.com']);
    }

    // tests
    public function homePagePostWithContent(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Hello world!');
        $I->click(['link' => 'Hello world!']);
        $I->see("Every day I’m shuffling");
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

    public function beAbleToAddCutomFieldToPost(AcceptanceTester $I)
    {
        define('TEXT', 'Foo text custom field');

        $id = $I->haveOnePostWithCustomFiled(TEXT);
        $I->seePostInDatabase(['ID' => $id]);
        // clean_post_cache($id);
        // wp_cache_flush();
        $post = get_post($id);
        $I->amOnPage('/' . $post->post_name);
        $I->see($post->post_title, 'h3');
        $I->see(TEXT, 'h4');
    }
}
