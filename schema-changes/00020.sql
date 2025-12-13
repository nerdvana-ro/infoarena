alter table ia_job
  add max_time int after score,
  add max_memory int after max_time;

update ia_job
  set max_time = (select max(exec_time) from ia_job_test where job_id = ia_job.id);

update ia_job
  set max_memory = (select max(mem_used) from ia_job_test where job_id = ia_job.id);

drop table ia_score_task_top_users;
