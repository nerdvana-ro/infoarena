<?php

class TestRoundSubmit extends FunctionalTest {

  function run(): void {
    $this->testCanOnlySubmitToRunningRound();
  }

  private function testCanOnlySubmitToRunningRound(): void {
    $this->login('admin', '1234');
    $this->visitTaskPage('task1'); // which is part of two running rounds
    $this->assertSelectNumOptions('#form_round', 3);
    $this->assertSelectVisibleText('#form_round', '[ Alegeți runda ]');
  }

}
