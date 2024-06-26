<?php

class Attachment extends Base {

  public static $_table = 'ia_file';

  static function getDirectory(): string {
    return Config::TESTING_MODE
      ? '/tmp/attach-test/'
      : Config::ROOT . 'attach/';
  }

  function getFileName(): string {
    return attachment_get_filepath($this->as_array());
  }

  static function normalizeAndGetByNamePage(string $name, string $page): ?Attachment {
    $page = normalize_page_name($page);

    return Attachment::get_by_name_page($name, $page) ?: null;
  }

  private function isTaskGrader(): bool {
    return
      Str::startsWith($this->name, 'grader_') &&
      $this->belongsToTask();
  }

  private function belongsToRound(): bool {
    return Str::isRoundPage($this->page);
  }

  private function belongsToTask(): bool {
    return Str::isTaskPage($this->page);
  }

  private function belongsToUser(): bool {
    return Str::isUserPage($this->page);
  }

  private function getSubject(): string {
    $parts = explode('/', $this->page);
    return $parts[1] ?? '';
  }

  private function getRound(): array {
    $roundId = $this->getSubject();
    return round_get($roundId) ?? [];
  }

  private function getTask(): ?Task {
    $taskId = $this->getSubject();
    return Task::get_by_id($taskId) ?: null;
  }

  function getUser(): ?User {
    $username = $this->getSubject();
    return User::get_by_username($username) ?: null;
  }

  function getGalleryThumbUrl(): string {
    return sprintf('%s/resize/%s/%s/gallery',
                   Config::URL_PREFIX, $this->page, $this->name);
  }

  function getUrl(): string {
    return sprintf('%s/download/%s/%s',
                   Config::URL_PREFIX, $this->page, $this->name);
  }

  private function getTextblock(): Textblock {
    return Textblock::get_by_name($this->page);
  }

  function isImage(): bool {
    return Image::isImage($this->getFileName());
  }

  function isViewable(): bool {
    if ($this->belongsToUser()) { // in particular, avatar images
      return true;
    }

    if ($this->belongsToRound()) {
      return true;
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return ($this->isTaskGrader())
        ? $task->areGraderAttachmentsViewable()
        : $task->isViewable();
    }

    // Otherwise it belongs to a textblock.
    $tb = $this->getTextblock();
    return
      !$tb->isPrivate() ||
      Identity::isAdmin();
  }

  function isEditableIrreversibly(): bool {
    if ($this->belongsToUser())  {
      $user = $this->getUser();
      return $user && $user->isEditable();
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return $task && Identity::ownsTask($task);
    }

    if ($this->belongsToRound()) {
      return
        Identity::isAdmin() ||
        Identity::isIntern();
    }

    return Identity::isAdmin();
  }

  static function deleteById($id): void {
    $att = Attachment::get_by_id($id);
    if (!$att) {
      FlashMessage::addError('Fișier inexistent.');
      Util::redirectToHome();
    }
    $att->delete();
  }

  function delete(): void {
    attachment_delete_by_id($this->id);
  }

}
