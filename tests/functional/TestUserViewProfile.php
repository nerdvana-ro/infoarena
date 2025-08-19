<?php

class TestUserViewProfile extends FunctionalTest {

  function run(): void {
    $this->login('normal', '1234');
    $this->visitUserProfile('admin');
    $this->assertTextExists('This is revision 5 of template/newuser.');
    $this->assertTextExists('Admin Admin (admin)');

    $this->clickLinkByText('Rating');
    $this->assertTextExists('Concursuri cu rating la care a participat');

    $this->clickLinkByText('Statistici');
    $this->assertTextExists('Probleme din arhivă rezolvate (0)');
  }
}
