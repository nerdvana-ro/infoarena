<?php

class TestUserViewProfile extends FunctionalTest {

  function run(): void {
    $this->login('normal', '1234');
    $this->visitUserProfile('admin');
    $this->assertTextExists(
      'This is the userheader template. This is revision 5.');
    $this->assertTextExists(
      'My username is admin. Here is something else about myself. This is revision 5.');

    $this->clickLinkByText('Rating');
    $this->assertTextExists(
      'This is the userrating template. This is revision 5.');

    $this->clickLinkByText('Statistici');
    $this->assertTextExists(
      'This is the userstats template. This is revision 5.');
  }
}
