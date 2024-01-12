<?php

function get_bool_arg(array $args, string $key, bool $default = false): bool {
  return Util::toBool($args[$key] ?? $default);
}

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
  $roundDescription = $args['rounds'] ?? null;
  if (!$roundDescription) {
    return macro_error("Parameter 'rounds' is required.");
  }

  $params = new RankingsParams();
  $params->roundDescription = $roundDescription;
  $params->detailRound = get_bool_arg($args, 'detail_round');
  $params->detailTask = get_bool_arg($args, 'detail_task');
  $params->showPagination = get_bool_arg($args, 'pagination');
  $params->pageNo = 1;
  $params->pageSize = Config::PAGE_SIZE;
  $params->sortField = 'total';
  $params->sortAsc = false;

  $rankings = new Rankings($params);
  $html = $rankings->getHtml();

  Smart::assign([
    'params' => $params,
    'rankingsHtml' => $html,
  ]);
  return Smart::fetch('macro/rankings.tpl');
}
