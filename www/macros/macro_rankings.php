<?php

// Displays rankings for one or more rounds.
//
// Arguments:
//     rounds   (required) a | (pipe) separated list of round_id : round_name.
//              Round name is the name which will appear in the column dedicated
//              to that round in case detail_round == true
//              If detail_round == false you can leave just the round_id (see examples)
//     detail_task   (optional) true/false print score columns for each task
//     detail_round  (optional) true/false print score columns for each round
function macro_rankings($args) {
  $args = new MacroArgs($args);
  $roundDescription = $args->get('rounds');
  if (!$roundDescription) {
    return macro_error("Parameter 'rounds' is required.");
  }

  $params = new RankingsParams();
  $params->roundDescription = $roundDescription;
  $params->detailRound = $args->getBool('detail_round');
  $params->detailTask = $args->getBool('detail_task');
  $params->showPagination = $args->getBool('pagination');

  $rankings = new Rankings($params);
  $rankings->run();

  Smart::assign([
    'params' => $params,
    'rankings' => $rankings,
  ]);
  return Smart::fetch('macro/rankings.tpl');
}
