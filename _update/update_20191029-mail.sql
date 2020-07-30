create table members_bk20191029 like members;
insert into members_bk20191029 select * from members;
update members set activate = 0 where memberid >= 'M0002020002';
