<?php

class TestMonitor extends FunctionalTest {

  function run(): void {
    $this->testAnonSeesAllJobs();
  }

  private function testAnonSeesAllJobs(): void {
    $this->ensureLoggedOut();
    $this->visitHomePage();
    $this->clickLinkByText('Monitorul de evaluare');
    $numJobs = $this->countElementsByCss('table.monitor tbody tr');
    $msg = sprintf('Expected 14 jobs, found %d instead.', $numJobs);
    $this->assert($numJobs == 14, $msg);
  }

}
