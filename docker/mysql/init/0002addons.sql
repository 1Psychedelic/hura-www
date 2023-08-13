create table vcd_event_addon
(
	id int(8) auto_increment,
	event int(8) unsigned not null,
	name varchar(128) not null,
	price int(5) default 0 not null,
	enabled int(1) default 1 not null,
	position int(2) default 0 not null,
	description text not null,
	icon varchar(255) not null,
	link_url varchar(255) default NULL null,
	link_text varchar(255) default NULL null,
	constraint vcd_event_addon_pk
		primary key (id),
	constraint vcd_event_addon_vcd_event_id_fk
		foreign key (event) references vcd_event (id)
			on update cascade on delete cascade
);

create index vcd_event_addon_enabled_index
	on vcd_event_addon (enabled);

create index vcd_event_addon_position_index
	on vcd_event_addon (position);



create table vcd_application_addon
(
	id int(8) auto_increment,
	addon int(8) not null,
	application int(8) unsigned not null,
	amount int(4) default 0 not null,
	constraint vcd_application_addon_pk
		primary key (id),
	constraint vcd_application_addon_vcd_application_id_fk
		foreign key (application) references vcd_application (id)
			on update cascade on delete cascade,
	constraint vcd_application_addon_vcd_event_addon_id_fk
		foreign key (addon) references vcd_event_addon (id)
);


alter table vcd_application_addon
	add price int(5) not null;

alter table vcd_application
	add notes text null;
