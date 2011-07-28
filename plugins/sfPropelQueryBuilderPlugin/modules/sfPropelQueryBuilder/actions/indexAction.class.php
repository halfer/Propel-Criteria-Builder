<?php
class indexAction extends sfAction
{
  /**
   * Executes index action
   *
   */
  public function execute()
  {
    $this->input = "person.gender = 'M' AND (person.location IN ('Birmingham', 'Coventry') OR person.location = 'Manchester') AND (person.enabled <> 0) AND person.age > 16";
    $this->code = $this->tree = null;
    return sfView::SUCCESS;
  }
}
