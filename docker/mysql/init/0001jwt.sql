create table system_user_session
(
	id int auto_increment,
	user int(8) not null,
	enabled int(1) default 1 not null,
	created_at datetime default NOW() not null,
	last_seen datetime default NOW() not null,
	ip varchar(50) not null,
	device_description varchar(255) not null,
	constraint system_user_session_pk
		primary key (id),
	constraint system_user_session_system_user_id_fk
		foreign key (user) references system_user (id)
			on update cascade on delete cascade
);
