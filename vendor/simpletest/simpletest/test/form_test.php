<?php

require_once __DIR__ . '/../autorun.php';
require_once __DIR__ . '/../url.php';
require_once __DIR__ . '/../form.php';
require_once __DIR__ . '/../page.php';
require_once __DIR__ . '/../encoding.php';

Mock::generate('SimplePage');

class TestOfForm extends UnitTestCase
{
    public function page($url, $action = false)
    {
        $page = new MockSimplePage();
        $page->returns('getUrl', new SimpleUrl($url));
        $page->returns('expandUrl', new SimpleUrl($url));

        return $page;
    }

    public function testFormAttributes()
    {
        $tag  = new SimpleFormTag(array('method' => 'GET', 'action' => 'here.php', 'id' => '33'));
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual($form->getMethod(), 'get');
        $this->assertIdentical($form->getId(), '33');
        $this->assertNull($form->getValue(new SelectByName('a')));
    }

    public function testAction()
    {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', array(new SimpleUrl('here.php')));
        $page->returnsByValue('expandUrl', new SimpleUrl('http://host/here.php'));
        $tag  = new SimpleFormTag(array('method' => 'GET', 'action' => 'here.php'));
        $form = new SimpleForm($tag, $page);
        $this->assertEqual($form->getAction(), new SimpleUrl('http://host/here.php'));
    }

    public function testEmptyAction()
    {
        $tag  = new SimpleFormTag(array('method' => 'GET', 'action' => '', 'id' => '33'));
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/a/index.html'));
    }

    public function testMissingAction()
    {
        $tag  = new SimpleFormTag(array('method' => 'GET'));
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/a/index.html'));
    }

    public function testRootAction()
    {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', array(new SimpleUrl('/')));
        $page->returnsByValue('expandUrl', new SimpleUrl('http://host/'));
        $tag  = new SimpleFormTag(array('method' => 'GET', 'action' => '/'));
        $form = new SimpleForm($tag, $page);
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/'));
    }

    public function testDefaultFrameTargetOnForm()
    {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', array(new SimpleUrl('here.php')));
        $page->returnsByValue('expandUrl', new SimpleUrl('http://host/here.php'));
        $tag  = new SimpleFormTag(array('method' => 'GET', 'action' => 'here.php'));
        $form = new SimpleForm($tag, $page);
        $form->setDefaultTarget('frame');
        $expected = new SimpleUrl('http://host/here.php');
        $expected->setTarget('frame');
        $this->assertEqual($form->getAction(), $expected);
    }

    public function testTextWidget()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleTextTag(
                array('name' => 'me', 'type' => 'text', 'value' => 'Myself')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'Myself');
        $this->assertTrue($form->setField(new SelectByName('me'), 'Not me'));
        $this->assertFalse($form->setField(new SelectByName('not_present'), 'Not me'));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'Not me');
        $this->assertNull($form->getValue(new SelectByName('not_present')));
    }

    public function testTextWidgetById()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleTextTag(
                array('name' => 'me', 'type' => 'text', 'value' => 'Myself', 'id' => 50)));
        $this->assertIdentical($form->getValue(new SelectById(50)), 'Myself');
        $this->assertTrue($form->setField(new SelectById(50), 'Not me'));
        $this->assertIdentical($form->getValue(new SelectById(50)), 'Not me');
    }

    public function testTextWidgetByLabel()
    {
        $form   = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $widget = new SimpleTextTag(array('name' => 'me', 'type' => 'text', 'value' => 'a'));
        $form->addWidget($widget);
        $widget->setLabel('thing');
        $this->assertIdentical($form->getValue(new SelectByLabel('thing')), 'a');
        $this->assertTrue($form->setField(new SelectByLabel('thing'), 'b'));
        $this->assertIdentical($form->getValue(new SelectByLabel('thing')), 'b');
    }

    public function testSubmitEmpty()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $this->assertIdentical($form->submit(), new SimpleGetEncoding());
    }

    public function testSubmitButton()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                array('type' => 'submit', 'name' => 'go', 'value' => 'Go!', 'id' => '9')));
        $this->assertTrue($form->hasSubmit(new SelectByName('go')));
        $this->assertEqual($form->getValue(new SelectByName('go')), 'Go!');
        $this->assertEqual($form->getValue(new SelectById(9)), 'Go!');
        $this->assertEqual(
                $form->submitButton(new SelectByName('go')),
                new SimpleGetEncoding(array('go' => 'Go!')));
        $this->assertEqual(
                $form->submitButton(new SelectByLabel('Go!')),
                new SimpleGetEncoding(array('go' => 'Go!')));
        $this->assertEqual(
                $form->submitButton(new SelectById(9)),
                new SimpleGetEncoding(array('go' => 'Go!')));
    }

    public function testSubmitWithAdditionalParameters()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                array('type' => 'submit', 'name' => 'go', 'value' => 'Go!')));
        $this->assertEqual(
                $form->submitButton(new SelectByLabel('Go!'), array('a' => 'A')),
                new SimpleGetEncoding(array('go' => 'Go!', 'a' => 'A')));
    }

    public function testSubmitButtonWithLabelOfSubmit()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                array('type' => 'submit', 'name' => 'test', 'value' => 'Submit')));
        $this->assertEqual(
                $form->submitButton(new SelectByName('test')),
                new SimpleGetEncoding(array('test' => 'Submit')));
        $this->assertEqual(
                $form->submitButton(new SelectByLabel('Submit')),
                new SimpleGetEncoding(array('test' => 'Submit')));
    }

    public function testSubmitButtonWithWhitespacePaddedLabelOfSubmit()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                array('type' => 'submit', 'name' => 'test', 'value' => ' Submit ')));
        $this->assertEqual(
                $form->submitButton(new SelectByLabel('Submit')),
                new SimpleGetEncoding(array('test' => ' Submit ')));
    }

    public function testImageSubmitButton()
    {
        $form = new SimpleForm(new SimpleFormTag(array()),  $this->page('htp://host'));
        $form->addWidget(new SimpleImageSubmitTag(array(
                'type' => 'image',
                'src'  => 'source.jpg',
                'name' => 'go',
                'alt'  => 'Go!',
                'id'   => '9')));
        $this->assertTrue($form->hasImage(new SelectByLabel('Go!')));
        $this->assertEqual(
                $form->submitImage(new SelectByLabel('Go!'), 100, 101),
                new SimpleGetEncoding(array('go.x' => 100, 'go.y' => 101)));
        $this->assertTrue($form->hasImage(new SelectByName('go')));
        $this->assertEqual(
                $form->submitImage(new SelectByName('go'), 100, 101),
                new SimpleGetEncoding(array('go.x' => 100, 'go.y' => 101)));
        $this->assertTrue($form->hasImage(new SelectById(9)));
        $this->assertEqual(
                $form->submitImage(new SelectById(9), 100, 101),
                new SimpleGetEncoding(array('go.x' => 100, 'go.y' => 101)));
    }

    public function testImageSubmitButtonWithAdditionalData()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleImageSubmitTag(array(
                'type' => 'image',
                'src'  => 'source.jpg',
                'name' => 'go',
                'alt'  => 'Go!')));
        $this->assertEqual(
                $form->submitImage(new SelectByLabel('Go!'), 100, 101, array('a' => 'A')),
                new SimpleGetEncoding(array('go.x' => 100, 'go.y' => 101, 'a' => 'A')));
    }

    public function testButtonTag()
    {
        $form   = new SimpleForm(new SimpleFormTag(array()), $this->page('http://host'));
        $widget = new SimpleButtonTag(
                array('type' => 'submit', 'name' => 'go', 'value' => 'Go', 'id' => '9'));
        $widget->addContent('Go!');
        $form->addWidget($widget);
        $this->assertTrue($form->hasSubmit(new SelectByName('go')));
        $this->assertTrue($form->hasSubmit(new SelectByLabel('Go!')));
        $this->assertEqual(
                $form->submitButton(new SelectByName('go')),
                new SimpleGetEncoding(array('go' => 'Go')));
        $this->assertEqual(
                $form->submitButton(new SelectByLabel('Go!')),
                new SimpleGetEncoding(array('go' => 'Go')));
        $this->assertEqual(
                $form->submitButton(new SelectById(9)),
                new SimpleGetEncoding(array('go' => 'Go')));
    }

    public function testMultipleFieldsWithSameNameSubmitted()
    {
        $form  = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $input = new SimpleTextTag(array('name' => 'elements[]', 'value' => '1'));
        $form->addWidget($input);
        $input = new SimpleTextTag(array('name' => 'elements[]', 'value' => '2'));
        $form->addWidget($input);
        $form->setField(new SelectByLabelOrName('elements[]'), '3', 1);
        $form->setField(new SelectByLabelOrName('elements[]'), '4', 2);
        $submit   = $form->submit();
        $requests = $submit->getAll();
        $this->assertEqual(count($requests), 2);
        $this->assertIdentical($requests[0], new SimpleEncodedPair('elements[]', '3'));
        $this->assertIdentical($requests[1], new SimpleEncodedPair('elements[]', '4'));
    }

    public function testSingleSelectFieldSubmitted()
    {
        $form   = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $select = new SimpleSelectionTag(array('name' => 'a'));
        $select->addTag(new SimpleOptionTag(
                array('value' => 'aaa', 'selected' => '')));
        $form->addWidget($select);
        $this->assertIdentical(
                $form->submit(),
                new SimpleGetEncoding(array('a' => 'aaa')));
    }

    public function testSingleSelectFieldSubmittedWithPost()
    {
        $form   = new SimpleForm(new SimpleFormTag(array('method' => 'post')), $this->page('htp://host'));
        $select = new SimpleSelectionTag(array('name'             => 'a'));
        $select->addTag(new SimpleOptionTag(
                array('value' => 'aaa', 'selected' => '')));
        $form->addWidget($select);
        $this->assertIdentical(
                $form->submit(),
                new SimplePostEncoding(array('a' => 'aaa')));
    }

    public function testUnchecked()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                array('name' => 'me', 'type' => 'checkbox')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), false);
        $this->assertTrue($form->setField(new SelectByName('me'), 'on'));
        $this->assertEqual($form->getValue(new SelectByName('me')), 'on');
        $this->assertFalse($form->setField(new SelectByName('me'), 'other'));
        $this->assertEqual($form->getValue(new SelectByName('me')), 'on');
    }

    public function testChecked()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                array('name' => 'me', 'value' => 'a', 'type' => 'checkbox', 'checked' => '')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'a');
        $this->assertTrue($form->setField(new SelectByName('me'), 'a'));
        $this->assertEqual($form->getValue(new SelectByName('me')), 'a');
        $this->assertTrue($form->setField(new SelectByName('me'), false));
        $this->assertEqual($form->getValue(new SelectByName('me')), false);
    }

    public function testSingleUncheckedRadioButton()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'a', 'type' => 'radio')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), false);
        $this->assertTrue($form->setField(new SelectByName('me'), 'a'));
        $this->assertEqual($form->getValue(new SelectByName('me')), 'a');
    }

    public function testSingleCheckedRadioButton()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'a', 'type' => 'radio', 'checked' => '')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'a');
        $this->assertFalse($form->setField(new SelectByName('me'), 'other'));
    }

    public function testUncheckedRadioButtons()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'a', 'type' => 'radio')));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'b', 'type' => 'radio')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), false);
        $this->assertTrue($form->setField(new SelectByName('me'), 'a'));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'a');
        $this->assertTrue($form->setField(new SelectByName('me'), 'b'));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'b');
        $this->assertFalse($form->setField(new SelectByName('me'), 'c'));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'b');
    }

    public function testCheckedRadioButtons()
    {
        $form = new SimpleForm(new SimpleFormTag(array()), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'a', 'type' => 'radio')));
        $form->addWidget(new SimpleRadioButtonTag(
                array('name' => 'me', 'value' => 'b', 'type' => 'radio', 'checked' => '')));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'b');
        $this->assertTrue($form->setField(new SelectByName('me'), 'a'));
        $this->assertIdentical($form->getValue(new SelectByName('me')), 'a');
    }

    public function testMultipleFieldsWithSameKey()
    {
        $form = new SimpleForm(new SimpleFormTag(array()),  $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                array('name' => 'a', 'type' => 'checkbox', 'value' => 'me')));
        $form->addWidget(new SimpleCheckboxTag(
                array('name' => 'a', 'type' => 'checkbox', 'value' => 'you')));
        $this->assertIdentical($form->getValue(new SelectByName('a')), false);
        $this->assertTrue($form->setField(new SelectByName('a'), 'me'));
        $this->assertIdentical($form->getValue(new SelectByName('a')), 'me');
    }

    public function testRemoveGetParamsFromAction()
    {
        Mock::generatePartial('SimplePage', 'MockPartialSimplePage', array('getUrl'));
        $page = new MockPartialSimplePage();
        $page->returns('getUrl', new SimpleUrl('htp://host/'));

        # Keep GET params in "action", if the form has no widgets
        $form = new SimpleForm(new SimpleFormTag(array('action'=> '?test=1')), $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(array('action'=> '?test=1')),  $page);
        $form->addWidget(new SimpleTextTag(array('name'        => 'me', 'type' => 'text', 'value' => 'a')));
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(array('action'=> '')),  $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(array('action'=> '?test=1', 'method'=>'post')),  $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/?test=1');
    }
}
