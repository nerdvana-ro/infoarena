<?php

require_once Config::ROOT . 'common/db/round.php';

/**
 * This class loads pages of rankings for one or more rounds and returns
 * HTML-formatted data. Used once when loading the page, then via Ajax for
 * subsequent page loads.
 **/

class Rankings {

  const COMPARATORS = [
    'rank' => 'cmpRank',
    'total' => 'cmpTotal',
    'username' => 'cmpUsername',
  ];

  private RankingsParams $params;
  private array $roundMap;
  private ?string $roundId;
  private array $totals;
  private array $roundScoreMap;
  private array $taskScoreMap;
  private array $columns;
  private ORMWrapper $query;
  private int $numResults;
  private array $rows;

  function __construct(RankingsParams $params) {
    $this->params = $params;
    $this->parseRoundDescription();
    $this->makeColumns();
    $this->loadRoundScores();
    $this->computeTotals();
    $this->loadTaskScores();
    $this->makeRows();
    $this->sortRows();
    $this->shrinkTotalsToPage();
  }

  // Parses a string of the form
  //
  //   S -> roundDef [ | roundDef ]...
  //   roundDef -> round ID [ : round name]
  private function parseRoundDescription(): void {
    $this->roundMap = [];

    $idNamePairs = explode('|', $this->params->roundDescription);
    foreach ($idNamePairs as $idNamePair) {
      $parts = explode(':', $idNamePair, 2);
      $roundId = trim($parts[0]);
      $roundName = trim($parts[1] ?? '');
      $round = round_get($roundId);

      if (Identity::mayViewRoundScores($round)) {
        $this->roundMap[] = [
          'roundId' => $roundId,
          'roundName' => $roundName,
        ];
      }
    }

    $this->roundId = (count($this->roundMap) == 1)
      ? $this->roundMap[0]['roundId']
      : null;
  }

  // Creates an array of [ 'roundId', ['taskId',] 'displayValue' ].
  private function makeColumns(): void {
    $this->columns = [];

    foreach ($this->roundMap as $r) {
      if ($this->params->detailTask) {
        $tasks = Task::loadByRoundId($r['roundId']);
        foreach ($tasks as $t) {
          $this->columns[] = [
            'roundId' => $r['roundId'],
            'taskId' => $t->id,
            'displayValue' => $t->title,
          ];
        }
      }

      if ($this->params->detailRound) {
        $this->columns[] = [
          'roundId' => $r['roundId'],
          'displayValue' => $r['roundName'],
        ];
      }
    }
  }

  private function loadRoundScores(): void {
    $roundIds = array_column($this->roundMap, 'roundId');
    $this->roundScoreMap = ScoreUserRound::loadByRoundIds($roundIds);
  }

  private function computeTotals(): void {
    $this->totals = [];
    foreach ($this->roundScoreMap as $userId => $roundScores) {
      foreach ($roundScores as $score) {
        $oldTotal = $this->totals[$userId] ?? 0;
        $this->totals[$userId] = $oldTotal + $score;
      }
    }
    arsort($this->totals);
    $this->numResults = count($this->totals);
  }

  private function loadTaskScores(): void {
    if ($this->params->detailTask) {
      $roundIds = array_column($this->roundMap, 'roundId');
      $userIds = array_keys($this->totals);
      $this->taskScoreMap = ScoreUserRoundTask::loadByRoundIdsUserIds($roundIds, $userIds);
    }
  }

  private function makeRows(): void {
    $userIds = array_keys($this->totals);
    $userMap = User::loadAndMapById($userIds);

    $this->rows = [];
    $rank = 1;
    foreach ($this->totals as $userId => $total) {
      $this->rows[] = new RankingsRow(
        $rank++,
        $userMap[$userId],
        $this->collectScores($userId),
        $total
      );
    }
  }

  private function collectScores(int $userId): array {
    $scores = [];
    foreach ($this->columns as $col) {
      $scores[] = isset($col['taskId'])
        ? ($this->taskScoreMap[$userId][$col['roundId']][$col['taskId']] ?? null)
        : ($this->roundScoreMap[$userId][$col['roundId']] ?? null);
    }
    return $scores;
  }

  private static function cmpRank(RankingsRow $a, RankingsRow $b): int {
    return $a->rank <=> $b->rank;
  }

  private static function cmpTotal(RankingsRow $a, RankingsRow $b): int {
    return $b->rank <=> $a->rank;
  }

  private static function cmpUsername(RankingsRow $a, RankingsRow $b): int {
    return $a->user->username <=> $b->user->username;
  }

  private static function cmpCol(int $col) {
    return function(RankingsRow $a, RankingsRow $b) use ($col) {
      return $a->scores[$col] <=> $b->scores[$col];
    };
  }

  private function sortRows(): void {
    $field = $this->params->sortField;
    $cmp = self::COMPARATORS[$field] ?? null;

    if ($cmp) {
      usort($this->rows, 'Rankings::' . $cmp);
    } else if (Str::startsWith($field, 'col-')) {
      $col = substr($field, 4);
      usort($this->rows, Rankings::cmpCol($col));
    }

    if (!$this->params->sortAsc) {
      $this->rows = array_reverse($this->rows);
    }
  }

  private function shrinkTotalsToPage(): void {
    if ($this->params->showPagination) {
      $this->rows = array_slice(
        $this->rows,
        ($this->params->pageNo - 1) * $this->params->pageSize,
        $this->params->pageSize,
        true);
    }
  }

  function getPageCount(): int {
    if ($this->params->showPagination) {
      return ceil($this->numResults / $this->params->pageSize);
    } else {
      return 1;
    }
  }

  function getFirstResult() {
    return ($this->params->pageNo - 1) * $this->params->pageSize + 1;
  }

  function getLastResult() {
    return $this->getFirstResult() + count($this->rows) - 1;
  }

  function getHtml(): string {
    Smart::assign([
      'columns' => $this->columns,
      'firstResult' => $this->getFirstResult(),
      'lastResult' => $this->getLastResult(),
      'numPages' => $this->getPageCount(),
      'numResults' => $this->numResults,
      'params' => $this->params,
      'roundId' => $this->roundId,
      'rows' => $this->rows,
    ]);
    return Smart::fetch('bits/rankings.tpl');
  }
}
