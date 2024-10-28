select t.meta_value as uid, GROUP_CONCAT(t.user_id) as owners, sum(t1.meta_value) as money_spent from wp_usermeta as t left join wp_usermeta as t1 on t1.user_id = t.user_id and t1.meta_key='_money_spent' where t.meta_key='acm_assigned_manager' group by t.meta_value order by money_spent desc


select t.meta_value as uid, sum(t1.meta_value) as money_spent from wp_usermeta as t left join wp_usermeta as t1 on t1.user_id = t.user_id and t1.meta_key='_money_spent' where t.meta_key='acm_assigned_manager' group by t.meta_value order by money_spent desc


select u.ID, sum(t1.meta_value) as money_spent from wp_users as u left join wp_usermeta as t on t.meta_value = u.ID left join wp_usermeta as t1 on t1.user_id = t.user_id and t1.meta_key='_money_spent' where t.meta_key='acm_assigned_manager' group by t.meta_value order by money_spent desc