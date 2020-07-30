drop view v_exapply_05_detail_ro;
create view v_exapply_05_detail_ro as
select `exhibitor_apply`.`appid`      AS `appid`,
       `exhibitor_apply`.`exhboothid` AS `exhboothid`,
       `exhibitor_apply`.`appno`      AS `appno`,
       `exhibitor_apply`.`seqno`      AS `seqno`,
       `exhibitor_apply`.`e01`        AS `itemcode`,
       `exhibitor_apply`.`e02`        AS `itemname`,
       (case
            when (`exhibitor_apply`.`e01` = 1) then 100
            when (`exhibitor_apply`.`e01` = 3) then 8000
            when (`exhibitor_apply`.`e01` = 4) then 3000
            when (`exhibitor_apply`.`e01` = 5) then 2000
            else 0 end)                         AS `price`,
       `exhibitor_apply`.`e06`        AS `quantity`,
       `exhibitor_apply`.`e08`        AS `addquantity`,
       `exhibitor_apply`.`e29`        AS `sent`,
       `exhibitor_apply`.`e30`        AS `sentdate`,
       `exhibitor_apply`.`token`      AS `token`,
       `exhibitor_apply`.`expired`    AS `expired`,
       `exhibitor_apply`.`created`    AS `created`,
       `exhibitor_apply`.`updated`    AS `updated`,
       `exhibitor_apply`.`deleted`    AS `deleted`
from `exhibitor_apply`
where ((`exhibitor_apply`.`appno` = 5) and (`exhibitor_apply`.`seqno` <> 0) and
       (`exhibitor_apply`.`expired` = 0));

