alter table invoice_detail add column tax_exclude_flag int not null default '0' after amount;

update invoice_detail set tax_exclude_flag = 1 where itemdetail like '出展料%';
