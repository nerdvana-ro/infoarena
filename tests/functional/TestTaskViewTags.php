<?php

class TestTaskViewTags extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewPublic();
  }

  private function testAnonCanViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitTaskPage('task1');
    $this->clickLinkByText('Arată 2 categorii');
    // FIXME: Ugly.
    $this->waitForElementByCss('ul.hidden[style="display: block;"]');
    $elems = $this->getElementsByXpath('//a[normalize-space()="1 etichete"]');
    foreach ($elems as $elem) {
      $elem->click();
    }

    $this->assertTextExists('category1');
    $this->assertTextExists('category2');
    $this->assertTextExists('tag1');
    $this->assertTextExists('tag3');
  }

}
