alter table ia_task_ratings
  drop primary key;

alter table ia_task_ratings
  add id int not null auto_increment first,
  add primary key(id),
  add key(task_id);
